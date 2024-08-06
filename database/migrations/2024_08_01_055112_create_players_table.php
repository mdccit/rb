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
        Schema::create('players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('player_parent_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('player_budget_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('has_parent')->default(false);
            $table->unique(['user_id']);
            $table->date('graduation_month_year')->nullable();
            $table->unsignedFloat('gpa')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->json('other_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
