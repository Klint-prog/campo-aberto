<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('animal_reproduction_records')) {
            Schema::create('animal_reproduction_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('female_animal_id')->constrained('animals')->cascadeOnDelete();
                $table->foreignUuid('male_animal_id')->nullable()->constrained('animals')->nullOnDelete();
                $table->string('type');
                $table->date('event_on');
                $table->date('expected_birth_on')->nullable();
                $table->string('status')->default('recorded');
                $table->boolean('pregnancy_confirmed')->nullable();
                $table->date('pregnancy_checked_on')->nullable();
                $table->foreignUuid('offspring_animal_id')->nullable()->constrained('animals')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('animal_movements')) {
            Schema::create('animal_movements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('animal_id')->constrained('animals')->cascadeOnDelete();
                $table->foreignUuid('from_animal_lot_id')->nullable()->constrained('animal_lots')->nullOnDelete();
                $table->foreignUuid('to_animal_lot_id')->nullable()->constrained('animal_lots')->nullOnDelete();
                $table->foreignUuid('from_pasture_id')->nullable()->constrained('pastures')->nullOnDelete();
                $table->foreignUuid('to_pasture_id')->nullable()->constrained('pastures')->nullOnDelete();
                $table->string('type');
                $table->date('moved_on');
                $table->decimal('amount', 14, 2)->nullable();
                $table->string('document')->nullable();
                $table->string('counterparty')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('feed_stock')) {
            Schema::create('feed_stock', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->nullable();
                $table->string('unit')->default('kg');
                $table->decimal('current_quantity', 14, 4)->default(0);
                $table->decimal('unit_cost', 14, 4)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['tenant_id', 'farm_id', 'name']);
            });
        }

        if (! Schema::hasTable('animal_feed_consumptions')) {
            Schema::create('animal_feed_consumptions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('animal_id')->nullable()->constrained('animals')->cascadeOnDelete();
                $table->foreignUuid('animal_lot_id')->nullable()->constrained('animal_lots')->cascadeOnDelete();
                $table->foreignUuid('feed_stock_id')->nullable()->constrained('feed_stock')->nullOnDelete();
                $table->date('consumed_on');
                $table->string('feed_name');
                $table->decimal('quantity', 14, 4);
                $table->string('unit');
                $table->decimal('unit_cost', 14, 4)->nullable();
                $table->decimal('total_cost', 14, 4)->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_feed_consumptions');
        Schema::dropIfExists('feed_stock');
        Schema::dropIfExists('animal_movements');
        Schema::dropIfExists('animal_reproduction_records');
    }
};
