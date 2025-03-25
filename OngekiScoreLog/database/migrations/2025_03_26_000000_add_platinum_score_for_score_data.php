<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlatinumScoreForScoreData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_datas', function (Blueprint $table){
        	$table->integer('platinum_score')->default(0)->after('technical_high_score');
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
        	$table->dropColumn('platinum_score');
        });
    }
}
