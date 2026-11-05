<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shifts', 'grace_minutes')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->unsignedSmallInteger('grace_minutes')->default(0);
                $table->unsignedSmallInteger('break_minutes')->default(0);
                $table->json('work_days')->nullable();
                $table->boolean('crosses_midnight')->default(false);
                $table->decimal('overtime_multiplier', 5, 2)->default(1.50);
            });
        }

        if (!Schema::hasColumn('employees', 'insurance_wage')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->decimal('insurance_wage', 10, 2)->nullable();
                $table->boolean('insurance_enabled')->default(true);
                $table->boolean('tax_enabled')->default(true);
                $table->string('payroll_bank_name')->nullable();
                $table->string('payroll_bank_account')->nullable();
            });
        }

        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'late_minutes')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'shift_id')) {
                    $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
                }
                $table->unsignedSmallInteger('late_minutes')->default(0);
                $table->unsignedSmallInteger('overtime_minutes')->default(0);
                $table->string('source', 30)->default('manual');
                $table->string('review_status', 30)->default('approved');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_notes')->nullable();
                $table->index(['employee_id','attendance_date']);
                $table->index(['attendance_date','review_status']);
            });
        }

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->date('work_date');
            $table->string('status', 30)->default('scheduled');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id','work_date']);
            $table->index(['shift_id','work_date']);
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('type', 20); // earning or deduction
            $table->boolean('taxable')->default(false);
            $table->boolean('insurable')->default(false);
            $table->boolean('recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->index(['employee_id','effective_from','effective_to'], 'emp_sal_comp_eff_idx');
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date');
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['period_start','period_end']);
            $table->index(['status','period_end']);
        });

        Schema::create('payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->decimal('value', 12, 4)->default(0);
            $table->string('unit', 30)->default('amount');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->decimal('lower_bound', 14, 2)->default(0);
            $table->decimal('upper_bound', 14, 2)->nullable();
            $table->decimal('rate_percent', 6, 3);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->index(['effective_from','effective_to','sort_order'], 'tax_brack_eff_sort_idx');
        });

        if (Schema::hasTable('salaries')) Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('payroll_run_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('absence_amount', 12, 2)->default(0);
            $table->decimal('late_amount', 12, 2)->default(0);
            $table->decimal('insurance_amount', 12, 2)->default(0);
            $table->decimal('employer_insurance_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->json('earnings_breakdown')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->json('calculation_snapshot')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['employee_id','payment_date','status']);
        });

        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('installments');
            $table->decimal('installment_amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->date('requested_at');
            $table->date('first_deduction_date');
            $table->string('reason');
            $table->string('status', 30)->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id','status']);
        });

        Schema::create('advance_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_advance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->date('paid_date')->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->timestamps();
            $table->index(['status','due_date']);
        });

        if (Schema::hasTable('leave_types')) Schema::table('leave_types', function (Blueprint $table) {
            $table->decimal('annual_entitlement_days', 6, 2)->nullable();
            $table->boolean('exclude_weekends')->default(true);
            $table->boolean('requires_coverage')->default(false);
            $table->unsignedSmallInteger('max_consecutive_days')->nullable();
        });

        if (Schema::hasTable('leaves')) Schema::table('leaves', function (Blueprint $table) {
            $table->decimal('days_count', 6, 2)->nullable();
            $table->foreignId('coverage_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('opening_balance', 7, 2)->default(0);
            $table->decimal('accrued_days', 7, 2)->default(0);
            $table->decimal('used_days', 7, 2)->default(0);
            $table->decimal('carried_days', 7, 2)->default(0);
            $table->timestamps();
            $table->unique(['employee_id','leave_type_id','year']);
        });

        Schema::create('leave_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_balance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->decimal('days', 7, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_transactions');
        Schema::dropIfExists('leave_balances');
        Schema::table('leaves', function (Blueprint $table) { $table->dropForeign(['coverage_employee_id']); $table->dropForeign(['reviewed_by']); $table->dropColumn(['days_count','coverage_employee_id','reviewed_by','reviewed_at','review_notes']); });
        Schema::table('leave_types', function (Blueprint $table) { $table->dropColumn(['annual_entitlement_days','exclude_weekends','requires_coverage','max_consecutive_days']); });
        Schema::dropIfExists('advance_installments');
        Schema::dropIfExists('employee_advances');
        Schema::table('salaries', function (Blueprint $table) { $table->dropForeign(['payroll_run_id']); $table->dropForeign(['paid_by']); $table->dropIndex(['employee_id','payment_date','status']); $table->dropColumn(['payroll_run_id','gross_amount','overtime_amount','absence_amount','late_amount','insurance_amount','employer_insurance_amount','tax_amount','advance_amount','earnings_breakdown','deductions_breakdown','calculation_snapshot','paid_at','paid_by']); });
        Schema::dropIfExists('payroll_tax_brackets');
        Schema::dropIfExists('payroll_policies');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('shift_assignments');
        Schema::table('attendances', function (Blueprint $table) { $table->dropForeign(['shift_id']); $table->dropForeign(['reviewed_by']); $table->dropIndex(['employee_id','attendance_date']); $table->dropIndex(['attendance_date','review_status']); $table->dropColumn(['shift_id','late_minutes','overtime_minutes','source','review_status','reviewed_by','review_notes']); });
        Schema::table('employees', function (Blueprint $table) { $table->dropColumn(['insurance_wage','insurance_enabled','tax_enabled','payroll_bank_name','payroll_bank_account']); });
        Schema::table('shifts', function (Blueprint $table) { $table->dropColumn(['grace_minutes','break_minutes','work_days','crosses_midnight','overtime_multiplier']); });
    }
};
