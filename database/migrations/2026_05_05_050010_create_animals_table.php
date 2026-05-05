<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('animals')) {
            return;
        }

        Schema::create('animals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignUuid('animal_lot_id')->nullable()->constrained('animal_lots')->nullOnDelete();
            $table->string('internal_code');
            $table->string('ear_tag')->nullable();
            $table->string('rfid')->nullable();
            $table->string('name')->nullable();
            $table->string('species');
            $table->string('breed')->nullable();
            $table->string('sex');
            $table->date('birth_date')->nullable();
            $table->string('status')->default('active');
            $table->decimal('birth_weight_kg', 10, 3)->nullable();
            $table->date('acquired_on')->nullable();
            $table->decimal('purchase_price', 14, 2)->nullable();
            $table->string('purchase_document')->nullable();
            $table->string('origin')->nullable();
            $table->date('sold_on')->nullable();
            $table->decimal('sale_price', 14, 2)->nullable();
            $table->string('sale_document')->nullable();
            $table->date('died_on')->nullable();
            $table->string('death_cause')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'farm_id', 'internal_code']);
            $table->unique(['tenant_id', 'farm_id', 'ear_tag']);
            $table->unique(['tenant_id', 'farm_id', 'rfid']);
            $table->index(['tenant_id', 'farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
