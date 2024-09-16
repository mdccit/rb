<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for unique identification
            $table->uuid('media_information_id'); // Foreign key referencing media_information table
            $table->uuid('entity_id'); // Foreign key to the entity (post, school, business, etc.)
            $table->string('entity_type'); // Type of the entity (post, school, business, user)
            $table->string('file_name'); // Name of the uploaded file
            $table->string('file_url'); // URL of the file in Azure Blob Storage
            $table->timestamps(); // Created and updated timestamps

            // Foreign key constraint for media_information_id
            $table->foreign('media_information_id')->references('id')->on('media_information')->onUpdate('cascade');

            // Indexes for faster querying by entity type and entity ID
            $table->index(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
