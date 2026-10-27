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
        Schema::create('operational_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('workshop_name')->default('OptimaBiz Leathercraft & Shoes');
            $table->integer('employee_count')->default(5);
            $table->decimal('salary_per_employee', 12, 2)->default(3200000);
            $table->decimal('monthly_fixed_cost', 14, 2)->default(6500000);
            $table->integer('monthly_max_capacity')->default(500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_settings');
    }
};
