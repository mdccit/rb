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
        Schema::table('transfer_players', function (Blueprint $table) {
            $table->json('other_data')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->foreignUuid('sport_id')->nullable()->constrained()->onDelete('cascade')->after('player_budget_id');
            $table->dropColumn('name');
            $table->dropColumn('utr_score_manual');
            $table->dropColumn('handedness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_players', function (Blueprint $table) {
            $table->dropColumn('other_data');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('sport_id');
            $table->string('utr_score_manual')->nullable();
            $table->string('name')->nullable();
            $table->enum('handedness', ['right', 'left', 'both'])->default('both');
        });
    }
};
