<?php

use App\Models\Bmi;
use App\Models\KebutuhanKalori;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;

// Owner request 2026-11-11: Kalkulator (Figma 06): BMI and daily calorie need, saved to the history,
// the calorie result becomes the user's daily target (pengguna.kalori) that Catatan Makanan and Beranda read.

beforeEach(function () {
    $this->withoutVite();
    Carbon::setTestNow('2026-11-11 10:00:00');
    $this->seed(RolePermissionSeeder::class);
    $this->user = User::factory()->create()->assignRole('user');
});

afterEach(fn () => Carbon::setTestNow());

function calKalori(array $o = []): array
{
    return $o + ['jenis_kelamin' => 'Perempuan', 'usia' => 38, 'tinggi_badan' => '160', 'berat_badan' => '54,5', 'aktivitas' => 'Moderate'];
}

it('sends a guest to login and refuses a user without the calculator permission', function () {
    $this->get('/kalkulator')->assertRedirect('/login');

    $nobody = User::factory()->create();
    $this->actingAs($nobody)->get('/kalkulator')->assertForbidden();
    $this->actingAs($nobody)->post('/kalkulator/bmi', ['tinggi_badan' => 160, 'berat_badan' => 54.5])->assertForbidden();
    $this->actingAs($nobody)->postJson('/kalkulator/kalori/pratinjau', calKalori())->assertForbidden();
});

it('shows the empty hub before anything was calculated and does not create a profile row on a read', function () {
    $p = $this->actingAs($this->user)->get('/kalkulator')->assertOk()->viewData('page')['props'];

    expect($p['terakhirBmi'])->toBeNull()->and($p['terakhirKalori'])->toBeNull()->and($p['riwayatBmi'])->toBe([])->and($p['riwayatKalori'])->toBe([])
        ->and($this->user->pengguna()->exists())->toBeFalse();
});

it('previews a BMI without saving anything', function () {
    $this->actingAs($this->user)->postJson('/kalkulator/bmi/pratinjau', ['tinggi_badan' => '160', 'berat_badan' => '54,5'])->assertOk()
        ->assertJsonPath('skor', 21.3)->assertJsonPath('kategori.kode', 'normal')->assertJsonPath('kategori.label', 'Normal')->assertJsonPath('anak', false);

    expect(Bmi::query()->count())->toBe(0);
});

it('saves a BMI to the history, updates the profile and redirects to the result page', function () {
    $response = $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '160', 'berat_badan' => '54,5']);

    $bmi = Bmi::query()->sole();
    $response->assertRedirect("/kalkulator/bmi/{$bmi->id}");
    $pengguna = $this->user->pengguna()->sole();
    expect($bmi->skor)->toBe('21.30')->and($bmi->keterangan)->toBe('Normal')->and($bmi->pengguna_id)->toBe($pengguna->id)
        ->and($pengguna->berat_badan)->toBe('54.50')->and($pengguna->tinggi_badan)->toBe('160.00')->and($pengguna->bmi)->toBe('21.30');
});

it('shows the BMI result with the scale and a marker, only to its owner', function () {
    $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '160', 'berat_badan' => '54,5']);
    $bmi = Bmi::query()->sole();

    $hasil = $this->actingAs($this->user)->get("/kalkulator/bmi/{$bmi->id}")->assertOk()->viewData('page')['props']['hasil'];

    expect($hasil['skor'])->toEqual(21.3)->and($hasil['kategori']['label'])->toBe('Normal')->and($hasil['anak'])->toBeFalse()
        ->and($hasil['skala'])->toHaveCount(5)->and($hasil['posisi_persen'])->toBeGreaterThan(0)->and($hasil['tanggal'])->toBe('11 Nov 2026');

    $other = User::factory()->create()->assignRole('user');
    $this->actingAs($other)->get("/kalkulator/bmi/{$bmi->id}")->assertNotFound();
});

it('rates a user under 18 by age: no category, no marker', function () {
    $this->user->pengguna()->create(['tanggal_lahir' => '2009-03-01']); // 17 years on 2026-11-11

    $this->actingAs($this->user)->postJson('/kalkulator/bmi/pratinjau', ['tinggi_badan' => '160', 'berat_badan' => '40'])->assertOk()
        ->assertJsonPath('anak', true)->assertJsonPath('kategori', null);
    $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '160', 'berat_badan' => '40']);

    $bmi = Bmi::query()->sole();
    $hasil = $this->actingAs($this->user)->get("/kalkulator/bmi/{$bmi->id}")->viewData('page')['props']['hasil'];
    expect($hasil['anak'])->toBeTrue()->and($hasil['kategori'])->toBeNull()->and($hasil['posisi_persen'])->toBeNull()->and($hasil['usia'])->toBe(17)
        ->and($bmi->keterangan)->toBe('Dinilai menurut usia');
});

