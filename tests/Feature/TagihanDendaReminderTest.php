<?php

namespace Tests\Feature;

use App\Console\Commands\KirimPengingatTagihan;
use App\Console\Commands\ProsesTagihan;
use App\Mail\PengingatTagihanMail;
use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\TagihanPengingat;
use App\Models\User;
use App\Services\PatunganService;
use App\Services\PembayaranService;
use App\Services\PenyewaanService;
use App\Services\TagihanReminderService;
use App\Services\TagihanService;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagihanDendaReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
        Pengaturan::simpanBanyak(['kos.denda_per_hari' => '5000.00']);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function sewaAktif(User $anak, int $dendaPerHari = 5000): Penyewaan
    {
        Tagihan::query()->update(['status' => 'lunas']);

        $properti = Properti::create([
            'pemilik_id' => $this->user('pemilik1@ngekos.test')->id,
            'nama' => 'Kos Uji '.uniqid(),
            'kota' => 'Yogyakarta',
            'denda_per_hari' => $dendaPerHari,
            'status' => 'aktif',
        ]);
        $kamar = Kamar::create([
            'properti_id' => $properti->id,
            'nama' => 'U1',
            'kapasitas' => 1,
            'harga_sewa_bulanan' => 1000000,
            'status' => 'tersedia',
        ]);

        return app(PenyewaanService::class)->sewaKamar(
            $anak, $kamar, today()->toDateString(), 1, null, 'ktp/uji.jpg'
        );
    }

    // ---------- TagihanService: denda sesuai hari bayar ----------

    public function test_denda_nol_sebelum_jatuh_tempo(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->addDays(5)->toDateString()]);

        $this->assertSame(0, TagihanService::hariTelat($tagihan));
        $this->assertEquals(0, TagihanService::dendaBerjalan($tagihan->refresh()));
        $this->assertEquals(1000000, TagihanService::totalBerjalan($tagihan->refresh()));
    }

    public function test_denda_bertambah_per_hari_sesuai_waktu_bayar(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(5)->toDateString(), 'denda' => 0]);

        $this->assertSame(5, TagihanService::hariTelat($tagihan->refresh()));
        $this->assertEquals(25000, TagihanService::dendaBerjalan($tagihan->refresh()));
        $this->assertEquals(1025000, TagihanService::totalBerjalan($tagihan->refresh()));

        $this->assertEquals(25000, TagihanService::sinkronDenda($tagihan->refresh()));
        $this->assertEquals(25000, (float) $tagihan->refresh()->denda);
    }

    public function test_denda_properti_mengalahkan_global(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak, 10000);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(2)->toDateString(), 'denda' => 0]);

        $this->assertEquals(20000, TagihanService::dendaBerjalan($tagihan->refresh()));
    }

    public function test_proses_tagihan_menghitung_denda_sampai_h0(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->toDateString(), 'denda' => 999]);

        $this->artisan(ProsesTagihan::class)->assertSuccessful();
        $this->assertEquals(0, (float) $tagihan->refresh()->denda);

        $tagihan->update(['jatuh_tempo' => today()->subDay()->toDateString()]);
        $this->artisan(ProsesTagihan::class)->assertSuccessful();
        $this->assertEquals(5000, (float) $tagihan->refresh()->denda);
    }

    // ---------- Bayar harus pas ----------

    public function test_api_bayar_ditolak_bila_kurang_dari_total_berjalan(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(3)->toDateString(), 'denda' => 0]);

        Sanctum::actingAs($anak, ['*']);

        $this->postJson('/api/dashboard/anak-kos/bayar', [
            'tagihan_id' => $tagihan->id,
            'metode' => 'transfer',
            'jumlah' => 1000000,
        ])->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'kurang'));

        $this->postJson('/api/dashboard/anak-kos/bayar', [
            'tagihan_id' => $tagihan->id,
            'metode' => 'cash',
            'jumlah' => 1015000,
        ])->assertCreated();

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan->id,
            'jumlah' => 1015000,
        ]);
    }

    public function test_api_bayar_patungan_mengacu_sisa_porsi(): void
    {
        $rina = $this->user('anak1@ngekos.test');
        $yoga = $this->user('anak2@ngekos.test');
        $sewa = $this->sewaAktif($rina);
        $sewa->kamar->update(['kapasitas' => 2]);
        PatunganService::tambahAnggota($sewa->refresh(), $yoga, 'ktp/yoga.jpg');

        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(2)->toDateString(), 'denda' => 0]);

        Sanctum::actingAs($yoga, ['*']);

        $this->postJson('/api/dashboard/anak-kos/bayar', [
            'tagihan_id' => $tagihan->id,
            'metode' => 'cash',
            'jumlah' => 500000,
        ])->assertStatus(422);

        $this->postJson('/api/dashboard/anak-kos/bayar', [
            'tagihan_id' => $tagihan->id,
            'metode' => 'cash',
            'jumlah' => 505000,
        ])->assertCreated();
    }

    public function test_verifikasi_melunasi_dengan_total_berjalan(): void
    {
        $pemilik = $this->user('pemilik1@ngekos.test');
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(4)->toDateString(), 'denda' => 0]);

        $bayar = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'anak_kos_id' => $anak->id,
            'metode' => 'cash',
            'jumlah' => 1020000,
            'status' => 'menunggu_verifikasi',
        ]);

        $hasil = PembayaranService::verifikasi($bayar, $pemilik->id);

        $this->assertTrue($hasil['tagihan_lunas']);
        $this->assertSame('lunas', $tagihan->refresh()->status);
    }

    // ---------- Reminder H-3 / H-1 / H0 / telat ----------

    public function test_reminder_h3_h1_h0_dan_telat_terkirim_via_chat(): void
    {
        Mail::fake();
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();

        foreach ([
            [3, 'h-3', '3 hari lagi'],
            [1, 'h-1', 'BESOK'],
            [0, 'h0', 'HARI INI'],
            [-2, 'telat', 'TERLAMBAT 2 hari'],
        ] as [$mundur, $jenis, $frasa]) {
            TagihanPengingat::query()->delete();
            ChatPesan::query()->delete();
            $tagihan->update(['jatuh_tempo' => today()->subDays(-$mundur)->toDateString(), 'status' => 'belum_bayar']);

            $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh(), Carbon::today()));
            $this->assertDatabaseHas('tagihan_pengingat', ['tagihan_id' => $tagihan->id, 'jenis' => $jenis]);

            $chat = ChatPesan::where('anak_kos_id', $anak->id)->latest('id')->firstOrFail();
            $this->assertStringContainsString($frasa, $chat->isi);
        }
    }

    public function test_reminder_idempoten_tidak_kirim_dua_kali(): void
    {
        Mail::fake();
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDay()->toDateString(), 'status' => 'belum_bayar']);

        $chatAwal = ChatPesan::where('anak_kos_id', $anak->id)->count();

        $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh()));
        $this->assertFalse(TagihanReminderService::prosesSatu($tagihan->refresh()));

        $this->assertSame($chatAwal + 1, ChatPesan::where('anak_kos_id', $anak->id)->count());
        $this->assertSame(1, TagihanPengingat::where('tagihan_id', $tagihan->id)->count());

        Tagihan::where('id', '!=', $tagihan->id)->update(['status' => 'lunas']);
        $this->artisan(KirimPengingatTagihan::class)->assertSuccessful();
        $this->assertSame($chatAwal + 1, ChatPesan::where('anak_kos_id', $anak->id)->count());
    }

    public function test_reminder_telat_terkirim_tiap_hari_sampai_lunas(): void
    {
        Mail::fake();
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->subDays(5)->toDateString(), 'status' => 'belum_bayar']);

        $hari1 = Carbon::today();
        $hari2 = Carbon::today()->addDay();

        $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh(), $hari1));
        $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh(), $hari2));
        $this->assertSame(2, TagihanPengingat::where('tagihan_id', $tagihan->id)->where('jenis', 'telat')->count());

        $tagihan->update(['status' => 'lunas']);
        $this->assertFalse(TagihanReminderService::prosesSatu($tagihan->refresh(), $hari2->copy()->addDay()));
    }

    public function test_reminder_tidak_untuk_sewa_nonaktif_atau_tanggal_acak(): void
    {
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();

        $tagihan->update(['jatuh_tempo' => today()->addDays(7)->toDateString(), 'status' => 'belum_bayar']);
        $this->assertFalse(TagihanReminderService::prosesSatu($tagihan->refresh()));

        $tagihan->update(['jatuh_tempo' => today()->toDateString()]);
        $sewa->update(['status' => 'selesai']);
        $hasil = TagihanReminderService::kirimHarian();
        $this->assertSame(0, $hasil['kirim']);
    }

    public function test_reminder_menghormati_preferensi_dan_masuk_email_queue(): void
    {
        Mail::fake();
        $anak = $this->user('anak1@ngekos.test');
        $anak->update(['preferensi_notifikasi' => ['pengingat_tagihan' => false, 'tagihan_telat' => true]]);
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->addDays(3)->toDateString(), 'status' => 'belum_bayar']);

        $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh()));

        Mail::assertNothingQueued();

        $chat = ChatPesan::where('anak_kos_id', $anak->id)->latest('id')->firstOrFail();
        $this->assertStringContainsString('3 hari lagi', $chat->isi);

        $anak->update(['preferensi_notifikasi' => ['pengingat_tagihan' => true]]);
        TagihanPengingat::query()->delete();
        $this->assertTrue(TagihanReminderService::prosesSatu($tagihan->refresh()));
        Mail::assertQueued(PengingatTagihanMail::class);
    }

    public function test_kirim_harian_hanya_memproses_yang_relevan(): void
    {
        Mail::fake();
        $anak = $this->user('anak1@ngekos.test');
        $sewa = $this->sewaAktif($anak);
        $tagihan = $sewa->tagihans()->firstOrFail();
        $tagihan->update(['jatuh_tempo' => today()->addDays(2)->toDateString(), 'status' => 'belum_bayar']);

        $hasil = TagihanReminderService::kirimHarian();

        $this->assertGreaterThanOrEqual(1, $hasil['cek']);
        $this->assertSame(0, $hasil['kirim']);
        $this->assertDatabaseMissing('tagihan_pengingat', ['tagihan_id' => $tagihan->id]);
    }
}
