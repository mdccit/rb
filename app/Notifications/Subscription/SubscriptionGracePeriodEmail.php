<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionGracePeriodEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $grace_period_end;

    public function __construct($user, $grace_period_end)
    {
        $this->user = $user;
        $this->grace_period_end = $grace_period_end;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Grace Period Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->name,
            'grace_period_end' => $this->grace_period_end->toFormattedDateString(),
        ]);

        try {
            // Send the email using the 'subscription-grace-period' Blade template
            return (new MailMessage)
                ->subject('Your Subscription is in Grace Period')
                ->view('vendor.emails.subscription.subscription-grace-period', [
                    'user' => $this->user,
                    'grace_period_end' => $this->grace_period_end,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Grace Period Email', [
                'email' => $notifiable->email,
                'error' => $e->getMessage(),
            ]);

            // Optionally rethrow if further handling is required
            throw $e;
        }
    }

    public function toArray($notifiable)
    {
        return [
            'grace_period_end' => $this->grace_period_end,
        ];
    }
}
