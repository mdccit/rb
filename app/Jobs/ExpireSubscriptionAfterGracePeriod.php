<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\Subscription\SubscriptionExpiredEmail;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptionAfterGracePeriod implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriptionId;
    protected $user;
    protected $last_billing_date;

    public function __construct($subscriptionId, $user, $last_billing_date)
    {
        $this->subscriptionId = $subscriptionId;
        $this->user = $user;
        $this->last_billing_date = $last_billing_date;
    }

    public function handle()
    {
        // Retrieve the subscription
        $subscription = Subscription::find($this->subscriptionId);

        if ($subscription && $subscription->status === 'grace' && Carbon::now()->gte($subscription->grace_period_end_date)) {
            // Grace period has ended, mark the subscription as expired
            $subscription->status = 'expired';
            $subscription->save();

            // Send expiration email
            $this->user->notify(new SubscriptionExpiredEmail(
                $this->user,
                $subscription->grace_period_end_date,
                $this->last_billing_date,
                0  // Since this is post-expiry, the amount paid can be 0 or retrieved from the last invoice
            ));

            // Log the expiration
            Log::info('Subscription expired for user: ' . $this->user->id . ' after grace period.');
        }
    }
}
