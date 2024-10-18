<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PaymentFailedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $failure_reason;
    protected $amount_due;
    protected $payment_date;

    public function __construct($user, $failure_reason, $amount_due, $payment_date)
    {
        $this->user = $user;
        $this->failure_reason = $failure_reason;
        $this->amount_due = $amount_due;
        $this->payment_date = $payment_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Payment Failed Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'failure_reason' => $this->failure_reason,
        ]);

        try {
            // Send the email using the 'payment-failed' Blade template
            return (new MailMessage)
                ->subject('Payment Failed: Action Required')
                ->view('vendor.emails.subscription.payment-failed', [
                    'user' => $this->user,
                    'failure_reason' => $this->failure_reason,
                    'amount_due' => $this->amount_due,
                    'payment_date' => $this->payment_date,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Payment Failed Email', [
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
            'failure_reason' => $this->failure_reason,
            'amount_due' => $this->amount_due,
            'payment_date' => $this->payment_date,
        ];
    }

    // $user->notify(new PaymentFailedEmail($user, $failure_reason, $amount_due, $payment_date));


}
