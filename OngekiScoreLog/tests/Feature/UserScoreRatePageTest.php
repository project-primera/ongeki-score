<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 詳細・Technical・更新差分ページに出る単曲のレート値を確認する。
 *
 * 譜面定数 14.7 の譜面で、983,333 点は 14.700 + 0.499 = 15.199、990,000 点は 14.700 + 0.750 + 0.100 = 15.550。
 */
class UserScoreRatePageTest extends TestCase
{
    use RefreshDatabase;

    private const USER_ID = 1;

    private const MUSIC_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();

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
            'rating' => 0,
            'rating_max' => 0,
            'money' => 0,
            'total_money' => 0,
            'total_play' => 0,
            'comment' => '',
            'friend_code' => 0,
            'unique_id' => 'status',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
        DB::table('music_datas')->insert([
            'id' => self::MUSIC_ID,
            'title' => 'Rate Song',
            'genre' => 'genre',
            'artist' => 'artist',
            'master_level' => 14.5,
            'master_extra_level' => 14.7,
            'normal_added_version' => 1,
            'unique_id' => 'music',
        ]);
    }

    private function setPremiumPlan(bool $premium): void
    {
        DB::table('user_informations')->insert([
            'user_id' => self::USER_ID,
            'is_standard_plan' => 1,
            'is_premium_plan' => $premium ? 1 : 0,
            'unique_id' => 'information',
        ]);
    }

    private function addMasterScore(int $generation, int $technicalScore): void
    {
        DB::table('score_datas')->insert([
            'user_id' => self::USER_ID,
            'song_id' => self::MUSIC_ID,
            'difficulty' => 3,
            'over_damage_high_score' => 10,
            'battle_high_score' => 1000,
            'technical_high_score' => $technicalScore,
            'full_bell' => 0,
            'full_combo' => 0,
            'all_break' => 0,
            'platinum_score' => 0,
            'generation' => $generation,
            'unique_id' => 'score' . $generation,
            'created_at' => '2026-01-0' . $generation . ' 00:00:00',
            'updated_at' => '2026-01-0' . $generation . ' 00:00:00',
        ]);
    }

    private function page(string $path): string
    {
        return $this->get('/user/' . self::USER_ID . '/' . $path)->assertStatus(200)->getContent();
    }

    /**
     * タグと空白を取り除く
     */
    private function text(string $html): string
    {
        return preg_replace('/\s+/u', '', html_entity_decode(strip_tags($html)));
    }

    public function test_details_page_shows_the_rate_with_three_decimals(): void
    {
        $this->setPremiumPlan(true);
        $this->addMasterScore(1, 983333);

        $html = $this->page('details');

        $this->assertStringContainsString('<td>15.199</td>', $html);
        // 並べ替え用の隠しセル
        $this->assertStringContainsString('<td class="table-hidden-data sort_rate">15.199</td>', $html);
    }

    public function test_technical_page_shows_the_rate_with_three_decimals(): void
    {
        $this->setPremiumPlan(true);
        $this->addMasterScore(1, 983333);

        $html = $this->page('technical');

        $this->assertStringContainsString('<td>15.199</td>', $html);
        $this->assertStringContainsString('<td class="table-hidden-data sort_rate">15.199</td>', $html);
    }

    public function test_rate_is_hidden_without_premium_plan(): void
    {
        $this->setPremiumPlan(false);
        $this->addMasterScore(1, 983333);

        $html = $this->page('details');

        $this->assertStringContainsString('<td>|||||||||</td>', $html);
        $this->assertStringNotContainsString('15.199', $html);
    }

    public function test_progress_page_shows_the_rate_and_its_difference(): void
    {
        $this->setPremiumPlan(true);
        $this->addMasterScore(1, 983333);
        $this->addMasterScore(2, 990000);

        $text = $this->text($this->page('progress'));

        // 15.550 - 15.199
        $this->assertStringContainsString('SingleRating15.550+0.351', $text);
    }

    public function test_progress_page_shows_the_rate_itself_for_a_newly_played_chart(): void
    {
        $this->setPremiumPlan(true);
        $this->addMasterScore(1, 983333);

        $text = $this->text($this->page('progress'));

        $this->assertStringContainsString('SingleRating15.199+15.199', $text);
    }
}
