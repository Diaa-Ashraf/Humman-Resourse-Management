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
        if (!Schema::hasTable('customers') || !Schema::hasColumn('customers', 'sales_id')) return;
        Schema::table('customers', function (Blueprint $table) {
            // حذف عمود sales_id الغير منطقي
            $table->dropForeign(['sales_id']);
            $table->dropColumn('sales_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('customers') || Schema::hasColumn('customers', 'sales_id')) return;
        Schema::table('customers', function (Blueprint $table) {
            // إعادة إضافة عمود sales_id إذا لزم الأمر
            $table->unsignedBigInteger('sales_id')->nullable();
        });
    }
};
