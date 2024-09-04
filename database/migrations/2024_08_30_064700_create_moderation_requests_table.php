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
        Schema::create('moderation_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('moderatable_type'); // model
            $table->string('moderatable_id'); // uuid or id
            $table->index(['moderatable_type', 'moderatable_id']);
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignUuid('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_requests');
    }
};
