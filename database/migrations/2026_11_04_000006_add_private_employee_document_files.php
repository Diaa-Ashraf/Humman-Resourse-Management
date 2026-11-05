<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_documents', 'file_path')) $table->string('file_path')->nullable();
            if (!Schema::hasColumn('employee_documents', 'original_filename')) $table->string('original_filename')->nullable();
            if (!Schema::hasColumn('employee_documents', 'mime_type')) $table->string('mime_type', 120)->nullable();
            if (!Schema::hasColumn('employee_documents', 'file_size')) $table->unsignedInteger('file_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $columns = array_filter(['file_path','original_filename','mime_type','file_size'], fn($column) => Schema::hasColumn('employee_documents', $column));
            if ($columns) $table->dropColumn($columns);
        });
    }
};
