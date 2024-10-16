<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Modify the 'status' enum to include the new statuses
            $table->enum('status', [
                'active', 
                'trial', 
                'grace', 
                'expired', 
                'cancelled', 
                'incomplete', 
                'incomplete_expired', 
                'past_due', 
                'unpaid', 
                'paused'
            ])->default('active')->change();
            
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Revert the changes made to the 'status' column
            $table->enum('status', ['active', 'trial', 'grace', 'expired', 'cancelled'])->default('active')->change();
        });
    }
};
