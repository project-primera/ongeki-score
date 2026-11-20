<?php

namespace App\Services;

/**
 * レート値の計算。
 *
 * すべて整数で計算し、割り切れない桁は切り捨てる。小数（float）で計算すると、
 * 3.054 + 16.203 + 0.659 が 19.915999… になるような誤差で最後の桁が変わるため。
 * 譜面定数は 1/100、レート値は 1/1000 を単位にする（定数 14.7 は 1470、レート値 17.400 は 17400）。
 *
 * 計算式は公式には公開されていない。単曲のレート値は有志の検証による暫定の式で、
 * 枠の集計（どこで切り捨てるか）は、オンゲキNET のレーティング値と一致するように決めたもの。
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

    /** 新曲枠は、10 曲の平均の 5 分の 1 が RATING に入る。この割り算を 1 曲ごとに行う */
    private const NEW_FRAME_DIVISOR = 5;

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

        // 8.2 * 100 が 819.999… になるような誤差を消すために、整数へ直すこの 1 か所だけ四捨五入する
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
     * 枠の平均値（1/1000 単位）。小数 4 桁目以下は切り捨てる。曲が足りない枠でも、枠の曲数で割る
     *
     * ベスト枠とプラチナスコア枠は、この値がそのまま RATING への寄与値になる。
     *
     * @param int $totalMilli 枠に入った曲の単曲値の合計（1/1000 単位）
     * @param int $frameSize 枠の曲数
     */
    public static function average(int $totalMilli, int $frameSize): int
    {
        return intdiv($totalMilli, $frameSize);
    }

    /**
     * 新曲枠が RATING に寄与する値（1/1000 単位）
     *
     * 1 曲ごとに単曲値を 5 で割って小数 4 桁目以下を切り捨て、その平均をもう一度切り捨てる。
     * 合計をまとめて割ると、1 曲ごとに捨てた分だけ大きくなり、オンゲキNET の値と合わない。
     *
     * @param int[] $ratingsMilli 枠に入った曲の単曲値（1/1000 単位）
     * @param int $frameSize 枠の曲数
     */
    public static function newFrameContribution(array $ratingsMilli, int $frameSize): int
    {
        $total = 0;
        foreach ($ratingsMilli as $rating) {
            $total += intdiv($rating, self::NEW_FRAME_DIVISOR);
        }

        return intdiv($total, $frameSize);
    }

    /**
     * RATING（1/1000 単位）。3 つの枠の寄与値を足す
     */
    public static function rating(int $newFrameContribution, int $bestFrameContribution, int $platinumFrameContribution): int
    {
        return $newFrameContribution + $bestFrameContribution + $platinumFrameContribution;
    }

    /**
     * 1/1000 単位の値を、小数 3 桁の文字列にする（17400 → "17.400"）
     */
    public static function formatMilli(int $milli): string
    {
        // 整数のまま整数部と小数部に分ける。float を経由しないので、丸めは起きない
        $absolute = abs($milli);

        return ($milli < 0 ? '-' : '')
            . intdiv($absolute, 1000)
            . '.'
            . str_pad((string) ($absolute % 1000), 3, '0', STR_PAD_LEFT);
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
            // 500,000 点の 0 から、800,000 点の「定数 - 6.000」まで上がる。ここだけは定数からの差ではなく値そのもの。
            // intdiv は負の数を 0 の向きに丸めるので、定数が 6.0 未満のときは割る前に 0 にしておく
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
