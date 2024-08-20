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
            $table->string('type');
            $table->boolean('seen')->default(false);
            $table->string('content', 5000);
            $table->boolean('is_delete_from_user_chat')->default(false);
            $table->boolean('is_delete_to_user_chat')->default(false);
            $table->timestamps();

            $table->foreignUuid('from_user_id')->constrained('users');
            $table->foreignUuid('to_user_id')->constrained('users');
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
