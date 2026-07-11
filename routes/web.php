<?php

use App\Http\Controllers\Admin\ClassPlottingController;
use App\Http\Controllers\AcademicCalendarPortalController;
use App\Http\Controllers\Admin\AcademicCalendarController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SemesterLockController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentNoteController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthRecoveryController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ClassAttendanceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\HomeroomPortalController;
use App\Http\Controllers\PortalAuthPageController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SubjectAttendanceController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\StudentViolationController;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request, AuthService $authService) {
    if ($request->user() !== null) {
        $defaultPortal = $authService->defaultPortal($request->user());

        if ($defaultPortal !== null) {
            return redirect(AuthService::portalMap()[$defaultPortal]['dashboard']);
        }
    }

    return redirect()->route('auth.page.login');
});

Route::get('/auth', [PortalAuthPageController::class, 'login'])->name('auth.page.login');
Route::get('/auth/verifikasi-email', [PortalAuthPageController::class, 'verifyEmail'])->name('auth.page.verify-email');
Route::get('/auth/verifikasi-kode', [PortalAuthPageController::class, 'verifyCode'])->name('auth.page.verify-code');
Route::get('/auth/lupa-kata-sandi', [PortalAuthPageController::class, 'resetPassword'])->name('auth.page.reset-password');
Route::post('/auth/verifikasi-email', [AuthRecoveryController::class, 'sendCode'])->middleware(['guest', 'throttle:auth-recovery'])->name('auth.recovery.send-code');
Route::post('/auth/verifikasi-kode', [AuthRecoveryController::class, 'verifyCode'])->middleware(['guest', 'throttle:auth-recovery'])->name('auth.recovery.verify-code');
Route::post('/auth/lupa-kata-sandi', [AuthRecoveryController::class, 'resetPassword'])->middleware(['guest', 'throttle:auth-recovery'])->name('auth.recovery.reset-password');

Route::get('/auth/portals', [AuthController::class, 'portals']);
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login']);
Route::post('/login/{portal}', [AuthController::class, 'portalLogin'])->middleware(['guest', 'throttle:login']);
Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

Route::prefix('admin')->middleware('role:admin')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'admin']);
    Route::get('/rekap-kehadiran', [PortalDashboardController::class, 'adminAttendanceRecap']);
    Route::get('/laporan-statistik', [PortalDashboardController::class, 'adminAttendanceReport']);
    Route::get('/data-siswa', [PortalDashboardController::class, 'adminStudents']);
    Route::get('/data-guru', [PortalDashboardController::class, 'adminTeachers']);
    Route::get('/data-kelas', [PortalDashboardController::class, 'adminClasses']);
    Route::get('/mata-pelajaran', [PortalDashboardController::class, 'adminSubjects']);
    Route::get('/kalender-akademik', [AcademicCalendarController::class, 'page']);
    Route::get('/manajemen-pengguna', [UserManagementController::class, 'page']);
    Route::get('/pengaturan', [AppSettingController::class, 'page']);
    Route::get('/catatan-siswa', [StudentNoteController::class, 'page']);
    Route::get('/export/{dataset}/{format?}', [ImportExportController::class, 'export']);
    Route::get('/import-template/{type}', [ImportExportController::class, 'template']);
    Route::post('/import/{type}', [ImportExportController::class, 'import']);

    Route::resource('pelanggaran', StudentViolationController::class)->except(['create', 'edit', 'show']);

    Route::apiResource('siswa', StudentController::class)->parameters(['siswa' => 'student']);
    Route::apiResource('guru', TeacherController::class)->parameters(['guru' => 'teacher']);
    Route::apiResource('kelas', SchoolClassController::class)->parameters(['kelas' => 'schoolClass']);
    Route::apiResource('mapel', SubjectController::class)->parameters(['mapel' => 'subject']);
    Route::apiResource('jadwal-ajar', TeachingAssignmentController::class)->parameters(['jadwal-ajar' => 'teachingAssignment']);
    Route::apiResource('kalender-akademik-data', AcademicCalendarController::class)->parameters(['kalender-akademik-data' => 'kalenderAkademik'])->except(['create', 'edit']);
    Route::get('/semester-lock', [SemesterLockController::class, 'status']);
    Route::post('/semester-lock', [SemesterLockController::class, 'lock']);
    Route::delete('/semester-lock', [SemesterLockController::class, 'unlock']);
    Route::post('pengguna/migrasi-profil', [UserManagementController::class, 'migrateProfiles']);
    Route::apiResource('pengguna', UserManagementController::class)->parameters(['pengguna' => 'pengguna']);
    Route::apiResource('setting', AppSettingController::class)->parameters(['setting' => 'setting']);
    Route::apiResource('catatan', StudentNoteController::class)->parameters(['catatan' => 'catatanSiswa']);
    Route::put('kelas/{schoolClass}/ploting', [ClassPlottingController::class, 'update']);
    Route::get('/pengumuman', [AnnouncementController::class, 'page']);
    Route::apiResource('announcements', AnnouncementController::class)->parameters(['announcements' => 'announcement'])->except(['create', 'edit']);

    Route::prefix('schedule')->name('admin.schedule.')->group(function () {
        Route::get('/generate', [ScheduleController::class, 'generatePage'])->name('generate.page');
        Route::post('/generate', [ScheduleController::class, 'generate'])->name('generate');
        Route::get('/class/{classId}/{academicYear}', [ScheduleController::class, 'showClassSchedule'])->name('class')->where('academicYear', '.*');
        Route::get('/teacher/{teacherId}/{academicYear}', [ScheduleController::class, 'showTeacherSchedule'])->name('teacher')->where('academicYear', '.*');
        Route::get('/analyze/{academicYear}', [ScheduleController::class, 'analyze'])->name('analyze')->where('academicYear', '.*');
        Route::get('/conflicts/teacher/{academicYear}', [ScheduleController::class, 'teacherConflicts'])->name('conflicts.teacher')->where('academicYear', '.*');
        Route::get('/conflicts/room/{academicYear}', [ScheduleController::class, 'roomConflicts'])->name('conflicts.room')->where('academicYear', '.*');
        Route::get('/workload/{academicYear}', [ScheduleController::class, 'teacherWorkload'])->name('workload')->where('academicYear', '.*');
        Route::get('/distribution/{classId}/{academicYear}', [ScheduleController::class, 'dailyDistribution'])->name('distribution')->where('academicYear', '.*');
        Route::get('/export/{classId}/{academicYear}/{format}', [ScheduleController::class, 'export'])->name('export')->where('academicYear', '\d{4}/\d{4}');
    });
});

