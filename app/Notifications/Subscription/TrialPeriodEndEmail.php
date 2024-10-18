<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TrialPeriodEndEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $days_left;
    protected $trial_end_date;

    public function __construct($user, $days_left, $trial_end_date)
    {
        $this->user = $user;
        $this->days_left = $days_left;
        $this->trial_end_date = $trial_end_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Trial Period End Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'days_left' => $this->days_left,
            'trial_end_date' => $this->trial_end_date,
        ]);

        try {
            // Send the email using the 'trial-period-end' Blade template
            return (new MailMessage)
                ->subject('Your Free Trial is Ending Soon')
                ->view('vendor.emails.subscription.trail-period-end', [
                    'user' => $this->user,
                    'days_left' => $this->days_left,
                    'trial_end_date' => $this->trial_end_date,
                ]);

        } catch (\Exception $e) {
            // Log any failure
            Log::error('Failed to send Trial Period End Email', [
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
            'days_left' => $this->days_left,
            'trial_end_date' => $this->trial_end_date,
        ];
    }

    // $user->notify(new TrialPeriodEndEmail($user, $days_left, $trial_end_date));

}
