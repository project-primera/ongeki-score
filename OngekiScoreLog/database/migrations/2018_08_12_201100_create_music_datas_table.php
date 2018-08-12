<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('music_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('genre');
            $table->decimal('basic_level', 4, 2)->nullable();
            $table->decimal('advanced_level', 4, 2)->nullable();
            $table->decimal('expert_level', 4, 2)->nullable();
            $table->decimal('master_level', 4, 2)->nullable();
            $table->decimal('lunatic_level', 4, 2)->nullable();
            $table->decimal('basic_extra_level', 4, 2)->nullable();
            $table->decimal('advanced_extra_level', 4, 2)->nullable();
            $table->decimal('expert_extra_level', 4, 2)->nullable();
            $table->decimal('master_extra_level', 4, 2)->nullable();
            $table->decimal('lunatic_extra_level', 4, 2)->nullable();
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
        Schema::dropIfExists('music_datas');
    }
}
