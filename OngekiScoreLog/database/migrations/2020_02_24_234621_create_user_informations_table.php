<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_informations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id");
            $table->tinyInteger('is_standard_plan')->default(0);
            $table->tinyInteger('is_premium_plan')->default(0);
            $table->string('unique_id');
            $table->timestamps();
        });

        // 以前の課金情報を削除する
        (new \App\User())->setRoleAllUser(0, 2);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_informations');
    }
}
