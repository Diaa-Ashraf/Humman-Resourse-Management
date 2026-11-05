<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            $table->json('scope_branch_ids')->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->text('mfa_secret')->nullable();
            $table->timestamp('mfa_enabled_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });
        Schema::create('user_branch_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id','branch_id']);
            $table->index(['branch_id','user_id']);
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('route_name', 160)->nullable();
            $table->string('method', 12);
            $table->string('subject_type', 180)->nullable();
            $table->string('subject_id', 80)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id','created_at']);
            $table->index(['action','created_at']);
            $table->index(['subject_type','subject_id']);
        });
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 160)->unique();
            $table->string('device_label', 160)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id','revoked_at','last_seen_at']);
        });
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('data_category', 80)->unique();
            $table->string('label', 160);
            $table->unsignedInteger('retention_days')->nullable();
            $table->boolean('cleanup_enabled')->default(false);
            $table->text('legal_basis')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_branch_scopes');
        Schema::table('payroll_runs', function (Blueprint $table) { $table->dropColumn('scope_branch_ids'); });
        Schema::table('users', function (Blueprint $table) { $table->dropColumn(['mfa_secret','mfa_enabled_at','password_changed_at']); });
    }
};
