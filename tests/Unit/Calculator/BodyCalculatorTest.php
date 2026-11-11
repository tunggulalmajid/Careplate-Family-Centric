<?php

use App\Services\Calculator\BodyCalculator;

// Owner request 2026-11-11: Kalkulator (Figma 06). Expected values are worked by hand from the Figma examples:
// 160 cm / 54,5 kg = BMI 21,3 (Normal); 38 y female, 160 cm, 54,5 kg, "Cukup aktif" = 1.850 kkal (Mifflin-St Jeor x 1,55).

it('computes BMI to one decimal', function (float $kg, float $cm, float $expected) {
    expect(BodyCalculator::bmi($kg, $cm))->toBe($expected);
})->with([
    'figma example' => [54.5, 160.0, 21.3],
    'second figma row' => [56.0, 158.0, 22.4],
    'tall and heavy' => [100.0, 180.0, 30.9],
    'light' => [40.0, 170.0, 13.8],
]);

it('classifies adults by the Kemenkes bands, boundaries included on the lower band', function (float $bmi, string $code, string $tone) {
    $category = BodyCalculator::category($bmi);

    expect($category['kode'])->toBe($code)->and($category['tone'])->toBe($tone);
})->with([
    'very thin' => [16.9, 'sangat_kurus', 'bahaya'],
    '17.0 is thin' => [17.0, 'kurus', 'waspada'],
    '18.4 is thin' => [18.4, 'kurus', 'waspada'],
    '18.5 is normal' => [18.5, 'normal', 'baik'],
    '25.0 is normal' => [25.0, 'normal', 'baik'],
    '25.1 is overweight' => [25.1, 'gemuk', 'waspada'],
    '27.0 is overweight' => [27.0, 'gemuk', 'waspada'],
    '27.1 is obese' => [27.1, 'obesitas', 'bahaya'],
]);

it('has five bands and places the marker inside the scale', function () {
    $scale = BodyCalculator::scale();

    expect($scale)->toHaveCount(5)->and(array_column($scale, 'kode'))->toBe(['sangat_kurus', 'kurus', 'normal', 'gemuk', 'obesitas']);
    foreach ([10.0, 16.0, 17.5, 21.3, 25.5, 26.9, 30.0, 60.0] as $bmi) {
        expect(BodyCalculator::markerPercent($bmi))->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
    }
    expect(BodyCalculator::markerPercent(21.3))->toBeGreaterThan(BodyCalculator::markerPercent(17.5))
        ->and(BodyCalculator::markerPercent(30.0))->toBeGreaterThan(BodyCalculator::markerPercent(21.3));
});

it('computes daily calories with Mifflin-St Jeor times the activity factor, to the nearest 10 kcal', function (string $sex, int $age, float $cm, float $kg, string $activity, int $expected) {
    expect(BodyCalculator::dailyCalories($sex, $age, $cm, $kg, $activity))->toBe($expected);
})->with([
    'figma example' => ['Perempuan', 38, 160.0, 54.5, 'Moderate', 1850],
    'male' => ['Laki-laki', 30, 175.0, 70.0, 'Light', 2270],
    'sedentary' => ['Perempuan', 25, 165.0, 60.0, 'Sedentary', 1610],
    'very active' => ['Laki-laki', 40, 180.0, 85.0, 'Very Active', 3380],
]);

it('rejects an unknown activity level and an unknown sex', function () {
    expect(fn () => BodyCalculator::dailyCalories('Perempuan', 30, 160.0, 55.0, 'Atlet'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => BodyCalculator::dailyCalories('Lainnya', 30, 160.0, 55.0, 'Light'))->toThrow(InvalidArgumentException::class);
});

it('gives macro ranges as 15-20 % protein, 50-60 % carbohydrate and 20-30 % fat of the calories', function () {
    $macros = BodyCalculator::macroRanges(1850);

    expect($macros['karbohidrat'])->toBe(['min' => 231, 'max' => 278])
        ->and($macros['lemak'])->toBe(['min' => 41, 'max' => 62])
        ->and($macros['protein']['min'])->toBeIn([69, 70])->and($macros['protein']['max'])->toBeIn([92, 93]);
});

it('lists the five activity levels in order with the Figma labels', function () {
    $levels = BodyCalculator::activityLevels();

    expect(array_column($levels, 'kode'))->toBe(['Sedentary', 'Light', 'Moderate', 'Active', 'Very Active'])
        ->and(array_column($levels, 'label'))->toBe(['Jarang bergerak', 'Sedikit aktif', 'Cukup aktif', 'Sangat aktif', 'Ekstra aktif']);
});
