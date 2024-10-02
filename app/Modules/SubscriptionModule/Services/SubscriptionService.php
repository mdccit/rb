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
    //         $subscription->startPaid($data['subscription_type'], $data['auto_renewal'] ?? false);
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
            $customer = $this->stripeAPI->createCustomer($user);
            $user->update(['stripe_id' => $customer->id]);
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
            $userSubscription->end_date = Carbon::now()->addMonth();
            $userSubscription->save();

            return $userSubscription;
        } else {
            // If it's a paid subscription (monthly or annually), create the subscription without trial
            $stripeSubscription = $this->stripeAPI->createSubscription($user->stripe_id, $priceId);

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

        if (!$subscription || !$subscription->auto_renewal) {
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
        // You should have predefined price IDs for each subscription type in Stripe
        $priceIds = [
            'trial' => 'price_trial_id', // Optional, depending on how you manage trials in Stripe
            'monthly' => 'price_1IvZvZH0z7aR4oY8xpHZyQ9N', // Replace with your Stripe monthly plan ID
            'annually' => 'price_1IvZvZH0z7aR4oY8xpHZyX12', // Replace with your Stripe annual plan ID
        ];

        return $priceIds[$subscriptionType] ?? null;
    }
}
