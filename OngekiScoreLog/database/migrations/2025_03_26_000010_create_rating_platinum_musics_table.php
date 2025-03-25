<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingPlatinumMusicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_platinum_musics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id");
            $table->integer('rank');
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('genre')->nullable();
            $table->integer("difficulty");
            $table->integer("platinum_score");
            $table->integer("star");
            $table->string('unique_id');
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
