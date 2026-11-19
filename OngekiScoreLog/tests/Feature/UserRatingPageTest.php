<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rating ページ（/user/{id}/rating）に出る統計と RATING を確認する。
 */
class UserRatingPageTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ID = 1;

    /** これと同じ追加バージョンの曲が新曲枠、小さい曲がベスト枠になる */
    private const CURRENT_VERSION = 2;

    private int $musicId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config(['env.ongeki-version' => self::CURRENT_VERSION]);

        DB::table('users')->insert([
            'id' => self::USER_ID,
            'name' => 'user',
            'email' => 'user@example.test',
            'password' => 'x',
            'private' => 0,
        ]);
        DB::table('user_status')->insert([
            'user_id' => self::USER_ID,
            'trophy' => '',
            'level' => 1,
            'name' => 'player',
            'battle_point' => 0,
            'rating' => 12.345,
            'rating_max' => 12.345,
            'money' => 0,
            'total_money' => 0,
            'total_play' => 0,
            'comment' => '',
            'friend_code' => 0,
            'unique_id' => 'status',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
        DB::table('user_informations')->insert([
            'user_id' => self::USER_ID,
            'is_standard_plan' => 1,
            'is_premium_plan' => 1,
            'unique_id' => 'information',
        ]);
    }

    /**
     * MASTER だけを持つ曲を足して、曲の ID を返す
     */
    private function addMusic(string $title, ?float $masterExtraLevel, int $addedVersion): int
    {
        $id = ++$this->musicId;

        DB::table('music_datas')->insert([
            'id' => $id,
            'title' => $title,
            'genre' => 'genre',
            'artist' => 'artist',
            'master_level' => 14,
            'master_extra_level' => $masterExtraLevel,
            'normal_added_version' => $addedVersion,
            'unique_id' => 'music' . $id,
        ]);

        return $id;
    }

    /**
     * @param array{full_bell?: int, full_combo?: int, all_break?: int} $lamp
     */
    private function addMasterScore(int $musicId, int $technicalScore, array $lamp = []): void
    {
        DB::table('score_datas')->insert($lamp + [
            'user_id' => self::USER_ID,
            'song_id' => $musicId,
            'difficulty' => 3,
            'over_damage_high_score' => 0,
            'battle_high_score' => 1,
            'technical_high_score' => $technicalScore,
            'full_bell' => 0,
            'full_combo' => 0,
            'all_break' => 0,
            'platinum_score' => 0,
            'generation' => 1,
            'unique_id' => 'score' . $musicId,
        ]);
    }

    private function addPlatinum(int $rank, string $title, int $star): void
    {
        DB::table('rating_platinum_musics')->insert([
            'user_id' => self::USER_ID,
            'rank' => $rank,
            'title' => $title,
            'genre' => 'genre',
            'artist' => 'artist',
            'difficulty' => 3,
            'platinum_score' => 1000,
            'star' => $star,
            'unique_id' => 'platinum' . $rank,
        ]);
    }

    /**
     * 新曲枠 1 曲・ベスト枠 2 曲・プラチナスコア枠 1 曲のデータを入れる
     */
    private function addSampleScores(): void
    {
        $new = $this->addMusic('New A', 14.7, self::CURRENT_VERSION);
        $oldA = $this->addMusic('Old A', 14.6, 1);
        $oldB = $this->addMusic('Old B', 15.0, 1);

        $this->addMasterScore($new, 1007500);
        $this->addMasterScore($oldA, 1010000, ['full_bell' => 1, 'full_combo' => 1, 'all_break' => 1]);
        $this->addMasterScore($oldB, 995000);

        $this->addPlatinum(1, 'Old A', 5);
    }

    private function ratingPage(): string
    {
        return $this->get('/user/' . self::USER_ID . '/rating')->assertStatus(200)->assertViewIs('user_rating')->getContent();
    }

    /**
     * タグと空白を取り除いて、表の行を「セル|セル|…」の形で見られるようにする
     */
    private function text(string $html): string
    {
        $text = preg_replace('#</t[dh]>#', '|', $html);
        $text = html_entity_decode(strip_tags($text));

        return preg_replace('/\s+/u', '', $text);
    }

    public function test_shows_statistics_of_each_frame_and_the_rating(): void
    {
        $this->addSampleScores();

        $text = $this->text($this->ratingPage());

        $this->assertStringContainsString('新曲枠|1.675|10|16.750|16.750|16.750|0.335|', $text);
        $this->assertStringContainsString('ベスト枠|0.668|50|17.300|16.100|33.400|0.668|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.021|50|1.066|1.066|1.066|0.021|', $text);
        $this->assertStringContainsString('RATING:1.024=(0.335+0.668+0.021)', $text);
    }

    public function test_does_not_output_a_row_that_is_not_shown(): void
    {
        $this->addSampleScores();

        $this->assertStringNotContainsString('レート対象曲平均', $this->ratingPage());
    }
}
