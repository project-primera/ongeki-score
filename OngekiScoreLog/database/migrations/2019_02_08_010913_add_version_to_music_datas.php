<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersionToMusicDatas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('music_datas', function (Blueprint $table){
        	$table->tinyInteger('normal_added_version')->after('lunatic_extra_level')->nullable()->unsigned();
        	$table->tinyInteger('lunatic_added_version')->after('normal_added_version')->nullable()->unsigned();
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
        	$table->dropColumn('normal_added_version');
        	$table->dropColumn('lunatic_added_version');
        });
    }
}