Route::prefix('guru-mapel')->middleware('role:guru_mapel')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'teacher']);
    Route::get('/jadwal-mengajar', [PortalDashboardController::class, 'teacherSchedulePage']);
    Route::redirect('/absensi-siswa', '/guru-mapel/absensi-siswa/tambah');
    Route::get('/absensi-siswa/tambah', [PortalDashboardController::class, 'teacherAttendancePage']);
    Route::get('/absensi-siswa/jadwal', [PortalDashboardController::class, 'teacherAttendanceSchedules']);
    Route::get('/absensi-siswa/daftar', [PortalDashboardController::class, 'teacherAttendanceListPage']);
    Route::get('/rekap-absensi', [PortalDashboardController::class, 'teacherAttendanceRecapPage']);
    Route::get('/rekap-absensi/print', [PortalDashboardController::class, 'teacherAttendancePrint']);
    Route::get('/rekap-absensi/export/{format}', [PortalDashboardController::class, 'teacherAttendanceExport']);

    Route::get('/jadwal-ajar', [TeacherPortalController::class, 'schedule']);
    Route::get('/siswa', [TeacherPortalController::class, 'students']);
    Route::get('/rekap-absensi-mapel', [SubjectAttendanceController::class, 'index']);
    Route::get('/kalender-akademik', [AcademicCalendarPortalController::class, 'index']);
    Route::get('/status-absensi', [AcademicCalendarPortalController::class, 'attendanceStatus']);
    Route::post('/absensi-mapel', [SubjectAttendanceController::class, 'store']);
});

Route::prefix('walikelas')->middleware('homeroom-class')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'homeroom']);
    Route::get('/data-siswa', [PortalDashboardController::class, 'homeroomStudentsPage']);
    Route::get('/absensi-kelas', [PortalDashboardController::class, 'homeroomAttendancePage']);
    Route::get('/rekap-absensi', [PortalDashboardController::class, 'homeroomAttendanceRecapPage']);
    Route::get('/rekap-absensi/print', [PortalDashboardController::class, 'homeroomAttendancePrint']);
    Route::get('/rekap-absensi/export/{format}', [PortalDashboardController::class, 'homeroomAttendanceExport']);

    Route::get('/kelas', [HomeroomPortalController::class, 'classes']);
    Route::get('/siswa', [HomeroomPortalController::class, 'students']);
    Route::get('/catatan-siswa', [StudentNoteController::class, 'homeroomPage']);
    Route::get('/catatan', [StudentNoteController::class, 'homeroomIndex']);
    Route::post('/catatan', [StudentNoteController::class, 'homeroomStore']);
    Route::get('/catatan/{catatanSiswa}', [StudentNoteController::class, 'homeroomShow']);
    Route::put('/catatan/{catatanSiswa}', [StudentNoteController::class, 'homeroomUpdate']);
    Route::patch('/catatan/{catatanSiswa}', [StudentNoteController::class, 'homeroomUpdate']);
    Route::delete('/catatan/{catatanSiswa}', [StudentNoteController::class, 'homeroomDestroy']);
    Route::get('/rekap-absensi-kelas', [ClassAttendanceController::class, 'index']);
    Route::get('/kalender-akademik', [AcademicCalendarPortalController::class, 'index']);
    Route::get('/status-absensi', [AcademicCalendarPortalController::class, 'attendanceStatus']);
    Route::post('/absensi-kelas', [ClassAttendanceController::class, 'store']);
});

Route::prefix('orang-tua')->middleware('role:orang_tua')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'parent']);
    Route::get('/kalender-akademik', [AcademicCalendarPortalController::class, 'index']);
});

Route::prefix('siswa')->middleware('role:siswa')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'student']);

    Route::get('/jadwal-mata-pelajaran', [StudentPortalController::class, 'schedulePage']);
    Route::get('/daftar-hadir', [StudentPortalController::class, 'attendancePage']);
    Route::get('/daftar-hadir/{assignmentId}', [StudentPortalController::class, 'attendanceDetailPage']);
    Route::get('/kalender-akademik', [AcademicCalendarPortalController::class, 'index']);
});

Route::prefix('wakasek-kesiswaan')->middleware('role:wakasek_kesiswaan')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'wakasekKesiswaan']);
    Route::resource('pelanggaran', StudentViolationController::class)->except(['create', 'edit', 'show']);
});

Route::prefix('guru-piket')->middleware('role:guru_piket')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'guruPiket']);
    Route::resource('pelanggaran', StudentViolationController::class)->except(['create', 'edit', 'show']);
});
