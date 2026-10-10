<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional pack prices in MAD. `price` stays the monthly price; a null pack
 * price means "no special rate", and Course::priceFor() falls back to
 * price × months.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('price_3_months')->nullable()->after('price');
            $table->unsignedInteger('price_6_months')->nullable()->after('price_3_months');
            $table->unsignedInteger('price_12_months')->nullable()->after('price_6_months');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['price_3_months', 'price_6_months', 'price_12_months']);
        });
    }
};
