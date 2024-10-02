<?php

namespace App\Modules\SubscriptionModule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SubscriptionModule\Services\SubscriptionService;
use App\Extra\CommonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
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
                'auto_renewal' => 'sometimes|boolean',
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
}
