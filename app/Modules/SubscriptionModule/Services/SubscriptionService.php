<?php

namespace App\Modules\SubscriptionModule\Services;

use App\Models\Subscription;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Carbon\Carbon;
use App\Extra\ThirdPartyAPI\StripeAPI;
use Illuminate\Http\Request;
use Stripe\Subscription AS StripeSubscription;

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
        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \Exception('No active subscription found.');
        }

        return $subscription;
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
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $stripeCustomerId]);

        // Set the default payment method for the customer
        Customer::update($stripeCustomerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);


        // Determine the Stripe price ID based on subscription type
        $priceId = $this->getPriceIdFromSubscriptionType($data['subscription_type']);

        // Check if it's a trial subscription
        if ($data['subscription_type'] === 'trial') {
            // Create a Stripe subscription with a 1-month trial period
            $stripeSubscription = $this->stripeAPI->createSubscriptionWithTrial($stripeCustomerId, $priceId, 30); // 30 days trial

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
            $stripeSubscription = $this->stripeAPI->createSubscription($stripeCustomerId, $priceId, $paymentMethodId);

            $userSubscription = new Subscription();
            $userSubscription->user_id = $user->id;
            $userSubscription->subscription_type = $data['subscription_type']; // 'monthly' or 'annually'
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
            $stripeSubscription->cancel();  // If you want to cancel at the end of the period, use cancel(['at_period_end' => true])
    
            // Now update the local database
            $subscription = Subscription::where('user_id', $user->id)->first();
    
            // Ensure the local subscription exists
            if (!$subscription) {
                throw new \Exception('No active subscription found in the local database.');
            }
    
            // Mark the subscription as canceled or inactive in the local database
            $subscription->status = 'inactive';  // or 'canceled'
            $subscription->save();
    
        } catch (\Exception $e) {
            // Throw an exception with more context if cancellation fails
            throw new \Exception('Failed to cancel subscription: ' . $e->getMessage());
        }
    }
    

    // Renew the current subscription if it's active and supports auto-renewal
    public function renewSubscription($user)
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->is_auto_renewal) {
            throw new \Exception('No renewable subscription found.');
        }

        $subscription->renewSubscription();
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
        return Subscription::all();
    }


    private function getPriceIdFromSubscriptionType($subscriptionType)
    {
        // Retrieve the price IDs from the config/services.php file
        $priceIds = [
            'trial' => null, // Optional, depending on how you manage trials in Stripe
            'monthly' => config('services.stripe.monthly_price_id'), // Retrieve monthly price ID from config
            'annually' => config('services.stripe.annual_price_id'), // Retrieve annual price ID from config
        ];

        return $priceIds[$subscriptionType] ?? null;
    }

}
