<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserUpdateStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_update_status', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id");
            $table->string('hash');
            $table->timestamp('begin_at');
            $table->timestamps();
            $table->index('user_id');
            $table->index('hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_update_status');
    }
}
