<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/pengaturan');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.pengaturan')
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form')
            ->assertSeeVolt('profile.delete-user-form');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('nama', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->nama);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('nama', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        // User memakai SoftDeletes: akun dinonaktifkan (trashed), bukan hilang permanen.
        $this->assertNotNull($user->fresh());
        $this->assertTrue($user->fresh()->trashed());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    #[DataProvider('peranPenggunaProvider')]
    public function test_foto_profil_bisa_diganti_lewat_web(string $peran): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole($peran);

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('nama', $user->nama)
            ->set('email', $user->email)
            ->set('fotoProfil', UploadedFile::fake()->image('avatar.png', 800, 600))
            ->call('updateProfileInformation');

        $component->assertHasNoErrors();

        $avatar = $user->refresh()->avatar;

        $this->assertNotNull($avatar);
        Storage::disk('public')->assertExists($avatar);
    }

    public function test_foto_profil_lama_dihapus_saat_diganti(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $lama = UploadedFile::fake()->image('lama.png')->store('avatar', 'public');
        $user->update(['avatar' => $lama]);

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('nama', $user->nama)
            ->set('email', $user->email)
            ->set('fotoProfil', UploadedFile::fake()->image('baru.png'))
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($user->refresh()->avatar);
    }

    #[DataProvider('peranPenggunaProvider')]
    public function test_foto_profil_bisa_diganti_lewat_api(string $peran): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole($peran);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user/profile', [
                'nama' => $user->nama,
            ]);

        // Tanpa file: avatar tidak berubah (tetap null), nama tetap tersimpan.
        $response->assertOk();
        $this->assertNull($user->refresh()->avatar);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/user/profile?_method=PUT', [
                'avatar' => UploadedFile::fake()->image('avatar.png', 800, 600),
            ]);

        $response->assertOk();

        $avatar = $user->refresh()->avatar;

        $this->assertNotNull($avatar);
        Storage::disk('public')->assertExists($avatar);
        $this->assertStringContainsString('/storage/', $response->json('user.avatar'));
    }

    public static function peranPenggunaProvider(): array
    {
        return [
            'pemilik' => ['pemilik'],
            'anak_kos' => ['anak_kos'],
        ];
    }
}
