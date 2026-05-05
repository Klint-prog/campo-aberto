<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('animal_weight_records')) {
            Schema::create('animal_weight_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('animal_id')->constrained('animals')->cascadeOnDelete();
                $table->date('weighed_on');
                $table->decimal('weight_kg', 10, 3);
                $table->decimal('average_daily_gain_kg', 10, 4)->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['animal_id', 'weighed_on']);
            });
        }

        if (! Schema::hasTable('animal_health_records')) {
            Schema::create('animal_health_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('animal_id')->nullable()->constrained('animals')->cascadeOnDelete();
                $table->foreignUuid('animal_lot_id')->nullable()->constrained('animal_lots')->cascadeOnDelete();
                $table->string('type');
                $table->date('recorded_on');
                $table->string('diagnosis')->nullable();
                $table->string('medicine_name')->nullable();
                $table->string('medicine_reference')->nullable();
                $table->decimal('dosage', 12, 4)->nullable();
                $table->string('dosage_unit')->nullable();
                $table->decimal('quantity_consumed', 14, 4)->nullable();
                $table->string('quantity_unit')->nullable();
                $table->string('withdrawal_period')->nullable();
                $table->string('responsible')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('animal_vaccination_records')) {
            Schema::create('animal_vaccination_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('animal_id')->nullable()->constrained('animals')->cascadeOnDelete();
                $table->foreignUuid('animal_lot_id')->nullable()->constrained('animal_lots')->cascadeOnDelete();
                $table->date('vaccinated_on');
                $table->string('vaccine_name');
                $table->string('vaccine_reference')->nullable();
                $table->string('batch_number')->nullable();
                $table->decimal('dose', 12, 4)->nullable();
                $table->string('dose_unit')->nullable();
                $table->date('next_due_on')->nullable();
                $table->string('responsible')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_vaccination_records');
        Schema::dropIfExists('animal_health_records');
        Schema::dropIfExists('animal_weight_records');
    }
};
