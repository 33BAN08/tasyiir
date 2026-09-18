<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // 'ثابت' = fixed monthly amount, 'بالعمولة' = commission. Only the
            // matching amount column is populated; the other is null.
            $table->string('salary_type')->default('ثابت')->after('hours');
            $table->unsignedInteger('fixed_salary')->nullable()->after('salary_type');
            // Percentage (0-100) of tuition collected from the teacher's students.
            // Stored now; the calculation lands with the Salaries module.
            $table->unsignedInteger('commission_rate')->nullable()->after('fixed_salary');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'fixed_salary', 'commission_rate']);
        });
    }
};
