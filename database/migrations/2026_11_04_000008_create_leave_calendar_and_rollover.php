<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up():void
    {
        Schema::table('leave_types',function(Blueprint $table){$table->decimal('carryover_cap_days',7,2)->nullable();});
        Schema::create('company_holidays',function(Blueprint $table){$table->id();$table->string('name');$table->date('holiday_date')->index();$table->boolean('is_recurring')->default(false);$table->string('scope',30)->default('company');$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();$table->timestamps();});
    }
    public function down():void
    {
        Schema::dropIfExists('company_holidays');Schema::table('leave_types',function(Blueprint $table){$table->dropColumn('carryover_cap_days');});
    }
};
