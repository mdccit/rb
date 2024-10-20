<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CardExpiredEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $card_expiry_date;

    public function __construct($user, $card_expiry_date)
    {
        $this->user = $user;
        $this->card_expiry_date = $card_expiry_date;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log email sending attempt
        Log::info('Attempting to send Card Expiry Email', [
            'email' => $notifiable->email,
            'user_name' => $this->user->display_name,
            'card_expiry_date' => $this->card_expiry_date
        ]);

        try {
            // Send the email using the 'card-expired' Blade template
            return (new MailMessage)
                ->subject('Your Credit Card is About to Expire')
                ->view('vendor.emails.subscription.card-expired', [
                    'user' => $this->user,
                    'card_expiry_date' => $this->card_expiry_date,
                ]);

        } catch (\Exception $e) {
            // Log any errors that occur during the sending process
            Log::error('Failed to send Card Expiry Email', [
                'email' => $notifiable->email,
                'error' => $e->getMessage(),
            ]);

            // Optionally, you can rethrow the exception if you want to handle it further up
            throw $e;
        }
    }

    public function toArray($notifiable)
    {
        return [
            'user_name' => $this->user->display_name,
            'card_expiry_date' => $this->card_expiry_date,
        ];
    }

    // $user->notify(new CardExpiredEmail($user, $card_expiry_date));


}
