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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_price_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 8, 2);
            // ENUM is MySQL-specific. Other drivers (SQLite under test) get a
            // plain string, which the later status migrations then leave alone.
            if (DB::getDriverName() === 'mysql') {
                $table->enum('status', ['new', 'paid', 'cancelled'])->default('new');
            } else {
                $table->string('status', 40)->default('new');
            }
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
