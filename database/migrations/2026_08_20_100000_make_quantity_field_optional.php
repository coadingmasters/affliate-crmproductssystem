<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Quantity is no longer built in, so an admin can remove it or move
        // it like any other field. Its own type keeps the stepper rendering.
        DB::table('form_fields')
            ->where('key', 'quantity')
            ->update(['is_system' => false, 'type' => 'quantity']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('form_fields')
            ->where('key', 'quantity')
            ->update(['is_system' => true, 'type' => 'number']);
    }
};
