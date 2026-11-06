<?php

namespace Tests\Feature;

use App\Facades\Slack;
use App\MusicData;
use App\User;
use App\UserUpdateStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * API v3 のスコア登録。
 * data は JSON → gzip → base64 の文字列で届き、復元後は v2 と同じ処理で保存される。
 * v3 の復元処理はコントリビューターの実装をそのまま移したものなので、
 * ここでは現状の挙動（gzip でないデータで 500 になる）も記録する。
 */
class ApiV3UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    private const HASH = 'hash-for-test';

    private User $user;

    private MusicData $music;

    protected function setUp(): void
    {
        parent::setUp();

        // status の Slack 通知と、未知曲の通知を送らない
        Slack::spy();
        // コントローラが $_SERVER から直接読む
        $_SERVER['HTTP_USER_AGENT'] = 'phpunit';

        $this->user = new User();
        $this->user->name = 'tester';
        $this->user->email = 'tester@example.test';
        $this->user->password = 'secret';
        $this->user->role = 1;
        $this->user->save();
        Passport::actingAs($this->user);

        UserUpdateStatus::create([
            'user_id' => $this->user->id,
            'hash' => self::HASH,
            'begin_at' => Carbon::parse('2026-11-06 00:00:00'),
            'generation' => 1,
        ]);

        $this->music = MusicData::create([
            'title' => 'テスト曲',
            'genre' => 'POPS＆ANIME',
            'artist' => 'テストアーティスト',
            'master_level' => 13.7,
            'lunatic_level' => 14.5,
            'unique_id' => 'seed',
        ]);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);

        parent::tearDown();
    }

    public function test_status_returns_a_hash_for_the_authenticated_user(): void
    {
        $response = $this->get('/api/v3/user/update/status');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'ok')
            ->assertJsonPath('id', $this->user->id)
            ->assertJsonPath('name', 'tester');
        $this->assertNotSame('', $response->json('hash'));
        $this->assertDatabaseHas('user_update_status', ['user_id' => $this->user->id, 'hash' => $response->json('hash')]);
    }

    public function test_scores_with_integer_difficulty_are_stored(): void
    {
        $response = $this->postScores([$this->song(['difficulty' => 3]), $this->song(['difficulty' => 10])]);

        $response->assertStatus(200)->assertJsonPath('isError', false);
        $this->assertDatabaseCount('score_datas', 2);
        $this->assertDatabaseHas('score_datas', [
            'user_id' => $this->user->id,
            'song_id' => $this->music->id,
            'difficulty' => 3,
            'generation' => 1,
            'battle_high_score' => 1000000,
            'technical_high_score' => 1005000,
            'platinum_score' => 1234,
        ]);
        $this->assertDatabaseHas('score_datas', ['song_id' => $this->music->id, 'difficulty' => 10]);
    }

    /** 復元したスコアデータはログに書かない */
    public function test_decoded_payload_is_not_written_to_the_log(): void
    {
        Log::spy();

        $this->postScores([$this->song(['difficulty' => 3])])->assertJsonPath('isError', false);

        Log::shouldNotHaveReceived('debug');
        Log::shouldNotHaveReceived('info');
    }

    public function test_response_echoes_the_decoded_data(): void
    {
        $this->postScores([$this->song(['difficulty' => 3])])
            ->assertJsonPath('data.0.title', 'テスト曲')
            ->assertJsonPath('data.0.difficulty', 3);
    }

    public function test_player_data_with_numeric_values_is_stored(): void
    {
        $response = $this->postUpdate([
            'hash' => self::HASH,
            'methodType' => '0',
            'data' => $this->gz([
                'trophy' => '称号',
                'level' => 50,
                'name' => 'tester',
                'battle_point' => 12345,
                'rating' => 15.5,
                'rating_max' => 16.0,
                'money' => 100,
                'total_money' => 2000,
                'total_play' => 300,
                'comment' => 'こめんと',
                'friend_code' => 123456789012,
            ]),
        ]);

        $response->assertStatus(200)->assertJsonPath('isError', false);
        $this->assertDatabaseHas('user_status', [
            'user_id' => $this->user->id,
            'level' => 50,
            'battle_point' => 12345,
            'total_play' => 300,
            'friend_code' => 123456789012,
        ]);
    }

    public function test_500_trophies_are_stored_in_one_request(): void
    {
        $trophies = [];
        for ($i = 1; $i <= 500; $i++) {
            $trophies[] = ['name' => '称号 '.$i, 'detail' => '詳細 '.$i, 'rank' => 'Gold'];
        }

        $response = $this->postUpdate(['hash' => self::HASH, 'methodType' => '2', 'data' => $this->gz($trophies)]);

        $response->assertStatus(200)->assertJsonPath('isError', false);
        $this->assertDatabaseCount('user_trophies', 500);
        $this->assertDatabaseHas('user_trophies', ['user_id' => $this->user->id, 'name' => '称号 500', 'grade' => 2]);
    }

    public function test_empty_array_stores_nothing(): void
    {
        $this->postScores([])->assertStatus(200)->assertJsonPath('isError', false);

        $this->assertDatabaseCount('score_datas', 0);
    }

    public function test_missing_data_is_rejected(): void
    {
        $this->postUpdate(['hash' => self::HASH, 'methodType' => '1'])
            ->assertStatus(200)
            ->assertJsonPath('isError', true)
            ->assertJsonPath('message', ['送信内容を確認できませんでした。']);
    }

    public function test_payload_that_is_not_json_is_rejected(): void
    {
        $this->postUpdate(['hash' => self::HASH, 'methodType' => '1', 'data' => base64_encode(gzencode('not json'))])
            ->assertStatus(200)
            ->assertJsonPath('isError', true)
            ->assertJsonPath('message', ['送信内容を確認できませんでした。']);
    }

    /** gzip でないデータは gzdecode() の警告が例外になり、現状は 500 で返る */
    public function test_payload_that_is_not_gzip_causes_a_server_error(): void
    {
        $this->postUpdate(['hash' => self::HASH, 'methodType' => '1', 'data' => base64_encode('hello')])
            ->assertStatus(500);

        $this->assertDatabaseCount('score_datas', 0);
    }

    public function test_unknown_hash_is_rejected(): void
    {
        $this->postUpdate(['hash' => 'unknown', 'methodType' => '1', 'data' => $this->gz([$this->song()])])
            ->assertStatus(200)
            ->assertJsonPath('isError', true)
            ->assertJsonPath('message', ['送信内容が正しくありません。']);
        $this->assertDatabaseCount('score_datas', 0);
    }

    /** v3 は難易度を整数で判定するので、v2 形式の文字列は受け付けない */
    public function test_string_difficulty_is_rejected(): void
    {
        $this->postScores([$this->song(['difficulty' => '3'])])
            ->assertStatus(200)
            ->assertJsonPath('isError', true)
            ->assertJsonPath('message.0', '未知の難易度が送信されました。(3)');
        $this->assertDatabaseCount('score_datas', 0);
    }

    /** JSON の boolean は 1 / 0 で保存される */
    public function test_boolean_flags_are_stored_as_integers(): void
    {
        $this->postScores([$this->song(['full_bell' => true, 'full_combo' => false, 'all_break' => true])])
            ->assertJsonPath('isError', false);

        $this->assertDatabaseHas('score_datas', [
            'song_id' => $this->music->id,
            'full_bell' => 1,
            'full_combo' => 0,
            'all_break' => 1,
        ]);
    }

    /** v3 のフラグは boolean で受け取るので、v2 形式の文字列 "true" は true 扱いしない */
    public function test_string_true_is_not_treated_as_a_flag(): void
    {
        $this->postScores([$this->song(['full_bell' => 'true', 'full_combo' => 'true', 'all_break' => 'true'])])
            ->assertJsonPath('isError', false);

        $this->assertDatabaseHas('score_datas', [
            'song_id' => $this->music->id,
            'full_bell' => 0,
            'full_combo' => 0,
            'all_break' => 0,
        ]);
    }

    public function test_payment_status_booleans_are_stored(): void
    {
        $response = $this->postUpdate([
            'hash' => self::HASH,
            'methodType' => '5',
            'data' => $this->gz(['isStandardPlan' => true, 'isPremiumPlan' => false]),
        ]);

        $response->assertStatus(200)->assertJsonPath('isError', false);
        $this->assertDatabaseHas('user_informations', [
            'user_id' => $this->user->id,
            'is_standard_plan' => 1,
            'is_premium_plan' => 0,
        ]);
    }

    /** JSON → gzip → base64（ブックマークレットの PostData.Post() と同じ形） */
    private function gz(mixed $payload): string
    {
        return base64_encode(gzencode(json_encode($payload, JSON_UNESCAPED_UNICODE)));
    }

    private function postUpdate(array $form): TestResponse
    {
        return $this->post('/api/v3/user/update', $form);
    }

    private function postScores(array $songs): TestResponse
    {
        return $this->postUpdate(['hash' => self::HASH, 'methodType' => '1', 'data' => $this->gz($songs)]);
    }

    private function song(array $overrides = []): array
    {
        return array_merge([
            'title' => 'テスト曲',
            'difficulty' => 3,
            'genre' => 'POPS＆ANIME',
            'level' => 13.7,
            'over_damage_high_score' => 123.45,
            'battle_high_score' => 1000000,
            'technical_high_score' => 1005000,
            'full_bell' => true,
            'full_combo' => false,
            'all_break' => false,
            'artist' => 'テストアーティスト',
            'platinum_score' => 1234,
            'star' => 3,
        ], $overrides);
    }
}
