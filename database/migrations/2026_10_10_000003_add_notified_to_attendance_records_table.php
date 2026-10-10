<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who warned the parent, and when. Both nullable: every attendance row that
 * already exists at a client simply reads "not notified yet", which is true.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('state');
            $table->foreignId('notified_by')->nullable()->after('notified_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('notified_by');
            $table->dropColumn('notified_at');
        });
    }
};
