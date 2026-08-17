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
        Schema::table('product_prices', function (Blueprint $table) {
            // What the affiliate earns per unit of this package.
            $table->decimal('commission', 8, 2)->default(0)->after('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Captured at order time so later commission changes never rewrite history.
            $table->decimal('commission_total', 8, 2)->default(0)->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn('commission');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('commission_total');
        });
    }
};
