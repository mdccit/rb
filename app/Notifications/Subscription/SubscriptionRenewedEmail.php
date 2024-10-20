<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionRenewedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $plan_name;
    protected $renewal_date;
    protected $next_billing_date;
    protected $amount_charged;

    public function __construct($user, $plan_name, $renewal_date, $next_billing_date, $amount_charged)
    {
        $this->user = $user;
        $this->plan_name = $plan_name;
        $this->renewal_date = $renewal_date;
        $this->next_billing_date = $next_billing_date;
        $this->amount_charged = $amount_charged;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Subscription Renewal Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'plan_name' => $this->plan_name,
            'renewal_date' => $this->renewal_date,
            'amount_charged' => $this->amount_charged,
        ]);

        try {
            // Send the email using the 'subscription-renew' Blade template
            return (new MailMessage)
                ->subject('Your Subscription Has Been Renewed')
                ->view('vendor.emails.subscription.subscription-renew', [
                    'user' => $this->user,
                    'plan_name' => $this->plan_name,
                    'renewal_date' => $this->renewal_date,
                    'next_billing_date' => $this->next_billing_date,
                    'amount_charged' => $this->amount_charged,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Subscription Renewal Email', [
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
            'plan_name' => $this->plan_name,
            'renewal_date' => $this->renewal_date,
            'next_billing_date' => $this->next_billing_date,
            'amount_charged' => $this->amount_charged,
        ];
    }

    // $user->notify(new SubscriptionRenewedEmail($user, $plan_name, $renewal_date, $next_billing_date, $amount_charged));

}
