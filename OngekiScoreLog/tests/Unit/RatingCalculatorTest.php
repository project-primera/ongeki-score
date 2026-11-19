<?php

namespace Tests\Unit;

use App\Services\RatingCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * レート値の計算を確認する。
 * 値はすべて整数で、譜面定数は 1/100、単曲値は 1/1000 を単位にしている（定数 14.7 は 1470、レート値 17.400 は 17400）。
 * 枠の平均値・寄与値・RATING は 1/100000 を単位にしている（21.63874 は 2163874）。
 */
class RatingCalculatorTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?int}>
     */
    public static function extraLevels(): array
    {
        return [
            'MySQL の decimal（文字列）' => ['14.70', 1470],
            '小数（sqlite・推定値）' => [14.7, 1470],
            '整数' => [15, 1500],
            '2 進数で表せない小数（1.1）' => [1.1, 110],
            '2 進数で表せない小数（13.9）' => ['13.90', 1390],
            '0' => ['0.00', 0],
            '未登録' => [null, null],
        ];
    }

    #[DataProvider('extraLevels')]
    public function test_extra_level_is_converted_to_an_integer_of_hundredths(mixed $extraLevel, ?int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::extraLevelToCenti($extraLevel));
    }

    /**
     * 譜面定数 14.7 の譜面。期待値の内訳は「スコア補正 + ランク補正 + ランプ補正」
     *
     * @return array<string, array{int, string, int}>
     */
    public static function technicalScores(): array
    {
        return [
            '1,010,000 FB/AB+（+2.000 +0.300 +0.400）' => [1010000, 'FB/AB+', 17400],
            '1,010,000 AB+（+2.000 +0.300 +0.350）' => [1010000, 'AB+', 17350],
            '1,010,000（+2.000 +0.300）' => [1010000, '', 17000],
            '1,009,999（+1.999 +0.300）' => [1009999, '', 16999],
            '1,007,510 は 10 点で +0.001' => [1007510, '', 16751],
            '1,007,509 はまだ上がらない' => [1007509, '', 16750],
            '1,007,500（+1.750 +0.300）' => [1007500, '', 16750],
            '1,007,499（+1.749 +0.200）' => [1007499, '', 16649],
            '1,000,015 は 15 点で +0.001' => [1000015, '', 16151],
            '1,000,014 はまだ上がらない' => [1000014, '', 16150],
            '1,000,000（+1.250 +0.200）' => [1000000, '', 16150],
            '999,999（+1.249 +0.100）' => [999999, '', 16049],
            '990,020 は 20 点で +0.001' => [990020, '', 15551],
            '990,019 はまだ上がらない' => [990019, '', 15550],
            '990,000（+0.750 +0.100）' => [990000, '', 15550],
            '989,999（+0.749）' => [989999, '', 15449],
            '970,000（±0）' => [970000, '', 14700],
            '969,999（-0.001）' => [969999, '', 14699],
            '969,983（-0.001）' => [969983, '', 14699],
            '969,982（-0.002）' => [969982, '', 14698],
            '935,000（-2.000）' => [935000, '', 12700],
            '900,000（-4.000）' => [900000, '', 10700],
            '899,999（-4.001）' => [899999, '', 10699],
            '850,000（-5.000）' => [850000, '', 9700],
            '800,000（-6.000）' => [800000, '', 8700],
            '799,999 は 8.700 × 299,999 / 300,000 の切り捨て' => [799999, '', 8699],
            '650,000 は 800,000 のときの半分' => [650000, '', 4350],
            '500,000' => [500000, '', 0],
            '499,999' => [499999, '', 0],
            '0' => [0, '', 0],
            '1,005,000 FB/FC（+1.583 +0.200 +0.150）' => [1005000, 'FB/FC', 16633],
            '995,000 FB（+1.000 +0.100 +0.050）' => [995000, 'FB', 15850],
        ];
    }

    #[DataProvider('technicalScores')]
    public function test_technical_rating_follows_the_score_table(int $technicalScore, string $lamp, int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::technical(1470, $technicalScore, $lamp));
    }

    /**
     * 970,000〜989,999 は 20,000 点で +0.750。0.001 あたり 80/3 点（26.666… 点）になる
     *
     * @return array<string, array{int, int}>
     */
    public static function scoresBetween970000And990000(): array
    {
        return [
            '26 点ではまだ上がらない' => [970026, 14700],
            '27 点で +0.001' => [970027, 14701],
            '80 点でちょうど +0.003' => [970080, 14703],
            '13,333 点は +0.499（13,333 × 3 / 80 = 499.98…）' => [983333, 15199],
            '13,334 点で +0.500' => [983334, 15200],
            '19,999 点は +0.749' => [989999, 15449],
        ];
    }

    #[DataProvider('scoresBetween970000And990000')]
    public function test_technical_rating_rises_by_one_thousandth_every_80_thirds_of_a_point(int $technicalScore, int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::technical(1470, $technicalScore, ''));
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function lamps(): array
    {
        return [
            'FB/AB+' => ['FB/AB+', 400],
            'AB+' => ['AB+', 350],
            'FB/AB' => ['FB/AB', 350],
            'AB' => ['AB', 300],
            'FB/FC' => ['FB/FC', 150],
            'FC' => ['FC', 100],
            'FB' => ['FB', 50],
            'なし' => ['', 0],
            '知らない文字列は補正なし' => ['??', 0],
        ];
    }

    #[DataProvider('lamps')]
    public function test_lamp_bonus_is_added(string $lamp, int $bonus): void
    {
        // 970,000 点はスコア補正もランク補正も 0 なので、定数との差がそのままランプ補正になる
        $this->assertSame(14700 + $bonus, RatingCalculator::technical(1470, 970000, $lamp));
    }

    /**
     * @return array<string, array{int, int, string, int}>
     */
    public static function lowExtraLevels(): array
    {
        return [
            '定数 0 は補正も加えずに 0' => [0, 1010000, 'FB/AB+', 0],
            '定数 1.0 の理論値' => [100, 1010000, '', 3300],
            '定数 5.0・900,000（5.000 - 4.000）' => [500, 900000, '', 1000],
            '定数 5.0・850,000（5.000 - 5.000）' => [500, 850000, '', 0],
            '定数 5.0・800,000 は負になるので 0' => [500, 800000, '', 0],
            '負を 0 にしてからランプ補正を足す' => [500, 800000, 'FB', 50],
            '定数 5.0・700,000 は 0' => [500, 700000, '', 0],
            '定数 6.0・650,000 は 0' => [600, 650000, '', 0],
            '定数 6.1・799,999（0.100 × 299,999 / 300,000 の切り捨て）' => [610, 799999, '', 99],
        ];
    }

    #[DataProvider('lowExtraLevels')]
    public function test_technical_rating_never_goes_below_zero(int $extraLevelCenti, int $technicalScore, string $lamp, int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::technical($extraLevelCenti, $technicalScore, $lamp));
    }

    /**
     * @return array<string, array{int}>
     */
    public static function extraLevelsForMonotonicity(): array
    {
        return [
            '1.0' => [100],
            '5.9' => [590],
            '6.0' => [600],
            '6.1' => [610],
            '14.7' => [1470],
            '15.9' => [1590],
        ];
    }

    #[DataProvider('extraLevelsForMonotonicity')]
    public function test_technical_rating_never_drops_when_the_score_rises(int $extraLevelCenti): void
    {
        $previous = 0;
        $drops = [];

        for ($technicalScore = 0; $technicalScore <= 1010000; $technicalScore++) {
            $rating = RatingCalculator::technical($extraLevelCenti, $technicalScore, '');
            if ($rating < $previous) {
                $drops[] = $technicalScore;
            }
            $previous = $rating;
        }

        $this->assertSame([], $drops);
    }

    /**
     * 譜面定数² × ☆ ÷ 1000 の小数 4 桁目以下を切り捨てる
     *
     * @return array<string, array{int, int, int}>
     */
    public static function platinumStars(): array
    {
        return [
            '14.6 ☆5（1.06580）' => [1460, 5, 1065],
            '14.1 ☆1（0.19881）' => [1410, 1, 198],
            '15.7 ☆5（1.23245）' => [1570, 5, 1232],
            '15.4 ☆5（1.18580）' => [1540, 5, 1185],
            '15.0 ☆3（0.67500）' => [1500, 3, 675],
            '☆6 以上は 5 として扱う' => [1570, 6, 1232],
            '☆0 は 0' => [1570, 0, 0],
            '1.0 ☆1（0.00100）' => [100, 1, 1],
            '定数 0 は 0' => [0, 5, 0],
        ];
    }

    #[DataProvider('platinumStars')]
    public function test_platinum_rating_is_truncated_to_three_decimals(int $extraLevelCenti, int $star, int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::platinum($extraLevelCenti, $star));
    }

    /**
     * 枠ごとの単曲値の合計と、そこから求める値。新曲枠は 10 曲、ベスト枠とプラチナスコア枠は 50 曲
     *
     * @return array<string, array{int, int, int, int, int, int, int, int}>
     */
    public static function frameTotals(): array
    {
        return [
            // 新曲枠の合計, ベスト枠の合計, プラチナスコア枠の合計, 新曲枠の平均, 新曲枠の寄与, ベスト枠の寄与, プラチナスコア枠の寄与, RATING
            '170.123 / 850.457 / 61.357' => [170123, 850457, 61357, 1701230, 340246, 1700914, 122714, 2163874],
            'float で足すと 19.915999… になる 3.054 + 16.203 + 0.659' => [152700, 810150, 32950, 1527000, 305400, 1620300, 65900, 1991600],
            '177.000 / 885.000 / 61.622' => [177000, 885000, 61622, 1770000, 354000, 1770000, 123244, 2247244],
            'すべて 0' => [0, 0, 0, 0, 0, 0, 0, 0],
        ];
    }

    #[DataProvider('frameTotals')]
    public function test_rating_is_the_sum_of_three_frames_divided_by_50(
        int $new,
        int $best,
        int $platinum,
        int $newAverage,
        int $newContribution,
        int $bestContribution,
        int $platinumContribution,
        int $rating,
    ): void {
        $this->assertSame($newAverage, RatingCalculator::average($new, 10));
        $this->assertSame($newContribution, RatingCalculator::contribution($new));
        $this->assertSame($bestContribution, RatingCalculator::average($best, 50));
        $this->assertSame($bestContribution, RatingCalculator::contribution($best));
        $this->assertSame($platinumContribution, RatingCalculator::average($platinum, 50));
        $this->assertSame($platinumContribution, RatingCalculator::contribution($platinum));
        $this->assertSame($rating, RatingCalculator::rating($new, $best, $platinum));

        // 小数 3 桁の合計を 50 で割った値は小数 5 桁で割り切れるので、枠ごとの寄与を足すと RATING になる
        $this->assertSame($rating, $newContribution + $bestContribution + $platinumContribution);
    }

    public function test_average_divides_by_the_size_of_the_frame_even_when_songs_are_missing(): void
    {
        // 10 曲の枠に 16.750 の 1 曲だけ
        $this->assertSame(167500, RatingCalculator::average(16750, 10));
    }

    public function test_digits_below_the_fifth_decimal_are_truncated(): void
    {
        // 0.001 / 3 = 0.000333…
        $this->assertSame(33, RatingCalculator::average(1, 3));
        // 0.002 / 3 = 0.000666…（四捨五入なら 67）
        $this->assertSame(66, RatingCalculator::average(2, 3));
    }

    public function test_total_is_expressed_in_the_unit_of_aggregated_values(): void
    {
        $this->assertSame(85045700, RatingCalculator::milliToAggregate(850457));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function milliValues(): array
    {
        return [
            '17.400' => [17400, '17.400'],
            '100.000' => [100000, '100.000'],
            '1 未満' => [50, '0.050'],
            '最小の刻み' => [1, '0.001'],
            '0' => [0, '0.000'],
            '負の値' => [-1, '-0.001'],
            '負の値（整数部あり）' => [-1500, '-1.500'],
        ];
    }

    #[DataProvider('milliValues')]
    public function test_single_rating_is_formatted_with_three_decimals(int $milli, string $expected): void
    {
        $this->assertSame($expected, RatingCalculator::formatMilli($milli));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function aggregateValues(): array
    {
        return [
            '21.63874' => [2163874, '21.63874'],
            '850.45700' => [85045700, '850.45700'],
            '1 未満' => [66, '0.00066'],
            '0' => [0, '0.00000'],
            '負の値' => [-5, '-0.00005'],
        ];
    }

    #[DataProvider('aggregateValues')]
    public function test_aggregated_value_is_formatted_with_five_decimals(int $value, string $expected): void
    {
        $this->assertSame($expected, RatingCalculator::formatAggregate($value));
    }
}
