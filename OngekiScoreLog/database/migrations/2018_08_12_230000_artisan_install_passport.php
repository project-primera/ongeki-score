<?php

use Illuminate\Database\Migrations\Migration;

class ArtisanInstallPassport extends Migration
{
    /**
     * Run the migrations.
     *
     * 旧版はここで `passport:install` を実行していた（oauth 鍵の生成と client 2 件の作成）。
     * Passport 13 の `passport:install` は UUID の client を作るため呼ばない。
     * 新規 DB では 2026_10_22_000000_upgrade_oauth_tables_for_passport_13 が
     * 同じ内容の client を作る。鍵は `passport:keys` で別途用意する。
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
