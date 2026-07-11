@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="guru-mapel">
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => false])

        <main class="portal-dashboard-main portal-directory-main">
            <div class="portal-directory-header">
                <div>
                    <span class="portal-hero__badge">{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                    <h1>Daftar Absen</h1>
                    <p>Daftar absensi mapel yang sudah pernah diinput.</p>
                </div>
            </div>

            @include('dashboard.partials.teacher-attendance-recap', [
                'recapSectionId' => 'daftar-absen',
                'recapSectionLabel' => 'Daftar Absen',
                'recapTitle' => 'Daftar Absen yang Sudah Diinput',
                'recapDescription' => 'Gunakan filter untuk mencari absensi berdasarkan mapel, kelas, atau rentang tanggal.',
                'showExportActions' => false,
                'showAttendanceSummary' => false,
                'showAttendanceMeetingTable' => true,
                'showAttendanceRecapTable' => false,
                'showAttendanceDetailTable' => false,
            ])
        </main>
    </div>
@endsection
