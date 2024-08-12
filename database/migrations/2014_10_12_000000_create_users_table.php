<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_role_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->string('display_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_first_login')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->text('bio')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['none', 'male', 'female', 'other'])->default('none');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_logged_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
