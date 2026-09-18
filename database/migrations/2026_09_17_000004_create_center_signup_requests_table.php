<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A prospective center's signup, held until a platform admin approves it.
 * Platform-level data: it exists before any tenant does, so no tenant_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_signup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('center_name');
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone', 30)->nullable();
            $table->string('password'); // hashed at submission; approval copies it as-is
            $table->string('status', 20)->default('pending'); // pending / approved / rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete(); // set on approval
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('owner_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_signup_requests');
    }
};
