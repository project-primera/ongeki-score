<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExcludeCertainSongsFromTheRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('music_datas', function (Blueprint $table){
            $table->tinyInteger('unrated')->after('lunatic_added_version')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('music_datas', function (Blueprint $table){
            $table->dropColumn('unrated');
        });
    }
}
