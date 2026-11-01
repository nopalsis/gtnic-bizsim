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
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'sales', 'craftsman'])->default('craftsman')->after('email');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'salary_type')) {
                $table->string('salary_type', 50)->default('fixed_monthly')->after('phone');
            }
            if (!Schema::hasColumn('users', 'base_salary')) {
                $table->decimal('base_salary', 12, 2)->default(0)->after('salary_type');
            }
            if (!Schema::hasColumn('users', 'piece_rate')) {
                $table->decimal('piece_rate', 10, 2)->default(0)->after('base_salary');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('piece_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [
                'role',
                'phone',
                'salary_type',
                'base_salary',
                'piece_rate',
                'payment_method',
                'bank_name',
                'bank_account_number',
                'is_active',
            ];

            $dropList = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropList[] = $column;
                }
            }

            if (!empty($dropList)) {
                $table->dropColumn($dropList);
            }
        });
    }
};

