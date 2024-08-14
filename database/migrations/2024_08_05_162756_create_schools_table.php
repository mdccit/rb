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
        Schema::create('schools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('bio')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_approved')->default(false);

            $table->unsignedInteger('gov_id')->unique()->nullable();
            $table->json('gov_sync_settings')->nullable();
            $table->string('url')->nullable();

            $table->enum('genders_recruiting', ['male', 'female', 'all'])->nullable();

            $table->json('other_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
