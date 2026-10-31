<?php

namespace App\Http\Controllers;

use App\Models\User;
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
     * Tampilkan halaman Team & User Management.
     */
    public function index(Request $request): Response
    {
        $hasRole = Schema::hasColumn('users', 'role');
        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasStatus = Schema::hasColumn('users', 'status');
        $hasAvatar = Schema::hasColumn('users', 'avatar_url');

        $query = User::query();

        // Filter role jika diminta
        if ($request->filled('role') && $request->role !== 'all') {
            if ($hasRole) {
                $query->where('role', $request->role);
            }
        }

        // Filter status jika diminta
        if ($request->filled('status') && $request->status !== 'all') {
            if ($hasStatus) {
                $query->where('status', $request->status);
            }
        }

        // Search text (nama, email, no telepon)
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search, $hasPhone) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
                if ($hasPhone) {
                    $q->orWhere('phone', 'like', $search);
                }
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderByDesc('name');
        } elseif ($sort === 'newest') {
            $query->orderByDesc('id');
        } elseif ($sort === 'oldest') {
            $query->orderBy('id');
        } else {
            $query->orderBy('name');
        }

        $allUsers = $query->get()->map(function ($u) use ($hasRole, $hasPhone, $hasStatus, $hasAvatar) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $hasRole ? ($u->role ?? 'Operational Staff') : 'Operational Staff',
                'phone' => $hasPhone ? ($u->phone ?? '+62 812-0000-0000') : '+62 812-0000-0000',
                'status' => $hasStatus ? ($u->status ?? 'active') : 'active',
                'avatar_url' => $hasAvatar ? $u->avatar_url : null,
                'created_at' => $u->created_at?->format('d M Y'),
            ];
        });

        // Hitung aggregasi untuk role filter badges
        $totalCount = User::count();
        $adminCount = $hasRole ? User::where('role', 'Administrator')->count() : 1;
        $managerCount = $hasRole ? User::where('role', 'Manager')->count() : 0;
        $opsCount = $hasRole ? User::where('role', 'Operational Staff')->count() : max(0, $totalCount - $adminCount);
        $contractorCount = $hasRole ? User::where('role', 'Contractor')->count() : 0;

        return Inertia::render('Users/Index', [
            'users' => $allUsers,
            'counts' => [
                'all' => $totalCount,
                'administrator' => $adminCount,
                'manager' => $managerCount,
                'operational' => $opsCount,
                'contractor' => $contractorCount,
            ],
            'filters' => [
                'role' => $request->get('role', 'all'),
                'search' => $request->get('search', ''),
                'status' => $request->get('status', 'all'),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Tambahkan staf baru ke dalam sistem.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|in:Administrator,Manager,Operational Staff,Contractor',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,on_leave,inactive',
            'password' => 'nullable|string|min:6',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'] ?? 'password123'),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $userData['role'] = $validated['role'];
        }
        if (Schema::hasColumn('users', 'phone')) {
            $userData['phone'] = $validated['phone'] ?? '+62 812-0000-0000';
        }
        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = $validated['status'];
        }

        User::create($userData);

        return redirect()->route('users.index')->with('success', 'Staf baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data staf.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:Administrator,Manager,Operational Staff,Contractor',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,on_leave,inactive',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if (Schema::hasColumn('users', 'role')) {
            $updateData['role'] = $validated['role'];
        }
        if (Schema::hasColumn('users', 'phone')) {
            $updateData['phone'] = $validated['phone'];
        }
        if (Schema::hasColumn('users', 'status')) {
            $updateData['status'] = $validated['status'];
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'Data staf berhasil diperbarui.');
    }

    /**
     * Hapus data staf.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Hindari menghapus user jika akun sendiri atau sisa 1
        if (User::count() <= 1) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus satu-satunya akun pengguna.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Akun staf berhasil dihapus.');
    }

    /**
     * Ekspor daftar staf ke file CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $hasRole = Schema::hasColumn('users', 'role');
        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasStatus = Schema::hasColumn('users', 'status');

        $users = User::orderBy('name')->get();
        $fileName = 'optima_staff_directory_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['ID', 'Nama Lengkap', 'Email', 'Peran', 'Nomor Kontak', 'Status', 'Tanggal Bergabung'];

        $callback = function () use ($users, $columns, $hasRole, $hasPhone, $hasStatus) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $statusLabel = 'Aktif';
                $userStatus = $hasStatus ? $user->status : 'active';
                if ($userStatus === 'on_leave') {
                    $statusLabel = 'On Leave';
                } elseif ($userStatus === 'inactive') {
                    $statusLabel = 'Nonaktif';
                }

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $hasRole ? ($user->role ?? 'Operational Staff') : 'Operational Staff',
                    $hasPhone ? ($user->phone ?? '-') : '-',
                    $statusLabel,
                    $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
