<?php

namespace App\Extra\ThirdPartyAPI;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Charge;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Invoice;
use Stripe\SetupIntent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription AS SubscriptionLocal;

class StripeAPI
{
  public function __construct()
  {
    // Set the Stripe API key from the environment variable
    Stripe::setApiKey(config('services.stripe.secret'));
  }


  public function getCustomerActiveSubscriptions($stripeCustomerId)
  {
    return Subscription::all([
      'customer' => $stripeCustomerId,
      'status' => 'all',
    ]);
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


  public function confirmSetupIntent($setupIntentId, $paymentMethodId)
  {
    try {
      // Make the POST request to Stripe API to confirm the SetupIntent with a return_url
      $response = Http::withToken(env('STRIPE_SECRET')) // Set your Stripe secret key
        ->asForm() // Set the content type to application/x-www-form-urlencoded
        ->post("https://api.stripe.com/v1/setup_intents/$setupIntentId/confirm", [
          'payment_method' => $paymentMethodId,
          'return_url' => env('STRIPE_RETURN_URL') // Use an environment variable or hardcoded URL
        ]);

      // Log the full response for debugging
      Log::info('Stripe Confirm SetupIntent Response: ' . json_encode($response->json()));

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
          return [
            'status' => 'error',
            'message' => 'SetupIntent confirmation failed with status: ' . $result['status']
          ];
        }
      }

      // Log the response if it's not successful
      Log::error('Failed Stripe SetupIntent Confirmation: ' . json_encode($response->json()));

      return [
        'status' => 'error',
        'message' => 'Failed to confirm SetupIntent: ' . json_encode($response->json())
      ];

    } catch (\Exception $e) {
      // Log the exception message for better error tracking
      Log::error('Exception in confirming SetupIntent: ' . $e->getMessage());
      return [
        'status' => 'error',
        'message' => 'Exception: ' . $e->getMessage()
      ];
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

      // Return a success message in case of successful attachment
      return ['status' => 'success', 'message' => 'Payment method attached successfully.'];


    } catch (\Exception $e) {
      Log::error('Error attaching payment method: ' . $e->getMessage());

      // Return an error message if there is an exception
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }


  // Create a new subscription for a customer with optional trial and subscription type
  public function createSubscription($customerId, $subscriptionType, $paymentMethodId = null, $isAutoRenewal = true)
  {
    try {
      // Attach the payment method to the customer if provided
      if ($paymentMethodId) {
        $this->attachPaymentMethodToCustomer($customerId, $paymentMethodId);
      }

      $priceId = $this->getPriceIdFromSubscriptionType($subscriptionType);

      // Create a subscription with auto-renewal
      $subscription = Subscription::create([
        'customer' => $customerId,
        'items' => [['price' => $priceId]],  // Pass the price ID
        'expand' => ['latest_invoice.payment_intent'],  // Optional: Expand the payment intent for more details
        'automatic_tax' => ['enabled' => false],
        'default_payment_method' => $paymentMethodId
      ]);

      return $subscription;

    } catch (\Exception $e) {
      Log::error('Stripe Subscription Creation Error: ' . $e->getMessage());
      throw new \Exception('Failed to create subscription: ' . $e->getMessage());
    }
  }


  public function createOneTimeSubscription($customerId, $subscriptionType, $paymentMethodId)
  {
      try {
          // Get the price and amount based on the subscription type
          $priceId = $this->getPriceIdFromSubscriptionType($subscriptionType);
          $amount = $this->getAmountForPriceId($priceId);
          Log::debug('One-time Payment Amount: ' . $priceId . ' ' . $amount);
  
          // At this point, the payment method is already confirmed and attached to the customer
          // Charge the customer directly for a one-time amount using Invoice or Charge
          $charge = Charge::create([
              'customer' => $customerId,
              'amount' => $amount,
              'currency' => 'usd',
              'payment_method' => $paymentMethodId,
              'off_session' => true, // Indicates the payment can happen without user interaction
              'confirm' => true,
          ]);
  
          // Check if $charge is actually an object with an `id` property
          if (!isset($charge->id)) {
              Log::error('Charge creation failed. Response: ' . json_encode($charge));
              throw new \Exception('Failed to create one-time charge.');
          }
  
          // Calculate subscription period locally
          $startDate = Carbon::now();
          $endDate = ($subscriptionType === 'monthly') ? $startDate->copy()->addMonth() : $startDate->copy()->addYear();
  
          Log::debug('One-time Charge ID: ' . $charge->id);
  
          // Store the one-time subscription details in the local database
          $oneTimeSubscription = new SubscriptionLocal();
          $oneTimeSubscription->user_id = $customerId;
          $oneTimeSubscription->subscription_type = $subscriptionType;
          $oneTimeSubscription->status = 'active';
          $oneTimeSubscription->is_auto_renewal = false;
          $oneTimeSubscription->start_date = $startDate;
          $oneTimeSubscription->end_date = $endDate;
          $oneTimeSubscription->payment_status = 'paid';
          $oneTimeSubscription->stripe_charge_id = $charge->id; // Store the charge ID for reference
          $oneTimeSubscription->save();
  
          // Return the subscription data or response as needed
          return response()->json(['status' => 'success', 'message' => 'One-time subscription created successfully']);
  
      } catch (\Exception $e) {
          Log::error('One-Time Subscription Creation Error: ' . $e->getMessage());
          return response()->json(['status' => 'error', 'message' => 'Failed to create one-time subscription: ' . $e->getMessage()], 500);
      }
  }
  

