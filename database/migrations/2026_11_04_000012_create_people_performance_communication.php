<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });
        Schema::table('job_openings', function (Blueprint $table) {
            $table->string('approval_status', 30)->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
        });
        Schema::table('candidates', function (Blueprint $table) {
            $table->decimal('offer_salary', 12, 2)->nullable();
            $table->string('offer_status', 30)->default('none');
            $table->timestamp('offer_sent_at')->nullable();
            $table->date('offer_response_date')->nullable();
            $table->timestamp('offer_decided_at')->nullable();
            $table->foreignId('hired_employee_id')->nullable()->constrained('employees')->nullOnDelete();
        });
        Schema::create('candidate_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('interview_type', 50)->default('مقابلة شخصية');
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('evaluation')->nullable();
            $table->string('decision', 30)->default('pending');
            $table->timestamps();
            $table->index(['candidate_id', 'scheduled_at']);
        });
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->text('employee_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::create('performance_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('measure_unit', 40)->nullable();
            $table->decimal('target_value', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable();
            $table->unsignedTinyInteger('weight')->default(100);
            $table->decimal('manager_score', 5, 2)->nullable();
            $table->text('employee_comment')->nullable();
            $table->timestamps();
        });
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['year', 'status']);
        });
        Schema::create('training_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('skill_or_need');
            $table->string('priority', 20)->default('normal');
            $table->text('rationale')->nullable();
            $table->timestamps();
        });
        Schema::table('training_courses', function (Blueprint $table) {
            $table->foreignId('training_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->decimal('attended_hours', 7, 2)->default(0);
            $table->decimal('assessment_before', 5, 2)->nullable();
            $table->decimal('assessment_after', 5, 2)->nullable();
            $table->unsignedTinyInteger('effectiveness_rating')->nullable();
            $table->text('feedback')->nullable();
        });
        Schema::create('internal_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30)->default('announcement');
            $table->string('title');
            $table->longText('body');
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('requires_acknowledgment')->default(false);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at', 'expires_at']);
        });
        Schema::create('announcement_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->unique(['internal_announcement_id', 'employee_id'], 'announcement_employee_ack_unique');
        });
        Schema::create('employee_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('request_type', 30);
            $table->string('subject');
            $table->json('details')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->string('resolution_file_path')->nullable();
            $table->string('resolution_original_filename')->nullable();
            $table->timestamps();
            $table->index(['status', 'request_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_requests');
        Schema::dropIfExists('announcement_acknowledgments');
        Schema::dropIfExists('internal_announcements');
        Schema::table('training_enrollments', function (Blueprint $table) { $table->dropColumn(['attended_hours','assessment_before','assessment_after','effectiveness_rating','feedback']); });
        Schema::table('training_courses', function (Blueprint $table) { $table->dropForeign(['training_plan_id']); $table->dropForeign(['branch_id']); $table->dropColumn(['training_plan_id','branch_id']); });
        Schema::dropIfExists('training_needs');
        Schema::dropIfExists('training_plans');
        Schema::dropIfExists('performance_goals');
        Schema::table('performance_reviews', function (Blueprint $table) { $table->dropForeign(['approved_by']); $table->dropColumn(['employee_comments','manager_comments','submitted_at','approved_by']); });
        Schema::dropIfExists('candidate_interviews');
        Schema::table('candidates', function (Blueprint $table) { $table->dropForeign(['hired_employee_id']); $table->dropColumn(['offer_salary','offer_status','offer_sent_at','offer_response_date','offer_decided_at','hired_employee_id']); });
        Schema::table('job_openings', function (Blueprint $table) { $table->dropForeign(['requested_by']); $table->dropForeign(['approved_by']); $table->dropColumn(['approval_status','requested_by','approved_by','approved_at','approval_notes']); });
        Schema::table('employees', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropUnique(['user_id']); $table->dropColumn('user_id'); });
    }
};
