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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['text'])->default('text');
            $table->enum('message_status', ['sent', 'delivered', 'seen'])->default('sent');
            $table->string('content', 5000);
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignId('conversation_id')->constrained('conversations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
