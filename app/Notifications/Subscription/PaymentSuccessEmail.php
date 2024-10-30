<?php

namespace App\Notifications\Subscription;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Stripe\Invoice;

class PaymentSuccessEmail extends Notification implements ShouldQueue
{
  use Queueable;

  protected $subscription;
  protected $price;
  protected $currency;
  protected $display_name;
  public function __construct($subscription, $price, $currency, $display_name)
  {
    $this->subscription = $subscription;
    $this->price = $price;
    $this->currency = $currency;
    $this->display_name = $display_name;
  }

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {

    $invoice = Invoice::retrieve($this->subscription->latest_invoice);
    $subscription_amount = $invoice->total / 100;

    // Log the start of the email sending process
    Log::info('Attempting to send Payment Success Email', [
      'subscription_id' => $this->subscription->id,
      'amount' => $subscription_amount,
      'currency' => $this->currency,
      'email' => $notifiable->email,
      'display_name' => $this->display_name
    ]);

    try {
      // Send the email
      $mailMessage = (new MailMessage)
        ->subject('Payment Success for Your Subscription')
        ->view('vendor.emails.subscription.payment-success', [
          'subscription' => $this->subscription,
          'amount' => $subscription_amount,
          'currency' => $this->currency,
          'display_name' => $this->display_name,
        ]);

      // Log success
      Log::info('Payment Success Email sent successfully', [
        'subscription_id' => $this->subscription->id,
        'email' => $notifiable->email,
      ]);

      return $mailMessage;
    } catch (\Exception $e) {
      // Log the failure
      Log::error('Failed to send Payment Success Email', [
        'subscription_id' => $this->subscription->id,
        'email' => $notifiable->email,
        'error' => $e->getMessage(),
      ]);

      // Optionally, rethrow the exception if you want to handle it elsewhere
      throw $e;
    }
  }
}
