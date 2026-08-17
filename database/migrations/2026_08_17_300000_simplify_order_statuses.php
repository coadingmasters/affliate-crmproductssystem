<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The pipeline this project actually uses.
     */
    private const NEW_STATUSES = ['new', 'sale', 'post_sale', 'cancel'];

    /**
     * What the eight-stage pipeline used before.
     */
    private const OLD_STATUSES = [
        'new', 'contacted', 'confirmed', 'shipped',
        'delivered', 'paid', 'cancelled', 'returned',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen to a superset first so both old and new values are valid
        // while the rows are remapped.
        $this->setEnum(array_unique([...self::OLD_STATUSES, ...self::NEW_STATUSES]));

        DB::table('orders')->whereIn('status', ['contacted', 'confirmed'])->update(['status' => 'new']);
        DB::table('orders')->whereIn('status', ['shipped', 'delivered', 'paid'])->update(['status' => 'sale']);
        DB::table('orders')->whereIn('status', ['cancelled', 'returned'])->update(['status' => 'cancel']);

        $this->setEnum(self::NEW_STATUSES);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setEnum(array_unique([...self::OLD_STATUSES, ...self::NEW_STATUSES]));

        DB::table('orders')->whereIn('status', ['sale', 'post_sale'])->update(['status' => 'paid']);
        DB::table('orders')->where('status', 'cancel')->update(['status' => 'cancelled']);

        $this->setEnum(self::OLD_STATUSES);
    }

    /**
     * Point the status column at the given set of values.
     *
     * @param  array<int, string>  $statuses
     */
    private function setEnum(array $statuses): void
    {
        $list = "'".implode("','", $statuses)."'";

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM($list) NOT NULL DEFAULT 'new'");
    }
};
