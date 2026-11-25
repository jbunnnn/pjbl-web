<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_always_creates_a_regular_user(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Energy User',
            'email' => 'energy-user@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => 'admin',
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'energy-user@example.com',
            'role' => 'user',
        ]);
    }

    public function test_only_an_administrator_can_publish_energy_guides(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.guides.index'))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $response = $this->actingAs($admin)->post(route('admin.guides.store'), [
            'title' => 'Mengenal Tarif Listrik',
            'slug' => 'mengenal-tarif-listrik',
            'excerpt' => 'Ringkasan tarif listrik.',
            'content' => 'Tarif listrik disusun berdasarkan golongan pelanggan.',
            'status' => 'published',
        ]);

        $article = Article::query()->where('slug', 'mengenal-tarif-listrik')->firstOrFail();

        $response->assertRedirect(route('admin.guides.edit', $article))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'user_id' => $admin->id,
            'slug' => 'mengenal-tarif-listrik',
            'status' => 'published',
        ]);

        $this->get(route('guides.show', 'mengenal-tarif-listrik'))
            ->assertOk()
            ->assertSee('Mengenal Tarif Listrik')
            ->assertSee('Tarif listrik disusun berdasarkan golongan pelanggan.');
    }

    public function test_an_administrator_can_create_a_guide_without_a_slug(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->post(route('admin.guides.store'), [
                'title' => 'Panduan Energi Terbarukan',
                'content' => 'Isi panduan energi.',
                'status' => 'draft',
            ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'user_id' => $admin->id,
            'slug' => 'panduan-energi-terbarukan',
            'status' => 'draft',
        ]);
    }

    public function test_users_can_only_view_and_change_their_own_notes(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = $owner->notes()->create([
            'title' => 'Tarif rumah tangga',
            'content' => 'Catatan pribadi milik pengguna.',
        ]);

        $this->actingAs($otherUser)
            ->get(route('profile.notes.index'))
            ->assertOk()
            ->assertDontSee('Catatan pribadi milik pengguna.');

        $this->put(route('profile.notes.update', $note), [
            'title' => 'Akses tidak sah',
            'content' => 'Percobaan mengubah catatan orang lain.',
        ])->assertNotFound();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'user_id' => $owner->id,
            'title' => 'Tarif rumah tangga',
            'content' => 'Catatan pribadi milik pengguna.',
        ]);

        $this->actingAs($owner)
            ->put(route('profile.notes.update', $note), [
                'title' => 'Catatan diperbarui',
                'content' => 'Tarif terbaru sudah dicatat.',
            ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'user_id' => $owner->id,
            'title' => 'Catatan diperbarui',
        ]);
    }

    public function test_profile_can_be_updated_without_exposing_the_role(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->put(route('profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'admin@example.com',
            'role' => 'user',
        ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'role' => 'admin',
        ]);
    }
}
