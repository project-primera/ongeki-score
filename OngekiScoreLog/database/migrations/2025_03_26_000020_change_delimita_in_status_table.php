<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDelimitaInStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_status', function (Blueprint $table) {
            $table->decimal("rating", 5, 3)->change();
            $table->decimal("rating_max", 5, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_status', function (Blueprint $table) {
            $table->decimal("rating", 4, 2)->change();
            $table->decimal("rating_max", 4, 2)->change();
        });
    }
}
