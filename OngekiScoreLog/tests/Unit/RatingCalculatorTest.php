<?php

namespace Tests\Unit;

use App\Services\RatingCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * レート値の計算を確認する。
 * 値はすべて整数で、譜面定数は 1/100、レート値は 1/1000 を単位にしている（定数 14.7 は 1470、レート値 17.400 は 17400）。
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
            // 8.2 * 100 は 819.999…、10.2 * 100 は 1019.999… になる。切り捨てると 1 小さくなる
            '100 倍すると誤差が出る小数（8.2）' => [8.2, 820],
            '100 倍すると誤差が出る小数（"10.20"）' => ['10.20', 1020],
            '100 倍すると誤差が出る小数（2.3）' => [2.3, 230],
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

    public function test_every_score_between_970000_and_990000_is_truncated(): void
    {
        $wrong = [];

        for ($points = 0; $points < 20000; $points++) {
            $rise = RatingCalculator::technical(1470, 970000 + $points, '') - 14700;

            // 上昇分 r が「points × 0.750 / 20,000 の切り捨て」なら、r ≦ points × 3 / 80 < r + 1
            if (!($rise * 80 <= $points * 3 && $points * 3 < ($rise + 1) * 80)) {
                $wrong[] = 970000 + $points;
            }
        }

        $this->assertSame([], $wrong);
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
            '5.0' => [500],
            '5.9' => [590],
            '6.0' => [600],
            '6.1' => [610],
            '7.0' => [700],
            '10.0' => [1000],
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
            '☆が負でも 0' => [1570, -1, 0],
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
     * 新曲枠の寄与値。1 曲ごとに「単曲値 ÷ 5」の小数 4 桁目以下を切り捨ててから、10 曲の平均を切り捨てる
     *
     * @return array<string, array{int[], int}>
     */
    public static function newFrames(): array
    {
        return [
            // 14.699 ÷ 5 = 2.9398 → 2.939、14.701 ÷ 5 = 2.9402 → 2.940、ほかは 2.940。合計 29.399 ÷ 10 = 2.9399 → 2.939。
            // 合計 147.000 をまとめて 50 で割ると 2.940 になり、0.001 大きくなる
            '1 曲ごとに切り捨てる' => [[14701, 14700, 14700, 14700, 14700, 14700, 14700, 14700, 14700, 14699], 2939],
            // 14.709 ÷ 5 = 2.9418 → 2.941 が 9 曲、14.699 ÷ 5 → 2.939。合計 29.408 ÷ 10 = 2.9408 → 2.940。
            // まとめて割ると 147.080 ÷ 50 = 2.9416 → 2.941。1 曲ごとに 50 で割って切り捨てて足すと 2.937
            '1 曲ごとに割るのは 5 で、10 で割るのは合計' => [[14709, 14709, 14709, 14709, 14709, 14709, 14709, 14709, 14709, 14699], 2940],
            '全曲が 5 で割り切れる値なら、まとめて割るのと同じ' => [[17500, 17500, 17500, 17500, 17500, 17500, 17500, 17500, 17500, 17500], 3500],
            // 16.751 ÷ 5 = 3.3502 → 3.350、3.350 ÷ 10 = 0.335
            '曲が足りなくても枠の曲数で割る' => [[16751], 335],
            '曲が無い' => [[], 0],
        ];
    }

    /**
     * @param int[] $ratings
     */
    #[DataProvider('newFrames')]
    public function test_new_frame_contribution_truncates_each_song_before_averaging(array $ratings, int $expected): void
    {
        $this->assertSame($expected, RatingCalculator::newFrameContribution($ratings, 10));
    }

    public function test_average_is_truncated_to_three_decimals(): void
    {
        // 868.592 ÷ 50 = 17.37184
        $this->assertSame(17371, RatingCalculator::average(868592, 50));
        // 1.999 ÷ 50 = 0.03998（四捨五入なら 0.040）
        $this->assertSame(39, RatingCalculator::average(1999, 50));
        // 1.065 ÷ 50 = 0.0213
        $this->assertSame(21, RatingCalculator::average(1065, 50));
        $this->assertSame(0, RatingCalculator::average(0, 50));
    }

    public function test_rating_is_the_sum_of_three_contributions(): void
    {
        $this->assertSame(17852, RatingCalculator::rating(2939, 14700, 213));
        // float で足すと 19.915999… になり、切り捨てると 19.915 になる組み合わせ
        $this->assertSame(19916, RatingCalculator::rating(3054, 16203, 659));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function milliValues(): array
    {
        return [
            '17.400' => [17400, '17.400'],
            '100.000' => [100000, '100.000'],
            '1000 以上でも桁区切りは入れない' => [1234567, '1234.567'],
            '1 未満' => [50, '0.050'],
            '最小の刻み' => [1, '0.001'],
            '0' => [0, '0.000'],
            '負の値' => [-1, '-0.001'],
            '負の値（整数部あり）' => [-1500, '-1.500'],
        ];
    }

    #[DataProvider('milliValues')]
    public function test_rating_is_formatted_with_three_decimals(int $milli, string $expected): void
    {
        $this->assertSame($expected, RatingCalculator::formatMilli($milli));
    }
}
