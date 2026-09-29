<?php

namespace App\Models;

use Database\Factories\OperationalSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalSetting extends Model
{
    /** @use HasFactory<OperationalSettingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_count',
        'salary_per_employee',
        'monthly_fixed_cost',
        'monthly_max_capacity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employee_count' => 'integer',
            'salary_per_employee' => 'decimal:2',
            'monthly_fixed_cost' => 'decimal:2',
            'monthly_max_capacity' => 'integer',
        ];
    }
}
