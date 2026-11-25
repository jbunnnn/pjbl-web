<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Harga BBM')
            ->assertSee('Impor Excel')
            ->assertSee('Unduh template Excel')
            ->assertSee('Buat laporan PDF')
            ->assertSee('Catatan analisis')
            ->assertSee('Daftar')
            ->assertDontSee('Rencana Perkuliahan')
            ->assertDontSee('PETA MATERI');
    }
}