  public function createSubscriptionWithTrial($customerId, $subscriptionType, $paymentMethodId, $trialDays = 30)
  {
      try {
          // Attach the payment method to the customer if not already attached
          $this->attachPaymentMethodToCustomer($customerId, $paymentMethodId);
  
          // Determine the price ID for the trial (typically, this is the same as the monthly/annual plan)
          $priceId = $this->getPriceIdFromSubscriptionType($subscriptionType);
  
          // Create a subscription with a trial period
          $subscription = Subscription::create([
              'customer' => $customerId,
              'items' => [['price' => $priceId]], // Price ID for the subscription plan
              'trial_period_days' => $trialDays,  // Set the trial period duration (e.g., 30 days)
              'default_payment_method' => $paymentMethodId,
              'automatic_tax' => ['enabled' => false],
              'expand' => ['latest_invoice.payment_intent'],
          ]);
  
          return $subscription;
  
      } catch (\Exception $e) {
          Log::error('Stripe Subscription Trial Creation Error: ' . $e->getMessage());
          throw new \Exception('Failed to create trial subscription: ' . $e->getMessage());
      }
  }

  // Create a payment intent (useful for one-time charges)
  public function createOneTimePaymentIntent($amount, $currency = 'usd', $paymentMethodId = null, $confirm = false)
  {
      $intentData = [
          'amount' => $amount,
          'currency' => $currency,
      ];
  
      if ($paymentMethodId) {
          $intentData['payment_method'] = $paymentMethodId;
          $intentData['confirm'] = $confirm;
      }
  
      return PaymentIntent::create($intentData);
  }
  

  // Get the price ID based on the subscription type (trial, monthly, annually)
  private function getPriceIdFromSubscriptionType($subscriptionType)
  {
    $priceIds = [
      'trial' => config('services.stripe.monthly_price_id'), // For trial subscriptions, Stripe may not charge immediately.
      'monthly' => config('services.stripe.monthly_price_id'), // Monthly plan price ID from .env
      'annually' => config('services.stripe.annual_price_id'), // Annual plan price ID from .env
    ];

    // Log the subscription type and price ID for debugging purposes
    Log::info('Retrieving price ID for subscription type: ' . $subscriptionType);
    Log::info(' price ID for subscription : ' . $priceIds[$subscriptionType]);

    // Check if the price ID is available
    if (!isset($priceIds[$subscriptionType]) || empty($priceIds[$subscriptionType])) {
      Log::error('Invalid price ID for subscription type: ' . $subscriptionType);
      throw new \Exception('Invalid price ID for subscription type');
    }

    return $priceIds[$subscriptionType];
  }

  public function getUserSubscriptions($stripeCustomerId)
  {
    return Subscription::all(['customer' => $stripeCustomerId, 'status' => 'all']);
  }

  public function getAllSubscriptions()
  {
    return Subscription::all();
  }

  /**
   * Cancel Stripe Subscription
   * 
   * @param string $subscriptionId
   * @return \Stripe\Subscription
   */
  public function cancelSubscription($subscriptionId)
  {
    try {
      // Retrieve and cancel the subscription from Stripe
      $subscription = Subscription::retrieve($subscriptionId);
      $subscription->cancel();
      return $subscription;
    } catch (\Exception $e) {
      throw new \Exception('Failed to cancel subscription: ' . $e->getMessage());
    }
  }

  // Renew subscription
  public function renewSubscription($subscriptionId)
  {
    $subscription = Subscription::retrieve($subscriptionId);
    return $subscription->save(); // Resuming subscription (usually from a canceled state)
  }

  /**
   * Retrieve Stripe Subscription
   * 
   * @param string $subscriptionId
   * @return \Stripe\Subscription
   */
  public function retrieveSubscription($subscriptionId)
  {
    try {
      // Retrieve the subscription from Stripe
      $subscription = Subscription::retrieve($subscriptionId);
      return $subscription;
    } catch (\Exception $e) {
      throw new \Exception('Failed to retrieve subscription: ' . $e->getMessage());
    }
  }

  // Change subscription plan
  public function changeSubscriptionPlan($subscriptionId, $newPlanId)
  {
    $subscription = Subscription::retrieve($subscriptionId);


    // Update the subscription's plan
    return Subscription::update($subscriptionId, [
      'items' => [
        [
          'id' => $subscription->items->data[0]->id,  // Item ID to update
          'price' => $newPlanId, // Plan ID is now called 'price' in Stripe
        ],
      ],
    ]);

  }

