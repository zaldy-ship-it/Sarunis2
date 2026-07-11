@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="guru-mapel">
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => false])

        <main class="portal-dashboard-main portal-directory-main">
            <div class="portal-directory-header">
                <div>
                    <span class="portal-hero__badge">{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                    <h1>Rekap Absen</h1>
                    <p>Pantau ringkasan absensi berdasarkan jadwal mengajar aktif.</p>
                </div>
            </div>

            @include('dashboard.partials.teacher-attendance-recap')
        </main>
    </div>
@endsection
