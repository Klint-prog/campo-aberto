<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('farm_id')->index();
            $table->string('provider', 40)->default('open_meteo');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone', 80)->default('America/Sao_Paulo');
            $table->timestamp('forecast_at')->index();
            $table->string('period', 20)->index();
            $table->decimal('temperature_celsius', 6, 2)->nullable();
            $table->decimal('precipitation_mm', 8, 2)->nullable();
            $table->decimal('relative_humidity_percent', 5, 2)->nullable();
            $table->decimal('wind_speed_kmh', 8, 2)->nullable();
            $table->decimal('solar_radiation_wm2', 10, 2)->nullable();
            $table->decimal('heat_index_celsius', 6, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'farm_id', 'provider', 'forecast_at', 'period'], 'weather_forecasts_unique_scope');
        });

        Schema::create('weather_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('farm_id')->index();
            $table->date('observed_on')->index();
            $table->string('source', 40)->default('manual');
            $table->decimal('min_temperature_celsius', 6, 2)->nullable();
            $table->decimal('max_temperature_celsius', 6, 2)->nullable();
            $table->decimal('precipitation_mm', 8, 2)->nullable();
            $table->decimal('relative_humidity_percent', 5, 2)->nullable();
            $table->decimal('wind_speed_kmh', 8, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'farm_id', 'observed_on', 'source'], 'weather_histories_unique_scope');
        });

        Schema::create('manual_rain_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('farm_id')->index();
            $table->date('measured_on')->index();
            $table->decimal('amount_mm', 8, 2);
            $table->string('gauge_name')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('internal_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('farm_id')->nullable()->index();
            $table->string('type', 80)->index();
            $table->string('severity', 20)->default('info')->index();
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('farm_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreignId('internal_alert_id')->nullable()->constrained('internal_alerts')->nullOnDelete();
            $table->string('channel', 40)->default('internal');
            $table->string('status', 30)->default('pending')->index();
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('internal_alerts');
        Schema::dropIfExists('manual_rain_records');
        Schema::dropIfExists('weather_histories');
        Schema::dropIfExists('weather_forecasts');
    }
};
