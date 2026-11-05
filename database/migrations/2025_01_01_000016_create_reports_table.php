<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('title');               
            $table->text('description')->nullable(); 
            $table->enum('type', ['attendance', 'leaves', 'salaries', 'deductions', 'bonuses'])->default('attendance');
            $table->json('filters')->nullable();    // الفلاتر المستخدمة في التقرير (مثل التواريخ)
            $table->timestamps();                   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
