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


class SubscriptionSettingController extends Controller
{
  protected $subscriptionService;
  protected $stripeAPI;

  public function __construct(SubscriptionService $subscriptionService, StripeAPI $stripeAPI)
  {
    $this->subscriptionService = $subscriptionService;
    $this->stripeAPI = $stripeAPI;
  }


  public function changeSubscriptionToNewPlan(Request $request)
  {
      $user = $request->user();  // Get the authenticated user
      $newPlanId = $request->input('new_plan_id');  // The new Stripe price ID for the new plan
  
      // Validation for the new_plan_id
      $validator = Validator::make($request->all(), [
          'new_plan_id' => 'required|string',
      ]);
  
      if ($validator->fails()) {
          return CommonResponse::getResponse(422, $validator->errors(), 'Invalid input data.');
      }
  
      try {
          $stripeCustomerId = $user->stripe_id;
  
          // Check if the user has a valid Stripe customer ID
          if (!$stripeCustomerId) {
              return CommonResponse::getResponse(404, 'No Stripe customer ID found.', 'Stripe customer ID is missing.');
          }
  
          // Retrieve the current active subscription
          $subscription = $user->subscription;
          if (!$subscription || !$subscription->stripe_subscription_id) {
              return CommonResponse::getResponse(404, 'No active subscription found.', 'User does not have an active subscription.');
          }
  
          // Retrieve the subscription from Stripe
          $stripeSubscription = Subscription::retrieve($subscription->stripe_subscription_id);
  
          // Step 1: Cancel the pending cancellation if `cancel_at_period_end` is true
          if ($stripeSubscription->cancel_at_period_end) {
              $stripeSubscription->cancel_at_period_end = false;  // Stop the cancellation
              $stripeSubscription->save();
          }
  
          // Step 2: Swap to the new plan
          $stripeSubscription->items = [
              [
                  'id' => $stripeSubscription->items->data[0]->id,
                  'price' => $newPlanId  // Set the new plan's price ID
              ]
          ];
          $stripeSubscription->save();
  
          // Update local subscription details
          $subscription->subscription_type = 'new_plan';  // You can customize the plan type if needed
          $subscription->save();
  
          // Return a success response using CommonResponse class
          return CommonResponse::getResponse(200, 'Subscription updated successfully.', 'The subscription has been successfully updated.');
  
      } catch (\Exception $e) {
          // Log the error for debugging
          Log::error('Failed to update subscription: ' . $e->getMessage());
          // Return an error response using CommonResponse class
          return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to update subscription.');
      }
  }
  


}