<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionCancelEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $subscription;
    protected $end_date;

    public function __construct($user, $subscription, $end_date)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->end_date = $end_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log the start of the email sending process
        Log::info('Attempting to send Subscription Cancelation Email', [
            'subscription_id' => $this->subscription->id,
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'end_date' => $this->end_date
        ]);

        try {
            // Send the email
            $mailMessage = (new MailMessage)
                ->subject('Subscription Canceled - ' . config('app.name'))
                ->view('vendor.emails.subscription.subscription-cancel', [
                    'user' => $this->user,
                    'subscription' => $this->subscription,
                    'end_date' => $this->end_date,
                ]);

            // Log success
            Log::info('Subscription Cancelation Email sent successfully', [
                'subscription_id' => $this->subscription->id,
                'email' => $notifiable->email,
            ]);

            return $mailMessage;
        } catch (\Exception $e) {
            // Log the failure
            Log::error('Failed to send Subscription Cancelation Email', [
                'subscription_id' => $this->subscription->id,
                'email' => $notifiable->email,
                'error' => $e->getMessage(),
            ]);

            // Optionally, rethrow the exception if you want to handle it elsewhere
            throw $e;
        }
    }

    public function toArray($notifiable)
    {
        return [
            'subscription_id' => $this->subscription->id,
            'user_name' => $this->user->display_name,
            'end_date' => $this->end_date,
        ];
    }
}
