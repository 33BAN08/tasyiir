<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Commission-based teachers have no hourly rate; rate is only informational
    // for fixed salaries (fixed_salary / hours).
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->unsignedInteger('rate')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->unsignedInteger('rate')->nullable(false)->default(0)->change();
        });
    }
};
