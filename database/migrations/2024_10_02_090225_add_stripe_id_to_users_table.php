<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the stripe_id column (nullable because not all users may have Stripe customers initially)
            $table->string('stripe_id')->nullable()->after('email'); 
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback by dropping the stripe_id column
            $table->dropColumn('stripe_id');
        });
    }
};
