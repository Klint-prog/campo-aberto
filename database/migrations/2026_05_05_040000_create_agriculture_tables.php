<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crops')) {
            Schema::create('crops', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('scientific_name')->nullable();
                $table->string('cycle_type')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'name']);
            });
        }

        if (! Schema::hasTable('crop_varieties')) {
            Schema::create('crop_varieties', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('crop_id')->constrained('crops')->cascadeOnDelete();
                $table->string('name');
                $table->string('cultivar_code')->nullable();
                $table->unsignedInteger('cycle_days')->nullable();
                $table->decimal('expected_yield_kg_ha', 12, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'crop_id', 'name']);
            });
        }

        if (! Schema::hasTable('seasons')) {
            Schema::create('seasons', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->string('name');
                $table->date('starts_on');
                $table->date('ends_on')->nullable();
                $table->string('status')->default('planned');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'farm_id', 'name']);
            });
        }

        if (! Schema::hasTable('activities')) {
            Schema::create('activities', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('plot_id')->nullable()->constrained('plots')->nullOnDelete();
                $table->foreignUuid('season_id')->nullable()->constrained('seasons')->nullOnDelete();
                $table->foreignUuid('crop_id')->nullable()->constrained('crops')->nullOnDelete();
                $table->foreignUuid('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
                $table->string('type');
                $table->string('status')->default('planned');
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('planned_start_on')->nullable();
                $table->date('planned_end_on')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->decimal('planned_area_ha', 12, 4)->nullable();
                $table->decimal('actual_area_ha', 12, 4)->nullable();
                $table->decimal('estimated_productivity_kg_ha', 12, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'farm_id', 'status']);
                $table->index(['tenant_id', 'plot_id', 'type']);
            });
        }

        if (! Schema::hasTable('activity_inputs')) {
            Schema::create('activity_inputs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('activity_id')->constrained('activities')->cascadeOnDelete();
                $table->string('input_name');
                $table->string('input_type')->nullable();
                $table->string('input_reference')->nullable();
                $table->decimal('quantity', 14, 4);
                $table->string('unit');
                $table->decimal('unit_cost', 14, 4)->nullable();
                $table->decimal('total_cost', 14, 4)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'farm_id']);
                $table->index(['activity_id', 'input_reference']);
            });
        }

        if (! Schema::hasTable('harvests')) {
            Schema::create('harvests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('plot_id')->nullable()->constrained('plots')->nullOnDelete();
                $table->foreignUuid('season_id')->nullable()->constrained('seasons')->nullOnDelete();
                $table->foreignUuid('crop_id')->constrained('crops')->cascadeOnDelete();
                $table->foreignUuid('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
                $table->foreignUuid('activity_id')->nullable()->constrained('activities')->nullOnDelete();
                $table->date('harvested_on');
                $table->decimal('harvested_area_ha', 12, 4);
                $table->decimal('total_weight_kg', 14, 4);
                $table->decimal('productivity_kg_ha', 14, 4);
                $table->string('quality_grade')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'farm_id', 'harvested_on']);
                $table->index(['season_id', 'crop_id']);
            });
        }

        if (! Schema::hasTable('domain_events')) {
            Schema::create('domain_events', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignUuid('farm_id')->nullable()->constrained('farms')->nullOnDelete();
                $table->string('event_name');
                $table->string('aggregate_type');
                $table->uuid('aggregate_id');
                $table->json('payload');
                $table->timestamp('occurred_at');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['event_name', 'occurred_at']);
                $table->index(['aggregate_type', 'aggregate_id']);
            });
        }

        if (! Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignUuid('farm_id')->nullable()->constrained('farms')->nullOnDelete();
                $table->uuidMorphs('attachable');
                $table->string('disk')->default('local');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->string('auditable_type')->nullable();
                $table->uuid('auditable_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'action']);
                $table->index(['auditable_type', 'auditable_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
        Schema::dropIfExists('activity_inputs');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('crop_varieties');
        Schema::dropIfExists('crops');
    }
};
