@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell portal-directory-shell" data-academic-calendar-page>
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => true])

        <main class="portal-dashboard-main portal-directory-main">
            <!-- Header Banner -->
            <section class="portal-panel portal-directory-banner">
                <div class="portal-directory-banner__bar"></div>
                <div class="portal-directory-banner__copy">
                    <h1>Kalender Akademik</h1>
                    <p>Agenda dan kalender kegiatan sekolah untuk Tahun Ajaran {{ $activeAcademicYear }} Semester {{ ucfirst($activeSemester) }}.</p>
                </div>
                <div class="portal-directory-banner__count">
                    {{ $semesterLock ? 'Semester ditutup' : $events->count().' Agenda' }}
                </div>
            </section>

            <!-- Main Layout Grid -->
            <div class="portal-calendar-layout">
                <!-- Left Side: Interactive Calendar Grouped by Months -->
                <div class="portal-calendar-events-list">
                    @php
                        $eventsByMonth = $events->groupBy(function($event) {
                            return $event->start_date->format('Y-m');
                        })->sortKeys();

                        $monthNames = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];

                        $dayNames = [
                            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
                        ];
                    @endphp

                    @forelse ($eventsByMonth as $yearMonth => $monthEvents)
                        @php
                            list($year, $month) = explode('-', $yearMonth);
                            $monthLabel = ($monthNames[$month] ?? 'Bulan') . ' ' . $year;
                        @endphp

                        <h3 class="portal-calendar-month-title">{{ $monthLabel }}</h3>

                        @foreach ($monthEvents as $event)
                            @php
                                $startDayNum = $event->start_date->format('d');
                                $startMonthLabel = strtoupper(substr($monthNames[$event->start_date->format('m')] ?? 'Jan', 0, 3));
                                $startDayName = $dayNames[$event->start_date->format('l')] ?? $event->start_date->format('l');
                            @endphp
                            <div class="portal-calendar-event-card">
                                <!-- Date Badge -->
                                <div class="portal-calendar-event-date-badge">
                                    <span class="portal-calendar-event-date-day">{{ $startDayNum }}</span>
                                    <span class="portal-calendar-event-date-month">{{ $startMonthLabel }}</span>
                                </div>

                                <!-- Event Details -->
                                <div class="portal-calendar-event-content">
                                    <div class="portal-calendar-event-header">
                                        <h4 class="portal-calendar-event-title">{{ $event->title }}</h4>
                                        <span class="portal-badge is-primary" style="font-size: 0.72rem; padding: 0.15rem 0.5rem;">
                                            {{ $event->category }}
                                        </span>
                                        @if ($event->is_holiday)
                                            <span class="portal-badge is-warning" style="font-size: 0.72rem; padding: 0.15rem 0.5rem;">
                                                Libur Sekolah
                                            </span>
                                        @endif
                                    </div>
                                    <p class="portal-calendar-event-desc">
                                        {{ $event->description ?: 'Tidak ada deskripsi tambahan.' }}
                                    </p>
                                    <div class="portal-calendar-event-footer">
                                        <span>🕒 {{ $startDayName }}, {{ $event->start_date->format('d-m-Y') }} 
                                            @if($event->end_date && $event->end_date != $event->start_date)
                                                s/d {{ $dayNames[$event->end_date->format('l')] ?? $event->end_date->format('l') }}, {{ $event->end_date->format('d-m-Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <div class="portal-panel text-center p-5 text-muted">
                            <span class="fs-1 d-block mb-3">📅</span>
                            Belum ada agenda akademik terdaftar pada semester ini.
                        </div>
                    @endforelse
                </div>

                <!-- Right Side: Sidebar info cards -->
                <div class="portal-calendar-sidebar">
                    <!-- Statistics Card -->
                    <div class="portal-panel portal-calendar-info-widget">
                        <h4>Ringkasan Semester</h4>
                        <div class="portal-progress-row portal-progress-row--legend" style="border-bottom: 1px dashed rgba(34,152,207,0.1); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                            <span>Status Semester</span>
                            <span class="text-{{ $semesterLock ? 'danger' : 'success' }} font-bold">
                                {{ $semesterLock ? 'Ditutup (Read-Only)' : 'Aktif (Terbuka)' }}
                            </span>
                        </div>
                        <div class="portal-progress-row portal-progress-row--legend" style="border-bottom: 1px dashed rgba(34,152,207,0.1); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                            <span>Total Kegiatan</span>
                            <strong class="text-primary">{{ $events->count() }} Agenda</strong>
                        </div>
                        <div class="portal-progress-row portal-progress-row--legend">
                            <span>Hari Libur Resmi</span>
                            <strong class="text-warning">{{ $holidays->count() }} Hari</strong>
                        </div>
                    </div>

                    <!-- Exam schedules Card -->
                    <div class="portal-panel portal-calendar-info-widget">
                        <h4>Jadwal Ujian (UTS/UAS)</h4>
                        @forelse ($exams as $exam)
                            <div class="portal-calendar-quick-item">
                                <span class="portal-calendar-quick-title">{{ $exam->title }}</span>
                                <span class="portal-calendar-quick-date">📅 {{ $exam->start_date->format('d M') }} s/d {{ $exam->end_date->format('d M Y') }}</span>
                            </div>
                        @empty
                            <span class="text-muted text-sm d-block text-center py-2">Belum ada ujian terdaftar.</span>
                        @endforelse
                    </div>

                    <!-- Upcoming holidays Card -->
                    <div class="portal-panel portal-calendar-info-widget">
                        <h4>Hari Libur Terdekat</h4>
                        @forelse ($holidays->take(4) as $holiday)
                            <div class="portal-calendar-quick-item">
                                <span class="portal-calendar-quick-title">{{ $holiday->title }}</span>
                                <span class="portal-calendar-quick-date">📅 {{ $holiday->start_date->format('d M') }} s/d {{ $holiday->end_date->format('d M Y') }}</span>
                            </div>
                        @empty
                            <span class="text-muted text-sm d-block text-center py-2">Tidak ada libur pada semester ini.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
