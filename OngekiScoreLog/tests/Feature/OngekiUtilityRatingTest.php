<?php

namespace Tests\Feature;

use App\Facades\OngekiUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 曲名と難易度から譜面定数を引いて、単曲のレート値（1/1000 単位の整数）を返す部分を確認する。
 * 式そのものは RatingCalculatorTest で確かめている。
 */
class OngekiUtilityRatingTest extends TestCase
{
    use RefreshDatabase;

    private int $musicId = 0;

    /**
     * @param array<string, mixed> $columns
     */
    private function addMusic(string $title, array $columns, string $artist = 'artist', string $genre = 'genre'): void
    {
        $id = ++$this->musicId;

        DB::table('music_datas')->insert($columns + [
            'id' => $id,
            'title' => $title,
            'genre' => $genre,
            'artist' => $artist,
            'unique_id' => 'music' . $id,
        ]);
    }

    public function test_technical_rating_uses_the_extra_level_of_the_difficulty(): void
    {
        $this->addMusic('Utility A', [
            'expert_level' => 12,
            'expert_extra_level' => 12.3,
            'master_level' => 14.5,
            'master_extra_level' => 14.7,
        ]);

        // 14.700 + 1.750 + 0.300
        $this->assertSame(16750, OngekiUtility::technicalRatingMilli('Utility A', 3, 1007500, '', 'genre', 'artist'));
        // 12.300 + 2.000 + 0.300 + 0.400
        $this->assertSame(15000, OngekiUtility::technicalRatingMilli('Utility A', 2, 1010000, 'FB/AB+', 'genre', 'artist'));
        // 難易度は列名でも指定できる
        $this->assertSame(16750, OngekiUtility::technicalRatingMilli('Utility A', 'master_extra_level', 1007500, '', 'genre', 'artist'));
    }

    public function test_rating_is_zero_when_the_difficulty_does_not_exist(): void
    {
        $this->addMusic('Utility B', ['master_level' => 14.5, 'master_extra_level' => 14.7]);

        $this->assertSame(0, OngekiUtility::technicalRatingMilli('Utility B', 10, 1010000, 'FB/AB+', 'genre', 'artist'));
        $this->assertSame(0, OngekiUtility::platinumRatingMilli('Utility B', 10, 5, 'genre', 'artist'));
    }

    public function test_estimated_extra_level_is_used_when_it_is_not_registered(): void
    {
        // レベル 14+ で譜面定数が未登録なら、14.7 として扱う
        $this->addMusic('Utility C', ['master_level' => 14.5]);

        $this->assertSame(14700, OngekiUtility::technicalRatingMilli('Utility C', 3, 970000, '', 'genre', 'artist'));
    }

    public function test_platinum_rating_is_truncated(): void
    {
        $this->addMusic('Utility D', ['master_level' => 14.5, 'master_extra_level' => 14.6]);

        // 14.6² × 5 / 1000 = 1.06580
        $this->assertSame(1065, OngekiUtility::platinumRatingMilli('Utility D', 3, 5, 'genre', 'artist'));
        // 14.6² × 3 / 1000 = 0.63948
        $this->assertSame(639, OngekiUtility::platinumRatingMilli('Utility D', 3, 3, 'genre', 'artist'));
    }

    public function test_songs_with_the_same_title_are_told_apart_by_artist_and_genre(): void
    {
        $this->addMusic('Utility Same', ['master_level' => 13, 'master_extra_level' => 13.0], 'artist 1', 'genre 1');
        $this->addMusic('Utility Same', ['master_level' => 14, 'master_extra_level' => 14.0], 'artist 2', 'genre 2');

        $this->assertSame(13000, OngekiUtility::technicalRatingMilli('Utility Same', 3, 970000, '', 'genre 1', 'artist 1'));
        $this->assertSame(14000, OngekiUtility::technicalRatingMilli('Utility Same', 3, 970000, '', 'genre 2', 'artist 2'));
    }

    public function test_unknown_song_throws(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        OngekiUtility::technicalRatingMilli('Utility Never Registered', 3, 1010000, '', 'genre', 'artist');
    }

    public function test_unknown_song_throws_for_platinum_rating(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        OngekiUtility::platinumRatingMilli('Utility Never Registered', 3, 5, 'genre', 'artist');
    }

    public function test_difficulty_must_be_an_integer_or_a_column_name(): void
    {
        $this->addMusic('Utility E', ['master_level' => 14.5, 'master_extra_level' => 14.7]);

        $this->expectException(\InvalidArgumentException::class);

        OngekiUtility::technicalRatingMilli('Utility E', 3.0, 1010000, '', 'genre', 'artist');
    }
}
