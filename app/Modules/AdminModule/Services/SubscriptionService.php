<?php

namespace App\Modules\AdminModule\Services;

use App\Models\Subscription;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Carbon\Carbon;
use App\Extra\ThirdPartyAPI\StripeAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use Stripe\Subscription as StripeSubscription;

class SubscriptionService
{
  protected $stripeAPI;


  public function __construct(StripeAPI $stripeAPI)
  {
    $this->stripeAPI = $stripeAPI;
  }



  // Retrieve all subscriptions
  public function getAllSubscriptions()
  {
    $subscriptions = Subscription::with('user')->get();

    return $subscriptions;
  }

  public function getSubscriptionBySubscriptionId($id)
  {
    // Retrieve the subscription with the associated user and Stripe data
    $subscription = Subscription::with('user')->findOrFail($id);

    // Optionally, retrieve Stripe subscription data if necessary
    if ($subscription->stripe_subscription_id) {
      $stripeSubscription = $this->stripeAPI->retrieveSubscription($subscription->stripe_subscription_id);
      $subscription->stripe_details = $stripeSubscription;
    }

    return $subscription;
  }

}