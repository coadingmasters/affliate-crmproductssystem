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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            // Machine name used as the input name and the answer key.
            $table->string('key')->unique();
            $table->string('type', 32);
            $table->string('label');

            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();

            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);

            // System fields map to real order columns and cannot be deleted.
            $table->boolean('is_system')->default(false);

            // 'half' sits two per row, 'full' spans the row.
            $table->string('width', 8)->default('half');

            // Choices for select, radio and checkbox group fields.
            $table->json('options')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Answers to admin-built fields, keyed by the field's key.
            $table->json('form_data')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });

        Schema::dropIfExists('form_fields');
    }
};
