<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedFlagInSongs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('music_datas', function (Blueprint $table){
        	$table->tinyInteger('deleted_normal')->after('unrated')->unsigned()->default(0);
        	$table->tinyInteger('deleted_lunatic')->after('deleted_normal')->unsigned()->default(0);
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
        	$table->dropColumn('deleted_normal');
        	$table->dropColumn('deleted_lunatic');
        });
    }
}
