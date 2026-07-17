<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\ResolvesSchoolProfiles;
use App\Services\SchoolClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeroomPortalController extends Controller
{
    use ResolvesSchoolProfiles;

    public function __construct(
        protected SchoolClassService $schoolClassService,
    ) {
    }

    public function classes(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
        ]);

        if ($request->user()->hasRole(UserRole::ADMIN)) {
            return response()->json([
                'data' => $this->schoolClassService->classes($filters['teacher_id'] ?? null),
            ]);
        }

        $teacher = $this->teacherFromRequest($request);

        return response()->json([
            'data' => $this->schoolClassService->classesForHomeroomTeacher($teacher),
        ]);
    }

    public function students(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
        ]);

        if ($request->user()->hasRole(UserRole::ADMIN)) {
            return response()->json([
                'data' => $this->schoolClassService->students(
                    $filters['teacher_id'] ?? null,
                    $filters['school_class_id'] ?? null,
                ),
            ]);
        }

        $teacher = $this->teacherFromRequest($request);
        $schoolClassId = $filters['school_class_id'] ?? null;

        if ($schoolClassId !== null) {
            $allowedClassIds = $this->schoolClassService->classesForHomeroomTeacher($teacher)
                ->pluck('id')
                ->all();

            abort_unless(in_array($schoolClassId, $allowedClassIds, true), 403, 'Anda tidak berhak mengakses siswa kelas ini.');
        }

        return response()->json([
            'data' => $this->schoolClassService->students($teacher->id, $schoolClassId),
        ]);
    }
}

