<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Date the current period's payment is due. Once it passes, a fully
            // paid enrollment rolls over to unpaid for the next month.
            $table->date('due_date')->nullable()->after('date')->index();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};
