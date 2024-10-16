<?php

namespace App\Modules\AdminModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AdminModule\Services\SubscriptionService;
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

  
  public function adminListSubscriptions(Request $request)
  {
    try {
      $subscriptions = $this->subscriptionService->getAllSubscriptions($request);

      return CommonResponse::getResponse(200, 'Subscription retrieved successfully', 'Recurring retrieved successfully.', $subscriptions);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to create subscription');
    } 
  }

  public function getSubscriptionDetails($id)
  {
    try {
      $subscription = $this->subscriptionService->getSubscriptionBySubscriptionId($id);
      return CommonResponse::getResponse(200, 'Subscription details retrieved successfully', 'Subscription data retrieved successfully', $subscription);
    } catch (\Exception $e) {
      return CommonResponse::getResponse(500, $e->getMessage(), 'Failed to retrieve subscription details');
    }
  }

}