<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthPortalEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    protected function tearDown(): void
    {
        foreach ([
            'admin@sarunis.test|127.0.0.1',
            'guru.mapel@sarunis.test|127.0.0.1',
            'walikelas@sarunis.test|127.0.0.1',
            'guru.wali@sarunis.test|127.0.0.1',
            'orangtua@sarunis.test|127.0.0.1',
            'siswa@sarunis.test|127.0.0.1',
            'unknown@sarunis.test|127.0.0.1',
        ] as $rateLimitKey) {
            RateLimiter::clear($rateLimitKey);
        }

        parent::tearDown();
    }

    public function test_generic_login_returns_available_portals_and_me_profile(): void
    {
        $response = $this->loginByPath('/login', 'orangtua@sarunis.test');

        $response->assertOk()
            ->assertJsonPath('data.active_portal', 'orang-tua')
            ->assertJsonPath('data.redirect_to', '/orang-tua/dashboard')
            ->assertJsonFragment(['key' => 'orang-tua']);

        $this->getJson('/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'orangtua@sarunis.test')
            ->assertJsonPath('data.active_portal', 'orang-tua');
    }

    public function test_generic_login_redirects_each_showcase_role_to_its_default_dashboard(): void
    {
        $accounts = [
            'admin@sarunis.test' => ['admin', '/admin/dashboard'],
            'guru.mapel@sarunis.test' => ['guru-mapel', '/guru-mapel/dashboard'],
            'walikelas@sarunis.test' => ['walikelas', '/walikelas/dashboard'],
            'orangtua@sarunis.test' => ['orang-tua', '/orang-tua/dashboard'],
            'siswa@sarunis.test' => ['siswa', '/siswa/dashboard'],
        ];

        foreach ($accounts as $email => [$portal, $dashboard]) {
            $this->postJson('/logout');

            $this->loginByPath('/login', $email)
                ->assertOk()
                ->assertJsonPath('data.active_portal', $portal)
                ->assertJsonPath('data.redirect_to', $dashboard);
        }
    }

    public function test_login_with_remember_flag_still_authenticates_correctly(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'admin@sarunis.test',
            'password' => 'password',
            'remember' => true,
        ]);

        $admin = User::query()->where('email', 'admin@sarunis.test')->firstOrFail();

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'admin@sarunis.test')
            ->assertJsonPath('data.active_portal', 'admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_portal_login_rejects_wrong_role(): void
    {
        $this->postJson('/login/guru-mapel', [
            'email' => 'walikelas@sarunis.test',
            'password' => 'password',
        ])->assertForbidden();

        $this->getJson('/me')->assertUnauthorized();
    }

    public function test_admin_can_manage_master_data_and_plot_class(): void
    {
        $this->loginByPath('/login/admin', 'admin@sarunis.test');

        $teacherUser = User::query()->create([
            'name' => 'Guru Baru',
            'email' => 'guru.baru@sarunis.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'roles' => [],
        ]);

        $studentUser = User::query()->create([
            'name' => 'Siswa Baru',
            'email' => 'siswa.baru@sarunis.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'roles' => [],
        ]);

        $teacherResponse = $this->postJson('/admin/guru', [
            'user_id' => $teacherUser->id,
            'nip' => '198900000010',
            'name' => 'Guru Baru',
            'is_subject_teacher' => true,
            'phone' => '081299999901',
            'address' => 'Jl. Guru Baru',
        ])->assertCreated();

        $subjectResponse = $this->postJson('/admin/mapel', [
            'code' => 'SEJ',
            'name' => 'Sejarah',
            'description' => 'Sejarah Indonesia',
        ])->assertCreated();

        $classResponse = $this->postJson('/admin/kelas', [
            'name' => 'X IPS 1',
            'level' => 'X',
            'academic_year' => '2025/2026',
            'description' => 'Kelas IPS baru',
        ])->assertCreated();

        $studentResponse = $this->postJson('/admin/siswa', [
            'user_id' => $studentUser->id,
            'nik' => '20001',
            'nisn' => '4000000001',
            'name' => 'Siswa Baru',
            'gender' => 'P',
            'birth_date' => '2010-07-16',
            'phone' => '081399999901',
            'address' => 'Jl. Siswa Baru',
        ])->assertCreated();

        $teacherId = $teacherResponse->json('data.id');
        $subjectId = $subjectResponse->json('data.id');
        $classId = $classResponse->json('data.id');
        $studentId = $studentResponse->json('data.id');

        $this->putJson("/admin/kelas/{$classId}/ploting", [
            'homeroom_teacher_id' => $teacherId,
            'student_ids' => [$studentId],
            'subject_ids' => [$subjectId],
        ])->assertOk()
            ->assertJsonPath('data.homeroom_teacher_id', $teacherId);

        $this->assertDatabaseHas('school_class_subject', [
            'school_class_id' => $classId,
            'subject_id' => $subjectId,
        ]);

        $this->postJson('/admin/jadwal-ajar', [
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'school_class_id' => $classId,
            'academic_year' => '2025/2026',
            'day_of_week' => 1,
            'start_time' => '12:00',
            'end_time' => '13:30',
            'room' => 'R-201',
        ])->assertCreated();

        $teacherUser->refresh();
        $studentUser->refresh();

        $this->assertContains('guru_mapel', $teacherUser->roles);
        $this->assertContains('siswa', $studentUser->roles);
    }

    public function test_admin_can_access_non_admin_portal_features_without_teacher_or_student_profile(): void
    {
        $this->loginByPath('/login/admin', 'admin@sarunis.test');

        $this->getJson('/guru-mapel/jadwal-ajar')
            ->assertOk()
            ->assertJsonCount(6, 'data');

        $this->getJson('/guru-mapel/siswa')
            ->assertOk()
            ->assertJsonCount(8, 'data');

        $this->getJson('/guru-mapel/rekap-absensi-mapel')
            ->assertOk()
            ->assertJsonCount(12, 'data');

        $this->getJson('/walikelas/kelas')
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->getJson('/walikelas/siswa')
            ->assertOk()
            ->assertJsonCount(8, 'data');

        $this->getJson('/walikelas/rekap-absensi-kelas')
            ->assertOk()
            ->assertJsonCount(8, 'data');

        $this->getJson('/siswa/jadwal-sekolah')
            ->assertOk()
            ->assertJsonCount(6, 'data');

        $this->getJson('/siswa/daftar-hadir-kelas')
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_guru_mapel_can_login_view_schedule_and_record_subject_attendance(): void
    {
        $this->loginByPath('/login/guru-mapel', 'guru.mapel@sarunis.test');

        $this->get('/guru-mapel/dashboard')
            ->assertOk()
            ->assertSee('Isi Absensi Mapel')
            ->assertSee('Simpan Absensi Mapel')
            ->assertDontSee('/guru-mapel/data-siswa', false)
            ->assertDontSee('/guru-mapel/absensi-kelas', false)
            ->assertDontSee('/admin/pengaturan', false);

        $this->get('/guru-mapel/data-siswa')
            ->assertNotFound();

        $this->get('/guru-mapel/absensi-kelas')
            ->assertNotFound();

        $this->get('/guru-mapel/absensi-siswa')
            ->assertOk()
            ->assertSee('Isi Absensi Mapel')
            ->assertSee('Absensi yang Telah Dilakukan');

        $this->getJson('/guru-mapel/jadwal-ajar')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/guru-mapel/siswa')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Andi Saputra'])
            ->assertJsonFragment(['name' => 'Fira Azzahra']);

        $assignment = TeachingAssignment::query()
            ->whereHas('teacher.user', fn ($query) => $query->where('email', 'guru.mapel@sarunis.test'))
            ->whereHas('schoolClass', fn ($query) => $query->where('name', 'X IPA 2'))
            ->firstOrFail();

        $students = Student::query()
            ->where('school_class_id', $assignment->school_class_id)
            ->orderBy('id')
            ->get();

        $this->postJson('/guru-mapel/absensi-mapel', [
            'teaching_assignment_id' => $assignment->id,
            'attendance_date' => '2026-03-20',
            'attendances' => [
                ['student_id' => $students[0]->id, 'status' => AttendanceStatus::HADIR->value],
                ['student_id' => $students[1]->id, 'status' => AttendanceStatus::IZIN->value, 'notes' => 'Urusan keluarga'],
                ['student_id' => $students[2]->id, 'status' => AttendanceStatus::HADIR->value],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('subject_attendances', [
            'teaching_assignment_id' => $assignment->id,
            'student_id' => $students[1]->id,
            'attendance_date' => '2026-03-20',
            'status' => AttendanceStatus::IZIN->value,
        ]);

        $this->getJson('/guru-mapel/rekap-absensi-mapel?teaching_assignment_id='.$assignment->id.'&attendance_date=2026-03-20')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_walikelas_can_login_view_students_and_record_class_attendance(): void
    {
        $this->loginByPath('/login/walikelas', 'walikelas@sarunis.test');

        $this->get('/walikelas/dashboard')
            ->assertOk()
            ->assertSee('Isi Absensi Kelas')
            ->assertSee('Simpan Absensi Kelas')
            ->assertSee('/walikelas/data-siswa', false)
            ->assertDontSee('/admin/pengaturan', false);

        $this->get('/walikelas/data-siswa')
            ->assertOk()
            ->assertSee('Data Siswa Perwalian')
            ->assertSee('Andi Saputra');

        $this->getJson('/walikelas/kelas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'X IPA 1']);

        $this->getJson('/walikelas/siswa')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Andi Saputra']);

        $schoolClass = SchoolClass::query()->where('name', 'X IPA 1')->firstOrFail();
        $students = Student::query()->where('school_class_id', $schoolClass->id)->orderBy('id')->get();

        $this->postJson('/walikelas/absensi-kelas', [
            'school_class_id' => $schoolClass->id,
            'attendance_date' => '2026-03-20',
            'attendances' => [
                ['student_id' => $students[0]->id, 'status' => AttendanceStatus::HADIR->value],
                ['student_id' => $students[1]->id, 'status' => AttendanceStatus::HADIR->value],
                ['student_id' => $students[2]->id, 'status' => AttendanceStatus::SAKIT->value, 'notes' => 'Flu'],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('class_attendances', [
            'school_class_id' => $schoolClass->id,
            'student_id' => $students[2]->id,
            'attendance_date' => '2026-03-20',
            'status' => AttendanceStatus::SAKIT->value,
        ]);

        $this->getJson('/walikelas/rekap-absensi-kelas?school_class_id='.$schoolClass->id.'&attendance_date=2026-03-20')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_parent_user_can_access_parent_dashboard_after_portal_login(): void
    {
        $response = $this->loginByPath('/login/orang-tua', 'orangtua@sarunis.test');

        $response->assertJsonPath('data.redirect_to', '/orang-tua/dashboard');

        $this->getJson('/orang-tua/dashboard')
            ->assertOk()
            ->assertJsonPath('area', 'orang tua');

        $this->get('/orang-tua/dashboard')
            ->assertOk()
            ->assertSee('Orang Tua')
            ->assertSee('Andi Saputra')
            ->assertDontSee('/admin/pengaturan', false);
    }

    public function test_siswa_can_login_view_schedule_attendance_and_logout(): void
    {
        $this->loginByPath('/login/siswa', 'siswa@sarunis.test');

        $this->getJson('/siswa/jadwal-sekolah')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/siswa/daftar-hadir-kelas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', AttendanceStatus::HADIR->value);

        $this->postJson('/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil.');

        $this->getJson('/me')->assertUnauthorized();
    }

    public function test_login_is_rate_limited_after_too_many_failed_attempts(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/login', [
                'email' => 'unknown@sarunis.test',
                'password' => 'salah',
            ])->assertStatus(422);
        }

        $this->postJson('/login', [
            'email' => 'unknown@sarunis.test',
            'password' => 'salah',
        ])->assertStatus(429);
    }

    protected function loginByPath(string $path, string $email): TestResponse
    {
        $response = $this->postJson($path, [
            'email' => $email,
            'password' => 'password',
        ]);

        $sessionCookie = $response->getCookie('laravel_session');

        if ($sessionCookie !== null) {
            $this->withCookie($sessionCookie->getName(), $sessionCookie->getValue());
            $this->withCredentials();
        }

        return $response;
    }
}
