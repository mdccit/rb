<?php

namespace App\Modules\SubscriptionModule\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
  public function handleWebhook(Request $request)
  {
    // You can retrieve the Stripe secret from the .env file
    $endpointSecret = config('services.stripe.webhook_secret'); // Add your webhook secret here

    // Retrieve the raw request body
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
      // Verify the webhook signature
      $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    } catch (SignatureVerificationException $e) {
      // Invalid signature
      Log::error('Stripe Webhook signature verification failed: ' . $e->getMessage());
      return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Handle the event type
    $eventType = $event['type'];
    Log::info('Stripe Webhook Event received: ' . $eventType);

    switch ($eventType) {
      case 'invoice.payment_succeeded':
        $this->handlePaymentSuccess($event['data']['object']);
        break;
      case 'invoice.payment_failed':
        $this->handlePaymentFailed($event['data']['object']);
        break;
      case 'customer.subscription.deleted':
        $this->handleSubscriptionCancelled($event['data']['object']);
        break;
      case 'invoice.upcoming':
        $this->handleUpcomingInvoice($event['data']['object']);
        break;
      case 'customer.source.expiring':
        $this->handleCardExpiring($event['data']['object']);
        break;
      case 'customer.subscription.trial_will_end':
        $this->handleTrialWillEnd($event['data']['object']);
        break;
      // Add other Stripe event cases as needed
      default:
        Log::info('Received unknown Stripe event type: ' . $eventType);
        return response()->json(['status' => 'event_not_handled'], 200);
    }

    return response()->json(['status' => 'success'], 200);
  }

  protected function handlePaymentSuccess($invoice)
  {
    // Handle successful payment (you can send email, update subscription, etc.)
    Log::info('Payment succeeded for invoice: ' . $invoice['id']);
    // Update subscription details here based on your logic
  }

  protected function handlePaymentFailed($invoice)
  {
    // Handle failed payment (you can send an email, update subscription status, etc.)
    Log::info('Payment failed for invoice: ' . $invoice['id']);
    // Send email notification for payment failure
  }

  protected function handleSubscriptionCancelled($subscription)
  {
    // Handle subscription cancellation (update local DB, send email, etc.)
    Log::info('Subscription canceled: ' . $subscription['id']);
    // Update subscription status to canceled in your system
  }

  protected function handleUpcomingInvoice($invoice)
  {
      // Notify user that their subscription is about to renew
      Log::info('Upcoming invoice for customer: ' . $invoice['customer']);
  }
  
  protected function handleCardExpiring($source)
  {
      // Notify user that their card is expiring
      Log::info('Card expiring for customer: ' . $source['customer']);
  }
  
  protected function handleTrialWillEnd($subscription)
  {
      // Notify user that their trial is ending
      Log::info('Trial ending for subscription: ' . $subscription['id']);
  }

}
