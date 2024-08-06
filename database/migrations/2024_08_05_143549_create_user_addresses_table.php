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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->string('address_line_1', 256)->nullable();
            $table->string('address_line_2', 256)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('state_province', 64)->nullable();
            $table->string('postal_code', 48)->nullable();
            $table->enum('type', ['permanent', 'residential', 'other'])->default('permanent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
