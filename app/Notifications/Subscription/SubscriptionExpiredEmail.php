<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionExpiredEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $expiration_date;
    protected $last_billing_date;
    protected $amount_paid;

    public function __construct($user, $expiration_date, $last_billing_date, $amount_paid)
    {
        $this->user = $user;
        $this->expiration_date = $expiration_date;
        $this->last_billing_date = $last_billing_date;
        $this->amount_paid = $amount_paid;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Subscription Expired Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'expiration_date' => $this->expiration_date,
        ]);

        try {
            // Send the email using the 'subscription-expired' Blade template
            return (new MailMessage)
                ->subject('Your Subscription Has Expired')
                ->view('vendor.emails.subscription.subscription-expired', [
                    'user' => $this->user,
                    'expiration_date' => $this->expiration_date,
                    'last_billing_date' => $this->last_billing_date,
                    'amount_paid' => $this->amount_paid,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Subscription Expired Email', [
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
            'expiration_date' => $this->expiration_date,
            'last_billing_date' => $this->last_billing_date,
            'amount_paid' => $this->amount_paid,
        ];
    }


    // $user->notify(new SubscriptionExpiredEmail($user, $expiration_date, $last_billing_date, $amount_paid));

}
