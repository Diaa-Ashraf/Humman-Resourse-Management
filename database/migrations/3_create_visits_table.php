<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            // مندوب المبيعات
            $table->unsignedBigInteger('sales_id');
            $table->foreign('sales_id')->references('id')->on('employees')->onDelete('cascade');

            // Lead أو Customer
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            // التخصص
            $table->unsignedBigInteger('industry_id');
            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('cascade');

            // وقت وتاريخ الزيارة
            $table->date('visit_date');
            $table->time('visit_time')->nullable();

            $table->string('location')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['Pending', 'Successful', 'Failed'])
                  ->default('Pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
