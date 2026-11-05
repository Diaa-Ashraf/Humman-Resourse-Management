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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            $table->unsignedBigInteger('industry_id')->nullable();
            $table->foreign('industry_id')->references('id')->on('industries')->onDelete('set null');

            $table->unsignedBigInteger('sales_id'); // المندوب المسؤول
            $table->foreign('sales_id')->references('id')->on('employees')->onDelete('cascade');

            // Define the column once during table creation; change() is only
            // valid for an existing column and breaks fresh SQLite installs.
            $table->enum('status', ['Active', 'Inactive', 'Waiting Confirmation'])->default('Waiting Confirmation');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
