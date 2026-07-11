@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="{{ $portalKey ?? 'guru-mapel' }}">
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => false])

        <main class="portal-dashboard-main portal-directory-main portal-teacher-attendance-page">
            <div class="portal-directory-header portal-teacher-attendance-hero">
                <div>
                    <span class="portal-hero__badge">{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                    <h1>Tambah Absen</h1>
                    <p>Pilih jadwal, tandai kehadiran siswa, lalu simpan absensi mapel.</p>
                </div>
                <div class="portal-teacher-attendance-hero__meta">
                    <span data-schedule-count-label>{{ count($scheduleRows) }} jadwal hari ini</span>
                    <strong data-schedule-student-count>{{ count($teacherStudents ?? []) }} siswa terkait</strong>
                </div>
            </div>

            @include('dashboard.partials.teacher-attendance-form')
        </main>
    </div>
@endsection

@push('scripts')
    @include('dashboard.partials.teacher-attendance-script')
@endpush
