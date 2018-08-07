<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trophy');
            $table->integer("level");
            $table->string('name');
            $table->integer("battle_point");
            $table->decimal("rating", 4, 2);
            $table->decimal("rating_max", 4, 2);
            $table->integer("money");
            $table->integer("total_money");
            $table->integer("total_play");
            $table->string('comment');
            $table->bigInteger("friend_code");
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
        Schema::dropIfExists('user_status');
    }
}
