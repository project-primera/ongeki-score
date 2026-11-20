<?php

namespace Tests\Feature;

use App\Facades\Slack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rating ページ（/user/{id}/rating）に出る統計と RATING を確認する。
 *
 * どの値も小数 3 桁で、4 桁目以下は切り捨てる。
 * RATING は 3 つの枠の寄与値を足した値。ベスト枠とプラチナスコア枠の寄与値は 50 曲の平均で、
 * 新曲枠の寄与値は「単曲値 ÷ 5 を 1 曲ごとに切り捨てた値」の 10 曲の平均。
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
            // レート値に使うのは☆の数で、プラチナスコアそのものは使わない
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
        // 新曲枠: 16.751 ÷ 10 = 1.6751、寄与値は 16.751 ÷ 5 = 3.3502 → 3.350 を 10 で割る
        $this->assertStringContainsString('新曲枠|1.675|10|16.751|16.751|16.751|0.335|', $text);
        // ベスト枠: 33.400 ÷ 50 = 0.668
        $this->assertStringContainsString('ベスト枠|0.668|50|17.300|16.100|33.400|0.668|', $text);
        // プラチナスコア枠: 1.065 ÷ 50 = 0.0213
        $this->assertStringContainsString('プラチナスコア枠|0.021|50|1.065|1.065|1.065|0.021|', $text);
        $this->assertStringContainsString('RATING:1.024=(0.335+0.668+0.021)', $text);
    }

    public function test_new_frame_contribution_truncates_each_song_before_averaging(): void
    {
        // 定数 14.7 の 10 曲。969,999 点は 14.699、970,240 点は 14.709
        $scores = [969999, 970240, 970240, 970240, 970240, 970240, 970240, 970240, 970240, 970240];
        foreach ($scores as $i => $score) {
            $this->addMasterScore($this->addMusic('New ' . $i, 14.7, self::CURRENT_VERSION), $score);
        }

        $text = $this->text($this->ratingPage());

        // 14.709 ÷ 5 = 2.9418 → 2.941 が 9 曲、14.699 ÷ 5 → 2.939。合計 29.408 ÷ 10 = 2.9408 → 2.940。
        // 合計 147.080 をまとめて 50 で割った 2.941 にはならない
        $this->assertStringContainsString('新曲枠|14.708|10|14.709|14.699|147.080|2.940|', $text);
        $this->assertStringContainsString('RATING:2.940=(2.940+0.000+0.000)', $text);
    }

    public function test_each_frame_is_truncated_before_the_three_are_added(): void
    {
        // 定数 14.6・971,307 点は 14.649。ベスト枠はこの 2 曲、プラチナスコア枠は 14.6・☆5（1.065）の 1 曲
        $oldA = $this->addMusic('Old A', 14.6, 1);
        $oldB = $this->addMusic('Old B', 14.6, 1);
        $this->addMasterScore($oldA, 971307);
        $this->addMasterScore($oldB, 971307);
        $this->addPlatinum(1, 'Old A', 5);

        $text = $this->text($this->ratingPage());

        // ベスト枠: 29.298 ÷ 50 = 0.58596 → 0.585（1 曲ごとに 50 で割って切り捨てると 0.292 × 2 = 0.584）
        $this->assertStringContainsString('ベスト枠|0.585|50|14.649|14.649|29.298|0.585|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.021|50|1.065|1.065|1.065|0.021|', $text);
        // 枠ごとに切り捨ててから足す。合計してから割ると (29.298 + 1.065) ÷ 50 = 0.60726 → 0.607 になる
        $this->assertStringContainsString('RATING:0.606=(0.000+0.585+0.021)', $text);
    }

    public function test_best_frame_is_averaged_without_truncating_each_song(): void
    {
        // 新曲枠なら 1 曲ごとに切り捨てて 2.940 になる組み合わせ（定数 14.7 の 14.709 が 9 曲と 14.699 が 1 曲）
        $scores = [969999, 970240, 970240, 970240, 970240, 970240, 970240, 970240, 970240, 970240];
        foreach ($scores as $i => $score) {
            $this->addMasterScore($this->addMusic('Old ' . $i, 14.7, 1), $score);
        }

        $text = $this->text($this->ratingPage());

        // ベスト枠は合計 147.080 を 50 で割るだけ: 2.9416 → 2.941
        $this->assertStringContainsString('ベスト枠|2.941|50|14.709|14.699|147.080|2.941|', $text);
        $this->assertStringContainsString('RATING:2.941=(0.000+2.941+0.000)', $text);
    }

    public function test_songs_are_ranked_by_the_rate_itself_not_by_how_it_is_shown(): void
    {
        // 定数 10.0・1,007,500 点は 12.050 で、スコアが高いので装飾付きで表示される
        $this->addMasterScore($this->addMusic('New Decorated', 10.0, self::CURRENT_VERSION), 1007500);
        for ($i = 1; $i <= 10; $i++) {
            $this->addMasterScore($this->addMusic('New ' . $i, 15.0, self::CURRENT_VERSION), 970000);
        }

        $html = $this->ratingPage();
        $text = $this->text($html);

        // 15.000 の 10 曲が枠に入り、12.050 の曲は入らない
        $this->assertStringContainsString('新曲枠|15.000|10|15.000|15.000|150.000|3.000|', $text);
        $this->assertStringNotContainsString('NewDecorated', $text);
        $this->assertStringNotContainsString('12.050', $html);
    }

    public function test_best_frame_is_ranked_by_the_rate_itself_too(): void
    {
        $this->addMasterScore($this->addMusic('Old Decorated', 10.0, 1), 1007500);
        for ($i = 1; $i <= 50; $i++) {
            $this->addMasterScore($this->addMusic('Old ' . $i, 15.0, 1), 970000);
        }

        $html = $this->ratingPage();
        $text = $this->text($html);

        $this->assertStringContainsString('ベスト枠|15.000|50|15.000|15.000|750.000|15.000|', $text);
        $this->assertStringNotContainsString('OldDecorated', $text);
        $this->assertStringNotContainsString('12.050', $html);
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
        $this->assertStringContainsString('ベスト枠|14.700|50|14.700|14.700|735.000|14.700|', $text);
        $this->assertStringContainsString('RATING:14.700=(0.000+14.700+0.000)', $text);
    }

    public function test_only_the_top_10_songs_count_in_the_new_frame(): void
    {
        // 定数 14.0, 14.1, …, 15.0 の 11 曲を低い順に登録する。970,000 点のレート値は定数と同じ
        for ($i = 0; $i <= 10; $i++) {
            $this->addMasterScore($this->addMusic('New ' . $i, (140 + $i) / 10, self::CURRENT_VERSION), 970000);
        }

        $text = $this->text($this->ratingPage());

        // 14.1 から 15.0 までの 10 曲で 145.500。14.0 の曲は入らない
        $this->assertStringContainsString('新曲枠|14.550|10|15.000|14.100|145.500|2.910|', $text);
        $this->assertStringContainsString('1|New10|Mas|15.0|970,000||15.000|2|New9|Mas|14.9|970,000||14.900|', $text);
        $this->assertStringContainsString('10|New1|Mas|14.1|970,000||14.100|', $text);
        $this->assertStringNotContainsString('New0|', $text);
    }

    public function test_platinum_rate_depends_on_the_number_of_stars(): void
    {
        $this->addSampleScores();
        $this->addPlatinum(2, 'Old B', 3);

        $text = $this->text($this->ratingPage());

        // 15.0² × 3 / 1000 = 0.675
        $this->assertStringContainsString('2|OldB|Mas|15.0|1,000|3|0.675|', $text);
        // 1.065 + 0.675 = 1.740、1.740 ÷ 50 = 0.0348
        $this->assertStringContainsString('プラチナスコア枠|0.034|50|1.065|0.675|1.740|0.034|', $text);
    }

    public function test_only_50_songs_count_in_the_platinum_frame(): void
    {
        $this->addMusic('Old A', 14.6, 1);
        for ($rank = 1; $rank <= 51; $rank++) {
            $this->addPlatinum($rank, 'Old A', 1);
        }

        $text = $this->text($this->ratingPage());

        // 14.6² × 1 / 1000 = 0.21316 → 0.213。50 曲で 10.650（51 曲なら 10.863 で、平均は 0.217 になる）
        $this->assertStringContainsString('プラチナスコア枠|0.213|50|0.213|0.213|10.650|0.213|', $text);
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

        $this->assertStringContainsString('新曲枠|0.000|10|0.000|0.000|0.000|0.000|', $text);
        $this->assertStringContainsString('ベスト枠|0.000|50|0.000|0.000|0.000|0.000|', $text);
        $this->assertStringContainsString('プラチナスコア枠|0.000|50|0.000|0.000|0.000|0.000|', $text);
        $this->assertStringContainsString('RATING:0.000=(0.000+0.000+0.000)', $text);
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
        $this->assertStringContainsString('プラチナスコア枠|0.021|50|1.065|0.000|1.065|0.021|', $text);
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
