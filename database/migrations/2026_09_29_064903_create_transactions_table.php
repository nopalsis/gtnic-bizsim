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
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->dateTime('transaction_date');
            $table->string('sales_person_name')->nullable();
            $table->decimal('total_amount', 14, 2);
            $table->decimal('total_discount', 14, 2)->default(0);
            $table->decimal('net_amount', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
