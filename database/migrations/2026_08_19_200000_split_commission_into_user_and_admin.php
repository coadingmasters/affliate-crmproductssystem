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
        $this->rename('product_prices', 'commission', 'user_commission');
        $this->rename('orders', 'commission_total', 'user_commission_total');

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

        $this->rename('product_prices', 'user_commission', 'commission');
        $this->rename('orders', 'user_commission_total', 'commission_total');
    }

    /**
     * Rename a column, keeping its decimal definition.
     *
     * MySQL wants CHANGE with the full type spelled out; every other driver is
     * happy with the schema builder, which is what the test suite uses.
     */
    private function rename(string $table, string $from, string $to): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE {$table} CHANGE {$from} {$to} DECIMAL(8,2) NOT NULL DEFAULT 0");

            return;
        }

        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->renameColumn($from, $to));
    }
};
