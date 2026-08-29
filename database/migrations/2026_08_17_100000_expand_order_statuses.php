<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The COD follow-up pipeline.
     */
    private const STATUSES = [
        'new', 'contacted', 'confirmed', 'shipped',
        'delivered', 'paid', 'cancelled', 'returned',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only MySQL has ENUM; elsewhere the column is already a plain string.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::STATUSES)."'";

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM($list) NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fold the newer statuses back into the original three.
        DB::table('orders')->whereIn('status', ['contacted', 'confirmed', 'shipped'])->update(['status' => 'new']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'paid']);
        DB::table('orders')->where('status', 'returned')->update(['status' => 'cancelled']);

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new','paid','cancelled') NOT NULL DEFAULT 'new'");
    }
};
