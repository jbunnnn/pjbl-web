<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnergyDataTest extends TestCase
{
    public function test_electricity_tariffs_are_fetched_with_the_api_key(): void
    {
        Config::set('services.api_indonesia.key', 'test-api-key');
        Http::preventStrayRequests();

        Http::fake([
            'https://use.apiindonesia.id/api/v1/energi/tarif-listrik' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'golongan' => 'R-1/TR',
                        'batas_daya' => '1.300 VA',
                        'tarif_rp_per_kwh' => 1444.7,
                    ],
                ],
                'meta' => ['tanggal' => '2026-11-25'],
            ]),
        ]);

        $response = $this->getJson('/api/energi/tarif-listrik');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.golongan', 'R-1/TR');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://use.apiindonesia.id/api/v1/energi/tarif-listrik'
            && $request->hasHeader('x-api-key', 'test-api-key'));
        $response->assertDontSee('test-api-key');
    }

    public function test_subsidized_fuel_prices_are_fetched_from_the_fuel_endpoint(): void
    {
        Config::set('services.api_indonesia.key', 'test-api-key');
        Http::preventStrayRequests();

        Http::fake([
            'https://use.apiindonesia.id/api/v1/energi/bbm' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'produk' => 'Pertalite RON 90 (JBKP)',
                        'harga' => 10000,
                        'satuan' => 'Rp/liter',
                    ],
                ],
                'meta' => ['tanggal' => '2026-11-25'],
            ]),
        ]);

        $this->getJson('/api/energi/bbm')
            ->assertOk()
            ->assertJsonPath('data.0.produk', 'Pertalite RON 90 (JBKP)');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://use.apiindonesia.id/api/v1/energi/bbm'
            && $request->hasHeader('x-api-key', 'test-api-key'));
    }

    public function test_energy_data_reports_missing_api_key(): void
    {
        Config::set('services.api_indonesia.key', null);
        Http::preventStrayRequests();

        $this->getJson('/api/energi/tarif-listrik')
            ->assertStatus(503)
            ->assertJsonPath('message', 'API Indonesia belum dikonfigurasi untuk data energi.');

        Http::assertNothingSent();
    }

    public function test_unavailable_energy_api_returns_a_gateway_error(): void
    {
        Config::set('services.api_indonesia.key', 'test-api-key');
        Http::preventStrayRequests();
        Http::fake([
            'https://use.apiindonesia.id/api/v1/energi/tarif-listrik' => Http::failedConnection(),
        ]);

        $this->getJson('/api/energi/tarif-listrik')
            ->assertStatus(502)
            ->assertJsonPath('message', 'API Indonesia tidak dapat dijangkau untuk data energi.');
    }
}
