<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoreDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('song_id');
            $table->integer('difficulty');
            $table->decimal("over_damage_high_score", 6, 2);
            $table->integer('battle_high_score');
            $table->integer('technical_high_score');
            $table->boolean('full_bell');
            $table->boolean('all_break');
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
        Schema::dropIfExists('score_datas');
    }
}
