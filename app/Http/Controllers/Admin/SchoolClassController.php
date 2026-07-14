<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\UpsertSchoolClassRequest;
use App\Models\SchoolClass;
use App\Services\SchoolClassService;
use App\Services\AppSettingService;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SchoolClassController extends Controller
{
    public function __construct(
        protected SchoolClassService $schoolClassService,
        protected AppSettingService $appSettingService,
    ) {
    }

    public function index(AdminListRequest $request): JsonResponse
    {
        return response()->json(
            $this->schoolClassService->paginate($request->integer('per_page', 15))
        );
    }

    public function store(UpsertSchoolClassRequest $request): JsonResponse
    {
        $schoolClass = $this->schoolClassService->create($request->validated());

        return response()->json([
            'message' => 'Data kelas berhasil dibuat.',
            'data' => $schoolClass,
        ], 201);
    }

    public function show(SchoolClass $schoolClass): JsonResponse
    {
        return response()->json([
            'data' => $schoolClass->load(['homeroomTeacher', 'students', 'teachingAssignments.subject', 'teachingAssignments.teacher']),
        ]);
    }

    public function update(UpsertSchoolClassRequest $request, SchoolClass $schoolClass): JsonResponse
    {
        $schoolClass = $this->schoolClassService->update($schoolClass, $request->validated());

        return response()->json([
            'message' => 'Data kelas berhasil diperbarui.',
            'data' => $schoolClass,
        ]);
    }

    public function destroy(SchoolClass $schoolClass): JsonResponse
    {
        $this->schoolClassService->delete($schoolClass);

        return response()->json([
            'message' => 'Data kelas berhasil dihapus.',
        ]);
    }

    public function meetings(SchoolClass $schoolClass): JsonResponse
    {
        $startDateVal = $this->appSettingService->value('school_start_date', '2025-07-14');
        $start = Carbon::parse($startDateVal);
        
        $meetings = [];
        $current = $start->copy();
        
        // Generate 120 school days (Monday to Saturday) starting from school_start_date
        $meetingNum = 1;
        while (count($meetings) < 120) {
            // Day of week: 0 is Sunday, 6 is Saturday in Carbon (actually 0 is Sunday, 1 is Monday... 6 is Saturday)
            // Carbon's dayOfWeek: 0 (Sunday) to 6 (Saturday)
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                $meetings[] = [
                    'number' => $meetingNum,
                    'date' => $current->toDateString(),
                    'formatted_date' => $current->translatedFormat('l, d F Y'),
                    'day_name' => $current->translatedFormat('l'),
                ];
                $meetingNum++;
            }
            $current->addDay();
        }

        return response()->json([
            'data' => $meetings,
        ]);
    }
}
