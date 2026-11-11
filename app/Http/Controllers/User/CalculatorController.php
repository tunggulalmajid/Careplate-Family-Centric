<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calculator\BmiRequest;
use App\Http\Requests\Calculator\KaloriPreviewRequest;
use App\Http\Requests\Calculator\KaloriRequest;
use App\Models\Bmi;
use App\Models\KebutuhanKalori;
use App\Models\Pengguna;
use App\Services\Calculator\BodyCalculator;
use App\Services\Calculator\CalculatorReader;
use App\Services\Calculator\CalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kalkulator (Figma 06): BMI and daily calorie need of the signed-in user. Every read and save is scoped to the
 * user's own profile; a result id that belongs to someone else answers 404.
 */
class CalculatorController extends Controller
{
    public function __construct(
        private readonly CalculatorService $service,
        private readonly CalculatorReader $reader,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('User/Kalkulator/Index', $this->reader->hub($this->pengguna($request)));
    }

    public function bmiHistory(Request $request): Response
    {
        $rentang = $request->query('rentang');

        return Inertia::render('User/Kalkulator/RiwayatBmi', $this->reader->bmiHistory($this->pengguna($request), is_string($rentang) ? $rentang : '30'));
    }

    public function bmiForm(Request $request): Response
    {
        $pengguna = $this->pengguna($request);

        return Inertia::render('User/Kalkulator/Bmi', [
            'prefill' => ['tinggi' => $this->number($pengguna?->tinggi_badan), 'berat' => $this->number($pengguna?->berat_badan)],
            'usia' => $pengguna?->usia,
        ]);
    }

    public function bmiPreview(BmiRequest $request): JsonResponse
    {
        $skor = BodyCalculator::bmi((float) $request->validated('berat_badan'), (float) $request->validated('tinggi_badan'));
        $anak = $this->service->isChild($this->pengguna($request));

        return response()->json([
            'skor' => $skor,
            'kategori' => $anak ? null : BodyCalculator::category($skor),
            'anak' => $anak,
        ]);
    }

    public function bmiStore(BmiRequest $request): RedirectResponse
    {
        $row = $this->service->recordBmi($request->user(), (float) $request->validated('tinggi_badan'), (float) $request->validated('berat_badan'));

        return redirect()->route('user.calculator.bmi.show', $row->id);
    }

    public function bmiShow(Request $request, int $bmi): Response
    {
        $pengguna = $this->pengguna($request);
        $row = Bmi::query()->where('pengguna_id', $pengguna instanceof Pengguna ? $pengguna->id : 0)->findOrFail($bmi);

        return Inertia::render('User/Kalkulator/BmiHasil', ['hasil' => $this->reader->bmiResult($row, $pengguna?->usia)]);
    }

    public function kaloriForm(Request $request): Response
    {
        $pengguna = $this->pengguna($request);

        return Inertia::render('User/Kalkulator/Kalori', [
            'prefill' => [
                'jenis_kelamin' => $pengguna?->jenis_kelamin,
                'usia' => $pengguna?->usia,
                'tinggi' => $this->number($pengguna?->tinggi_badan),
                'berat' => $this->number($pengguna?->berat_badan),
                'aktivitas' => $pengguna?->aktivitas_fisik,
            ],
            'aktivitasOpsi' => BodyCalculator::activityLevels(),
        ]);
    }

    public function kaloriPreview(KaloriPreviewRequest $request): JsonResponse
    {
        $input = $request->all();
        $usia = $input['usia'] ?? null;
        if (is_numeric($usia) && (int) $usia >= 0 && (int) $usia < 18) {
            return response()->json(['kkal' => null, 'alasan' => 'Target kalori tidak ditampilkan untuk anggota di bawah 18 tahun.']);
        }

        $valid = Validator::make($input, (new KaloriRequest)->rules());
        if ($valid->fails()) {
            return response()->json(['kkal' => null, 'alasan' => null]);
        }
        $data = $valid->validated();

        return response()->json([
            'kkal' => BodyCalculator::dailyCalories($data['jenis_kelamin'], (int) $data['usia'], (float) $data['tinggi_badan'], (float) $data['berat_badan'], $data['aktivitas']),
            'alasan' => null,
        ]);
    }

    public function kaloriStore(KaloriRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $row = $this->service->recordCalories($request->user(), $data['jenis_kelamin'], (int) $data['usia'], (float) $data['tinggi_badan'], (float) $data['berat_badan'], $data['aktivitas']);

        return redirect()->route('user.calculator.kalori.show', $row->id);
    }

    public function kaloriShow(Request $request, int $kebutuhan): Response
    {
        $pengguna = $this->pengguna($request);
        $row = KebutuhanKalori::query()->where('pengguna_id', $pengguna instanceof Pengguna ? $pengguna->id : 0)->findOrFail($kebutuhan);

        return Inertia::render('User/Kalkulator/KaloriHasil', ['hasil' => $this->reader->kaloriResult($row)]);
    }

    /** The signed-in user's profile row; a read never creates it. */
    private function pengguna(Request $request): ?Pengguna
    {
        // Queried, not read from the relation, so an instance that cached "no profile" before the first save is not stale.
        $user = $request->user();

        return $user ? Pengguna::query()->where('user_id', $user->id)->first() : null;
    }

    private function number(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