it('rejects impossible BMI inputs and saves nothing', function (array $body, string $field) {
    $this->actingAs($this->user)->post('/kalkulator/bmi', $body)->assertSessionHasErrors($field);

    expect(Bmi::query()->count())->toBe(0);
})->with([
    'no height' => [['berat_badan' => '55'], 'tinggi_badan'],
    'height 30 cm' => [['tinggi_badan' => '30', 'berat_badan' => '55'], 'tinggi_badan'],
    'height 300 cm' => [['tinggi_badan' => '300', 'berat_badan' => '55'], 'tinggi_badan'],
    'weight zero' => [['tinggi_badan' => '160', 'berat_badan' => '0'], 'berat_badan'],
    'weight 500 kg' => [['tinggi_badan' => '160', 'berat_badan' => '500'], 'berat_badan'],
    'text weight' => [['tinggi_badan' => '160', 'berat_badan' => 'banyak'], 'berat_badan'],
]);

it('previews the daily calories (1.850 kkal for the Figma example) and says nothing until the data is complete', function () {
    $this->actingAs($this->user)->postJson('/kalkulator/kalori/pratinjau', calKalori())->assertOk()->assertJsonPath('kkal', 1850)->assertJsonPath('alasan', null);
    $this->actingAs($this->user)->postJson('/kalkulator/kalori/pratinjau', ['jenis_kelamin' => 'Perempuan', 'usia' => 38])->assertOk()->assertJsonPath('kkal', null);

    expect(KebutuhanKalori::query()->count())->toBe(0);
});

it('gives no calorie number under 18, with the reason', function () {
    $this->actingAs($this->user)->postJson('/kalkulator/kalori/pratinjau', calKalori(['usia' => 17]))->assertOk()
        ->assertJsonPath('kkal', null)->assertJsonPath('alasan', 'Target kalori tidak ditampilkan untuk anggota di bawah 18 tahun.');
    $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori(['usia' => 17]))->assertSessionHasErrors('usia');

    expect(KebutuhanKalori::query()->count())->toBe(0);
});

it('saves the calorie result, makes it the daily target and keeps the profile in step', function () {
    $response = $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori());

    $row = KebutuhanKalori::query()->sole();
    $response->assertRedirect("/kalkulator/kalori/{$row->id}");
    $pengguna = $this->user->pengguna()->sole();
    expect($row->skor)->toBe('1850.00')->and($row->usia)->toBe(38)->and($row->aktivitas_fisik)->toBe('Moderate')->and($row->keterangan)->toBe('Rumus standar')
        ->and($pengguna->kalori)->toBe('1850.00')->and($pengguna->jenis_kelamin)->toBe('Perempuan')->and($pengguna->aktivitas_fisik)->toBe('Moderate')
        ->and($pengguna->berat_badan)->toBe('54.50')->and($pengguna->tinggi_badan)->toBe('160.00');

    // The target is what Catatan Makanan shows as "dari 1.850 kkal".
    $meals = $this->actingAs($this->user)->get('/catatan-makanan')->viewData('page')['props'];
    expect($meals['ringkasan']['target_kalori'])->toEqual(1850);
});

it('shows the calorie result with the macro ranges and the activity label, only to its owner', function () {
    $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori());
    $row = KebutuhanKalori::query()->sole();

    $hasil = $this->actingAs($this->user)->get("/kalkulator/kalori/{$row->id}")->assertOk()->viewData('page')['props']['hasil'];

    expect($hasil['kkal'])->toBe(1850)->and($hasil['aktivitas_label'])->toBe('Cukup aktif')
        ->and($hasil['makro']['karbohidrat'])->toBe(['min' => 231, 'max' => 278])->and($hasil['makro']['lemak'])->toBe(['min' => 41, 'max' => 62]);

    $this->actingAs(User::factory()->create()->assignRole('user'))->get("/kalkulator/kalori/{$row->id}")->assertNotFound();
});

it('rejects invalid calorie inputs and saves nothing', function (array $override, string $field) {
    $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori($override))->assertSessionHasErrors($field);

    expect(KebutuhanKalori::query()->count())->toBe(0);
})->with([
    'unknown sex' => [['jenis_kelamin' => 'Lainnya'], 'jenis_kelamin'],
    'age 121' => [['usia' => 121], 'usia'],
    'age text' => [['usia' => 'tua'], 'usia'],
    'unknown activity' => [['aktivitas' => 'Atlet'], 'aktivitas'],
    'no weight' => [['berat_badan' => ''], 'berat_badan'],
    'height 20' => [['tinggi_badan' => '20'], 'tinggi_badan'],
]);

