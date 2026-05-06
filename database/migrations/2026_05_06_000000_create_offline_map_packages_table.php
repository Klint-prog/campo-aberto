<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_map_packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('name');
            $table->string('status', 30)->default('pending')->index();
            $table->string('tile_format', 20)->default('png');
            $table->unsignedTinyInteger('min_zoom')->default(10);
            $table->unsignedTinyInteger('max_zoom')->default(18);
            $table->decimal('north', 10, 7)->nullable();
            $table->decimal('south', 10, 7)->nullable();
            $table->decimal('east', 10, 7)->nullable();
            $table->decimal('west', 10, 7)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('storage_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_map_packages');
    }
};
