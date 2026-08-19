<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The existing commission becomes the user's share, so no data is lost.
        DB::statement('ALTER TABLE product_prices CHANGE commission user_commission DECIMAL(8,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders CHANGE commission_total user_commission_total DECIMAL(8,2) NOT NULL DEFAULT 0');

        Schema::table('product_prices', function (Blueprint $table) {
            $table->decimal('admin_commission', 8, 2)->default(0)->after('user_commission');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('admin_commission_total', 8, 2)->default(0)->after('user_commission_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn('admin_commission');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('admin_commission_total');
        });

        DB::statement('ALTER TABLE product_prices CHANGE user_commission commission DECIMAL(8,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE orders CHANGE user_commission_total commission_total DECIMAL(8,2) NOT NULL DEFAULT 0');
    }
};
