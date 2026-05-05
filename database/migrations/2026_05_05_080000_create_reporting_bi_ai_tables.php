<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('report_exports')) {
            Schema::create('report_exports', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('report_key', 80);
                $table->string('format', 20);
                $table->json('filters')->nullable();
                $table->string('status', 30)->default('generated');
                $table->string('file_path')->nullable();
                $table->unsignedInteger('row_count')->default(0);
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'report_key']);
                $table->index(['tenant_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('dashboard_snapshots')) {
            Schema::create('dashboard_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->string('dashboard_key', 80);
                $table->json('metrics');
                $table->timestamp('calculated_at');
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'dashboard_key']);
            });
        }

        if (! Schema::hasTable('ai_consultations')) {
            Schema::create('ai_consultations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('use_case', 80);
                $table->text('question');
                $table->json('context')->nullable();
                $table->string('status', 30)->default('answered');
                $table->timestamp('consulted_at');
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'use_case']);
                $table->index(['tenant_id', 'consulted_at']);
            });
        }

        if (! Schema::hasTable('ai_recommendations')) {
            Schema::create('ai_recommendations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ai_consultation_id')->constrained('ai_consultations')->cascadeOnDelete();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('recommendation');
                $table->string('risk_level', 30)->default('informational');
                $table->boolean('critical_action')->default(false);
                $table->boolean('requires_confirmation')->default(false);
                $table->json('safety_disclaimer');
                $table->timestamp('recommended_at');
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'critical_action']);
            });
        }

        if (! Schema::hasTable('ai_action_confirmations')) {
            Schema::create('ai_action_confirmations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ai_recommendation_id')->constrained('ai_recommendations')->cascadeOnDelete();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action_key', 120);
                $table->text('confirmation_note');
                $table->timestamp('confirmed_at');
                $table->timestamps();

                $table->index(['tenant_id', 'farm_id', 'action_key']);
            });
        }

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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_action_confirmations');
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_consultations');
        Schema::dropIfExists('dashboard_snapshots');
        Schema::dropIfExists('report_exports');
    }
};
