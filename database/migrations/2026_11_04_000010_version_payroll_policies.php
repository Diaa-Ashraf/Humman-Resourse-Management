<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->dropUnique('payroll_policies_key_unique');
            $table->index(['key', 'effective_from', 'effective_to'], 'payroll_policies_effective_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_policies', function (Blueprint $table) {
            $table->dropIndex('payroll_policies_effective_lookup');
            $table->unique('key');
        });
    }
};
