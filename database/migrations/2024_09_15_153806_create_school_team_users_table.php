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
        Schema::create('school_team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('team_id')->references('id')->on('school_teams')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->unique(['team_id', 'user_id']);
            $table->string('role')->default('member'); 
            $table->enum('status', ['player', 'coache '])->default('player');
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('coache_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_team_users');
    }
};
