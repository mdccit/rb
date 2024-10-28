<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable()->after('status');
            $table->boolean('is_one_time')->default(false)->after('trial_ends_at');
            $table->timestamp('last_renewal_date')->nullable()->after('is_one_time');
            $table->string('stripe_charge_id')->nullable()->after('stripe_subscription_id');
            $table->string('stripe_invoice_id')->nullable()->after('stripe_charge_id');
            $table->json('metadata')->nullable()->after('last_payment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'trial_ends_at',
                'is_one_time',
                'last_renewal_date',
                'stripe_charge_id',
                'stripe_invoice_id',
                'metadata',
            ]);
        });
    }
};
