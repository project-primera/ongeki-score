<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingRecentMusicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_recent_musics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id");
            $table->integer('rank');
            $table->string('title');
            $table->integer("difficulty");
            $table->integer("technical_score");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rating_recent_musics');
    }
}
