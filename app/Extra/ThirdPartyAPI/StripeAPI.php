<?php

namespace App\Extra\ThirdPartyAPI;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class StripeAPI
{
  public function __construct()
  {
    // Set the Stripe API key from the environment variable
    Stripe::setApiKey(config('services.stripe.secret'));
  }

  public function createSetupIntent($customerId)
  {
    try {
      // Create a SetupIntent for the customer
      $setupIntent = SetupIntent::create([
        'customer' => $customerId,
      ]);

      // Check if the required fields are present
      if (isset($setupIntent->client_secret) && isset($setupIntent->id)) {
        return response()->json([
          'message' => 'SetupIntent created. Please confirm the payment method.',
          'client_secret' => $setupIntent->client_secret,
          'setup_intent_id' => $setupIntent->id
        ]);
      } else {
        return response()->json([
          'message' => 'Failed to create SetupIntent',
          'error' => 'SetupIntent is missing required fields.'
        ], 500);
      }
    } catch (\Exception $e) {
      // Log the error for debugging
      Log::error('Error creating SetupIntent: ' . $e->getMessage());
      return response()->json([
        'message' => 'Failed to create SetupIntent',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function confirmSetupIntent($setupIntentId, $paymentMethodId, $clientSecret)
  {
    try {
      // Make the POST request to Stripe API to confirm the SetupIntent
      $response = Http::withToken(env('STRIPE_SECRET')) // Set your Stripe secret key
        ->post("https://api.stripe.com/v1/setup_intents/$setupIntentId/confirm", [
          'payment_method' => $paymentMethodId,
          'client_secret' => $clientSecret
        ]);

      // Handle the response
      if ($response->successful()) {
        $result = $response->json();

        // Check if the SetupIntent was confirmed successfully
        if ($result['status'] === 'succeeded') {
          return [
            'status' => 'success',
            'payment_method_id' => $result['payment_method'] // Return the payment method ID
          ];
        } else {
          return ['status' => 'error', 'message' => 'SetupIntent confirmation failed'];
        }
      }

      return ['status' => 'error', 'message' => 'Failed to confirm SetupIntent'];

    } catch (\Exception $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  // Method to create a new Stripe customer
  public function createCustomer($user)
  {
    // Check if the user already has a Stripe ID
    if (!$user->stripe_id) {
      // Create a Stripe customer
      $customer = Customer::create([
        'email' => $user->email,
        'name' => $user->first_name . ' ' . $user->last_name,
      ]);

      // Update the user with the Stripe customer ID
      $user->stripe_id = $customer->id;
      $user->save();

      return $customer->id;
    }

    // If customer already exists, return the existing Stripe ID
    return $user->stripe_id;
  }



  // Attach a payment method to the customer
  public function attachPaymentMethodToCustomer($customerId, $paymentMethodId)
  {
    try {

      // Retrieve the payment method from Stripe
      Log::info("Retrieving payment method: $paymentMethodId");
      $paymentMethod = PaymentMethod::retrieve($paymentMethodId);

      if (!$paymentMethod) {
        throw new \Exception('Payment method not found.');
      }

      // Attach the payment method to the customer
      Log::info("Attaching payment method $paymentMethodId to customer $customerId");
      $paymentMethod->attach(['customer' => $customerId]);

      // Set the default payment method for future invoices
      Customer::update($customerId, [
        'invoice_settings' => [
          'default_payment_method' => $paymentMethodId,
        ],
      ]);

      Log::info("Payment method $paymentMethodId attached successfully to customer $customerId");

    } catch (\Exception $e) {
      Log::error('Error attaching payment method: ' . $e->getMessage());
      throw new \Exception('Failed to attach payment method to customer: ' . $e->getMessage());
    }
  }


  // Create a new subscription for a customer with optional trial and subscription type
  public function createSubscription($customerId, $subscriptionType, $paymentMethodId = null, $trialDays = 0)
  {
    // Attach the payment method if provided
    if ($paymentMethodId) {
      $this->attachPaymentMethodToCustomer($customerId, $paymentMethodId);
    }

    // Determine the price ID based on subscription type (monthly, annually, etc.)
    $priceId = $this->getPriceIdFromSubscriptionType($subscriptionType);

    // Create a subscription with an optional trial period
    return Subscription::create([
      'customer' => $customerId,
      'items' => [['price' => $priceId]],
      'trial_period_days' => $trialDays, // Set trial period days, default is 0 (no trial)
    ]);
  }

  public function createSubscriptionWithTrial($customerId, $priceId, $trialDays = 30)
  {
    // Create a subscription with a trial period
    return Subscription::create([
      'customer' => $customerId,
      'items' => [['price' => $priceId]],
      'trial_period_days' => $trialDays, // Set the trial period (default is 30 days)
    ]);
  }

  // Create a payment intent (useful for one-time charges)
  public function createPaymentIntent($amount, $currency = 'usd')
  {
    return PaymentIntent::create([
      'amount' => $amount,
      'currency' => $currency,
    ]);
  }

  // Get the price ID based on the subscription type (trial, monthly, annually)
  private function getPriceIdFromSubscriptionType($subscriptionType)
  {
    $priceIds = [
      'trial' => null, // For trial subscriptions, Stripe may not charge immediately.
      'monthly' => config('services.stripe.monthly_price_id'), // Monthly plan price ID from .env
      'annually' => config('services.stripe.annual_price_id'), // Annual plan price ID from .env
    ];

    return $priceIds[$subscriptionType] ?? null;
  }
}
