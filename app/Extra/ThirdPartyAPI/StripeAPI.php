<?php

namespace App\Extra\ThirdPartyAPI;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;

class StripeAPI
{
  public function __construct()
  {
    // Set the Stripe API key from the environment variable
    Stripe::setApiKey(config('services.stripe.secret'));
  }

  // Create a new Stripe customer
  public function createCustomer($user)
  {
    return Customer::create([
      'email' => $user->email,
      'name' => $user->name,
    ]);
  }

  // Create a new subscription for a customer
  public function createSubscription($customerId, $planId)
  {
    return Subscription::create([
      'customer' => $customerId,
      'items' => [['price' => $planId]],
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

  // TODO Handle other Stripe-related logic, such as handling invoices, webhooks, etc.
}