  // Update payment method for subscription
  public function updatePaymentMethod($customerId, $paymentMethodId)
  {
    // Attach new payment method to the customer
    $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
    $paymentMethod->attach(['customer' => $customerId]);

    // Update the customer's invoice settings to use the new payment method
    $customer = Customer::update($customerId, [
      'invoice_settings' => ['default_payment_method' => $paymentMethodId],
    ]);

    return $customer;
  }

  public function getCustomerPaymentHistory($stripeCustomerId)
  {
    try {
      Stripe::setApiKey(config('services.stripe.secret'));

      // Retrieve all invoices for the customer
      $invoices = Invoice::all([
        'customer' => $stripeCustomerId,
        'limit' => 100 // Limit the number of invoices returned
      ]);

      // Format the payment history data
      $paymentHistory = [];
      foreach ($invoices->data as $invoice) {
        $paymentHistory[] = [
          'invoice_id' => $invoice->id,
          'amount_paid' => $invoice->amount_paid / 100, // Convert amount to dollars
          'currency' => $invoice->currency,
          'status' => $invoice->status,
          'created' => date('Y-m-d H:i:s', $invoice->created),
          'payment_method' => $invoice->payment_method,
          'hosted_invoice_url' => $invoice->hosted_invoice_url // Link to Stripe invoice
        ];
      }

      return $paymentHistory;
    } catch (\Exception $e) {
      return ['error' => $e->getMessage()];
    }
  }

  public function getCustomerPaymentMethods($customerId)
  {
      try {
          // Set up Stripe with your secret key
          Stripe::setApiKey(config('services.stripe.secret'));
          
          // Retrieve the customer object to get the default payment method ID
          $customer = Customer::retrieve($customerId);
          $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method;
  
          // Retrieve all payment methods for the customer
          $paymentMethods = PaymentMethod::all([
              'customer' => $customerId,
              'type' => 'card',
          ]);
  
          // Append 'is_default' flag to each payment method
          $paymentMethodsWithDefaultStatus = collect($paymentMethods->data)->map(function ($method) use ($defaultPaymentMethodId) {
              return [
                  'id' => $method->id,
                  'brand' => $method->card->brand,
                  'last4' => $method->card->last4,
                  'exp_month' => $method->card->exp_month,
                  'exp_year' => $method->card->exp_year,
                  'is_default' => $method->id === $defaultPaymentMethodId, // Set default status
              ];
          });
  
          return response()->json([
              'success' => true,
              'data' => $paymentMethodsWithDefaultStatus,
          ]);
      } catch (\Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }

  /**
   * Get the active subscription of the customer and its associated payment method (card details).
   */
  public function getSubscriptionPaymentMethod($customerId)
  {
    // Retrieve all active subscriptions of the customer
    $subscriptions = Subscription::all([
      'customer' => $customerId,
      'status' => 'active', // Only active subscriptions
    ]);

    if (count($subscriptions->data) > 0) {
      // Assume the customer has one active subscription. You can adjust logic for multiple subscriptions.
      $subscription = $subscriptions->data[0];

      Log::info($subscription);
      // Check if the subscription has a default payment method
      if ($subscription->default_payment_method) {
        // Retrieve the payment method (typically a card)
        $paymentMethod = PaymentMethod::retrieve($subscription->default_payment_method);

        // Return the payment method data, especially card details
        return [
          'brand' => $paymentMethod->card->brand,
          'last4' => $paymentMethod->card->last4,
          'exp_month' => $paymentMethod->card->exp_month,
          'exp_year' => $paymentMethod->card->exp_year,
          'billing_details' => $paymentMethod->billing_details,
        ];
      } else {
        throw new \Exception('No default payment method found for this subscription.');
      }
    } else {
      throw new \Exception('No active subscription found for this customer.');
    }
  }

  public function detachPaymentMethod($paymentMethodId)
  {
    try {
      // Retrieve the payment method and detach it from the customer
      $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
      $paymentMethod->detach();
      return $paymentMethod;
    } catch (\Exception $e) {
      throw new \Exception('Failed to remove the payment method: ' . $e->getMessage());
    }
  }


  public function cancelRecurringSubscription($stripeSubscriptionId)
  {
    $subscription = Subscription::retrieve($stripeSubscriptionId);
    $subscription->cancel();  // Optionally, use cancel_at_period_end to delay cancellation until the end of the billing cycle
  }



  private function getAmountForPriceId($priceId)
{
    try {
        // Retrieve the price from Stripe using the price ID
        $price =  Price::retrieve($priceId);

        // Return the amount in cents (Stripe uses smallest currency unit)
        return $price->unit_amount;
    } catch (\Exception $e) {
        Log::error('Error retrieving amount for price ID: ' . $priceId . ' - ' . $e->getMessage());
        throw new \Exception('Failed to retrieve amount for price ID: ' . $e->getMessage());
    }
}



}
