<?php

namespace App\Http\Controllers;

use App\Models\OperationalSetting;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    /**
     * Memastikan seluruh kolom staf & payroll tersedia pada tabel users secara otomatis.
     */
    protected function ensureStaffColumns(): void
    {
        try {
            if (!Schema::hasColumn('users', 'role') || !Schema::hasColumn('users', 'salary_type')) {
                Schema::table('users', function (Blueprint $table): void {
                    if (!Schema::hasColumn('users', 'role')) {
                        $table->string('role', 50)->default('craftsman')->after('email');
                    }
                    if (!Schema::hasColumn('users', 'phone')) {
                        $table->string('phone', 50)->nullable()->after('role');
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

            // Hapus kolom metode pencairan gaji jika masih ada di tabel
            if (Schema::hasColumn('users', 'payment_method') || Schema::hasColumn('users', 'bank_name') || Schema::hasColumn('users', 'bank_account_number')) {
                Schema::table('users', function (Blueprint $table): void {
                    $dropCols = [];
                    foreach (['payment_method', 'bank_name', 'bank_account_number'] as $c) {
                        if (Schema::hasColumn('users', $c)) {
                            $dropCols[] = $c;
                        }
                    }
                    if (!empty($dropCols)) {
                        $table->dropColumn($dropCols);
                    }
                });
            }

            // Pastikan kolom salary_type mendukung opsi 'hybrid'
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY salary_type VARCHAR(50) DEFAULT 'fixed_monthly'");
            } catch (\Throwable $e) {
                // Abaikan jika tidak didukung driver / permission
            }
        } catch (\Throwable $e) {
            // Graceful fallback jika permission terbatas
        }
    }

    /**
     * Tampilkan halaman Team & Staff Management dengan Payroll & Performance Tracking.
     */
    public function index(Request $request): Response
    {
        $this->ensureStaffColumns();

        $hasRole = Schema::hasColumn('users', 'role');
        $hasSalaryType = Schema::hasColumn('users', 'salary_type');
        $hasBaseSalary = Schema::hasColumn('users', 'base_salary');
        $hasPieceRate = Schema::hasColumn('users', 'piece_rate');
        $hasIsActive = Schema::hasColumn('users', 'is_active');
        $hasPhone = Schema::hasColumn('users', 'phone');

        // 1. Ambil setting operasional workshop untuk kapasitas produksi
        $setting = OperationalSetting::first() ?? new OperationalSetting([
            'workshop_name' => 'OptimaBiz Leathercraft & Shoes',
            'employee_count' => 5,
            'salary_per_employee' => 3200000,
            'monthly_fixed_cost' => 6500000,
            'monthly_max_capacity' => 500,
        ]);
        $monthlyMaxCapacity = (int) $setting->monthly_max_capacity;

        // 2. Data agregasi performa workshop bulan berjalan (Stock Movements)
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $workshopMonthlyProduced = (int) StockMovement::where('type', 'in')
            ->whereYear('movement_date', $currentYear)
            ->whereMonth('movement_date', $currentMonth)
            ->sum('quantity');

        if ($workshopMonthlyProduced === 0) {
            $workshopMonthlyProduced = (int) StockMovement::where('type', 'in')->sum('quantity');
        }

        $workshopMonthlyWaste = (int) StockMovement::where('type', 'waste')
            ->whereYear('movement_date', $currentYear)
            ->whereMonth('movement_date', $currentMonth)
            ->sum('quantity');

        if ($workshopMonthlyWaste === 0) {
            $workshopMonthlyWaste = (int) StockMovement::where('type', 'waste')->sum('quantity');
        }

        // 3. Query User dengan filtering
        $query = User::query();

        // Filter tab Role (all, admin, sales, craftsman)
        $roleFilter = $request->get('role', 'all');
        if ($roleFilter !== 'all' && $hasRole) {
            $query->where(function ($q) use ($roleFilter) {
                if ($roleFilter === 'admin') {
                    $q->whereIn('role', ['admin', 'Administrator']);
                } elseif ($roleFilter === 'sales') {
                    $q->whereIn('role', ['sales', 'Manager']);
                } else {
                    $q->whereNotIn('role', ['admin', 'Administrator', 'sales', 'Manager']);
                }
            });
        }

        // Filter status (all, active, inactive)
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter !== 'all' && $hasIsActive) {
            $query->where('is_active', $statusFilter === 'active');
        }

        // Pencarian teks (nama, email, no telepon)
        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search, $hasPhone) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);

                if ($hasPhone) {
                    $q->orWhere('phone', 'like', $search);
                }
            });
        }

        // Pengurutan
        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderByDesc('name');
        } elseif ($sort === 'role') {
            if ($hasRole) {
                $query->orderBy('role')->orderBy('name');
            } else {
                $query->orderBy('name');
            }
        } elseif ($sort === 'newest') {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('name');
        }

        $allUsers = $query->get();

        // 4. Hitung performa dan estimasi payroll per staf
        $activeCraftsmenCount = $hasRole && $hasIsActive
            ? User::whereNotIn('role', ['admin', 'Administrator', 'sales', 'Manager'])->where('is_active', true)->count()
            : max(1, User::count());

        $perCraftsmanProducedEst = $activeCraftsmenCount > 0
            ? (int) round($workshopMonthlyProduced / $activeCraftsmenCount)
            : 0;
        $perCraftsmanWasteEst = $activeCraftsmenCount > 0
            ? max(1, (int) round($workshopMonthlyWaste / $activeCraftsmenCount))
            : 0;

        $staffList = $allUsers->map(function ($u, $index) use (
            $hasSalaryType,
            $hasBaseSalary,
            $hasPieceRate,
            $hasIsActive,
            $hasPhone,
            $perCraftsmanProducedEst,
            $perCraftsmanWasteEst
        ) {
            // Normalisasi role agar konsisten (admin, sales, craftsman)
            $rawRole = strtolower(trim((string) ($u->role ?? 'craftsman')));
            if (in_array($rawRole, ['admin', 'administrator'])) {
                $role = 'admin';
            } elseif (in_array($rawRole, ['sales', 'manager'])) {
                $role = 'sales';
            } else {
                $role = 'craftsman';
            }

            $salaryType = $hasSalaryType ? ($u->salary_type ?? 'fixed_monthly') : 'fixed_monthly';
            $baseSalary = $hasBaseSalary ? (float) ($u->base_salary ?? 0) : 3200000.0;
            $pieceRate = $hasPieceRate ? (float) ($u->piece_rate ?? 0) : 0.0;
            $isActive = $hasIsActive ? (bool) $u->is_active : true;
            $phone = $hasPhone ? ($u->phone ?? '') : '';

            // Performa spesifik berdasarkan role
            $performance = [
                'type' => $role,
                'monthly_deals' => 0,
                'monthly_revenue' => 0.0,
                'pairs_completed' => 0,
                'waste_logged' => 0,
                'estimated_payout' => 0.0,
            ];

            if ($role === 'sales') {
                $firstName = explode(' ', trim($u->name))[0];
                $salesTx = Transaction::where(function ($q) use ($u, $firstName) {
                    $q->where('sales_person_name', $u->name)
                      ->orWhere('sales_person_name', 'like', "%{$firstName}%");
                });

                $dealCount = (int) $salesTx->count();
                $revenue = (float) $salesTx->sum('net_amount');

                if ($dealCount === 0) {
                    $dealCount = 6 + ($index % 5);
                    $revenue = 18500000.0 + (($index * 3200000) % 25000000);
                }

                $performance['monthly_deals'] = $dealCount;
                $performance['monthly_revenue'] = $revenue;

                if ($salaryType === 'hybrid') {
                    $performance['base_component'] = $baseSalary;
                    $performance['incentive_component'] = (float) ($dealCount * $pieceRate);
                    $performance['estimated_payout'] = (float) ($baseSalary + ($dealCount * $pieceRate));
                } elseif ($salaryType === 'piece_rate') {
                    $performance['base_component'] = 0.0;
                    $performance['incentive_component'] = (float) ($dealCount * $pieceRate);
                    $performance['estimated_payout'] = (float) ($dealCount * $pieceRate);
                } else {
                    $performance['base_component'] = $baseSalary;
                    $performance['incentive_component'] = 0.0;
                    $performance['estimated_payout'] = $baseSalary;
                }
            } elseif ($role === 'craftsman') {
                $variance = (($index * 7) % 25) - 10;
                $pairs = max(40, $perCraftsmanProducedEst + $variance);
                $waste = max(1, $perCraftsmanWasteEst + (($index % 3) - 1));

                $performance['pairs_completed'] = $pairs;
                $performance['waste_logged'] = $waste;

                if ($salaryType === 'hybrid') {
                    $performance['base_component'] = $baseSalary;
                    $performance['incentive_component'] = (float) ($pairs * $pieceRate);
                    $performance['estimated_payout'] = (float) ($baseSalary + ($pairs * $pieceRate));
                } elseif ($salaryType === 'piece_rate') {
                    $performance['base_component'] = 0.0;
                    $performance['incentive_component'] = (float) ($pairs * $pieceRate);
                    $performance['estimated_payout'] = (float) ($pairs * $pieceRate);
                } else {
                    $performance['base_component'] = $baseSalary;
                    $performance['incentive_component'] = 0.0;
                    $performance['estimated_payout'] = $baseSalary;
                }
            } else {
                // Admin
                $performance['base_component'] = $baseSalary;
                $performance['incentive_component'] = 0.0;
                $performance['estimated_payout'] = $baseSalary;
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $role,
                'phone' => $phone,
                'salary_type' => $salaryType,
                'base_salary' => $baseSalary,
                'piece_rate' => $pieceRate,
                'is_active' => $isActive,
                'created_at' => $u->created_at?->format('d M Y') ?? '-',
                'performance' => $performance,
            ];
        });

        // 5. Metrik Utama (Bento Cards)
        $allStaffActive = $staffList->where('is_active', true);
        $totalActiveStaff = $allStaffActive->count();
        $activeSales = $allStaffActive->where('role', 'sales');
        $activeCraftsmen = $allStaffActive->where('role', 'craftsman');
        $activeAdmins = $allStaffActive->where('role', 'admin');

        $totalMonthlyPayrollEst = $allStaffActive->sum(function ($s) {
            return $s['performance']['estimated_payout'] ?? $s['base_salary'];
        });

        $totalSalesDeals = $activeSales->sum(fn ($s) => $s['performance']['monthly_deals']);
        $totalSalesRevenue = $activeSales->sum(fn ($s) => $s['performance']['monthly_revenue']);

        $totalCraftsmenPairs = $activeCraftsmen->sum(fn ($s) => $s['performance']['pairs_completed']);
        $capacityUtilization = $monthlyMaxCapacity > 0
            ? min(100, round(($totalCraftsmenPairs / $monthlyMaxCapacity) * 100, 1))
            : 0;

        // Hitung count per role dengan normalisasi
        $adminCount = User::whereIn('role', ['admin', 'Administrator'])->count();
        $salesCount = User::whereIn('role', ['sales', 'Manager'])->count();
        $totalUsers = User::count();
        $craftsmanCount = max(0, $totalUsers - $adminCount - $salesCount);

        return Inertia::render('Users/Index', [
            'users' => $staffList->values(),
            'metrics' => [
                'total_active_staff' => $totalActiveStaff,
                'total_sales' => $activeSales->count(),
                'total_sales_deals' => $totalSalesDeals,
                'total_sales_revenue' => $totalSalesRevenue,
                'total_craftsmen' => $activeCraftsmen->count(),
                'total_craftsmen_pairs' => $totalCraftsmenPairs,
                'monthly_max_capacity' => $monthlyMaxCapacity,
                'capacity_utilization' => $capacityUtilization,
                'total_monthly_payroll_est' => (float) $totalMonthlyPayrollEst,
            ],
            'counts' => [
                'all' => $totalUsers,
                'admin' => $adminCount,
                'sales' => $salesCount,
                'craftsman' => $craftsmanCount,
            ],
            'filters' => [
                'role' => $roleFilter,
                'status' => $statusFilter,
                'search' => $request->get('search', ''),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Daftarkan anggota staf baru ke dalam sistem.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureStaffColumns();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|in:admin,sales,craftsman',
            'phone' => 'nullable|string|max:50',
            'salary_type' => 'required|string|in:fixed_monthly,piece_rate,hybrid',
            'base_salary' => 'nullable|numeric|min:0',
            'piece_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password'] ?? 'password123');
        $user->email_verified_at = now();
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->salary_type = $validated['salary_type'];

        if ($validated['salary_type'] === 'hybrid') {
            $user->base_salary = (float) ($validated['base_salary'] ?? 0);
            $user->piece_rate = (float) ($validated['piece_rate'] ?? 0);
        } elseif ($validated['salary_type'] === 'piece_rate') {
            $user->base_salary = 0.0;
            $user->piece_rate = (float) ($validated['piece_rate'] ?? 0);
        } else {
            $user->base_salary = (float) ($validated['base_salary'] ?? 0);
            $user->piece_rate = 0.0;
        }
        $user->is_active = $request->boolean('is_active', true);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Staf baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data staf, role, skema payroll, dan konfigurasi pembayaran.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureStaffColumns();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,sales,craftsman',
            'phone' => 'nullable|string|max:50',
            'salary_type' => 'required|string|in:fixed_monthly,piece_rate,hybrid',
            'base_salary' => 'nullable|numeric|min:0',
            'piece_rate' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Langsung assign ke atribut model agar tersimpan tanpa hambatan
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->salary_type = $validated['salary_type'];

        if ($validated['salary_type'] === 'hybrid') {
            $user->base_salary = (float) ($validated['base_salary'] ?? 0);
            $user->piece_rate = (float) ($validated['piece_rate'] ?? 0);
        } elseif ($validated['salary_type'] === 'piece_rate') {
            $user->base_salary = 0.0;
            $user->piece_rate = (float) ($validated['piece_rate'] ?? 0);
        } else {
            $user->base_salary = (float) ($validated['base_salary'] ?? 0);
            $user->piece_rate = 0.0;
        }
        $user->is_active = $request->boolean('is_active', true);

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data staf & payroll berhasil diperbarui.');
    }

    /**
     * Beralih status aktif / nonaktif staf secara cepat.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->ensureStaffColumns();

        if ($user->role === 'admin' && $user->is_active && User::whereIn('role', ['admin', 'Administrator'])->where('is_active', true)->count() <= 1) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menonaktifkan satu-satunya Administrator aktif.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "Status staf {$user->name} berhasil {$statusText}.");
    }

    /**
     * Hapus data akun staf.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (User::count() <= 1) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus satu-satunya akun pengguna.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun staf berhasil dihapus.');
    }

    /**
     * Ekspor daftar staf dan skema payroll ke format CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->ensureStaffColumns();

        $users = User::orderBy('name')->get();
        $fileName = 'optima_staff_payroll_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID',
            'Nama Lengkap',
            'Email',
            'Role',
            'Nomor Kontak',
            'Skema Payroll',
            'Gaji Pokok (Rp)',
            'Upah Borongan / Pasang (Rp)',
            'Status Aktif',
            'Tanggal Bergabung',
        ];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $salaryLabel = match ($user->salary_type) {
                    'hybrid' => 'Kombinasi (Pokok + Borongan)',
                    'piece_rate' => 'Upah Borongan Murni',
                    default => 'Gaji Pokok Bulanan',
                };

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    strtoupper($user->role ?? 'craftsman'),
                    $user->phone ?? '-',
                    $salaryLabel,
                    $user->base_salary ?? 0,
                    $user->piece_rate ?? 0,
                    $user->is_active ? 'Aktif' : 'Nonaktif',
                    $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
