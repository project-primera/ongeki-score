<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rating ページ（/user/{id}/rating）に出る統計と RATING を確認する。
 *
 * 単曲のレート値は小数 3 桁、枠の平均値・合計・寄与値と RATING は小数 5 桁で出る。
 * RATING は 3 つの枠の合計を 50 で割った値で、枠ごとの寄与値を足したものと一致する。
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
     *
     * - New A: 定数 14.7・1,007,510 点 → 14.700 + 1.751 + 0.300 = 16.751
     * - Old A: 定数 14.6・1,010,000 点 FB/AB+ → 14.600 + 2.000 + 0.300 + 0.400 = 17.300
     * - Old B: 定数 15.0・995,000 点 → 15.000 + 1.000 + 0.100 = 16.100
     * - プラチナスコア枠の Old A: ☆5 → 14.6² × 5 / 1000 = 1.0658 → 1.065
     */
    private function addSampleScores(): void
    {
        $new = $this->addMusic('New A', 14.7, self::CURRENT_VERSION);
        $oldA = $this->addMusic('Old A', 14.6, 1);
        $oldB = $this->addMusic('Old B', 15.0, 1);

        $this->addMasterScore($new, 1007510);
        $this->addMasterScore($oldB, 995000);
        $this->addMasterScore($oldA, 1010000, ['full_bell' => 1, 'full_combo' => 1, 'all_break' => 1]);

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

        // 列は 平均値 | 対象曲数 | Max | Min | 合計レート値 | レート寄与値。曲が足りない枠も、枠の曲数で割る
        $this->assertStringContainsString('新曲枠|1.67510|10|16.751|16.751|16.75100|0.33502|', $text);
        $this->assertStringContainsString('ベスト枠|0.66800|50|17.300|16.100|33.40000|0.66800|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.02130|50|1.065|1.065|1.06500|0.02130|', $text);
        // (16.751 + 33.400 + 1.065) / 50
        $this->assertStringContainsString('RATING:1.02432=(0.33502+0.66800+0.02130)', $text);
    }

    public function test_lists_songs_of_each_frame_with_three_decimals(): void
    {
        $this->addSampleScores();

        $text = $this->text($this->ratingPage());

        $this->assertStringContainsString('1|NewA|Mas|14.7|1,007,510||16.751|', $text);
        $this->assertStringContainsString('1|OldA|Mas|14.6|1,000|5|1.065|', $text);
    }

    public function test_songs_are_listed_from_the_highest_rate(): void
    {
        $this->addSampleScores();

        $text = $this->text($this->ratingPage());

        // スコアを登録した順は Old B → Old A
        $this->assertStringContainsString('1|OldA|Mas|14.6|1,010,000|FB/AB+|17.300|2|OldB|Mas|15.0|995,000||16.100|', $text);
    }

    public function test_rates_are_decorated_by_score_and_stars(): void
    {
        $this->addSampleScores();

        $html = $this->ratingPage();

        $this->assertStringContainsString("<i><span class='upper-rating'>16.751</span></i>", $html);
        $this->assertStringContainsString("<i><span class='max-rating'>17.300</span></i>", $html);
        $this->assertStringContainsString("<i><span class='max-rating'>1.065</span></i>", $html);
    }

    public function test_only_the_top_50_songs_count_in_the_best_frame(): void
    {
        // 定数 14.0 の 1 曲は 51 番目になる。970,000 点のレート値は定数と同じ
        $this->addMasterScore($this->addMusic('Old Lowest', 14.0, 1), 970000);
        for ($i = 1; $i <= 50; $i++) {
            $this->addMasterScore($this->addMusic('Old ' . $i, 14.7, 1), 970000);
        }

        $text = $this->text($this->ratingPage());

        // 14.700 × 50 = 735.000
        $this->assertStringContainsString('ベスト枠|14.70000|50|14.700|14.700|735.00000|14.70000|', $text);
        $this->assertStringContainsString('RATING:14.70000=(0.00000+14.70000+0.00000)', $text);
    }

    public function test_estimated_extra_level_is_marked(): void
    {
        // レベル 14 で譜面定数が未登録なら、14.0 として計算する
        $this->addMasterScore($this->addMusic('Old Estimated', null, 1), 970000);

        $html = $this->ratingPage();

        $this->assertStringContainsString("<i><span class='estimated-rating'>14.0?</span></i>", $html);
        $this->assertStringContainsString("<i><span class='estimated-rating'>14.000</span></i>", $html);
    }

    public function test_everything_is_zero_without_scores(): void
    {
        $text = $this->text($this->ratingPage());

        $this->assertStringContainsString('新曲枠|0.00000|10|0.000|0.000|0.00000|0.00000|', $text);
        $this->assertStringContainsString('ベスト枠|0.00000|50|0.000|0.000|0.00000|0.00000|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.00000|50|0.000|0.000|0.00000|0.00000|', $text);
        $this->assertStringContainsString('RATING:0.00000=(0.00000+0.00000+0.00000)', $text);
    }

    public function test_unknown_song_in_the_platinum_frame_counts_as_zero(): void
    {
        Slack::spy();
        $this->addSampleScores();
        $this->addPlatinum(2, 'Rating Never Registered', 5);

        $html = $this->ratingPage();
        $text = $this->text($html);

        $this->assertStringContainsString('プラチナスコア枠に未知の楽曲が含まれるため、正常に計算を行えませんでした。', $html);
        $this->assertStringContainsString('2|(未知の楽曲)RatingNeverRegistered|-|-|1,000|5|0.000|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.02130|50|1.065|0.000|1.06500|0.02130|', $text);
        Slack::shouldHaveReceived('Notice')->once();
    }

    public function test_does_not_output_a_row_that_is_not_shown(): void
    {
        $this->addSampleScores();

        $this->assertStringNotContainsString('レート対象曲平均', $this->ratingPage());
    }

    public function test_does_not_show_the_rating_fetched_from_ongeki_net(): void
    {
        $this->addSampleScores();

        // user_status.rating に入れた値
        $this->assertStringNotContainsString('12.345', $this->ratingPage());
    }
}
