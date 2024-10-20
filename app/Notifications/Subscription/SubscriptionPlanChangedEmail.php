<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanChangedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $plan_status;
    protected $new_plan_name;
    protected $new_plan_price;
    protected $billing_cycle;
    protected $next_billing_date;

    public function __construct($user, $plan_status, $new_plan_name, $new_plan_price, $billing_cycle, $next_billing_date)
    {
        $this->user = $user;
        $this->plan_status = $plan_status; // upgraded or downgraded
        $this->new_plan_name = $new_plan_name;
        $this->new_plan_price = $new_plan_price;
        $this->billing_cycle = $billing_cycle;
        $this->next_billing_date = $next_billing_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Subscription Plan Changed Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'new_plan_name' => $this->new_plan_name,
        ]);

        try {
            // Send the email using the 'plan-changed' Blade template
            return (new MailMessage)
                ->subject('Your Subscription Plan Has Changed')
                ->view('vendor.emails.subscription.plan-changed', [
                    'user' => $this->user,
                    'plan_status' => $this->plan_status,
                    'new_plan_name' => $this->new_plan_name,
                    'new_plan_price' => $this->new_plan_price,
                    'billing_cycle' => $this->billing_cycle,
                    'next_billing_date' => $this->next_billing_date,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Subscription Plan Changed Email', [
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
            'plan_status' => $this->plan_status,
            'new_plan_name' => $this->new_plan_name,
            'new_plan_price' => $this->new_plan_price,
            'billing_cycle' => $this->billing_cycle,
            'next_billing_date' => $this->next_billing_date,
        ];
    }

    // $user->notify(new SubscriptionPlanChangedEmail($user, $plan_status, $new_plan_name, $new_plan_price, $billing_cycle, $next_billing_date));

}
