<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let a partner hand an invoice to someone who cannot sign in.
     *
     * The token is only minted when sharing is asked for, and clearing it
     * closes the link again, so an invoice is private until it is not.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('admin_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
