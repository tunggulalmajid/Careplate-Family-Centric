<?php

namespace App\Services\Calculator;

use InvalidArgumentException;

/**
 * The formulas of "Kalkulator" (Figma 06). Pure functions, no database.
 *
 * Rules read from the design (not invented):
 *  - BMI = kg / m^2, shown to one decimal; adult bands of Kemenkes (< 17,0 / 17,0 to 18,5 / 18,5 to 25,0 / 25,0 to 27,0 / > 27,0).
 *  - Calories: the Figma example (38 y, woman, 160 cm, 54,5 kg, "Cukup aktif") is 1.850 kkal, which is exactly Mifflin-St Jeor x 1,55.
 *  - Macro ranges of the result screen: protein 15 to 20 %, carbohydrate 50 to 60 %, fat 20 to 30 % of the calories
 *    (1.850 kkal -> 231 to 278 g carbohydrate and 41 to 62 g fat, as drawn).
 *  - Under 18 years no category and no calorie target are shown (handled by the callers).
 */
final class BodyCalculator
{
    /** Marker weights of the five bands on the scale bar, in percent of its width (sums to 100). */
    private const BAND_WIDTHS = [12, 10, 40, 14, 24];

    /** BMI used as the left and right end of the open bands for the marker. */
    private const SCALE_MIN = 12.0;

    private const SCALE_MAX = 35.0;

    /** @var list<array{kode: string, label: string, tone: string, sampai: float|null}> upper bound inclusive, null = open */
    private const BANDS = [
        ['kode' => 'sangat_kurus', 'label' => 'Sangat kurus', 'tone' => 'bahaya', 'sampai' => 16.9],
        ['kode' => 'kurus', 'label' => 'Kurus', 'tone' => 'waspada', 'sampai' => 18.4],
        ['kode' => 'normal', 'label' => 'Normal', 'tone' => 'baik', 'sampai' => 25.0],
        ['kode' => 'gemuk', 'label' => 'Gemuk', 'tone' => 'waspada', 'sampai' => 27.0],
        ['kode' => 'obesitas', 'label' => 'Obesitas', 'tone' => 'bahaya', 'sampai' => null],
    ];

    /** @var array<string, array{label: string, deskripsi: string, faktor: float}> */
    private const ACTIVITY = [
        'Sedentary' => ['label' => 'Jarang bergerak', 'deskripsi' => 'Kerja di meja, hampir tidak olahraga', 'faktor' => 1.2],
        'Light' => ['label' => 'Sedikit aktif', 'deskripsi' => 'Olahraga ringan 1 sampai 3 kali seminggu', 'faktor' => 1.375],
        'Moderate' => ['label' => 'Cukup aktif', 'deskripsi' => 'Olahraga sedang 3 sampai 5 kali seminggu', 'faktor' => 1.55],
        'Active' => ['label' => 'Sangat aktif', 'deskripsi' => 'Olahraga berat 6 sampai 7 kali seminggu', 'faktor' => 1.725],
        'Very Active' => ['label' => 'Ekstra aktif', 'deskripsi' => 'Kerja fisik berat atau latihan 2 kali sehari', 'faktor' => 1.9],
    ];

    public static function bmi(float $kg, float $cm): float
    {
        $meter = $cm / 100;

        return round($kg / ($meter * $meter), 1);
    }

    /** @return array{kode: string, label: string, tone: string} category of an adult BMI (already rounded to one decimal) */
    public static function category(float $bmi): array
    {
        foreach (self::BANDS as $band) {
            if ($band['sampai'] === null || $bmi <= $band['sampai']) {
                return ['kode' => $band['kode'], 'label' => $band['label'], 'tone' => $band['tone']];
            }
        }

        throw new InvalidArgumentException('Unreachable: the last band is open.');
    }

    /** @return list<array{kode: string, label: string, dari: float|null, sampai: float|null, tone: string}> the five bands, edges as printed under the bar */
    public static function scale(): array
    {
        $edges = [[null, 17.0], [17.0, 18.5], [18.5, 25.0], [25.0, 27.0], [27.0, null]];
        $out = [];
        foreach (self::BANDS as $i => $band) {
            $out[] = ['kode' => $band['kode'], 'label' => $band['label'], 'dari' => $edges[$i][0], 'sampai' => $edges[$i][1], 'tone' => $band['tone']];
        }

        return $out;
    }

    /** Position of the "Kamu" marker on the bar, 0 to 100, interpolated inside its band (bands have the widths of the drawing). */
    public static function markerPercent(float $bmi): float
    {
        $edges = [self::SCALE_MIN, 17.0, 18.5, 25.0, 27.0, self::SCALE_MAX];
        $bmi = max(self::SCALE_MIN, min(self::SCALE_MAX, $bmi));
        $start = 0.0;
        foreach (self::BAND_WIDTHS as $i => $width) {
            if ($bmi <= $edges[$i + 1] || $i === count(self::BAND_WIDTHS) - 1) {
                $span = $edges[$i + 1] - $edges[$i];
                $fraction = $span > 0 ? ($bmi - $edges[$i]) / $span : 0.0;

                return round($start + max(0.0, min(1.0, $fraction)) * $width, 1);
            }
            $start += $width;
        }

        return 100.0;
    }

    /** Mifflin-St Jeor x activity factor, rounded to the nearest 10 kcal ("sekitar"). */
    public static function dailyCalories(string $sex, int $age, float $cm, float $kg, string $activity): int
    {
        $factor = self::ACTIVITY[$activity]['faktor'] ?? throw new InvalidArgumentException("Unknown activity level: {$activity}");
        $offset = match ($sex) {
            'Laki-laki' => 5,
            'Perempuan' => -161,
            default => throw new InvalidArgumentException("Unknown sex: {$sex}"),
        };
        $bmr = 10 * $kg + 6.25 * $cm - 5 * $age + $offset;

        return (int) (round($bmr * $factor / 10) * 10);
    }

    /** @return array{protein: array{min: int, max: int}, karbohidrat: array{min: int, max: int}, lemak: array{min: int, max: int}} grams per day */
    public static function macroRanges(int $kcal): array
    {
        $grams = fn (float $from, float $to, int $perGram) => ['min' => (int) round($kcal * $from / $perGram), 'max' => (int) round($kcal * $to / $perGram)];

        return [
            'protein' => $grams(0.15, 0.20, 4),
            'karbohidrat' => $grams(0.50, 0.60, 4),
            'lemak' => $grams(0.20, 0.30, 9),
        ];
    }

    /** @return list<array{kode: string, label: string, deskripsi: string}> */
    public static function activityLevels(): array
    {
        $out = [];
        foreach (self::ACTIVITY as $code => $level) {
            $out[] = ['kode' => $code, 'label' => $level['label'], 'deskripsi' => $level['deskripsi']];
        }

        return $out;
    }

    public static function activityLabel(string $code): string
    {
        return self::ACTIVITY[$code]['label'] ?? $code;
    }
}
