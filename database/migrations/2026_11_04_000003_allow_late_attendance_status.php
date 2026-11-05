<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'absent', 'late', 'leave') NOT NULL DEFAULT 'present'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('attendances')->where('status', 'late')->update(['status' => 'present']);
            DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'absent', 'leave') NOT NULL DEFAULT 'present'");
        }
    }
};
