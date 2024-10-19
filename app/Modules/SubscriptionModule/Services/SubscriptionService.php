<?php

namespace App\Modules\SubscriptionModule\Services;

use App\Models\Subscription;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Carbon\Carbon;
use App\Extra\ThirdPartyAPI\StripeAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Notifications\Subscription\SubscriptionCancelEmail;
use App\Notifications\Subscription\SubscriptionRenewedEmail;
use Illuminate\Support\Facades\Log;

use Stripe\Subscription as StripeSubscription;

class SubscriptionService
{
    protected $stripeAPI;


    public function __construct(StripeAPI $stripeAPI)
    {
        $this->stripeAPI = $stripeAPI;
    }

    // Get the current user's subscription
    public function getUserSubscription($user)
    {
        // Ensure the user has a Stripe customer ID
        if (!$user->stripe_id) {
            throw new \Exception('User does not have a Stripe customer ID.');
        }

        // Retrieve active subscription for the customer from Stripe
        $stripeSubscriptions = $this->stripeAPI->getCustomerActiveSubscriptions($user->stripe_id);

        // Ensure there is at least one active subscription
        if (empty($stripeSubscriptions->data)) {
            throw new \Exception('No active subscription found for this customer on Stripe.');
        }

        // Get the first active subscription (assuming the user only has one active subscription)
        $activeSubscription = $stripeSubscriptions->data[0];
        

        // Check if the subscription contains items and retrieve the price
        if (!empty($activeSubscription->items->data) && isset($activeSubscription->items->data[0]->price)) {
            $priceObject = $activeSubscription->items->data[0]->price;
            $is_cancel_at_period_end = false;

            $stripeSubscription = StripeSubscription::retrieve($activeSubscription->id);
            if ($stripeSubscription->cancel_at_period_end) {

                $is_cancel_at_period_end = true;
            }

            // Prepare subscription data with price and currency
            $subscription = [
                'stripe_subscription_id' => $activeSubscription->id,
                'start_date' => Carbon::createFromTimestamp($activeSubscription->current_period_start),
                'end_date' => Carbon::createFromTimestamp($activeSubscription->current_period_end),
                'status' => $activeSubscription->status,
                'price' => isset($priceObject->unit_amount) ? $priceObject->unit_amount / 100 : null,  // Convert to dollars
                'currency' => isset($priceObject->currency) ? strtoupper($priceObject->currency) : null,
                'cancel_at_period_end' => $is_cancel_at_period_end,
            ];

            return $subscription;
        } else {
            throw new \Exception('No price found for this subscription on Stripe.');
        }
    }


