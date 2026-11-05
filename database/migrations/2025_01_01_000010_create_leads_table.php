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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            $table->unsignedBigInteger('industry_id')->nullable();
            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('set null');

            $table->unsignedBigInteger('sales_id'); // الموظف المسؤول
            $table->foreign('sales_id')->references('id')->on('employees')->onDelete('cascade');

            $table->enum('source', ['Facebook', 'Website', 'Phone', 'Visit', 'Other'])->default('Other');

            $table->enum('status', ['New', 'In Progress', 'Hot', 'Converted', 'Lost'])
                ->default('New');

            $table->unsignedBigInteger('customer_id')->nullable(); // بعد التحويل
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
