<?php

namespace App\Extra\ThirdPartyAPI;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

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

    // Attach a payment method to the customer
    public function attachPaymentMethodToCustomer($customerId, $paymentMethodId)
    {
        // Retrieve the payment method and then attach it to the customer
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);

        // Set the default payment method for future payments
        Customer::update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);
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
