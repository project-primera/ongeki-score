<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Passport 6 の oauth_clients を Passport 13 の構造へ変換する。
 *
 * - 列は追加と変更だけを行い、drop はしない（user_id / redirect / personal_access_client /
 *   password_client は残す）。
 * - 既存の整数 id と発行済みトークンはそのまま使う（Passport::$clientUuids = false）。
 * - secret は Passport 13 がハッシュ化して比較するので、平文のものはハッシュ化する。
 * - client が 1 件もない新規 DB では、旧 passport:install と同じ 2 件（personal access /
 *   password grant）を作る。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (! Schema::hasColumn('oauth_clients', 'owner_type')) {
                $table->nullableMorphs('owner', after: 'user_id');
            }
            if (! Schema::hasColumn('oauth_clients', 'provider')) {
                $table->string('provider')->nullable()->after('secret');
            }
            if (! Schema::hasColumn('oauth_clients', 'redirect_uris')) {
                $table->text('redirect_uris')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('oauth_clients', 'grant_types')) {
                $table->text('grant_types')->nullable()->after('redirect_uris');
            }
        });

        // Passport 13 が新しい client を insert できるよう、旧列の NOT NULL を緩める
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('secret', 100)->nullable()->change();
            $table->text('redirect')->nullable()->change();
            $table->boolean('personal_access_client')->default(false)->change();
            $table->boolean('password_client')->default(false)->change();
        });

        foreach (DB::table('oauth_clients')->orderBy('id')->cursor() as $client) {
            if ($client->personal_access_client) {
                $grantTypes = ['personal_access'];
            } elseif ($client->password_client) {
                $grantTypes = ['password', 'refresh_token'];
            } else {
                $grantTypes = ['authorization_code', 'refresh_token'];
            }

            $secret = $client->secret;
            if (! is_null($secret) && ! Hash::isHashed($secret)) {
                $secret = Hash::make($secret);
            }

            DB::table('oauth_clients')->where('id', $client->id)->update([
                'owner_id' => $client->user_id,
                'owner_type' => is_null($client->user_id) ? null : config('auth.providers.users.model'),
                'secret' => $secret,
                'redirect_uris' => json_encode(array_values(array_filter(explode(',', (string) $client->redirect)))),
                'grant_types' => $client->grant_types ?? json_encode($grantTypes),
            ]);
        }

        if (DB::table('oauth_clients')->count() === 0) {
            $this->createDefaultClients();
        }

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->text('redirect_uris')->nullable(false)->change();
            $table->text('grant_types')->nullable(false)->change();
        });
    }

    /**
     * 旧 passport:install が作っていた 2 件の client を作る（新規 DB 向け）。
     */
    private function createDefaultClients(): void
    {
        $now = now();
        $name = config('app.name');

        DB::table('oauth_clients')->insert([
            [
                'user_id' => null,
                'owner_type' => null,
                'owner_id' => null,
                'name' => $name.' Personal Access Client',
                'secret' => Hash::make(\Illuminate\Support\Str::random(40)),
                'provider' => null,
                'redirect' => 'http://localhost',
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['personal_access']),
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => null,
                'owner_type' => null,
                'owner_id' => null,
                'name' => $name.' Password Grant Client',
                'secret' => Hash::make(\Illuminate\Support\Str::random(40)),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'redirect_uris' => json_encode(['http://localhost']),
                'grant_types' => json_encode(['password', 'refresh_token']),
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $personalAccessClientId = DB::table('oauth_clients')->where('personal_access_client', true)->value('id');
        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $personalAccessClientId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // 戻す場合は DB のバックアップから復元する
    }
};
