<?php

namespace App\Services;

use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\SubjectAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TeachingAssignment;
use App\Services\TeachingAssignmentService;
use App\Services\AppSettingService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvImportExportService
{
    public function __construct(
        protected StudentService $studentService,
        protected TeacherService $teacherService,
        protected TeachingAssignmentService $teachingAssignmentService,
        protected AppSettingService $appSettingService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function export(string $dataset, string $format = 'csv', array $filters = []): StreamedResponse|Response
    {
        abort_unless(in_array($format, ['csv', 'xls', 'pdf'], true), 404, 'Format export tidak ditemukan.');

        [$baseFilename, $title, $headers, $rows] = match ($dataset) {
            'siswa' => ['data-siswa', 'Data Siswa', $this->studentHeaders(), $this->studentRows($filters)],
            'guru' => ['data-guru', 'Data Guru', $this->teacherHeaders(), $this->teacherRows($filters)],
            'kelas' => ['data-kelas', 'Data Kelas', $this->classHeaders(), $this->classRows($filters)],
            'mapel' => ['data-mapel', 'Data Mata Pelajaran', $this->subjectHeaders(), $this->subjectRows($filters)],
            'absensi' => ['data-absensi', 'Data Absensi Gabungan', $this->attendanceHeaders(), $this->attendanceRows($filters)],
            'catatan-siswa' => ['catatan-siswa', 'Catatan Siswa', $this->studentNoteHeaders(), $this->studentNoteRows($filters)],
            default => abort(404, 'Dataset export tidak ditemukan.'),
        };

        $filename = $this->exportFilename($baseFilename, $filters);

        if ($format === 'xls') {
            return response()
                ->view('dashboard.exports.admin-table-xls', compact('title', 'headers', 'rows'))
                ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"');
        }

        if ($format === 'pdf') {
            return response()->view('dashboard.exports.admin-table-print', compact('title', 'headers', 'rows'));
        }

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{created:int,updated:int,failed:int,errors:array<int, array{row:int,messages:array<int,string>}>}
     */
    public function importStudents(UploadedFile $file): array
    {
        return $this->importRows($file, function (array $row): string {
            $schoolClass = $this->resolveSchoolClass($row['school_class_id'] ?? null, $row['class_name'] ?? null);
            $student = Student::query()->where('nik', (string) ($row['nik'] ?? ''))->first();
            $payload = [
                'school_class_id' => $schoolClass?->id,
                'nik' => $row['nik'] ?? null,
                'nisn' => $row['nisn'] ?? null,
                'name' => $row['name'] ?? null,
                'gender' => $row['gender'] ?? null,
                'birth_date' => $row['birth_date'] ?? null,
                'phone' => $row['phone'] ?? null,
                'address' => $row['address'] ?? null,
                'detail_siswa' => [
                    'religion' => $row['religion'] ?? null,
                    'birth_place' => $row['birth_place'] ?? null,
                    'address_street' => $row['address_street'] ?? null,
                    'address_village' => $row['address_village'] ?? null,
                    'address_district' => $row['address_district'] ?? null,
                    'address_province' => $row['address_province'] ?? null,
                    'address_city' => $row['address_city'] ?? null,
                    'father_name' => $row['father_name'] ?? null,
                    'father_education' => $row['father_education'] ?? null,
                    'father_occupation' => $row['father_occupation'] ?? null,
                    'mother_name' => $row['mother_name'] ?? null,
                    'mother_education' => $row['mother_education'] ?? null,
                    'mother_occupation' => $row['mother_occupation'] ?? null,
                    'parent_address' => $row['parent_address'] ?? null,
                    'parent_province' => $row['parent_province'] ?? null,
                    'parent_city' => $row['parent_city'] ?? null,
                    'postal_code' => $row['postal_code'] ?? null,
                    'parent_phone' => $row['parent_phone'] ?? null,
                    'previous_school' => $row['previous_school'] ?? null,
                ],
            ];

            $validator = Validator::make($payload, [
                'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
                'nik' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[0-9A-Za-z]+$/', Rule::unique('students', 'nik')->ignore($student?->id)],
                'nisn' => ['nullable', 'string', 'regex:/^[0-9]{10,20}$/', Rule::unique('students', 'nisn')->ignore($student?->id)],
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'gender' => ['nullable', Rule::in(['L', 'P'])],
                'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
                'phone' => ['nullable', 'string', 'min:10', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
                'address' => ['nullable', 'string', 'max:1000'],
                'detail_siswa' => ['nullable', 'array'],
                'detail_siswa.religion' => ['nullable', 'string', 'max:255'],
                'detail_siswa.birth_place' => ['nullable', 'string', 'max:255'],
                'detail_siswa.address_street' => ['nullable', 'string', 'max:1000'],
                'detail_siswa.address_village' => ['nullable', 'string', 'max:255'],
                'detail_siswa.address_district' => ['nullable', 'string', 'max:255'],
                'detail_siswa.address_province' => ['nullable', 'string', 'max:255'],
                'detail_siswa.address_city' => ['nullable', 'string', 'max:255'],
                'detail_siswa.father_name' => ['nullable', 'string', 'max:255'],
                'detail_siswa.father_education' => ['nullable', 'string', 'max:255'],
                'detail_siswa.father_occupation' => ['nullable', 'string', 'max:255'],
                'detail_siswa.mother_name' => ['nullable', 'string', 'max:255'],
                'detail_siswa.mother_education' => ['nullable', 'string', 'max:255'],
                'detail_siswa.mother_occupation' => ['nullable', 'string', 'max:255'],
                'detail_siswa.parent_address' => ['nullable', 'string', 'max:1000'],
                'detail_siswa.parent_province' => ['nullable', 'string', 'max:255'],
                'detail_siswa.parent_city' => ['nullable', 'string', 'max:255'],
                'detail_siswa.postal_code' => ['nullable', 'string', 'max:10'],
                'detail_siswa.parent_phone' => ['nullable', 'string', 'max:30'],
                'detail_siswa.previous_school' => ['nullable', 'string', 'max:255'],
            ]);

            $validator->validate();

            if ($student === null) {
                $this->studentService->create($payload);

                return 'created';
            }

            $this->studentService->update($student, $payload);

            return 'updated';
        });
    }

    /**
     * @return array{created:int,updated:int,failed:int,errors:array<int, array{row:int,messages:array<int,string>}>}
     */
    public function importTeachers(UploadedFile $file): array
    {
        return $this->importRows($file, function (array $row): string {
            $teacher = Teacher::query()->where('nip', (string) ($row['nip'] ?? ''))->first();
            $payload = [
                'nik' => $row['nik'] ?? null,
                'nip' => $row['nip'] ?? null,
                'name' => $row['name'] ?? null,
                'birth_place' => $row['birth_place'] ?? null,
                'birth_date' => $row['birth_date'] ?? null,
                'gender' => $row['gender'] ?? null,
                'religion' => $row['religion'] ?? null,
                'employment_status' => $row['employment_status'] ?? null,
                'position' => $row['position'] ?? null,
                'join_date' => $row['join_date'] ?? null,
                'last_education' => $row['last_education'] ?? null,
                'major' => $row['major'] ?? null,
                'university' => $row['university'] ?? null,
                'phone' => $row['phone'] ?? null,
                'address' => $row['address'] ?? null,
            ];

            $validator = Validator::make($payload, [
                'nik' => ['nullable', 'string', 'min:8', 'max:30', 'regex:/^[0-9]+$/', Rule::unique('teachers', 'nik')->ignore($teacher?->id)],
                'nip' => ['required', 'string', 'min:6', 'max:30', 'regex:/^[0-9A-Za-z.\/-]+$/', Rule::unique('teachers', 'nip')->ignore($teacher?->id)],
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'birth_place' => ['nullable', 'string', 'max:255'],
                'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
                'gender' => ['nullable', Rule::in(['L', 'P'])],
                'religion' => ['nullable', 'string', 'max:100'],
                'employment_status' => ['nullable', 'string', 'max:255'],
                'position' => ['nullable', 'string', 'max:255'],
                'join_date' => ['nullable', 'date', 'before_or_equal:today'],
                'last_education' => ['nullable', 'string', 'max:255'],
                'major' => ['nullable', 'string', 'max:255'],
                'university' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'min:10', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
                'address' => ['nullable', 'string', 'max:1000'],
            ]);

            $validator->validate();

            if ($teacher === null) {
                $this->teacherService->create($payload);

                return 'created';
            }

            $this->teacherService->update($teacher, $payload);

            return 'updated';
        });
    }

    /**
     * @return array{created:int,updated:int,failed:int,errors:array<int, array{row:int,messages:array<int,string>}>}
     */
    public function importSchedules(UploadedFile $file): array
    {
        $academicYear = $this->appSettingService->value('academic_year', '2025/2026') ?: '2025/2026';
        $dayMap = [
            'senin' => 0, 'selasa' => 1, 'rabu' => 2, 'kamis' => 3, 'jumat' => 4, 'sabtu' => 5, 'minggu' => 6,
            'monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4, 'saturday' => 5, 'sunday' => 6
        ];

        return $this->importRows($file, function (array $row) use ($academicYear, $dayMap): string {
            // Resolve Teacher
            $teacher = Teacher::query()->where('nip', (string) ($row['nip_guru'] ?? ''))->first();
            // Resolve Subject
            $subject = Subject::query()
                ->where('code', (string) ($row['nama_mapel'] ?? ''))
                ->orWhere('name', (string) ($row['nama_mapel'] ?? ''))
                ->first();
            // Resolve Class
            $schoolClass = SchoolClass::query()->where('name', (string) ($row['nama_kelas'] ?? ''))->first();

            // Resolve Day
            $dayInput = strtolower(trim((string) ($row['hari'] ?? '')));
            $dayOfWeek = is_numeric($dayInput) ? (int)$dayInput : ($dayMap[$dayInput] ?? null);

            $payload = [
                'academic_year' => $academicYear,
                'teacher_id' => $teacher?->id,
                'subject_id' => $subject?->id,
                'school_class_id' => $schoolClass?->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => $row['jam_mulai'] ?? null,
                'end_time' => $row['jam_selesai'] ?? null,
                'room' => $row['ruangan'] ?? null,
            ];

            $validator = Validator::make($payload, [
                'academic_year' => ['required', 'string'],
                'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
                'subject_id' => ['required', 'integer', 'exists:subjects,id'],
                'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
                'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'room' => ['nullable', 'string', 'max:50'],
            ], [
                'teacher_id.required' => 'NIP Guru tidak valid atau tidak terdaftar.',
                'subject_id.required' => 'Kode/Nama Mapel tidak valid atau tidak terdaftar.',
                'school_class_id.required' => 'Nama Kelas tidak valid atau tidak terdaftar.',
                'day_of_week.required' => 'Hari tidak valid.',
            ]);

            $validator->validate();

            // Check overlap for Teacher
            $teacherConflict = TeachingAssignment::where('teacher_id', $payload['teacher_id'])
                ->where('academic_year', $academicYear)
                ->where('day_of_week', $payload['day_of_week'])
                ->where(function($q) use ($payload) {
                    $q->whereBetween('start_time', [$payload['start_time'], $payload['end_time']])
                      ->orWhereBetween('end_time', [$payload['start_time'], $payload['end_time']])
                      ->orWhere(function($q2) use ($payload) {
                          $q2->where('start_time', '<=', $payload['start_time'])
                             ->where('end_time', '>=', $payload['end_time']);
                      });
                });

            // Check overlap for Class
            $classConflict = TeachingAssignment::where('school_class_id', $payload['school_class_id'])
                ->where('academic_year', $academicYear)
                ->where('day_of_week', $payload['day_of_week'])
                ->where(function($q) use ($payload) {
                    $q->whereBetween('start_time', [$payload['start_time'], $payload['end_time']])
                      ->orWhereBetween('end_time', [$payload['start_time'], $payload['end_time']])
                      ->orWhere(function($q2) use ($payload) {
                          $q2->where('start_time', '<=', $payload['start_time'])
                             ->where('end_time', '>=', $payload['end_time']);
                      });
                });

            // Find if there is an exact schedule slot already
            $existing = TeachingAssignment::where('school_class_id', $payload['school_class_id'])
                ->where('academic_year', $academicYear)
                ->where('day_of_week', $payload['day_of_week'])
                ->where('start_time', $payload['start_time'])
                ->where('end_time', $payload['end_time'])
                ->first();

            if ($existing) {
                // If existing, we can update it. Conflict checks should exclude this exact record.
                $teacherConflict = $teacherConflict->where('id', '!=', $existing->id);
                $classConflict = $classConflict->where('id', '!=', $existing->id);
            }

            if ($teacherConflict->exists()) {
                throw new \Exception("Konflik Guru: Guru NIP {$row['nip_guru']} sudah mengajar di kelas lain pada jam tersebut.");
            }

            if ($classConflict->exists()) {
                throw new \Exception("Konflik Kelas: Kelas {$row['nama_kelas']} sudah memiliki jadwal pelajaran lain pada jam tersebut.");
            }

            if ($existing) {
                $this->teachingAssignmentService->update($existing, $payload);
                return 'updated';
            }

            $this->teachingAssignmentService->create($payload);
            return 'created';
        });
    }

    public function template(string $type): StreamedResponse
    {
        [$filename, $headers] = match ($type) {
            'siswa' => ['template-import-siswa.csv', [
                'nik', 'nisn', 'name', 'gender', 'birth_date', 'phone', 'address', 'school_class_id', 'class_name',
                'religion', 'birth_place', 'address_street', 'address_village', 'address_district',
                'address_province', 'address_city', 'father_name', 'father_education', 'father_occupation',
                'mother_name', 'mother_education', 'mother_occupation', 'parent_address', 'parent_province',
                'parent_city', 'postal_code', 'parent_phone', 'previous_school'
            ]],
            'guru' => ['template-import-guru.csv', ['nip', 'nik', 'name', 'birth_place', 'birth_date', 'gender', 'religion', 'employment_status', 'position', 'join_date', 'last_education', 'major', 'university', 'phone', 'address']],
            'jadwal' => ['template-import-jadwal.csv', ['nip_guru', 'nama_mapel', 'nama_kelas', 'hari', 'jam_mulai', 'jam_selesai', 'ruangan']],
            default => abort(404, 'Template import tidak ditemukan.'),
        };

        return response()->streamDownload(function () use ($headers): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBFsep=,\n");
            fputcsv($handle, $headers);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param callable(array<string, string|null>): string $callback
     * @return array{created:int,updated:int,failed:int,errors:array<int, array{row:int,messages:array<int,string>}>}
     */
    protected function importRows(UploadedFile $file, callable $callback): array
    {
        $rows = $this->readCsv($file);
        $summary = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $rowNumber => $row) {
            try {
                $result = $callback($row);
                $summary[$result]++;
            } catch (\Throwable $throwable) {
                $summary['failed']++;
                $summary['errors'][] = [
                    'row' => $rowNumber,
                    'messages' => method_exists($throwable, 'errors')
                        ? Arr::flatten($throwable->errors())
                        : [$throwable->getMessage()],
                ];
            }
        }

        return $summary;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        abort_if($handle === false, 422, 'File import tidak dapat dibaca.');

        $header = fgetcsv($handle);
        abort_if($header === false, 422, 'File import harus memiliki header CSV.');

        if (count($header) === 1 && str_starts_with(strtolower($header[0] ?? ''), 'sep=')) {
            $header = fgetcsv($handle);
            abort_if($header === false, 422, 'File import harus memiliki header CSV.');
        }

        $keys = array_map(function (string $value): string {
            $cleaned = str_replace("\xEF\xBB\xBF", '', $value);
            return str($cleaned)->trim()->lower()->replace(' ', '_')->toString();
        }, $header);
        $rows = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $row = [];

            foreach ($keys as $index => $key) {
                $value = isset($data[$index]) ? trim((string) $data[$index]) : null;
                $row[$key] = $value === '' ? null : $value;
            }

            if (collect($row)->filter()->isEmpty()) {
                continue;
            }

            $rows[$rowNumber] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function resolveSchoolClass(?string $schoolClassId, ?string $className): ?SchoolClass
    {
        if ($schoolClassId !== null) {
            return SchoolClass::query()->find((int) $schoolClassId);
        }

        if ($className !== null) {
            return SchoolClass::query()->where('name', $className)->first();
        }

        return null;
    }

    protected function studentHeaders(): array
    {
        return [
            'id', 'nik', 'nisn', 'name', 'gender', 'birth_date', 'phone', 'address', 'class_name',
            'religion', 'birth_place', 'address_street', 'address_village', 'address_district',
            'address_province', 'address_city', 'father_name', 'father_education', 'father_occupation',
            'mother_name', 'mother_education', 'mother_occupation', 'parent_address', 'parent_province',
            'parent_city', 'postal_code', 'parent_phone', 'previous_school'
        ];
    }

    protected function teacherHeaders(): array
    {
        return ['id', 'nip', 'nik', 'name', 'gender', 'birth_date', 'phone', 'position', 'employment_status'];
    }

    protected function classHeaders(): array
    {
        return ['id', 'name', 'level', 'academic_year', 'homeroom_teacher', 'students_count', 'description'];
    }

    protected function attendanceHeaders(): array
    {
        return ['context', 'date', 'student', 'class_name', 'subject', 'teacher', 'status', 'notes'];
    }

    protected function studentNoteHeaders(): array
    {
        return ['id', 'student', 'class_name', 'teacher', 'title', 'category', 'note', 'follow_up_at', 'resolved_at'];
    }

    protected function subjectHeaders(): array
    {
        return ['id', 'code', 'name', 'lesson_hours', 'hari', 'jam_mulai', 'jam_selesai', 'kelas', 'teachers', 'classes', 'description'];
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function studentRows(array $filters = []): array
    {
        $gender = $filters['gender'] ?? null;
        $level = $filters['level'] ?? null;
        $academicYear = $filters['academic_year'] ?? null;
        $search = $filters['search'] ?? null;

        return Student::query()
            ->with(['schoolClass', 'detailSiswa'])
            ->when(
                $this->filterInt($filters, 'school_class_id'),
                fn ($query, int $schoolClassId) => $query->where('school_class_id', $schoolClassId),
            )
            ->when(
                is_string($gender) && $gender !== '',
                fn ($query) => $query->where('gender', $gender),
            )
            ->when(is_string($level) && $level !== '', function ($query) use ($level): void {
                $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('level', $level));
            })
            ->when(is_string($academicYear) && $academicYear !== '', function ($query) use ($academicYear): void {
                $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('academic_year', $academicYear));
            })
            ->when(is_string($search) && $search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Student $student): array => [
                $student->id,
                $student->nik,
                $student->nisn,
                $student->name,
                $student->gender,
                $student->birth_date?->toDateString(),
                $student->phone,
                $student->address,
                $student->schoolClass?->name,
                $student->detailSiswa?->religion,
                $student->detailSiswa?->birth_place,
                $student->detailSiswa?->address_street,
                $student->detailSiswa?->address_village,
                $student->detailSiswa?->address_district,
                $student->detailSiswa?->address_province,
                $student->detailSiswa?->address_city,
                $student->detailSiswa?->father_name,
                $student->detailSiswa?->father_education,
                $student->detailSiswa?->father_occupation,
                $student->detailSiswa?->mother_name,
                $student->detailSiswa?->mother_education,
                $student->detailSiswa?->mother_occupation,
                $student->detailSiswa?->parent_address,
                $student->detailSiswa?->parent_province,
                $student->detailSiswa?->parent_city,
                $student->detailSiswa?->postal_code,
                $student->detailSiswa?->parent_phone,
                $student->detailSiswa?->previous_school,
            ])
            ->all();
    }

    protected function teacherRows(array $filters = []): array
    {
        $category = $filters['category'] ?? null;
        $gender = $filters['gender'] ?? null;
        $employmentStatus = $filters['employment_status'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Teacher::query()
            ->withCount(['subjects', 'teachingAssignments', 'homeroomClasses'])
            ->when(
                is_string($gender) && $gender !== '',
                fn ($teacherQuery) => $teacherQuery->where('gender', $gender),
            )
            ->when(
                is_string($employmentStatus) && $employmentStatus !== '',
                fn ($teacherQuery) => $teacherQuery->where('employment_status', $employmentStatus),
            )
            ->when(is_string($search) && $search !== '', function ($teacherQuery) use ($search): void {
                $teacherQuery->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        if ($category !== null && $category !== '') {
            $query = $query->filter(fn (Teacher $teacher) => $teacher->roleMeta()['key'] === $category);
        }

        return $query
            ->map(fn (Teacher $teacher): array => [
                $teacher->id,
                $teacher->nip,
                $teacher->nik,
                $teacher->name,
                $teacher->gender,
                $teacher->birth_date?->toDateString(),
                $teacher->phone,
                $teacher->position,
                $teacher->employment_status,
            ])
            ->all();
    }

    protected function classRows(array $filters = []): array
    {
        $level = $filters['level'] ?? null;
        $academicYear = $filters['academic_year'] ?? null;

        return SchoolClass::query()
            ->with('homeroomTeacher')
            ->withCount('students')
            ->when(
                $this->filterInt($filters, 'school_class_id'),
                fn ($query, int $schoolClassId) => $query->where('id', $schoolClassId),
            )
            ->when(
                $level !== null && $level !== '',
                fn ($query) => $query->where('level', $level),
            )
            ->when(
                $academicYear !== null && $academicYear !== '',
                fn ($query) => $query->where('academic_year', $academicYear),
            )
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $schoolClass): array => [
                $schoolClass->id,
                $schoolClass->name,
                $schoolClass->level,
                $schoolClass->academic_year,
                $schoolClass->homeroomTeacher?->name,
                $schoolClass->students_count,
                $schoolClass->description,
            ])
            ->all();
    }

    protected function subjectRows(array $filters = []): array
    {
        $usage = $filters['usage'] ?? null;
        $search = $filters['search'] ?? null;

        return Subject::query()
            ->with(['teachers', 'schoolClass', 'schoolClasses'])
            ->when(
                $this->filterInt($filters, 'subject_id'),
                fn ($query, int $subjectId) => $query->where('id', $subjectId),
            )
            ->when($this->filterInt($filters, 'school_class_id'), function ($query, int $schoolClassId): void {
                $query->where(function ($q) use ($schoolClassId) {
                    $q->where('school_class_id', $schoolClassId)
                      ->orWhereHas('schoolClasses', fn ($classQuery) => $classQuery->where('school_classes.id', $schoolClassId));
                });
            })
            ->when($usage === 'dipakai', function ($query): void {
                $query->has('teachingAssignments');
            })
            ->when($usage === 'belum-dipakai', function ($query): void {
                $query->doesntHave('teachingAssignments');
            })
            ->when(is_string($search) && $search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Subject $subject): array => [
                $subject->id,
                $subject->code,
                $subject->name,
                $subject->lesson_hours,
                $subject->day_of_week !== null ? (config('schedule.day_names')[$subject->day_of_week] ?? '-') : '-',
                $subject->start_time ? substr($subject->start_time, 0, 5) : '-',
                $subject->end_time ? substr($subject->end_time, 0, 5) : '-',
                $subject->schoolClass?->name ?? '-',
                $subject->teachers->pluck('name')->implode(', '),
                $subject->schoolClasses->pluck('name')->implode(', '),
                $subject->description,
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function attendanceRows(array $filters = []): array
    {
        $type = $filters['type'] ?? 'gabungan';
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $teacherId = $this->filterInt($filters, 'teacher_id');
        $studentId = $this->filterInt($filters, 'student_id');

        $classRows = ClassAttendance::query()
            ->with(['student', 'schoolClass', 'recordedByTeacher'])
            ->when(
                $this->filterInt($filters, 'school_class_id'),
                fn ($query, int $schoolClassId) => $query->where('school_class_id', $schoolClassId),
            )
            ->when(
                $teacherId,
                fn ($query, int $recordedByTeacherId) => $query->where('recorded_by_teacher_id', $recordedByTeacherId),
            )
            ->when(
                $studentId,
                fn ($query, int $attendanceStudentId) => $query->where('student_id', $attendanceStudentId),
            )
            ->when(is_string($search) && $search !== '', function ($query) use ($search): void {
                $query->whereHas('student', function ($studentQuery) use ($search): void {
                    $studentQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when(
                is_string($status) && $status !== '',
                fn ($query) => $query->where('status', $status),
            )
            ->when(
                $this->filterDate($filters, 'attendance_date'),
                fn ($query, string $date) => $query->whereDate('attendance_date', $date),
            )
            ->when(
                $this->filterDate($filters, 'date_from'),
                fn ($query, string $date) => $query->whereDate('attendance_date', '>=', $date),
            )
            ->when(
                $this->filterDate($filters, 'date_to'),
                fn ($query, string $date) => $query->whereDate('attendance_date', '<=', $date),
            )
            ->orderByDesc('attendance_date')
            ->get()
            ->map(fn (ClassAttendance $attendance): array => [
                'Absensi Kelas Perwalian',
                $attendance->attendance_date?->toDateString(),
                $attendance->student?->name,
                $attendance->schoolClass?->name,
                null,
                $attendance->recordedByTeacher?->name,
                $attendance->status,
                $attendance->notes,
            ]);

        $subjectRows = SubjectAttendance::query()
            ->with(['student', 'recordedByTeacher', 'teachingAssignment.subject', 'teachingAssignment.schoolClass'])
            ->whereHas('teachingAssignment', function ($query) use ($filters): void {
                $query
                    ->when(
                        $this->filterInt($filters, 'school_class_id'),
                        fn ($assignmentQuery, int $schoolClassId) => $assignmentQuery->where('school_class_id', $schoolClassId),
                    )
                    ->when(
                        $this->filterInt($filters, 'subject_id'),
                        fn ($assignmentQuery, int $subjectId) => $assignmentQuery->where('subject_id', $subjectId),
                    )
                    ->when(
                        $this->filterInt($filters, 'teacher_id'),
                        fn ($assignmentQuery, int $assignmentTeacherId) => $assignmentQuery->where('teacher_id', $assignmentTeacherId),
                    );
            })
            ->when(
                $teacherId,
                fn ($query, int $recordedByTeacherId) => $query->where('recorded_by_teacher_id', $recordedByTeacherId),
            )
            ->when(
                $studentId,
                fn ($query, int $attendanceStudentId) => $query->where('student_id', $attendanceStudentId),
            )
            ->when(is_string($search) && $search !== '', function ($query) use ($search): void {
                $query->whereHas('student', function ($studentQuery) use ($search): void {
                    $studentQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when(
                is_string($status) && $status !== '',
                fn ($query) => $query->where('status', $status),
            )
            ->when(
                $this->filterDate($filters, 'attendance_date'),
                fn ($query, string $date) => $query->whereDate('attendance_date', $date),
            )
            ->when(
                $this->filterDate($filters, 'date_from'),
                fn ($query, string $date) => $query->whereDate('attendance_date', '>=', $date),
            )
            ->when(
                $this->filterDate($filters, 'date_to'),
                fn ($query, string $date) => $query->whereDate('attendance_date', '<=', $date),
            )
            ->orderByDesc('attendance_date')
            ->get()
            ->map(fn (SubjectAttendance $attendance): array => [
                'Absensi Mapel',
                $attendance->attendance_date?->toDateString(),
                $attendance->student?->name,
                $attendance->teachingAssignment?->schoolClass?->name,
                $attendance->teachingAssignment?->subject?->name,
                $attendance->recordedByTeacher?->name,
                $attendance->status,
                $attendance->notes,
            ]);

        return match ($type) {
            'kelas' => $classRows->all(),
            'mapel' => $subjectRows->all(),
            default => $classRows->concat($subjectRows)->all(),
        };
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function studentNoteRows(array $filters = []): array
    {
        $category = $filters['category'] ?? null;

        return StudentNote::query()
            ->with(['student.schoolClass', 'teacher'])
            ->when($this->filterInt($filters, 'school_class_id'), function ($query, int $schoolClassId): void {
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('school_class_id', $schoolClassId));
            })
            ->when(
                $this->filterInt($filters, 'teacher_id'),
                fn ($query, int $teacherId) => $query->where('teacher_id', $teacherId),
            )
            ->when(
                is_string($category) && $category !== '',
                fn ($query) => $query->where('category', $category),
            )
            ->when(
                $this->filterDate($filters, 'date_from'),
                fn ($query, string $date) => $query->whereDate('created_at', '>=', $date),
            )
            ->when(
                $this->filterDate($filters, 'date_to'),
                fn ($query, string $date) => $query->whereDate('created_at', '<=', $date),
            )
            ->latest()
            ->get()
            ->map(fn (StudentNote $note): array => [
                $note->id,
                $note->student?->name,
                $note->student?->schoolClass?->name,
                $note->teacher?->name,
                $note->title,
                $note->category,
                $note->note,
                $note->follow_up_at?->toDateString(),
                $note->resolved_at?->toDateString(),
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function filterInt(array $filters, string $key): ?int
    {
        $value = $filters[$key] ?? null;

        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function filterDate(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function exportFilename(string $baseFilename, array $filters): string
    {
        $parts = [$baseFilename];

        foreach (['type', 'subject_id', 'school_class_id', 'teacher_id', 'student_id', 'attendance_date', 'date_from', 'date_to', 'status', 'category', 'gender', 'employment_status', 'academic_year', 'level', 'usage', 'search'] as $key) {
            $value = $filters[$key] ?? null;

            if ($value !== null && $value !== '') {
                $parts[] = str_replace('_', '-', $key) . '-' . preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $value);
            }
        }

        $parts[] = now()->format('Ymd-His');

        return implode('-', $parts);
    }
}
