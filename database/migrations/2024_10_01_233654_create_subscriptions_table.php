<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('subscription_type', ['trial', 'monthly', 'annually']); 
            $table->boolean('is_auto_renewal')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'trial', 'grace', 'expired'])->notNull();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('grace_period_end_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamp('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 8, 2)->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
