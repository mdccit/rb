<?php

namespace App\Modules\SubscriptionModule\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Customer;
use Carbon\Carbon;
use App\Extra\ThirdPartyAPI\StripeAPI;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use Stripe\Exception\SignatureVerificationException;
use App\Notifications\Subscription\CardExpiredEmail;
use App\Notifications\Subscription\PaymentFailedEmail;
use App\Notifications\Subscription\PaymentSuccessEmail;
use App\Notifications\Subscription\SubscriptionCreatedEmail;
use App\Notifications\Subscription\SubscriptionCancelEmail;
use App\Notifications\Subscription\SubscriptionExpiredEmail;
use App\Notifications\Subscription\SubscriptionPlanChangedEmail;
use App\Notifications\Subscription\SubscriptionRenewedEmail;
use App\Notifications\Subscription\TrialPeriodEndEmail;
use App\Notifications\Subscription\SubscriptionGracePeriodEmail;

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
    Log::info('Stripe Webhook Event received:-- ' . $eventType);

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
      case 'customer.subscription.updated':
        $this->handleSubscriptionUpdated($event['data']['object']);
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
    // Retrieve the user based on the Stripe customer ID
    $user = User::where('stripe_id', $invoice['customer'])->first();

    // If no user is found, log an error and return
    if (!$user) {
      Log::error('No user found with Stripe customer ID: ' . $invoice['customer']);
      return;
    }

    // Extract relevant details from the invoice object
    $failure_reason = $invoice['payment_intent']['last_payment_error']['message'] ?? 'Payment failed'; // Extract the failure reason if available
    $amount_due = $invoice['amount_due'] / 100; // Convert from cents to dollars (if applicable)
    $payment_date = Carbon::createFromTimestamp($invoice['created']); // Convert Stripe timestamp to Carbon date

    // Log payment failure for debugging purposes
    Log::debug('Payment failed for invoice: ' . $invoice['id'] . ', Reason: ' . $failure_reason);

    // Send payment failure notification to the user
    $user->notify(new PaymentFailedEmail($user, $failure_reason, $amount_due, $payment_date));

    // Update payment_status in the subscriptions table where id matches the subscription_id
    $subscription = Subscription::where('id', $invoice['subscription'])->first();

    if ($subscription) {
      $subscription->payment_status = 'failed'; // Set the payment_status to 'failed' or any status you prefer
      $subscription->save();
    } else {
      Log::error('No subscription found with Stripe subscription ID: ' . $invoice['subscription']);
    }
  }


  protected function handleSubscriptionCancelled($subscription)
  {
    // Handle subscription cancellation (update local DB, send email, etc.)
    Log::info('Subscription canceled: ' . $subscription['id']);
    // Update subscription status to canceled in your system
  }


  protected function handleSubscriptionUpdated($subscription)
  {
      // Retrieve the user based on the Stripe customer ID
      $user = User::where('stripe_id', $subscription['customer'])->first();
  
      // If no user is found, log an error and return
      if (!$user) {
          Log::error('No user found with Stripe customer ID: ' . $subscription['customer']);
          return;
      }
  
      // Get the current period end timestamp (when the subscription expired)
      $current_period_end = Carbon::createFromTimestamp($subscription['current_period_end']);
      
      // Get the last billing date (Stripe stores it as the period start)
      $last_billing_date = Carbon::createFromTimestamp($subscription['current_period_start']);
  
      // Check if the subscription has expired or is in a canceled state
      if ($subscription['status'] == 'canceled' || $subscription['status'] == 'past_due') {
          // Set a 7-day grace period
          $grace_period_end = $current_period_end->copy()->addDays(7);
  
          // Notify the user that their subscription is in a grace period
          Log::info('Subscription expired but in grace period for user: ' . $user->id);
          $user->notify(new SubscriptionGracePeriodEmail($user, $grace_period_end));
  
          // Update the subscription status in the database to 'grace'
          $dbSubscription = Subscription::where('id', $subscription['id'])->first();
          if ($dbSubscription) {
              $dbSubscription->status = 'grace';  // Set status to grace
              $dbSubscription->grace_period_end_date = $grace_period_end;  // Set grace period end date
              $dbSubscription->save();
          }
  
          // Schedule a job to check for the grace period expiry and send expiration email
          $this->scheduleGracePeriodCheck($dbSubscription, $grace_period_end, $user, $last_billing_date);
      }
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

    Log::debug('Trial ending for user ID: ', $subscription['customer']);
    // Retrieve the user from the database based on the stripe_id
    $user = User::where('stripe_id', $subscription['customer'])->first();

    // If user is not found, log an error and return
    if (!$user) {
      Log::error('No user found with Stripe ID: ' . $subscription['customer']);
      return;
    }

    // Calculate the number of days left until the trial ends
    $trial_end_timestamp = $subscription['trial_end'];
    $trial_end_date = Carbon::createFromTimestamp($trial_end_timestamp);
    $days_left = Carbon::now()->diffInDays($trial_end_date);

    // Send a notification to the user about the trial ending
    $user->notify(new TrialPeriodEndEmail($user, $days_left, $trial_end_date));

    // Log the trial ending event for tracking
    Log::debug('Trial ending for user ID: ' . $user->id . ', Stripe subscription ID: ' . $subscription['id']);
  }

}
