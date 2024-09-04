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
        Schema::create('connection_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignUuid('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('connection_status', ['pending', 'accepted', 'rejected', 'cancelled','removed'])->default('pending');
            $table->foreignUuid('removed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_requests');
    }
};
