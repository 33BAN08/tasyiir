<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription packs: an enrollment now covers 1, 3, 6 or 12 months.
 * Every row that exists today was billed monthly, and the default keeps it
 * that way — nothing about an existing enrollment changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_months')->default(1)->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('duration_months');
        });
    }
};
