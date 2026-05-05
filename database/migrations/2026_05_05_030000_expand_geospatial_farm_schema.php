<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        DB::statement('ALTER TABLE farms ADD COLUMN IF NOT EXISTS boundary geometry(MultiPolygon, 4326)');
        DB::statement('CREATE INDEX IF NOT EXISTS farms_boundary_gist ON farms USING GIST (boundary)');

        Schema::create('fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('area_ha', 14, 4)->nullable();
            $table->decimal('perimeter_m', 14, 2)->nullable();
            $table->jsonb('properties')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['farm_id', 'code']);
            $table->index(['tenant_id', 'farm_id']);
        });

        DB::statement('ALTER TABLE fields ADD COLUMN geom geometry(MultiPolygon, 4326) NOT NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS fields_geom_gist ON fields USING GIST (geom)');

        Schema::create('plots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignUuid('field_id')->nullable()->constrained('fields')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('area_ha', 14, 4)->nullable();
            $table->decimal('perimeter_m', 14, 2)->nullable();
            $table->jsonb('properties')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['farm_id', 'code']);
            $table->index(['tenant_id', 'farm_id']);
            $table->index('field_id');
        });

        DB::statement('ALTER TABLE plots ADD COLUMN geom geometry(MultiPolygon, 4326) NOT NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS plots_geom_gist ON plots USING GIST (geom)');

        Schema::create('pastures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('area_ha', 14, 4)->nullable();
            $table->decimal('perimeter_m', 14, 2)->nullable();
            $table->jsonb('properties')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['farm_id', 'code']);
            $table->index(['tenant_id', 'farm_id']);
        });

        DB::statement('ALTER TABLE pastures ADD COLUMN geom geometry(MultiPolygon, 4326) NOT NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS pastures_geom_gist ON pastures USING GIST (geom)');

        Schema::create('map_features', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('poi');
            $table->jsonb('properties')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'farm_id']);
            $table->index('type');
        });

        DB::statement('ALTER TABLE map_features ADD COLUMN geom geometry(Geometry, 4326) NOT NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS map_features_geom_gist ON map_features USING GIST (geom)');

        Schema::create('attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farm_id')->nullable()->constrained('farms')->cascadeOnDelete();
            $table->string('attachable_type');
            $table->uuid('attachable_id');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'farm_id']);
            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('map_features');
        Schema::dropIfExists('pastures');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('fields');
        DB::statement('DROP INDEX IF EXISTS farms_boundary_gist');
        DB::statement('ALTER TABLE farms DROP COLUMN IF EXISTS boundary');
    }
};
