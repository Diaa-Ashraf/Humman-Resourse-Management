<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The base customers migration already defines this status column.
        // This historical migration is intentionally a no-op: changing an
        // already-created enum rebuilds the table unnecessarily on SQLite.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No schema change was applied in up().
    }
};
