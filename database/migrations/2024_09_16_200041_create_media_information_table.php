<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_information', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for unique identification
            $table->string('storage_provider')->default('azure'); // Storage provider name (Azure, AWS, etc.)
            $table->string('container_name'); // Azure Blob Storage container name
            $table->string('blob_name'); // Blob directory for media storage
            $table->string('media_type'); // Type of media (post media, profile picture, etc.)
            $table->string('storage_path')->nullable();
            $table->string('base_url'); // Base URL for blob storage
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_information');
    }
}
