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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('iso2')->nullable();
            $table->string('iso3')->nullable();
            $table->string('top_level_domain')->nullable();
            $table->string('fips')->nullable();
            $table->unsignedInteger('iso_numeric')->nullable();
            $table->string('geo_name_id')->nullable();
            $table->unsignedInteger('e164')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('continent')->nullable();
            $table->string('capital')->nullable();
            $table->string('time_zone_in_capital')->nullable();
            $table->string('currency')->nullable();
            $table->string('language_codes')->nullable();
            $table->text('languages')->nullable();
            $table->unsignedInteger('area_km2')->nullable();
            $table->string('internet_hosts')->nullable();
            $table->string('internet_users')->nullable();
            $table->string('phones_mobile')->nullable();
            $table->string('phones_land')->nullable();
            $table->string('gdp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
