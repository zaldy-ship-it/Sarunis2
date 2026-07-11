<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicCalendar;
use App\Models\AppSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\UserRoleService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class HomeroomTodayScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $today = CarbonImmutable::now();
        $academicYear = $this->academicYearFor($today);
        $semester = $this->semesterFor($today);
        $scheduleDay = $today->dayOfWeekIso - 1;

        $this->setting('academic_year', 'Tahun Ajaran Aktif', $academicYear);
        $this->setting('active_semester', 'Semester Aktif', $semester);

        $user = User::query()->updateOrCreate(
            ['email' => 'guru.kelas@sarunis.test'],
            [
                'name' => 'Guru Kelas Demo',
                'password' => 'password',
                'email_verified_at' => now(),
                'roles' => [UserRole::GURU_MAPEL->value],
            ],
        );

        $teacher = Teacher::query()->updateOrCreate(
            ['nip' => 'GK-DEMO-001'],
            [
                'user_id' => $user->id,
                'name' => 'Guru Kelas Demo',
                'is_subject_teacher' => true,
                'position' => 'Wali Kelas',
                'phone' => '081200009001',
                'address' => 'Alamat demo guru kelas',
            ],
        );

        $schoolClass = SchoolClass::query()->updateOrCreate(
            [
                'name' => 'DEMO KELAS HARI INI',
                'academic_year' => $academicYear,
            ],
            [
                'level' => 'DEMO',
                'homeroom_teacher_id' => $teacher->id,
                'description' => 'Kelas demo untuk mencoba absensi kelas hari ini.',
            ],
        );

        $subject = Subject::query()->updateOrCreate(
            ['code' => 'GK-HARI-INI'],
            [
                'name' => 'Jam Wali Kelas',
                'lesson_hours' => 1,
                'description' => 'Jadwal demo agar guru kelas punya sesi hari ini.',
            ],
        );

        $teacher->subjects()->syncWithoutDetaching([$subject->id]);
        $schoolClass->subjects()->syncWithoutDetaching([$subject->id]);

        TeachingAssignment::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'school_class_id' => $schoolClass->id,
                'academic_year' => $academicYear,
                'day_of_week' => $scheduleDay,
                'start_time' => '07:00',
            ],
            [
                'end_time' => '07:45',
                'room' => 'Ruang Demo',
            ],
        );

        foreach ($this->students() as $student) {
            Student::query()->updateOrCreate(
                ['nik' => $student['nik']],
                [
                    'school_class_id' => $schoolClass->id,
                    'nisn' => $student['nisn'],
                    'name' => $student['name'],
                    'gender' => $student['gender'],
                    'birth_date' => $student['birth_date'],
                    'phone' => $student['phone'],
                    'address' => 'Alamat demo siswa',
                ],
            );
        }

        $this->disableBlockingCalendarFor($academicYear, $semester, $today);

        AcademicCalendar::query()->updateOrCreate(
            [
                'academic_year' => $academicYear,
                'semester' => $semester,
                'title' => 'Hari Efektif Demo Hari Ini',
                'start_date' => $today->toDateString(),
                'end_date' => $today->toDateString(),
            ],
            [
                'category' => 'Hari Efektif',
                'type' => 'hari_efektif',
                'description' => 'Dibuat oleh seeder agar absensi demo hari ini bisa diisi.',
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => $user->id,
            ],
        );

        $teacher->load('user', 'subjects', 'teachingAssignments', 'homeroomClasses');
        app(UserRoleService::class)->syncTeacherRoles($teacher);
    }

    protected function disableBlockingCalendarFor(string $academicYear, string $semester, CarbonImmutable $date): void
    {
        AcademicCalendar::query()
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->where(function ($query): void {
                $query
                    ->where('is_holiday', true)
                    ->orWhereIn('type', ['libur_nasional', 'libur_sekolah']);
            })
            ->update(['is_active' => false]);
    }

    protected function setting(string $key, string $label, string $value): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'label' => $label,
                'value' => $value,
                'type' => 'text',
            ],
        );
    }

    protected function academicYearFor(CarbonImmutable $date): string
    {
        $startYear = $date->month >= 7 ? $date->year : $date->year - 1;

        return $startYear . '/' . ($startYear + 1);
    }

    protected function semesterFor(CarbonImmutable $date): string
    {
        return $date->month >= 7 ? 'ganjil' : 'genap';
    }

    /**
     * @return array<int, array{nik:string,nisn:string,name:string,gender:string,birth_date:string,phone:string}>
     */
    protected function students(): array
    {
        return [
            ['nik' => 'GK-DEMO-SISWA-001', 'nisn' => '9901000001', 'name' => 'Alya Putri Demo', 'gender' => 'P', 'birth_date' => '2012-02-10', 'phone' => '081300009001'],
            ['nik' => 'GK-DEMO-SISWA-002', 'nisn' => '9901000002', 'name' => 'Bagas Pratama Demo', 'gender' => 'L', 'birth_date' => '2012-03-12', 'phone' => '081300009002'],
            ['nik' => 'GK-DEMO-SISWA-003', 'nisn' => '9901000003', 'name' => 'Citra Lestari Demo', 'gender' => 'P', 'birth_date' => '2012-04-14', 'phone' => '081300009003'],
            ['nik' => 'GK-DEMO-SISWA-004', 'nisn' => '9901000004', 'name' => 'Dimas Saputra Demo', 'gender' => 'L', 'birth_date' => '2012-05-16', 'phone' => '081300009004'],
        ];
    }
}
