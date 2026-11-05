<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('headcount')->default(1);
            $table->string('employment_type')->default('دوام كامل');
            $table->enum('status', ['open', 'paused', 'closed'])->default('open');
            $table->date('closing_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('source')->nullable();
            $table->enum('status', ['new', 'screening', 'interview', 'offer', 'hired', 'rejected'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvement_areas')->nullable();
            $table->text('goals')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'returned'])->default('draft');
            $table->timestamps();
            $table->index(['employee_id', 'period_start', 'period_end']);
        });

        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->unsignedSmallInteger('duration_hours')->default(1);
            $table->decimal('cost', 10, 2)->default(0);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'completed', 'failed', 'withdrawn'])->default('enrolled');
            $table->decimal('score', 5, 2)->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['training_course_id', 'employee_id']);
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->string('reference_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['category', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('training_enrollments');
        Schema::dropIfExists('training_courses');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('job_openings');
    }
};
