<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('hours')->default(0);
            $table->unsignedInteger('rate')->default(0);
            $table->unsignedInteger('salary')->default(0);
            $table->unsignedInteger('paid')->default(0);
            $table->unsignedInteger('remaining')->default(0);
            $table->string('status')->default('غير مدفوع');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
