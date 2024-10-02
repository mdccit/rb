<?php

namespace App\Modules\SubscriptionModule\Services;

use App\Models\Subscription;
use Carbon\Carbon;
use App\Extra\ThirdPartyAPI\StripeAPI;

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

    // // Create a new subscription for the user
    // public function createSubscription($data, $user)
    // {
    //     if ($user->subscription) {
    //         throw new \Exception('User already has an active subscription.');
    //     }

    //     $subscription = new Subscription();
    //     $subscription->user_id = $user->id;

    //     if ($data['subscription_type'] === 'trial') {
    //         $subscription->startTrial();
    //     } else {
    //         $subscription->startPaid($data['subscription_type'], $data['is_auto_renewal'] ?? false);
    //     }

    //     return $subscription;
    // }

    public function createSubscription($data, $user)
    {
        // Check if the user already has an active subscription
        if ($user->subscription) {
            throw new \Exception('User already has an active subscription.');
        }
    
        // Create Stripe customer if the user doesn't already have one
        if (!$user->stripe_id) {
            // Create a Stripe customer
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
            ]);
    
            // Update the user with the Stripe customer ID
            $user->stripe_id = $customer->id;
            $user->save();
        }
    
        // Determine the Stripe price ID based on subscription type
        $priceId = $this->getPriceIdFromSubscriptionType($data['subscription_type']);
    
        // Check if it's a trial subscription
        if ($data['subscription_type'] === 'trial') {
            // Create a Stripe subscription with a 1-month trial period
            $stripeSubscription = $this->stripeAPI->createSubscriptionWithTrial($user->stripe_id, $priceId, 30); // 30 days trial
    
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
            // If it's a paid subscription (monthly or annually), create the subscription without trial
            $paymentMethodId = $data['payment_method']; // Get the payment method ID from the request
            $stripeSubscription = $this->stripeAPI->createSubscription($user->stripe_id, $data['subscription_type'], $paymentMethodId);
    
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
        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \Exception('No active subscription found.');
        }

        $subscription->status = 'inactive';
        $subscription->save();
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
