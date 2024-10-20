<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionCreatedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $plan_name;
    protected $start_date;
    protected $price;
    protected $billing_cycle;
    protected $next_billing_date;

    public function __construct($user, $plan_name, $start_date, $price, $billing_cycle, $next_billing_date)
    {
        $this->user = $user;
        $this->plan_name = $plan_name;
        $this->start_date = $start_date;
        $this->price = $price;
        $this->billing_cycle = $billing_cycle;
        $this->next_billing_date = $next_billing_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log the email attempt
        Log::info('Attempting to send Subscription Created Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'plan_name' => $this->plan_name,
            'start_date' => $this->start_date,
        ]);

        try {
            // Send the email
            return (new MailMessage)
                ->subject('Welcome to Recruited - Subscription Confirmed')
                ->view('vendor.emails.subscription.subscription-created', [
                    'user' => $this->user,
                    'plan_name' => $this->plan_name,
                    'start_date' => $this->start_date,
                    'price' => $this->price,
                    'billing_cycle' => $this->billing_cycle,
                    'next_billing_date' => $this->next_billing_date,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Subscription Created Email', [
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
            'start_date' => $this->start_date,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'next_billing_date' => $this->next_billing_date,
        ];
    }

    // $user->notify(new SubscriptionCreatedEmail($user, $plan_name, $start_date, $price, $billing_cycle, $next_billing_date));

}
