<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom roles belong to one tenant. Spatie keeps (name, guard_name) globally
 * unique, so a tenant's custom role is stored under a namespaced `name`
 * ("tenant-3:مسؤول التسويق") and the plain label lives in `display_name`.
 * Built-in roles keep tenant_id NULL and are shared by every center.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('display_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('display_name');
        });
    }
};
