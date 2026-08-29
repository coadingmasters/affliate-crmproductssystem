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
            // A voice note the customer attaches to their own order.
            $table->string('voice_note_path')->nullable()->after('form_data');
            $table->string('voice_note_name')->nullable()->after('voice_note_path');
            $table->timestamp('voice_note_uploaded_at')->nullable()->after('voice_note_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['voice_note_path', 'voice_note_name', 'voice_note_uploaded_at']);
        });
    }
};
