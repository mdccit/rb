<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessEmail extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $password_reset;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $password_reset)
    {
        $this->user =  $user;
        $this->password_reset =  $password_reset;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->subject(config('app.name').' Subscription Payment Success')->view(
            'vendor.emails.subscription.payment-success',
            [
                'user'=> $this->user,
                'password_reset'=> $this->password_reset,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
