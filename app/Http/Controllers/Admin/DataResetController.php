<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DataResetController extends Controller
{
    /**
     * Defines the table groups that can be reset.
     * Order within each group matters for FK-safe deletion (children first).
     *
     * @return array<string, array{label: string, description: string, icon: string, tables: string[], danger_level: string}>
     */
    protected function tableGroups(): array
    {
        return [
            'absensi_kelas' => [
                'label' => 'Absensi Kelas',
                'description' => 'Data absensi harian kelas (class_attendances).',
                'icon' => 'clipboard',
                'tables' => ['class_attendances'],
                'danger_level' => 'high',
            ],
            'absensi_mapel' => [
                'label' => 'Absensi Mapel',
                'description' => 'Data absensi per mata pelajaran (subject_attendances).',
                'icon' => 'clipboard',
                'tables' => ['subject_attendances'],
                'danger_level' => 'high',
            ],
            'absensi_offline' => [
                'label' => 'Absensi Offline',
                'description' => 'Data absensi dari perangkat offline.',
                'icon' => 'wifi-off',
                'tables' => ['offline_attendances'],
                'danger_level' => 'medium',
            ],
            'catatan_siswa' => [
                'label' => 'Catatan Siswa',
                'description' => 'Catatan perilaku/akademik oleh wali kelas.',
                'icon' => 'file-text',
                'tables' => ['student_notes'],
                'danger_level' => 'medium',
            ],
            'pelanggaran' => [
                'label' => 'Pelanggaran Siswa',
                'description' => 'Data pelanggaran dan poin siswa.',
                'icon' => 'alert-triangle',
                'tables' => ['student_violations'],
                'danger_level' => 'medium',
            ],
            'jadwal_ajar' => [
                'label' => 'Jadwal Ajar',
                'description' => 'Jadwal mengajar dan riwayat generate jadwal.',
                'icon' => 'calendar',
                'tables' => ['subject_attendances', 'teaching_assignments', 'schedule_generations'],
                'danger_level' => 'high',
            ],
            'mata_pelajaran' => [
                'label' => 'Mata Pelajaran',
                'description' => 'Data mapel beserta relasi guru-mapel dan kelas-mapel.',
                'icon' => 'book-open',
                'tables' => ['subject_attendances', 'teaching_assignments', 'school_class_subject', 'subject_teacher', 'subjects'],
                'danger_level' => 'critical',
            ],
            'data_siswa' => [
                'label' => 'Data Siswa',
                'description' => 'Seluruh data siswa termasuk detail, absensi, dan catatan terkait.',
                'icon' => 'graduation-cap',
                'tables' => ['class_attendances', 'subject_attendances', 'student_notes', 'student_violations', 'student_details', 'students'],
                'danger_level' => 'critical',
            ],
            'data_guru' => [
                'label' => 'Data Guru',
                'description' => 'Seluruh data guru termasuk jadwal ajar terkait.',
                'icon' => 'users',
                'tables' => ['subject_attendances', 'teaching_assignments', 'subject_teacher', 'school_classes', 'teachers'],
                'danger_level' => 'critical',
            ],
            'data_kelas' => [
                'label' => 'Data Kelas',
                'description' => 'Seluruh data kelas, plotting siswa dan mapel kelas.',
                'icon' => 'layout',
                'tables' => ['class_attendances', 'teaching_assignments', 'school_class_subject', 'students', 'school_classes'],
                'danger_level' => 'critical',
            ],
            'kalender_akademik' => [
                'label' => 'Kalender Akademik',
                'description' => 'Event kalender akademik (libur, ujian, dll).',
                'icon' => 'calendar-days',
                'tables' => ['academic_calendars'],
                'danger_level' => 'low',
            ],
            'pengumuman' => [
                'label' => 'Pengumuman',
                'description' => 'Data pengumuman sekolah.',
                'icon' => 'megaphone',
                'tables' => ['announcements'],
                'danger_level' => 'low',
            ],
            'semester_lock' => [
                'label' => 'Kunci Semester',
                'description' => 'Status kunci semester.',
                'icon' => 'lock',
                'tables' => ['semester_locks'],
                'danger_level' => 'low',
            ],
        ];
    }

    /**
     * GET /admin/data-reset
     * Returns the list of resetable table groups with row counts.
     */
    public function index(): JsonResponse
    {
        $groups = [];

        foreach ($this->tableGroups() as $key => $group) {
            $tables = [];
            foreach (array_unique($group['tables']) as $table) {
                if (Schema::hasTable($table)) {
                    $tables[] = [
                        'name' => $table,
                        'row_count' => DB::table($table)->count(),
                    ];
                }
            }

            $totalRows = collect($tables)->sum('row_count');

            $groups[] = [
                'key' => $key,
                'label' => $group['label'],
                'description' => $group['description'],
                'icon' => $group['icon'],
                'danger_level' => $group['danger_level'],
                'tables' => $tables,
                'total_rows' => $totalRows,
            ];
        }

        return response()->json([
            'groups' => $groups,
            'protected_tables' => ['users', 'app_settings', 'migrations', 'cache', 'sessions', 'jobs', 'failed_jobs', 'personal_access_tokens', 'auth_verification_codes'],
        ]);
    }

    /**
     * POST /admin/data-reset
     * Validates password, then truncates selected table groups.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'confirmation_text' => ['required', 'string', 'in:HAPUS DATA'],
            'groups' => ['required', 'array', 'min:1'],
            'groups.*' => ['string'],
        ], [
            'password.required' => 'Password wajib diisi untuk konfirmasi.',
            'confirmation_text.in' => 'Teks konfirmasi harus "HAPUS DATA".',
            'groups.required' => 'Pilih minimal satu kelompok data.',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Re-authenticate with password
        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Password salah. Penghapusan dibatalkan.',
                'errors' => ['password' => ['Password tidak sesuai.']],
            ], 422);
        }

        $allGroups = $this->tableGroups();
        $selectedKeys = $request->input('groups');
        $invalidKeys = array_diff($selectedKeys, array_keys($allGroups));

        if (! empty($invalidKeys)) {
            return response()->json([
                'message' => 'Kelompok data tidak valid: ' . implode(', ', $invalidKeys),
            ], 422);
        }

        // Collect all unique tables from selected groups (preserving FK order)
        $allTables = [];
        foreach ($selectedKeys as $key) {
            foreach ($allGroups[$key]['tables'] as $table) {
                if (! in_array($table, $allTables, true) && Schema::hasTable($table)) {
                    $allTables[] = $table;
                }
            }
        }

        // Count rows before deletion for the summary
        $summary = [];
        foreach ($allTables as $table) {
            $summary[$table] = DB::table($table)->count();
        }

        // Execute deletion inside a transaction
        try {
            DB::transaction(function () use ($allTables): void {
                // Disable FK checks temporarily for clean truncation
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                foreach ($allTables as $table) {
                    DB::table($table)->truncate();
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });
        } catch (\Throwable $e) {
            // Make sure FK checks are re-enabled even on failure
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Log::error('Data reset failed', [
                'user_id' => $user->id,
                'groups' => $selectedKeys,
                'tables' => $allTables,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.',
            ], 500);
        }

        // Log the action for audit
        Log::warning('DATA RESET executed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'groups' => $selectedKeys,
            'tables_truncated' => $allTables,
            'rows_deleted' => $summary,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Invalidate caches
        cache()->flush();

        $totalDeleted = array_sum($summary);

        return response()->json([
            'message' => "Berhasil menghapus {$totalDeleted} data dari " . count($allTables) . ' tabel.',
            'summary' => collect($summary)->map(fn (int $count, string $table) => [
                'table' => $table,
                'rows_deleted' => $count,
            ])->values()->all(),
            'total_deleted' => $totalDeleted,
        ]);
    }
}
