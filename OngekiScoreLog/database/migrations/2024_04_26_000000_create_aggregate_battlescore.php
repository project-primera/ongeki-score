<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregateBattleScore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregate_battlescore', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('song_id')->unsigned();
            $table->integer("difficulty")->unsigned();
            $table->integer("max");
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
        Schema::dropIfExists('aggregate_battlescore');
    }
}
