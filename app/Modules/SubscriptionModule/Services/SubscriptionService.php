<?php

namespace App\Modules\SubscriptionModule\Services;

use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionService
{
    // Get the current user's subscription
    public function getUserSubscription($user)
    {
        $subscription = $user->subscription;

        if (!$subscription) {
            throw new \Exception('No active subscription found.');
        }

        return $subscription;
    }

    // Create a new subscription for the user
    public function createSubscription($data, $user)
    {
        if ($user->subscription) {
            throw new \Exception('User already has an active subscription.');
        }

        $subscription = new Subscription();
        $subscription->user_id = $user->id;

        if ($data['subscription_type'] === 'trial') {
            $subscription->startTrial();
        } else {
            $subscription->startPaid($data['subscription_type'], $data['auto_renewal'] ?? false);
        }

        return $subscription;
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
}