    public function createSubscription($data, $user)
    {
        // Check if the user already has an active subscription
        if ($user->subscription) {
            throw new \Exception('User already has an active subscription.');
        }

        // Create Stripe customer if the user doesn't already have one
        if (!$user->stripe_id) {
            $stripeCustomerId = $this->stripeAPI->createCustomer($user);
            $user->stripe_id = $stripeCustomerId;
            $user->save(); // Save the stripe_id to the user
        } else {
            $stripeCustomerId = $user->stripe_id;
        }


        // If payment method is already confirmed, proceed with subscription
        $paymentMethodId = $data['payment_method_id'];
        $subscriptionType = $data['subscription_type'];
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $stripeCustomerId]);

        // Set the default payment method for the customer
        Customer::update($stripeCustomerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
        
        // Check if it's a trial subscription
        if ($data['subscription_type'] === 'trial') {
            // Create a Stripe subscription with a 1-month trial period
            $stripeSubscription = $this->stripeAPI->createSubscriptionWithTrial($stripeCustomerId, $subscriptionType, 30); // 30 days trial

            // Save trial details
            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->subscription_type = 'trial'; // Setting the enum value
            $userSubscription->status = 'trial'; // The status is 'trial' during the trial period
            $userSubscription->start_date = Carbon::now();
            $userSubscription->end_date = Carbon::now()->addMonth(); // 1-month trial
            $userSubscription->save();

            return $userSubscription;
        } else {
            // If it's a paid subscription (monthly or annually), create the subscription using the payment method
            $stripeSubscription = $this->stripeAPI->createSubscription($stripeCustomerId, $subscriptionType, $paymentMethodId);

            if ($stripeSubscription) {
                $amount = $stripeSubscription->amount;
                $currency = strtoupper($stripeSubscription->currency);
            }

            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->subscription_type = 'monthly'; // 'monthly' or 'annually'
            $userSubscription->status = 'active'; // Set status to active for paid subscriptions
            $userSubscription->start_date = Carbon::now();
            $userSubscription->end_date = $data['subscription_type'] === 'monthly' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
            $userSubscription->save();


            return $userSubscription;
        }
    }


    // Cancel the current subscription of the user
    public function cancelSubscription($user)
    {
        $stripeCustomerId = $user->stripe_id;

        Log::info('Cancelling Subscription');
        // Check if the user has a Stripe customer ID
        if (!$stripeCustomerId) {
            throw new \Exception('No Stripe customer ID found.');
        }

        // Retrieve the active subscription for the customer from Stripe
        try {
            // Fetch all active subscriptions for the customer from Stripe
            $subscriptions = StripeSubscription::all([
                'customer' => $stripeCustomerId,
                'status' => 'active',
            ]);

            // Ensure there is at least one active subscription
            if (empty($subscriptions->data)) {
                throw new \Exception('No active subscription found for this customer on Stripe.');
            }

            // Get the first active subscription (assuming the user has only one active subscription)
            $activeSubscription = $subscriptions->data[0];

            // Cancel the subscription on Stripe (you can add options here like `cancel_at_period_end`)
            $stripeSubscription = StripeSubscription::retrieve($activeSubscription->id);
            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            // Now update the local database
            $subscription = Subscription::where('user_id', $user->id)->first();

            $user->notify(new SubscriptionCancelEmail($user, $subscription, $subscription->end_date));

            // Ensure the local subscription exists
            if (!$subscription) {
                throw new \Exception('No active subscription found in the local database.');
            }

            // Mark the subscription as canceled or inactive in the local database
            $subscription->status = 'cancelled';  // or 'cancelled'
            $subscription->save();

        } catch (\Exception $e) {
            // Throw an exception with more context if cancellation fails
            throw new \Exception('Failed to cancel subscription: ' . $e->getMessage());
        }
    }


    // Renew the current subscription if it's active and supports auto-renewal
    public function renewSubscription($user)
    {
        // Retrieve the user's current subscription from the local database
        $subscription = $user->subscription;
    
        if (!$subscription || !$subscription->stripe_subscription_id) {
            throw new \Exception('No active subscription found to renew.');
        }
    
        try {
            // Retrieve the subscription from Stripe using its Stripe ID
            $stripeSubscription = Subscription::retrieve($subscription->stripe_subscription_id);
    
            // Check if the subscription is set to cancel at the end of the period
            if ($stripeSubscription->cancel_at_period_end) {
                // Reactivate the subscription by setting cancel_at_period_end to false
                $stripeSubscription->cancel_at_period_end = false;
                $stripeSubscription->save();
    
                // Optionally update the local subscription status if necessary
                $subscription->status = 'active'; // Ensure the status is active
                $subscription->save();
            }
    
            // Retrieve required details to pass to the email
            $plan_name = $stripeSubscription->items->data[0]->plan->nickname; // Plan name
            $renewal_date = Carbon::createFromTimestamp($stripeSubscription->current_period_start); // Renewal date (current period start)
            $next_billing_date = Carbon::createFromTimestamp($stripeSubscription->current_period_end); // Next billing date (current period end)
            $amount_charged = $stripeSubscription->items->data[0]->plan->amount / 100; // Amount charged in dollars (Stripe stores amounts in cents)
    
            // Send renewal email to the user with the details
            $user->notify(new SubscriptionRenewedEmail($user, $plan_name, $renewal_date, $next_billing_date, $amount_charged));
    
            // Optionally, return success response if everything went well
            return $subscription;
    
        } catch (\Exception $e) {
            throw new \Exception('Failed to renew subscription: ' . $e->getMessage());
        }
    }
    
    
    // Retrieve a subscription by user ID
    public function getSubscriptionByUserId($userId)
    {
        $subscription = Subscription::where('user_id', $userId)->first();

        if (!$subscription) {
            throw new \Exception('No subscription found for the given user ID.');
        }

        return $subscription;
    }

    // Retrieve all subscriptions
    public function getAllSubscriptions()
    {
        $subscriptions = Subscription::with('user')->get();

        return $subscriptions;
    }


    private function getPriceIdFromSubscriptionType($subscriptionType)
    {
        // Retrieve the price IDs from the config/services.php file
        $priceIds = [
            'trial' => config('services.stripe.monthly_price_id'),
            'monthly' => config('services.stripe.monthly_price_id'), // Retrieve monthly price ID from config
            'annually' => config('services.stripe.annual_price_id'), // Retrieve annual price ID from config
        ];

        return $priceIds[$subscriptionType] ?? null;
    }



    public function removePaymentMethod($user, $paymentMethodId)
    {
        // Check if the user has a Stripe customer ID
        $stripeCustomerId = $user->stripe_id;
        if (!$stripeCustomerId) {
            throw new \Exception('No Stripe customer ID found.');
        }

        // Retrieve active subscriptions for the customer from Stripe
        $subscriptions = StripeSubscription::all([
            'customer' => $stripeCustomerId,
            'status' => 'active',
        ]);

        // Check if the payment method is being used in any active subscription
        foreach ($subscriptions->data as $subscription) {
            if ($subscription->default_payment_method === $paymentMethodId) {
                throw new \Exception('Cannot remove payment method as it is attached to an active subscription.');
            }
        }

        // Call the StripeAPI to remove the payment method
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->detach();
        } catch (\Exception $e) {
            throw new \Exception('Failed to remove payment method: ' . $e->getMessage());
        }
    }


    public function createRecurringSubscription($user, $data)
    {
        $stripeCustomerId = $user->stripe_id;

        if (!$stripeCustomerId) {
            // Create customer if not exists
            $stripeCustomerId = $this->stripeAPI->createCustomer($user);
            $user->stripe_id = $stripeCustomerId;
            $user->save();
        }

        // Attach Payment Method
        $paymentMethodId = $data['payment_method_id'];
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $stripeCustomerId]);

        // Set default payment method for future invoices
        Customer::update($stripeCustomerId, [
            'invoice_settings' => ['default_payment_method' => $paymentMethodId],
        ]);

        // Create Stripe subscription
        $stripeSubscription = $this->stripeAPI->createSubscription($stripeCustomerId, $data['plan_id'], $paymentMethodId);

        // Save the subscription to the local database
        $userSubscription = new Subscription();
        $userSubscription->user_id = $user->id;
        $userSubscription->stripe_subscription_id = $stripeSubscription->id;
        $userSubscription->status = 'active';
        $userSubscription->start_date = Carbon::now();
        $userSubscription->end_date = $this->calculateEndDate($data['plan_id']); // Calculate based on the plan
        $userSubscription->save();

        return $userSubscription;
    }


    public function cancelRecurringSubscription($user)
    {
        $stripeSubscriptionId = $user->subscription->stripe_subscription_id;

        // Cancel subscription in Stripe
        $this->stripeAPI->cancelRecurringSubscription($stripeSubscriptionId);

        // Update local subscription status
        $userSubscription = $user->subscription;
        $userSubscription->status = 'inactive';
        $userSubscription->save();
    }

    public function calculateEndDate($planId)
    {
        // Fetch the monthly and annual price IDs from the configuration
        $monthlyPriceId = Config::get('services.stripe.monthly_price_id');
        $annualPriceId = Config::get('services.stripe.annual_price_id');

        // This assumes you have different plans for monthly and yearly
        switch ($planId) {
            case $monthlyPriceId:  // Monthly plan ID
                return Carbon::now()->addMonth();  // For a monthly plan, add 1 month

            case $annualPriceId:  // Annual plan ID
                return Carbon::now()->addYear();  // For a yearly plan, add 1 year

            default:
                throw new \Exception('Unknown plan ID');
        }
    }

    public function getSubscriptionBySubscriptionId($id)
    {
        // Retrieve the subscription with the associated user
        $subscription = Subscription::with('user')->findOrFail($id);

        // Retrieve the subscription from Stripe if available
        if ($subscription->stripe_subscription_id) {
            $stripeSubscription = $this->stripeAPI->retrieveSubscription($subscription->stripe_subscription_id);
            $subscription->stripe_details = $stripeSubscription;

            // Check if the subscription contains items and retrieve the price
            if (!empty($stripeSubscription->items->data) && isset($stripeSubscription->items->data[0]->price)) {
                $priceObject = $stripeSubscription->items->data[0]->price;

                // Get the unit amount and currency
                $subscription->price = isset($priceObject->unit_amount) ? $priceObject->unit_amount / 100 : null;  // Convert to dollars
                $subscription->currency = isset($priceObject->currency) ? strtoupper($priceObject->currency) : null;
            } else {
                throw new \Exception('No price found for this subscription.');
            }
        }

        return $subscription;
    }


}
