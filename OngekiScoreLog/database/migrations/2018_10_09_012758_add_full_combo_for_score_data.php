<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFullComboForScoreData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_datas', function (Blueprint $table){
        	$table->integer('full_combo')->after('full_bell');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('score_datas', function (Blueprint $table){
        	$table->dropColumn('full_combo');
        });
    }
}
