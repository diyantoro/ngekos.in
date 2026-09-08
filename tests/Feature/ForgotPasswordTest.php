<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DomainDataSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, UserSeeder::class, DomainDataSeeder::class]);
    }

    public function test_halaman_lupa_password_terbuka(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Lupa Password');
    }

    public function test_dev_fallback_membuat_token_tersimpan_otp_dan_mengirim_email(): void
    {
        Notification::fake();

        $user = User::where('email', 'anak1@ngekos.test')->firstOrFail();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();

        $otp = Cache::get('password_reset_otp_'.$user->email);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_kotak_dev_terender_saat_flash_tersedia(): void
    {
        session()->flash('dev_reset_link', 'http://localhost:8000/reset-password/token?email=anak1@ngekos.test');
        session()->flash('dev_otp', '123456');
        session()->flash('dev_email', 'anak1@ngekos.test');

        Volt::test('pages.auth.forgot-password')
            ->assertSee('Mode pengembangan')
            ->assertSee('Buka tautan reset (klik di sini)')
            ->assertSee('123456');
    }

    public function test_email_tidak_terdaftar_tidak_membuat_token_atau_otp(): void
    {
        Volt::test('pages.auth.forgot-password')
            ->set('email', 'siapa@bukan-user.test')
            ->call('sendPasswordResetLink')
            ->assertHasErrors('email');

        $this->assertNull(Cache::get('password_reset_otp_siapa@bukan-user.test'));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'siapa@bukan-user.test']);
    }
}