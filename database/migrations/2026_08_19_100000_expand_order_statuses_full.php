<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The full call centre pipeline.
     */
    private const NEW_STATUSES = [
        'new',
        'post_date',
        'going_to_return',
        'sale',
        'awaiting_payment',
        'confirmation_department',
        'duplicate',
        'cancelled',
        'confirmation_failure',
        'card_declined',
        'callback',
        'active_account',
    ];

    /**
     * What was in use before this migration.
     */
    private const OLD_STATUSES = ['new', 'sale', 'post_sale', 'cancel'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->setEnum(array_unique([...self::OLD_STATUSES, ...self::NEW_STATUSES]));

        // post_sale becomes an active account; cancel becomes cancelled.
        DB::table('orders')->where('status', 'post_sale')->update(['status' => 'active_account']);
        DB::table('orders')->where('status', 'cancel')->update(['status' => 'cancelled']);

        $this->setEnum(self::NEW_STATUSES);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setEnum(array_unique([...self::OLD_STATUSES, ...self::NEW_STATUSES]));

        DB::table('orders')->whereIn('status', ['active_account', 'awaiting_payment'])->update(['status' => 'post_sale']);
        DB::table('orders')->whereIn('status', [
            'cancelled', 'duplicate', 'confirmation_failure', 'card_declined', 'going_to_return',
        ])->update(['status' => 'cancel']);
        DB::table('orders')->whereIn('status', ['post_date', 'confirmation_department', 'callback'])->update(['status' => 'new']);

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
