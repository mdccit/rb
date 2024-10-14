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

  // Store a new subscription
  public function store(Request $request)
  {
    try {

      $validator = Validator::make($request->all(), [
        'subscription_type' => 'required|in:trial,monthly,annually',
        'is_auto_renewal' => 'required|boolean',
        'payment_method' => 'required|string',
      ]);

      if ($validator->fails()) {
        return CommonResponse::getResponse(422, $validator->errors(), 'Input validation failed');
      }

      $subscription = $this->subscriptionService->createSubscription($request->all(), $request->user());
      return CommonResponse::getResponse(201, 'Subscription created successfully', 'Subscription created and saved successfully', $subscription);

    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to create subscription');
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
    $paymentMethodId = $request->input('payment_method_id');
    $subscriptionType = $request->input('subscription_type'); // monthly, annually, or trial

    try {
      // Check if user already has an active subscription
      if ($user->subscription) {
        throw new \Exception('User already has an active subscription.');
      }

      // Determine the Stripe price ID based on subscription type
      $priceId = $this->getPriceIdFromSubscriptionType($subscriptionType);

      // Create the subscription with the payment method
      $stripeSubscription = $this->stripeAPI->createSubscription($user->stripe_id, $priceId, $paymentMethodId);

      // Save the subscription details to the database
      $userSubscription = new Subscription();
      $userSubscription->user_id = $user->id;
      $userSubscription->subscription_type = $subscriptionType;
      $userSubscription->status = 'active';
      $userSubscription->start_date = Carbon::now();
      $userSubscription->end_date = $subscriptionType === 'monthly' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
      $userSubscription->save();

      return response()->json(['status' => 'success', 'message' => 'Subscription created successfully']);

    } catch (\Exception $e) {
      Log::error('Error creating subscription: ' . $e->getMessage());
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * Helper function to get Stripe price ID from subscription type.
   */
  private function getPriceIdFromSubscriptionType($subscriptionType)
  {
    // Assuming you're pulling these from config/services.php
    if ($subscriptionType === 'monthly') {
      return config('services.stripe.monthly_price_id');
    } elseif ($subscriptionType === 'annually') {
      return config('services.stripe.annual_price_id');
    } elseif ($subscriptionType === 'trial') {
      return 'price_for_trial_plan'; // You may need to set a plan for trials
    } else {
      throw new \Exception('Invalid subscription type.');
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


}
