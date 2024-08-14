<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->uuid('user_id')->nullable();
            $table->enum('type', ['blog', 'event', 'post'])->notNull(); 
            $table->string('title')->nullable(); 
            $table->string('seo_url')->unique()->notNull(); 
            $table->text('description')->notNull(); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