it('prefills the wizards from the profile and offers the five activity levels', function () {
    $this->user->pengguna()->create(['jenis_kelamin' => 'Perempuan', 'berat_badan' => '54.5', 'tinggi_badan' => '160', 'tanggal_lahir' => '1988-05-01', 'aktivitas_fisik' => 'Moderate']);

    $kalori = $this->actingAs($this->user)->get('/kalkulator/kalori')->assertOk()->viewData('page')['props'];
    $bmi = $this->actingAs($this->user)->get('/kalkulator/bmi')->assertOk()->viewData('page')['props'];

    expect($kalori['prefill'])->toEqual(['jenis_kelamin' => 'Perempuan', 'usia' => 38, 'tinggi' => 160, 'berat' => 54.5, 'aktivitas' => 'Moderate'])
        ->and($kalori['aktivitasOpsi'])->toHaveCount(5)
        ->and($bmi['prefill'])->toEqual(['tinggi' => 160, 'berat' => 54.5])->and($bmi['usia'])->toBe(38);
});

it('lists the latest results and the history, newest first and only the user\'s own', function () {
    $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '158', 'berat_badan' => '57']);
    Carbon::setTestNow('2026-11-12 10:00:00');
    $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '160', 'berat_badan' => '54,5']);
    $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori());
    $other = User::factory()->create()->assignRole('user');
    $this->actingAs($other)->post('/kalkulator/bmi', ['tinggi_badan' => '170', 'berat_badan' => '90']);

    $p = $this->actingAs($this->user)->get('/kalkulator')->viewData('page')['props'];

    expect($p['terakhirBmi']['skor'])->toEqual(21.3)->and($p['terakhirBmi']['kategori']['label'])->toBe('Normal')->and($p['terakhirBmi']['tanggal'])->toBe('12 November')
        ->and(collect($p['riwayatBmi'])->pluck('skor')->all())->toEqual([21.3, 22.8])
        ->and($p['terakhirKalori']['kkal'])->toBe(1850)->and($p['terakhirKalori']['aktivitas_label'])->toBe('Cukup aktif')
        ->and($p['riwayatKalori'][0])->toMatchArray(['kkal' => 1850, 'aktivitas_label' => 'Cukup aktif', 'metode' => 'Rumus standar']);
});

it('filters the BMI history by range and returns chart points oldest first', function () {
    $pengguna = $this->user->pengguna()->create([]);
    foreach (['2026-08-20' => 23.0, '2026-10-20' => 22.5, '2026-11-05' => 22.0, '2026-11-10' => 21.5] as $date => $skor) {
        Bmi::unguarded(fn () => Bmi::query()->create(['pengguna_id' => $pengguna->id, 'skor' => $skor, 'keterangan' => 'Normal', 'berat_badan' => 55, 'tinggi_badan' => 160, 'created_at' => $date.' 08:00:00', 'updated_at' => $date.' 08:00:00']));
    }

    $history = fn (string $query) => $this->actingAs($this->user)->get('/kalkulator/riwayat/bmi'.$query)->assertOk()->viewData('page')['props'];

    expect(collect($history('?rentang=7')['titik'])->pluck('tanggal')->all())->toBe(['2026-11-05', '2026-11-10'])
        ->and(collect($history('')['titik'])->pluck('tanggal')->all())->toBe(['2026-10-20', '2026-11-05', '2026-11-10'])
        ->and($history('?rentang=90')['titik'])->toHaveCount(4)
        ->and($history('?rentang=semua')['titik'])->toHaveCount(4)
        ->and($history('?rentang=abc')['rentang'])->toBe('30')
        ->and($history('')['terakhir'])->toEqual(['skor' => 21.5, 'tanggal' => '10 November'])
        ->and($history('')['normal'])->toEqual(['min' => 18.5, 'max' => 25.0])
        ->and($history('')['tabel'][0]['skor'])->toEqual(21.5);
});

it('answers an empty history without errors', function () {
    $p = $this->actingAs($this->user)->get('/kalkulator/riwayat/bmi')->assertOk()->viewData('page')['props'];

    expect($p['titik'])->toBe([])->and($p['terakhir'])->toBeNull()->and($p['tabel'])->toBe([]);
});

it('unlocks the "Kenal Tubuhku" and "Tahu Kebutuhanku" badges from the saved results', function () {
    $this->actingAs($this->user)->post('/kalkulator/bmi', ['tinggi_badan' => '160', 'berat_badan' => '54,5']);
    $this->actingAs($this->user)->post('/kalkulator/kalori', calKalori());

    $badges = collect($this->actingAs($this->user)->get('/profile')->viewData('page')['props']['lencana']['daftar'])->pluck('terbuka', 'key');

    expect($badges['kenal_tubuhku'])->toBeTrue()->and($badges['tahu_kebutuhanku'])->toBeTrue();
});
