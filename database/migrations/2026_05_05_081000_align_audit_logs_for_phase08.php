<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event', 160);
                $table->string('auditable_type')->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->json('metadata')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'event']);
            });

            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('audit_logs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            }

            if (! Schema::hasColumn('audit_logs', 'farm_id')) {
                $table->unsignedBigInteger('farm_id')->nullable()->index();
            }

            if (! Schema::hasColumn('audit_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event', 160)->nullable()->index();
            }

            if (! Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'metadata')) {
                $table->json('metadata')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->ipAddress('ip_address')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Não remove colunas para preservar trilhas de auditoria já registradas.
    }
};
