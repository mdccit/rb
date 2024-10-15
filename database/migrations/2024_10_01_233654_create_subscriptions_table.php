<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->uuid('user_id'); // UUID foreign key to users table
            $table->enum('subscription_type', ['trial', 'monthly', 'annually']); 
            $table->boolean('is_auto_renewal')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'trial', 'grace', 'expired']);
            $table->timestamp('next_billing_date')->nullable()->after('end_date');
            $table->timestamp('canceled_at')->nullable()->after('next_billing_date');
            $table->timestamp('grace_period_end_date')->nullable()->after('canceled_at');
            $table->string('payment_status')->nullable()->after('grace_period_end_date'); 
            $table->string('stripe_subscription_id')->nullable()->after('payment_status');
            $table->timestamp('last_payment_date')->nullable()->after('stripe_subscription_id');
            $table->decimal('last_payment_amount', 8, 2)->nullable()->after('last_payment_date');
            $table->timestamps();

            // Foreign key constraint
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
