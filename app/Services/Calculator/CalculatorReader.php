<?php

namespace App\Services\Calculator;

use App\Models\Bmi;
use App\Models\KebutuhanKalori;
use App\Models\Pengguna;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/** Read side of "Kalkulator": turns the user's own rows into the props the pages draw. */
class CalculatorReader
{
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    private const RENTANG = ['7' => 7, '30' => 30, '90' => 90, 'semua' => null];

    /** @return array<string, mixed> */
    public function hub(?Pengguna $pengguna): array
    {
        $bmi = $pengguna ? Bmi::query()->where('pengguna_id', $pengguna->id)->latest()->latest('id')->limit(10)->get() : collect();
        $kalori = $pengguna ? KebutuhanKalori::query()->where('pengguna_id', $pengguna->id)->latest()->latest('id')->limit(10)->get() : collect();

        $latestBmi = $bmi->first();
        $latestKalori = $kalori->first();

        return [
            'terakhirBmi' => $latestBmi ? ['tanggal' => $this->longDate($latestBmi->created_at)] + $this->bmiRow($latestBmi) : null,
            'terakhirKalori' => $latestKalori ? ['tanggal' => $this->longDate($latestKalori->created_at)] + $this->kaloriRow($latestKalori) : null,
            'riwayatBmi' => $bmi->map(fn (Bmi $r) => $this->bmiRow($r))->values()->all(),
            'riwayatKalori' => $kalori->map(fn (KebutuhanKalori $r) => $this->kaloriRow($r))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function bmiHistory(?Pengguna $pengguna, string $rentang): array
    {
        $rentang = array_key_exists($rentang, self::RENTANG) ? $rentang : '30';
        $query = Bmi::query()->where('pengguna_id', $pengguna instanceof Pengguna ? $pengguna->id : 0);
        if (self::RENTANG[$rentang] !== null) {
            $query->where('created_at', '>=', Carbon::now()->subDays(self::RENTANG[$rentang])->startOfDay());
        }
        $rows = $query->orderBy('created_at')->orderBy('id')->get();
        $newest = $rows->last();

        return [
            'rentang' => $rentang,
            'terakhir' => $newest ? ['skor' => (float) $newest->skor, 'tanggal' => $this->longDate($newest->created_at)] : null,
            'titik' => $rows->map(fn (Bmi $r) => [
                'tanggal' => $r->created_at?->format('Y-m-d'),
                'label' => $this->shortDate($r->created_at, false),
                'skor' => (float) $r->skor,
            ])->values()->all(),
            'normal' => ['min' => 18.5, 'max' => 25.0],
            'tabel' => $rows->reverse()->map(fn (Bmi $r) => $this->bmiRow($r))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function bmiResult(Bmi $bmi, ?int $usia): array
    {
        $anak = $bmi->keterangan === CalculatorService::KETERANGAN_ANAK;
        $skor = (float) $bmi->skor;

        return [
            'id' => $bmi->id,
            'skor' => $skor,
            'kategori' => $anak ? null : BodyCalculator::category($skor),
            'tinggi' => (float) $bmi->tinggi_badan,
            'berat' => (float) $bmi->berat_badan,
            'usia' => $usia,
            'anak' => $anak,
            'tanggal' => $this->shortDate($bmi->created_at),
            'skala' => BodyCalculator::scale(),
            'posisi_persen' => $anak ? null : BodyCalculator::markerPercent($skor),
        ];
    }

    /** @return array<string, mixed> */
    public function kaloriResult(KebutuhanKalori $row): array
    {
        $kkal = (int) round((float) $row->skor);

        return [
            'id' => $row->id,
            'kkal' => $kkal,
            'aktivitas_label' => BodyCalculator::activityLabel((string) $row->aktivitas_fisik),
            'tanggal' => $this->shortDate($row->created_at),
            'makro' => BodyCalculator::macroRanges($kkal),
        ];
    }

    /** @return array<string, mixed> */
    private function bmiRow(Bmi $r): array
    {
        $anak = $r->keterangan === CalculatorService::KETERANGAN_ANAK;
        $skor = (float) $r->skor;

        return [
            'id' => $r->id,
            'tanggal' => $this->shortDate($r->created_at),
            'skor' => $skor,
            'kategori' => $anak ? null : BodyCalculator::category($skor),
            'anak' => $anak,
            'tinggi' => (float) $r->tinggi_badan,
            'berat' => (float) $r->berat_badan,
        ];
    }

    /** @return array<string, mixed> */
    private function kaloriRow(KebutuhanKalori $r): array
    {
        return [
            'id' => $r->id,
            'tanggal' => $this->shortDate($r->created_at),
            'kkal' => (int) round((float) $r->skor),
            'tinggi' => (float) $r->tinggi_badan,
            'berat' => (float) $r->berat_badan,
            'aktivitas_label' => BodyCalculator::activityLabel((string) $r->aktivitas_fisik),
            'metode' => CalculatorService::METODE,
        ];
    }

    /** "2 Nov 2026" (or "2 Nov" without the year). */
    private function shortDate(?CarbonInterface $date, bool $withYear = true): string
    {
        if ($date === null) {
            return '';
        }

        return $date->day.' '.mb_substr(self::BULAN[$date->month], 0, 3).($withYear ? ' '.$date->year : '');
    }

    /** "12 November" */
    private function longDate(?CarbonInterface $date): string
    {
        return $date === null ? '' : $date->day.' '.self::BULAN[$date->month];
    }
}
