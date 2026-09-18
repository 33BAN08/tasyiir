<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->date('registered_at');
            $table->string('enrollment_status')->default('نشط');
            $table->string('financial_status')->default('غير مؤدي');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'enrollment_status']);
            $table->index(['tenant_id', 'financial_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
