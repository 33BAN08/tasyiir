<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One attendance record per student per day. group_id is deliberately NOT
     * part of the key: a student belongs to a single group at a time
     * (Student.group_id), so (student_id, date) already identifies the session
     * and saving the same roster twice must update, not duplicate. If a student
     * changes group, the record keeps the group they attended with that day.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unique(['student_id', 'date'], 'attendance_records_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('attendance_records_student_date_unique');
        });
    }
};
