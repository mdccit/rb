<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionSummaryEventsTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_summary_events', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->foreignUuid('subscription_id')->nullable()->references('id')->on('subscriptions')->onDelete('cascade'); 

            $table->enum('event_type', [
                'payment_success',                // Payment was successful
                'payment_failed',                 // Payment attempt failed
                'subscription_created',           // New subscription was created
                'subscription_deleted',           // Subscription was canceled/deleted
                'subscription_expired',           // Subscription expired after the end of its term or grace period
                'subscription_paused',            // Subscription was temporarily paused
                'subscription_resumed',           // Subscription was resumed from a paused state
                'subscription_upgraded',          // Subscription was upgraded to a higher plan
                'subscription_downgraded',        // Subscription was downgraded to a lower plan
                'subscription_renewed',           // Subscription was renewed for another billing cycle
                'subscription_ended_grace',       // Subscription ended but entered a grace period
                'subscription_grace_period_ended',// Grace period ended without renewal, marking subscription as expired
                'card_expired',                   // User's card has expired
                'invoice_created',                // Invoice was created for an upcoming billing cycle
                'invoice_sent',                   // Invoice was sent to the user
                'invoice_paid',                   // Invoice was successfully paid
                'invoice_payment_failed',         // Invoice payment attempt failed
                'refund_issued',                  // Refund was issued for a payment
                'chargeback_received',            // Chargeback was received from the bank
                'payment_dispute_opened',         // Payment dispute was opened
                'payment_dispute_closed',         // Payment dispute was closed
                'card_updated',                   // User updated their card information
                'billing_address_updated',        // User updated their billing address
                'trial_started',                  // Trial period started for the subscription
                'trial_ended',                    // Trial period ended without conversion to a paid subscription
            ])->notNull(); // Type of event
            

            $table->string('description')->nullable(); // Detailed description of the event
            $table->decimal('amount', 10, 2)->nullable(); // Amount associated with the event, if applicable
            $table->timestamp('event_date')->nullable(); // Date and time of the event
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_summary_events');
    }
}
