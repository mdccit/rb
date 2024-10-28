<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Notifications\Subscription\SubscriptionExpiredEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check for subscriptions that have ended their grace period and mark them as expired.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get subscriptions that are in the 'grace' period and whose grace period has ended
        $expiredSubscriptions = Subscription::where('status', 'grace')
            ->where('grace_period_end_date', '<=', Carbon::now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $user = $subscription->user;

            // Mark the subscription as expired
            $subscription->status = 'expired';
            $subscription->save();

            // Update the user's type to 2
            $user->user_type = 2;
            $user->save();


            // Log that the subscription has expired
            Log::info('Subscription expired for user: ' . $user->id . ' after grace period.');

            // Send the subscription expired email to the user
            $user->notify(new SubscriptionExpiredEmail($user, $subscription->grace_period_end_date, $subscription->last_billing_date, 0));
        }

        return 0; // Success
    }
}
