<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('animal_lots')) {
            return;
        }

        Schema::create('animal_lots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignUuid('pasture_id')->nullable()->constrained('pastures')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('species');
            $table->string('purpose')->nullable();
            $table->string('status')->default('active');
            $table->date('started_on')->nullable();
            $table->date('closed_on')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'farm_id', 'name']);
            $table->unique(['tenant_id', 'farm_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_lots');
    }
};
