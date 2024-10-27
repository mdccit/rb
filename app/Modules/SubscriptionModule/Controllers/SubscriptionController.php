<?php

namespace App\Modules\SubscriptionModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SubscriptionModule\Services\SubscriptionService;
use App\Extra\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Extra\ThirdPartyAPI\StripeAPI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use Carbon\Carbon;
use App\Notifications\Subscription\PaymentSuccessEmail;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;


class SubscriptionController extends Controller
{
  protected $subscriptionService;
  protected $stripeAPI;

  public function __construct(SubscriptionService $subscriptionService, StripeAPI $stripeAPI)
  {
    $this->subscriptionService = $subscriptionService;
    $this->stripeAPI = $stripeAPI;
  }

  // Show the current subscription of the user
  public function show(Request $request)
  {
    try {
      $subscription = $this->subscriptionService->getUserSubscription($request->user());
      return CommonResponse::getResponse(200, 'Subscription retrieved successfully', 'Subscription data retrieved successfully', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscription');
    }
  }

  // Cancel the user's subscription
  public function cancel(Request $request)
  {
    try {
      $this->subscriptionService->cancelSubscription($request->user());
      return CommonResponse::getResponse(200, 'Subscription canceled successfully', 'The subscription has been canceled.');
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to cancel subscription');
    }
  }

  // Renew the user's subscription (if applicable)
  public function renew(Request $request)
  {
    try {
      $this->subscriptionService->renewSubscription($request->user());
      return CommonResponse::getResponse(200, 'Subscription renewed successfully', 'The subscription has been renewed.');
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to renew subscription');
    }
  }


  // Get subscription by user ID
  public function getSubscriptionByUserId($userId)
  {
    // Validate the user ID format (UUID or numeric if using integers for IDs)
    $validator = Validator::make(['user_id' => $userId], [
      'user_id' => 'required|exists:users,id', // Validate that the user exists in the users table
    ]);

    if ($validator->fails()) {
      return CommonResponse::getResponse(422, $validator->errors(), 'Invalid User ID');
    }

    // Proceed if validation passes
    try {
      $subscription = $this->subscriptionService->getSubscriptionByUserId($userId);
      return CommonResponse::getResponse(200, 'Subscription retrieved successfully', 'Subscription data retrieved successfully', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(404, $e->getMessage(), 'Subscription not found');
    }
  }

  // Get all subscriptions
  public function getAllSubscriptions()
  {
    try {
      $subscriptions = $this->subscriptionService->getAllSubscriptions();
      return CommonResponse::getResponse(200, 'Subscriptions retrieved successfully', 'All subscriptions retrieved successfully', $subscriptions);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscriptions');
    }
  }


  public function getStripeCustomerId(Request $request)
  {
    try {
      // Retrieve the authenticated user
      $user = $request->user();  // Assuming you are using token-based authentication like JWT or Laravel Passport

      // Check if the user already has a Stripe customer ID
      if (!$user->stripe_id) {
        // If not, create a new Stripe customer and save the stripe_id
        $stripeCustomerId = $this->stripeAPI->createCustomer($user);
        $user->stripe_id = $stripeCustomerId;
        $user->save();
      } else {
        // If the user already has a Stripe customer ID
        $stripeCustomerId = $user->stripe_id;
      }

      return response()->json([
        'status' => 'success',
        'stripe_customer_id' => $stripeCustomerId
      ], 200);

    } catch (\Exception $e) {
      Log::error('Error retrieving Stripe customer ID: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to retrieve Stripe customer ID'
      ], 500);
    }
  }

  /**
   * Create a SetupIntent to get client_secret for payment method confirmation.
   */
  public function createSetupIntent(Request $request)
  {
    try {
      // Retrieve the customer ID from the request
      $customerId = $request->input('customer_id');

      // Ensure customer_id is provided
      if (!$customerId) {
        return response()->json([
          'message' => 'Customer ID is required.'
        ], 422);
      }

      // Create a SetupIntent for the provided customer
      $setupIntent = \Stripe\SetupIntent::create([
        'customer' => $customerId,
      ]);

      // Log the entire SetupIntent object for debugging
      Log::info('SetupIntent created: ' . json_encode($setupIntent));

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


  /**
   * Confirm SetupIntent by passing setup_intent_id, payment_method_id, and client_secret.
   */
  public function confirmSetupIntent(Request $request)
  {
    $setupIntentId = $request->input('setup_intent_id');
    $paymentMethodId = $request->input('payment_method_id');
    $clientSecret = $request->input('client_secret');

    try {
      // Confirm the SetupIntent to attach the payment method
      $confirmedPaymentMethod = $this->stripeAPI->confirmSetupIntent($setupIntentId, $paymentMethodId, $clientSecret);

      if ($confirmedPaymentMethod['status'] === 'success') {
        return response()->json([
          'status' => 'success',
          'message' => 'Payment method confirmed successfully',
          'payment_method_id' => $confirmedPaymentMethod['payment_method_id']
        ]);
      }

      return response()->json(['status' => 'error', 'message' => 'Failed to confirm payment method.']);

    } catch (\Exception $e) {
      Log::error('Error confirming payment method: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * Create subscription for the user using confirmed payment method.
   */
  public function createSubscription(Request $request)
  {
    $user = $request->user(); // Assuming the user is authenticated
    $displayName = $user->display_name;
    $paymentMethodId = $request->input('payment_method_id');
    $subscriptionType = $request->input('subscription_type'); // monthly, annually, or trial

    try {
      // Check if user already has an active subscription
      if ($user->subscription) {
        throw new \Exception('User already has an active subscription.');
      }

      // Create Stripe customer if the user doesn't already have one
      if (!$user->stripe_id) {
        $stripeCustomerId = $this->stripeAPI->createCustomer($user);
        $user->stripe_id = $stripeCustomerId;
        $user->save(); // Save the stripe_id to the user
      } else {
        $stripeCustomerId = $user->stripe_id;
      }

      // If payment method is already confirmed, proceed with subscription

      $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
      $paymentMethod->attach(['customer' => $stripeCustomerId]);

      // Set the default payment method for the customer
      Customer::update($stripeCustomerId, [
        'invoice_settings' => [
          'default_payment_method' => $paymentMethodId,
        ],
      ]);

      // Check if the subscription type is 'trial'
      if ($subscriptionType === 'trial') {
        // Create a trial subscription using the Stripe API
        $stripeSubscription = $this->stripeAPI->createSubscriptionWithTrial($user->stripe_id, $subscriptionType, $paymentMethodId, 30); // 30 days trial

        $subscriptionId = $stripeSubscription->id;
        $startDate = Carbon::createFromTimestamp($stripeSubscription->current_period_start);
        $endDate = Carbon::createFromTimestamp($stripeSubscription->current_period_end);
        $paymentStatus = $stripeSubscription->status;



        // Save the trial subscription to the database
        $userSubscription = new Subscription();
        $userSubscription->user_id = $user->id;
        $userSubscription->subscription_type = 'trial';
        $userSubscription->status = 'active'; // Set status to 'trial'
        $userSubscription->start_date = $startDate;
        $userSubscription->end_date = $endDate;
        $userSubscription->payment_status = $paymentStatus;
        $userSubscription->stripe_subscription_id = $subscriptionId;
        $userSubscription->save();

        if ($stripeSubscription) {
          $amount = $stripeSubscription->amount;
          $currency = strtoupper($stripeSubscription->currency);
          $user->has_used_trial = 1;
          $user->user_type_id = 3;
          $user->save();  // Save the user with the updated user_type

          // Trigger the payment success email notification
          $user->notify(new PaymentSuccessEmail($stripeSubscription, $amount, $currency, $displayName));
        }


        return response()->json(['status' => 'success', 'message' => 'Trial subscription created successfully']);
      } else if ($subscriptionType === 'premium') {

        // Create the subscription with the payment method
        $stripeSubscription = $this->stripeAPI->createSubscription($user->stripe_id, 'monthly', $paymentMethodId, true);

        $subscriptionId = $stripeSubscription->id;
        $startDate = Carbon::createFromTimestamp($stripeSubscription->current_period_start);
        $endDate = Carbon::createFromTimestamp($stripeSubscription->current_period_end);
        $paymentStatus = $stripeSubscription->status;

        // Save the subscription details to the database
        $userSubscription = new Subscription();
        $userSubscription->user_id = $user->id;
        $userSubscription->subscription_type = 'monthly';
        $userSubscription->status = 'active';
        $userSubscription->start_date = $startDate;
        $userSubscription->end_date = $endDate;
        $userSubscription->payment_status = $paymentStatus;
        $userSubscription->stripe_subscription_id = $subscriptionId;
        $userSubscription->save();

        if ($stripeSubscription) {
          $amount = $stripeSubscription->amount;
          $currency = strtoupper($stripeSubscription->currency);
          $user->has_used_trial = 1;
          $user->user_type_id = 3;
          $user->save();  // Save the user with the updated user_type

          // Trigger the payment success email notification
          $user->notify(new PaymentSuccessEmail($stripeSubscription, $amount, $currency, $displayName));
        }

        return response()->json(['status' => 'success', 'message' => 'Subscription created successfully']);
      }

    } catch (\Exception $e) {
      Log::error('Error creating subscription: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }


  public function showUserSubscription(Request $request)
  {
    // Get the authenticated user
    $user = $request->user();

    // Assuming you have stored the Stripe customer ID in your database for the user
    $stripeCustomerId = $user->stripe_id;

    if ($stripeCustomerId) {
      try {
        // Retrieve the user's subscriptions
        $subscriptions = $this->stripeAPI->getUserSubscriptions($stripeCustomerId);

        return $subscriptions;
      } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to retrieve subscriptions'], 500);
      }
    } else {
      return response()->json(['error' => 'Stripe customer ID not found'], 404);
    }
  }


  // function to view all subscriptions
  public function showAllSubscriptions()
  {
    try {
      // Retrieve all subscriptions (Admin only)
      $subscriptions = $this->stripeAPI->getAllSubscriptions();
      return $subscriptions;
    } catch (\Exception $e) {
      return response()->json(['error' => 'Failed to retrieve all subscriptions'], 500);
    }
  }


  public function getPaymentHistoryFromStripe(Request $request)
  {
    // Assuming you have the authenticated user's stripe_customer_id
    $user = $request->user(); // Or however you're getting the current user
    $stripeCustomerId = $user->stripe_id;

    // Get payment history from the StripeAPI service
    $paymentHistory = $this->stripeAPI->getCustomerPaymentHistory($stripeCustomerId);

    if (isset($paymentHistory['error'])) {
      return response()->json(['error' => $paymentHistory['error']], 500);
    }

    return response()->json($paymentHistory);
  }


  public function getCustomerPaymentMethods(Request $request)
  {
    $user = Auth::user();

    if (!$user->stripe_id) {
      return response()->json(['error' => 'No Stripe customer ID found'], 404);
    }

    try {
      $paymentMethods = $this->stripeAPI->getCustomerPaymentMethods($user->stripe_id);

      return CommonResponse::getResponse(200, 'Subscription retrieved successfully', 'Subscription data retrieved successfully', $paymentMethods);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscription');

    }
  }

  /**
   * Get the payment method summary for the currently active subscription.
   */
  public function getSubscriptionPaymentMethod(Request $request)
  {
    $user = auth()->user();

    if (!$user->stripe_id) {
      return response()->json(['error' => 'User does not have a Stripe customer ID.'], 404);
    }

    try {
      $paymentMethod = $this->stripeAPI->getSubscriptionPaymentMethod($user->stripe_id);

      return CommonResponse::getResponse(200, 'Subscription retrieved successfully', 'Subscription data retrieved successfully', $paymentMethod);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscription');

    }
  }


  public function removePaymentMethod(Request $request, $payment_method_id)
  {
    try {
      $user = $request->user();  // Get the logged-in user

      // Call the service to remove the payment method
      $this->subscriptionService->removePaymentMethod($user, $payment_method_id);

      // Return success response using CommonResponse class
      return CommonResponse::getResponse(200, 'Payment method removed successfully', 'The payment method has been removed successfully');
    } catch (\Exception $e) {
      // Return failure response using CommonResponse class
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to remove payment method');
    }
  }


  /**
   * Create recurring subscription
   */
  public function createRecurringSubscription(Request $request)
  {
    try {
      $user = $request->user(); // Get the authenticated user
      $data = $request->all(); // Subscription data sent from frontend (e.g., plan, payment method)

      // Call the service to create the subscription
      $subscription = $this->subscriptionService->createRecurringSubscription($data, $user);

      return CommonResponse::getResponse(200, 'Subscription created successfully', 'Recurring subscription created.', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to create subscription');
    }
  }

  public function update(Request $request)
  {
    try {
      $user = $request->user();
      $subscription = $user->subscription;

      if (!$subscription) {
        return CommonResponse::getResponse(404, 'No active subscription found', 'Subscription not found.');
      }

      $validatedData = $request->validate([
        'subscription_type' => 'in:trial,monthly,annually',
        'is_auto_renewal' => 'boolean',
      ]);

      if (isset($validatedData['subscription_type'])) {
        $subscription->subscription_type = $validatedData['subscription_type'];
      }

      if (isset($validatedData['is_auto_renewal'])) {
        $subscription->is_auto_renewal = $validatedData['is_auto_renewal'];
      }

      $subscription->save();

      return CommonResponse::getResponse(200, 'Subscription updated successfully', 'Subscription updated successfully.', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to update subscription');
    }
  }


  public function upgrade(Request $request)
  {
    try {
      $user = $request->user();
      $subscription = $user->subscription;

      if (!$subscription || $subscription->status !== 'active') {
        return CommonResponse::getResponse(404, 'No active subscription found', 'Subscription not found.');
      }

      $validatedData = $request->validate([
        'subscription_type' => 'required|in:monthly,annually',
      ]);

      if ($validatedData['subscription_type'] === 'monthly') {
        $subscription->subscription_type = 'monthly';
        $subscription->end_date = now()->addMonth();
      } elseif ($validatedData['subscription_type'] === 'annually') {
        $subscription->subscription_type = 'annually';
        $subscription->end_date = now()->addYear();
      }

      $subscription->save();

      return CommonResponse::getResponse(200, 'Subscription upgraded successfully', 'Subscription upgraded successfully.', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to upgrade subscription');
    }
  }

  public function checkStatus(Request $request)
  {
    try {
      $user = $request->user();
      $subscription = $user->subscription;

      if (!$subscription) {
        return CommonResponse::getResponse(404, 'No active subscription found', 'Subscription not found.');
      }

      return CommonResponse::getResponse(200, 'Subscription status retrieved successfully', 'Subscription status retrieved.', [
        'subscription_type' => $subscription->subscription_type,
        'status' => $subscription->status,
        'start_date' => $subscription->start_date,
        'end_date' => $subscription->end_date,
        'is_auto_renewal' => $subscription->is_auto_renewal,
      ]);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscription status');
    }
  }

  public function getUpcomingInvoice(Request $request)
  {
    try {
      $user = $request->user();
      $stripeCustomerId = $user->stripe_id;

      if (!$stripeCustomerId) {
        return CommonResponse::getResponse(404, 'Stripe customer ID not found', 'User does not have a Stripe customer ID.');
      }

      $upcomingInvoice = \Stripe\Invoice::upcoming([
        'customer' => $stripeCustomerId,
      ]);

      return CommonResponse::getResponse(200, 'Upcoming invoice retrieved successfully', 'Stripe upcoming invoice retrieved.', $upcomingInvoice);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve upcoming invoice');
    }
  }
  public function updatePaymentMethod(Request $request)
  {
    try {
      $user = $request->user();
      $stripeCustomerId = $user->stripe_id;

      if (!$stripeCustomerId) {
        return CommonResponse::getResponse(404, 'Stripe customer ID not found', 'User does not have a Stripe customer ID.');
      }

      $validatedData = $request->validate([
        'payment_method_id' => 'required|string',
      ]);

      $paymentMethodId = $validatedData['payment_method_id'];

      // Call StripeAPI service to attach the payment method
      $result = $this->stripeAPI->attachPaymentMethodToCustomer($paymentMethodId, $stripeCustomerId);

      if ($result['status'] === 'error') {
        return CommonResponse::getResponse(500, $result['message'], 'Failed to update payment method.');
      }

      return CommonResponse::getResponse(200, 'Payment method updated successfully', 'Stripe payment method updated successfully.');
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to update payment method');
    }
  }


  public function stopSubscriptionCancellation(Request $request)
  {
    $user = $request->user();  // Get the authenticated user

    try {
      // Retrieve the user's active subscription
      $subscription = $user->subscription;
      if (!$subscription || !$subscription->stripe_subscription_id) {
        return CommonResponse::getResponse(404, 'No active subscription found.', 'User does not have an active subscription.');
      }

      // Retrieve the subscription from Stripe
      $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

      // Check if the subscription is set to cancel at the end of the period
      if ($stripeSubscription->cancel_at_period_end) {
        // Stop the cancellation
        $stripeSubscription->cancel_at_period_end = false;
        $stripeSubscription->save();

        // Optionally update local subscription status (if needed)
        $subscription->status = 'active';  // Mark it as active in the local database
        $subscription->save();

        return CommonResponse::getResponse(200, 'Subscription cancellation stopped.', 'Your subscription will continue beyond the current period.');
      } else {
        // If the subscription is not set to cancel, return a response
        return CommonResponse::getResponse(200, 'Subscription is already active.', 'No cancellation was scheduled.');
      }

    } catch (\Exception $e) {
      Log::error('Failed to stop subscription cancellation: ' . $e->getMessage());
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to stop subscription cancellation.');
    }
  }


  public function setDefaultPaymentMethod(Request $request)
  {


    $validatedData = $request->validate([
      'payment_method_id' => 'required|string',
    ]);

    $paymentMethodId = $validatedData['payment_method_id'];

    if (empty($paymentMethodId)) {
      return CommonResponse::getResponse(422, null, 'Payment method ID is required.');
    }
    $user = $request->user();
    $stripeCustomerId = $user->stripe_id;

    if (!$stripeCustomerId) {
      return CommonResponse::getResponse(404, null, 'No Stripe customer ID found for this user.');
    }

    try {
      // Retrieve the payment method from Stripe
      $paymentMethod = PaymentMethod::retrieve($paymentMethodId);


      // Attach the payment method to the customer (if not already attached)
      $paymentMethod->attach([
        'customer' => $stripeCustomerId,
      ]);

      // Update the customer’s invoice settings to use this payment method as the default
      Customer::update($stripeCustomerId, [
        'invoice_settings' => [
          'default_payment_method' => $paymentMethodId
        ]
      ]);

      // Optionally, update the local database with the default payment method ID
      $user->default_payment_method_id = $paymentMethodId; // Assuming you have this column in your users table
      $user->save();

      // Return a success response using the CommonResponse structure
      return CommonResponse::getResponse(200, null, 'Default payment method updated successfully.');

    } catch (\Exception $e) {
      // Log the error and return an error response using CommonResponse
      Log::error('Error setting default payment method: ' . $e->getMessage());
      return CommonResponse::getResponse(500, null, 'Failed to set default payment method: ' . $e->getMessage());
    }
  }



}
