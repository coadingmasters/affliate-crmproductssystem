<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Each of these statuses carries its own date, so an order that
            // was sold and later flagged for return keeps both.
            $table->date('sale_date')->nullable()->after('post_date');
            $table->date('return_date')->nullable()->after('sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['sale_date', 'return_date']);
        });
    }
};
