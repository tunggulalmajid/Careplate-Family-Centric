<?php

namespace App\Services\Calculator;

use App\Models\Bmi;
use App\Models\KebutuhanKalori;
use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Saves the results of "Kalkulator". One transaction per save: the history row and the profile (weight, height, BMI,
 * daily calorie target) move together or not at all.
 */
class CalculatorService
{
    public const KETERANGAN_ANAK = 'Dinilai menurut usia';

    public const METODE = 'Rumus standar';

    public function recordBmi(User $user, float $cm, float $kg): Bmi
    {
        $pengguna = Pengguna::query()->firstOrCreate(['user_id' => $user->id]);
        $skor = BodyCalculator::bmi($kg, $cm);
        $keterangan = $this->isChild($pengguna) ? self::KETERANGAN_ANAK : BodyCalculator::category($skor)['label'];

        return DB::transaction(function () use ($pengguna, $cm, $kg, $skor, $keterangan) {
            $row = Bmi::query()->create([
                'pengguna_id' => $pengguna->id,
                'skor' => $skor,
                'keterangan' => $keterangan,
                'berat_badan' => $kg,
                'tinggi_badan' => $cm,
            ]);
            $pengguna->update(['berat_badan' => $kg, 'tinggi_badan' => $cm, 'bmi' => $skor]);

            return $row;
        });
    }

    public function recordCalories(User $user, string $sex, int $age, float $cm, float $kg, string $activity): KebutuhanKalori
    {
        $pengguna = Pengguna::query()->firstOrCreate(['user_id' => $user->id]);
        $kkal = BodyCalculator::dailyCalories($sex, $age, $cm, $kg, $activity);

        return DB::transaction(function () use ($pengguna, $sex, $age, $cm, $kg, $activity, $kkal) {
            $row = KebutuhanKalori::query()->create([
                'pengguna_id' => $pengguna->id,
                'skor' => $kkal,
                'keterangan' => self::METODE,
                'jenis_kelamin' => $sex,
                'berat_badan' => $kg,
                'tinggi_badan' => $cm,
                'usia' => $age,
                'aktivitas_fisik' => $activity,
            ]);
            $pengguna->update([
                'jenis_kelamin' => $sex,
                'berat_badan' => $kg,
                'tinggi_badan' => $cm,
                'aktivitas_fisik' => $activity,
                'kalori' => $kkal,
            ]);

            return $row;
        });
    }

    public function isChild(?Pengguna $pengguna): bool
    {
        $usia = $pengguna?->usia;

        return $usia !== null && $usia < 18;
    }
}
