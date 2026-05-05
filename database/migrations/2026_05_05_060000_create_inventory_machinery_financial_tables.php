<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('input');
                $table->string('sku')->nullable();
                $table->string('unit', 50);
                $table->decimal('current_quantity', 16, 4)->default(0);
                $table->decimal('minimum_quantity', 16, 4)->nullable();
                $table->decimal('unit_cost', 16, 4)->nullable();
                $table->string('supplier')->nullable();
                $table->string('batch_number')->nullable();
                $table->date('expires_on')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'farm_id', 'sku']);
                $table->index(['tenant_id', 'farm_id', 'type']);
                $table->index(['tenant_id', 'farm_id', 'name']);
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignUuid('source_event_id')->nullable()->constrained('domain_events')->nullOnDelete();
                $table->string('direction');
                $table->string('reason');
                $table->decimal('quantity', 16, 4);
                $table->string('unit', 50);
                $table->decimal('unit_cost', 16, 4)->nullable();
                $table->decimal('total_cost', 16, 4)->nullable();
                $table->date('moved_on');
                $table->nullableUuidMorphs('movable');
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('source_event_id');
                $table->index(['tenant_id', 'farm_id', 'direction']);
                $table->index(['inventory_item_id', 'moved_on']);
            });
        }

        if (! Schema::hasTable('machines')) {
            Schema::create('machines', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('type')->default('machine');
                $table->string('brand')->nullable();
                $table->string('model')->nullable();
                $table->unsignedSmallInteger('manufacture_year')->nullable();
                $table->decimal('hour_meter', 12, 2)->nullable();
                $table->decimal('odometer_km', 12, 2)->nullable();
                $table->string('fuel_type')->nullable();
                $table->string('operational_status')->default('operational');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'farm_id', 'code']);
                $table->index(['tenant_id', 'farm_id', 'operational_status']);
            });
        }

        if (! Schema::hasTable('maintenance_records')) {
            Schema::create('maintenance_records', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
                $table->foreignUuid('machine_id')->constrained('machines')->cascadeOnDelete();
                $table->string('type');
                $table->string('status')->default('planned');
                $table->date('scheduled_on')->nullable();
                $table->date('performed_on')->nullable();
                $table->string('description');
                $table->decimal('hour_meter', 12, 2)->nullable();
                $table->decimal('odometer_km', 12, 2)->nullable();
                $table->decimal('cost', 16, 2)->nullable();
                $table->string('supplier')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'farm_id', 'status']);
                $table->index(['machine_id', 'performed_on']);
            });
        }

        if (! Schema::hasTable('financial_accounts')) {
            Schema::create('financial_accounts', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->nullable()->constrained('farms')->nullOnDelete();
                $table->string('name');
                $table->string('type')->default('cash');
                $table->decimal('opening_balance', 16, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'farm_id', 'name']);
            });
        }

        if (! Schema::hasTable('financial_categories')) {
            Schema::create('financial_categories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type');
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'type', 'name']);
            });
        }

        if (! Schema::hasTable('financial_transactions')) {
            Schema::create('financial_transactions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('farm_id')->nullable()->constrained('farms')->nullOnDelete();
                $table->foreignUuid('financial_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
                $table->foreignUuid('financial_category_id')->nullable()->constrained('financial_categories')->nullOnDelete();
                $table->foreignUuid('source_event_id')->nullable()->constrained('domain_events')->nullOnDelete();
                $table->string('type');
                $table->string('status')->default('pending');
                $table->string('description');
                $table->decimal('amount', 16, 2);
                $table->date('due_on')->nullable();
                $table->date('paid_on')->nullable();
                $table->nullableUuidMorphs('transactionable');
                $table->foreignUuid('season_id')->nullable()->constrained('seasons')->nullOnDelete();
                $table->foreignUuid('plot_id')->nullable()->constrained('plots')->nullOnDelete();
                $table->foreignUuid('animal_lot_id')->nullable()->constrained('animal_lots')->nullOnDelete();
                $table->foreignUuid('machine_id')->nullable()->constrained('machines')->nullOnDelete();
                $table->foreignUuid('activity_id')->nullable()->constrained('activities')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('source_event_id');
                $table->index(['tenant_id', 'farm_id', 'type', 'status']);
                $table->index(['due_on', 'paid_on']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('financial_categories');
        Schema::dropIfExists('financial_accounts');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
    }
};
