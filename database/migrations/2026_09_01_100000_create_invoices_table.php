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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // One invoice per order, so a customer cannot bill twice.
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Filled in from the id the moment the row lands, so the number
            // is sequential without a second sequence to keep in step.
            $table->string('number', 20)->nullable()->unique();
            $table->decimal('amount', 10, 2)->default(0);

            // Plain string rather than an enum, so every driver understands it.
            $table->string('status', 20)->default('pending');

            $table->text('note')->nullable();        // from the customer
            $table->text('admin_note')->nullable();  // from the admin

            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
