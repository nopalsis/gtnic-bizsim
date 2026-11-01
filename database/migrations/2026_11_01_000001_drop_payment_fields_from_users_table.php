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
        Schema::table('users', function (Blueprint $table): void {
            $columnsToDrop = [];
            foreach (['payment_method', 'bank_name', 'bank_account_number'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'payment_method')) {
                $table->string('payment_method', 50)->default('transfer')->after('piece_rate');
            }
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number', 100)->nullable()->after('bank_name');
            }
        });
    }
};

