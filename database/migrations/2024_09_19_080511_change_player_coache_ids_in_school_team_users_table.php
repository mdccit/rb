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
        Schema::table('school_team_users', function (Blueprint $table) {
             // Drop existing foreignId columns
             $table->dropForeign(['player_id']);
             $table->dropForeign(['coache_id']);
             $table->dropColumn(['player_id', 'coache_id']);
 
             // Add new foreignUuid columns
             $table->foreignUuid('player_id')->nullable()->constrained()->onDelete('cascade');
             $table->foreignUuid('coache_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_team_users', function (Blueprint $table) {
            // Drop new foreignUuid columns
            $table->dropForeign(['player_id']);
            $table->dropForeign(['coache_id']);
            $table->dropColumn(['player_id', 'coache_id']);

            // Add original foreignId columns back
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('coache_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
