<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_consulta_clima_por_fazenda_autorizada_e_salva_previsao(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(60, 12), 200),
        ]);

        $response = $this->getJson('/api/internal/v1/farms/weather/forecast?tenant_id=1&farm_id=10&latitude=-7.56&longitude=-35.0');

        $response->assertOk()
            ->assertJsonPath('data.provider', 'open_meteo')
            ->assertJsonCount(1, 'data.alerts');

        $this->assertDatabaseHas('weather_forecasts', [
            'tenant_id' => 1,
            'farm_id' => 10,
            'provider' => 'open_meteo',
            'period' => 'daily',
        ]);

        $this->assertDatabaseHas('internal_alerts', [
            'tenant_id' => 1,
            'farm_id' => 10,
            'type' => 'weather',
            'title' => 'Alerta climático',
        ]);
    }

    public function test_falha_externa_nao_quebra_aplicacao(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['error' => true], 500),
        ]);

        $response = $this->getJson('/api/internal/v1/farms/weather/forecast?tenant_id=1&farm_id=10&latitude=-7.56&longitude=-35.0');

        $response->assertStatus(503)
            ->assertJsonPath('message', 'Falha ao consultar API climática externa. A aplicação permaneceu disponível.')
            ->assertJsonPath('fallback.source', 'empty');
    }

    public function test_cache_climatico_funciona(): void
    {
        Cache::flush();
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->openMeteoPayload(5, 10), 200),
        ]);

        $this->getJson('/api/internal/v1/farms/weather/forecast?tenant_id=2&farm_id=20&latitude=-8.0&longitude=-36.0')
            ->assertOk();

        $this->getJson('/api/internal/v1/farms/weather/forecast?tenant_id=2&farm_id=20&latitude=-8.0&longitude=-36.0')
            ->assertOk();

        Http::assertSentCount(1);
    }

    public function test_registro_manual_de_chuva_e_salvo(): void
    {
        $response = $this->postJson('/api/internal/v1/farms/weather/manual-rain', [
            'tenant_id' => 3,
            'farm_id' => 30,
            'measured_on' => '2026-05-05',
            'amount_mm' => 24.7,
            'gauge_name' => 'Pluviometro sede',
            'notes' => 'Leitura manual da manha.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', 3)
            ->assertJsonPath('data.farm_id', 30);

        $this->assertDatabaseHas('manual_rain_records', [
            'tenant_id' => 3,
            'farm_id' => 30,
            'amount_mm' => 24.7,
        ]);

        $this->assertDatabaseHas('weather_histories', [
            'tenant_id' => 3,
            'farm_id' => 30,
            'source' => 'manual_rain_gauge',
        ]);
    }

    public function test_notificacao_respeita_tenant_e_fazenda(): void
    {
        $this->postJson('/api/internal/v1/alerts', [
            'tenant_id' => 4,
            'farm_id' => 40,
            'type' => 'maintenance_pending',
            'severity' => 'warning',
            'title' => 'Manutenção pendente',
            'message' => 'Revisar trator.',
        ])->assertCreated();

        $this->postJson('/api/internal/v1/alerts', [
            'tenant_id' => 5,
            'farm_id' => 50,
            'type' => 'low_stock',
            'severity' => 'critical',
            'title' => 'Estoque baixo',
            'message' => 'Comprar insumo.',
        ])->assertCreated();

        $response = $this->getJson('/api/internal/v1/notifications?tenant_id=4&farm_id=40');

        $response->assertOk()
            ->assertJsonPath('data.data.0.tenant_id', 4)
            ->assertJsonPath('data.data.0.farm_id', 40);
    }

    private function openMeteoPayload(float $rain, float $wind): array
    {
        return [
            'daily' => [
                'time' => ['2026-05-05'],
                'temperature_2m_max' => [31.0],
                'temperature_2m_min' => [22.0],
                'precipitation_sum' => [$rain],
                'wind_speed_10m_max' => [$wind],
            ],
            'hourly' => [
                'time' => ['2026-05-05T00:00', '2026-05-05T01:00'],
                'temperature_2m' => [25.0, 24.5],
                'relative_humidity_2m' => [82, 84],
                'precipitation' => [0.0, 1.2],
                'wind_speed_10m' => [7.0, 9.0],
                'shortwave_radiation' => [0.0, 0.0],
            ],
        ];
    }
}
