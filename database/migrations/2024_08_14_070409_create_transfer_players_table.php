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
        Schema::create('transfer_players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('school')->nullable();;
            $table->string('utr_score_manual')->nullable();
            $table->enum('year', ['freshman', 'sophomore', 'junior','senior'])->default('freshman');;
            $table->integer('win')->default(0);
            $table->integer('loss')->default(0);
            $table->string('profile_photo_path', 2048)->nullable();
            $table->enum('handedness', ['right', 'left', 'both'])->default('both');
            $table->string('email')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone_code',20)->nullable();
            $table->string('phone_number')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->enum('gender', ['none', 'male', 'female', 'other'])->default('none');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_players');
    }
};
