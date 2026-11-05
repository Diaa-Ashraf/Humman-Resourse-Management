<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('code', 30)->nullable()->unique();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_number', 40)->nullable()->unique();
            $table->string('national_id', 40)->nullable()->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('education_level')->nullable();
            $table->string('employment_type', 40)->default('دوام كامل');
            $table->date('contract_end_date')->nullable();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->index(['branch_id', 'section_id', 'status']);
        });

        Schema::create('employee_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 60);
            $table->json('changes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'created_at']);
        });

        Schema::create('employee_lifecycle_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phase', 30);
            $table->string('title');
            $table->text('notes')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['phase', 'completed_at', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_lifecycle_tasks');
        Schema::dropIfExists('employee_change_logs');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['manager_employee_id']);
            $table->dropIndex(['branch_id', 'section_id', 'status']);
            $table->dropUnique(['employee_number']);
            $table->dropUnique(['national_id']);
            $table->dropColumn(['employee_number','national_id','date_of_birth','gender','address','emergency_contact_name','emergency_contact_phone','education_level','employment_type','contract_end_date','manager_employee_id','termination_date','termination_reason']);
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropUnique(['code']);
            $table->dropColumn(['branch_id','code','is_active']);
        });
    }
};
