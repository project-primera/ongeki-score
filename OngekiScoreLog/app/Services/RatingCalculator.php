<?php

namespace App\Services;

/**
 * レート値の計算。
 *
 * すべて整数で計算し、割り切れない桁は切り捨てる。小数（float）で計算すると、
 * 3.054 + 16.203 + 0.659 が 19.915999… になるような誤差で最後の桁が変わるため。
 * 譜面定数は 1/100、単曲のレート値は 1/1000 を単位にする（定数 14.7 は 1470、レート値 17.400 は 17400）。
 *
 * 計算式は公式には公開されていない。ここにあるのは有志の検証による暫定の式。
 */
final class RatingCalculator
{
    /** ランプごとの補正（1/1000 単位）。FULL BELL の 0.050 は、ほかのランプの補正に足される */
    private const LAMP_BONUS = [
        'FB/AB+' => 400,
        'AB+' => 350,
        'FB/AB' => 350,
        'AB' => 300,
        'FB/FC' => 150,
        'FC' => 100,
        'FB' => 50,
    ];

    /** プラチナスコア枠で数える☆の上限 */
    private const MAX_STAR = 5;

    /**
     * DB の譜面定数を 1/100 単位の整数にする。その難易度が無い（null）ときは null
     *
     * @param string|float|int|null $extraLevel "14.70"（MySQL の decimal）、14.7、15 など
     */
    public static function extraLevelToCenti(string|float|int|null $extraLevel): ?int
    {
        if ($extraLevel === null) {
            return null;
        }

        // 13.9 * 100 が 1389.999… になるような誤差を消すために、整数へ直すこの 1 か所だけ四捨五入する
        return (int) round((float) $extraLevel * 100);
    }

    /**
     * テクニカルスコアから求める単曲のレート値（1/1000 単位）
     *
     * @param string $lampForRating "FB/AB+" "AB+" "FB/AB" "AB" "FB/FC" "FC" "FB" のどれか。ほかは補正なし
     */
    public static function technical(int $extraLevelCenti, int $technicalScore, string $lampForRating): int
    {
        // 譜面定数 0 の譜面は、スコアやランプに関係なく 0
        if ($extraLevelCenti === 0) {
            return 0;
        }

        return max(0, self::technicalBase($extraLevelCenti * 10, $technicalScore))
            + self::rankBonus($technicalScore)
            + (self::LAMP_BONUS[$lampForRating] ?? 0);
    }

    /**
     * プラチナスコア枠の単曲のレート値（1/1000 単位）。譜面定数² × ☆ ÷ 1000
     */
    public static function platinum(int $extraLevelCenti, int $star): int
    {
        $star = max(0, min($star, self::MAX_STAR));

        // (定数 / 100)² × ☆ / 1000 を 1/1000 単位にすると、定数² × ☆ / 10000
        return intdiv($extraLevelCenti * $extraLevelCenti * $star, 10000);
    }

    /**
     * 譜面定数にスコア補正を足した値（1/1000 単位）。負になることがある
     *
     * 補正は基準点の間を直線でつなぐ。どの区間も「下の基準点の値 + そこからの上昇分の切り捨て」で求めるので、
     * 割り切れない分は必ず値が小さくなる向きに捨てられる。
     */
    private static function technicalBase(int $extraLevelMilli, int $technicalScore): int
    {
        return match (true) {
            $technicalScore >= 1010000 => $extraLevelMilli + 2000,
            // 2,500 点で +0.250（10 点ごとに +0.001）
            $technicalScore >= 1007500 => $extraLevelMilli + 1750 + intdiv($technicalScore - 1007500, 10),
            // 7,500 点で +0.500（15 点ごと）
            $technicalScore >= 1000000 => $extraLevelMilli + 1250 + intdiv($technicalScore - 1000000, 15),
            // 10,000 点で +0.500（20 点ごと）
            $technicalScore >= 990000 => $extraLevelMilli + 750 + intdiv($technicalScore - 990000, 20),
            // 20,000 点で +0.750（80/3 点ごと）
            $technicalScore >= 970000 => $extraLevelMilli + intdiv(($technicalScore - 970000) * 3, 80),
            // 70,000 点で +4.000（17.5 点ごと）
            $technicalScore >= 900000 => $extraLevelMilli - 4000 + intdiv(($technicalScore - 900000) * 2, 35),
            // 100,000 点で +2.000（50 点ごと）
            $technicalScore >= 800000 => $extraLevelMilli - 6000 + intdiv($technicalScore - 800000, 50),
            // 500,000 点の 0 から、800,000 点の「定数 - 6.000」まで上がる。ここだけは定数からの差ではなく値そのもの
            $technicalScore >= 500000 => intdiv(max(0, $extraLevelMilli - 6000) * ($technicalScore - 500000), 300000),
            default => 0,
        };
    }

    /**
     * スコアランクの補正（1/1000 単位）
     */
    private static function rankBonus(int $technicalScore): int
    {
        return match (true) {
            $technicalScore >= 1007500 => 300, // SSS+
            $technicalScore >= 1000000 => 200, // SSS
            $technicalScore >= 990000 => 100, // SS
            default => 0,
        };
    }
}
