@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
<div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="{{ $portalKey }}">
    @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => true])

    <main class="portal-dashboard-main">
        <div class="portal-dashboard-toolbar">
            <label class="portal-dashboard-search" for="portal-dashboard-search">
                <span class="portal-dashboard-search__icon">
                    @include('dashboard.partials.icon', ['name' => 'search'])
                </span>
                <input id="portal-dashboard-search" type="search" placeholder="Pencarian..." aria-label="Pencarian dashboard">
            </label>

            <button class="portal-dashboard-search__button" type="button">Cari</button>
        </div>

        <div class="portal-dashboard-feedback d-none" data-search-feedback></div>

        <div class="portal-dashboard-grid">
            <div class="portal-dashboard-content">
                <section class="portal-panel portal-hero portal-hero--{{ $portalKey }}" id="beranda" data-dashboard-section data-search-item data-section-label="Beranda">
                    <div class="portal-hero__content">
                        <span class="portal-hero__badge">{{ $hero['badge'] }}</span>
                        <h1>{{ $hero['title'] }}</h1>
                        <p>{{ $hero['subtitle'] }}</p>
                    </div>

                    <div class="portal-hero__asset">
                        @include('auth.portal.partials.asset', [
                        'asset' => $hero['asset'],
                        'portalLabel' => $pageTitle,
                        ])
                    </div>
                </section>

                @if (isset($announcements) && $announcements->isNotEmpty())
                <section class="portal-panel portal-announcements-card mb-4" style="padding: 20px; border-radius: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="color: #ff9f43; font-size: 1.25rem;">📢</span>
                        <h2 class="h5 mb-0 font-semibold" style="color: #fff; font-size: 1.1rem;">Pengumuman Terbaru</h2>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @foreach ($announcements as $announcement)
                        <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.02); border-left: 4px solid #7367f0;">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h3 class="h6 mb-0 font-bold" style="color: #fff; font-size: 0.95rem;">{{ $announcement->title }}</h3>
                                <span class="text-muted small" style="font-size: 0.8rem;">{{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-muted mb-0 small" style="white-space: pre-line; line-height: 1.5; font-size: 0.875rem;">{{ $announcement->content }}</p>
                            <div class="mt-2 text-muted small d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                <span>✍️ Oleh: <strong>{{ $announcement->creator?->name ?? 'System' }}</strong></span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @if ($portalKey === 'admin')
                @php
                $teacherSummaries = collect($tableRows)
                ->groupBy('homeroom_teacher')
                ->map(function ($rows, $teacher): array {
                return [
                'teacher' => $teacher,
                'class_count' => $rows->count(),
                'students_count' => $rows->sum('students_count'),
                'present_count' => $rows->sum('present_count'),
                ];
                })
                ->values();
                @endphp

                <section class="portal-admin-overview" data-dashboard-section data-section-label="Ringkasan Admin">
                    <div class="portal-admin-actions">
                        <a class="portal-panel portal-action-card" href="{{ url('/admin/rekap-kehadiran') }}" data-note-preset="Tinjau rekap kehadiran kelas hari ini.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'recap'])
                            </span>
                            <strong>Rekap Kehadiran</strong>
                            <small>Pantau kelas dan wali kelas.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/admin/laporan-statistik') }}" data-note-preset="Buka laporan statistik absensi.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'chart'])
                            </span>
                            <strong>Laporan Statistik</strong>
                            <small>Analisis tren absensi.</small>
                        </a>
                    </div>

                    <div class="portal-kpi-grid">
                        @foreach ($stats as $index => $stat)
                        <article class="portal-panel portal-kpi-card" id="{{ ['data-siswa', 'data-guru', 'data-kelas', 'mata-pelajaran'][$index] ?? 'stat-'.$index }}" data-search-item>
                            <span class="portal-kpi-card__icon">
                                @include('dashboard.partials.icon', ['name' => ['students', 'teacher', 'class', 'subject'][$index] ?? 'report'])
                            </span>
                            <div class="portal-kpi-card__value">{{ $stat['value'] }}</div>
                            <div class="portal-kpi-card__label">{{ $stat['label'] }}</div>
                            <div class="portal-kpi-card__meter">
                                <span style="width: {{ min(($stat['value'] * 4), 100) }}%"></span>
                            </div>
                            <div class="portal-kpi-card__meta">{{ $stat['meta'] }}</div>
                        </article>
                        @endforeach
                    </div>
                </section>

                @if (false)
                <section class="portal-panel portal-table-card" id="rekap-kehadiran" data-dashboard-section data-section-label="Rekap Kehadiran">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Rekap Kehadiran Kelas</h2>
                            <p>{{ $latestAttendanceDate ? 'Data terakhir '.$latestAttendanceDate : 'Belum ada data absensi kelas.' }}</p>
                        </div>

                        <div class="portal-segmented-tabs" role="tablist" aria-label="Tampilan rekap admin">
                            <button class="is-active" type="button" role="tab" data-admin-panel-tab="students">Siswa</button>
                            <button type="button" role="tab" data-admin-panel-tab="teachers">Guru</button>
                        </div>
                    </div>

                    <div data-admin-panel="students">
                        <div class="table-responsive">
                            <table class="table portal-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Wali Kelas</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Hadir</th>
                                        <th>Tidak Hadir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tableRows as $row)
                                    <tr data-search-item>
                                        <td>{{ $row['class_name'] }}</td>
                                        <td>{{ $row['homeroom_teacher'] }}</td>
                                        <td>{{ $row['students_count'] }}</td>
                                        <td>{{ $row['present_count'] }}</td>
                                        <td>{{ $row['absent_count'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada data kelas untuk ditampilkan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="portal-admin-summary-grid d-none" data-admin-panel="teachers">
                        @forelse ($teacherSummaries as $summaryCard)
                        <article class="portal-panel portal-admin-summary-card" data-search-item>
                            <h3>{{ $summaryCard['teacher'] }}</h3>
                            <p>{{ $summaryCard['class_count'] }} kelas aktif</p>
                            <div class="portal-admin-summary-card__stats">
                                <span>{{ $summaryCard['students_count'] }} siswa</span>
                                <span>{{ $summaryCard['present_count'] }} hadir</span>
                            </div>
                        </article>
                        @empty
                        <article class="portal-panel portal-placeholder-card">
                            <h2>Data Guru</h2>
                            <p>Belum ada guru yang terhubung ke rekap saat ini.</p>
                        </article>
                        @endforelse
                    </div>
                </section>

                <section class="portal-panel portal-attendance-report" id="laporan-tren" data-dashboard-section data-section-label="Laporan Statistik Absensi">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Laporan Statistik Absensi</h2>
                            <p>Tren kehadiran mingguan dan bulanan, siswa perlu perhatian, kelas rendah hadir, serta rekap mapel/guru.</p>
                        </div>
                        <div class="portal-report-actions">
                            <a class="btn btn-outline-primary btn-sm" href="{{ url('/admin/export/absensi') }}">Export Absensi</a>
                            <a class="btn btn-outline-primary btn-sm" href="{{ url('/admin/export/catatan-siswa') }}">Export Catatan</a>
                        </div>
                    </div>

                    <div class="portal-report-trend-grid">
                        <article class="portal-report-card" data-search-item>
                            <div class="portal-report-card__header">
                                <h3>Kehadiran per Minggu</h3>
                                <span>{{ count($attendanceReports['weekly_trends']) }} periode</span>
                            </div>

                            <div class="portal-report-bars">
                                @forelse ($attendanceReports['weekly_trends'] as $trend)
                                <div class="portal-report-bar">
                                    <div class="portal-report-bar__meta">
                                        <span>{{ $trend['label'] }}</span>
                                        <strong>{{ $trend['present_rate'] }}%</strong>
                                    </div>
                                    <div class="portal-report-bar__track">
                                        <span style="width: {{ max($trend['present_rate'], 6) }}%"></span>
                                    </div>
                                    <small>{{ $trend['present'] }} hadir | {{ $trend['absent'] }} tidak hadir</small>
                                </div>
                                @empty
                                <p class="portal-report-empty">Belum ada data tren mingguan.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="portal-report-card" data-search-item>
                            <div class="portal-report-card__header">
                                <h3>Kehadiran per Bulan</h3>
                                <span>{{ count($attendanceReports['monthly_trends']) }} periode</span>
                            </div>

                            <div class="portal-report-bars">
                                @forelse ($attendanceReports['monthly_trends'] as $trend)
                                <div class="portal-report-bar">
                                    <div class="portal-report-bar__meta">
                                        <span>{{ $trend['label'] }}</span>
                                        <strong>{{ $trend['present_rate'] }}%</strong>
                                    </div>
                                    <div class="portal-report-bar__track">
                                        <span style="width: {{ max($trend['present_rate'], 6) }}%"></span>
                                    </div>
                                    <small>{{ $trend['present'] }} hadir | {{ $trend['absent'] }} tidak hadir</small>
                                </div>
                                @empty
                                <p class="portal-report-empty">Belum ada data tren bulanan.</p>
                                @endforelse
                            </div>
                        </article>
                    </div>

                    <div class="portal-report-list-grid">
                        <article class="portal-report-card" data-search-item>
                            <div class="portal-report-card__header">
                                <h3>Siswa Sering Alpha/Sakit/Izin</h3>
                                <span>Top {{ count($attendanceReports['top_absent_students']) }}</span>
                            </div>

                            <div class="portal-report-list">
                                @forelse ($attendanceReports['top_absent_students'] as $student)
                                <div class="portal-report-row">
                                    <div>
                                        <strong>{{ $student['student'] }}</strong>
                                        <small>{{ $student['class_name'] }}</small>
                                    </div>
                                    <span>{{ $student['total'] }} kasus</span>
                                    <small>A {{ $student['alpha'] }} | S {{ $student['sakit'] }} | I {{ $student['izin'] }}</small>
                                </div>
                                @empty
                                <p class="portal-report-empty">Belum ada siswa dengan catatan tidak hadir.</p>
                                @endforelse
                            </div>
                        </article>

                        <article class="portal-report-card" data-search-item>
                            <div class="portal-report-card__header">
                                <h3>Kelas Hadir Rendah</h3>
                                <span>{{ $attendanceReports['effective_days'] }} hari efektif</span>
                            </div>

                            <div class="portal-report-list">
                                @forelse ($attendanceReports['low_attendance_classes'] as $classReport)
                                <div class="portal-report-row">
                                    <div>
                                        <strong>{{ $classReport['class_name'] }}</strong>
                                        <small>{{ $classReport['homeroom_teacher'] }}</small>
                                    </div>
                                    <span>{{ $classReport['present_rate'] }}%</span>
                                    <small>{{ $classReport['present'] }} hadir | {{ $classReport['absent'] }} tidak hadir</small>
                                </div>
                                @empty
                                <p class="portal-report-empty">Belum ada data kelas yang bisa dihitung.</p>
                                @endforelse
                            </div>
                        </article>
                    </div>

                    <div class="portal-report-card portal-report-card--wide" data-search-item>
                        <div class="portal-report-card__header">
                            <h3>Rekap Mapel/Guru</h3>
                            <span>{{ count($attendanceReports['subject_teacher_recaps']) }} jadwal</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table portal-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mapel</th>
                                        <th>Guru</th>
                                        <th>Kelas</th>
                                        <th>Hadir</th>
                                        <th>Alpha</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>% Hadir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($attendanceReports['subject_teacher_recaps'] as $recap)
                                    <tr>
                                        <td>{{ $recap['subject'] }}</td>
                                        <td>{{ $recap['teacher'] }}</td>
                                        <td>{{ $recap['class_name'] }}</td>
                                        <td>{{ $recap['present'] }}</td>
                                        <td>{{ $recap['alpha'] }}</td>
                                        <td>{{ $recap['sakit'] }}</td>
                                        <td>{{ $recap['izin'] }}</td>
                                        <td>{{ $recap['present_rate'] }}%</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada rekap mapel/guru untuk ditampilkan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                @endif

                <section class="portal-panel portal-access-card" id="manajemen-pengguna" data-dashboard-section data-section-label="Manajemen Pengguna">
                    <h2>Manajemen Pengguna</h2>
                    <p>Hak akses inti admin yang aktif pada portal ini.</p>
                    <div class="portal-access-pills">
                        @foreach ($apiPayload['akses'] as $access)
                        <span class="portal-access-pill" data-search-item>{{ $access }}</span>
                        @endforeach
                    </div>
                </section>
                @elseif ($portalKey === 'guru-mapel')
                <section class="portal-panel portal-schedule-card" id="jadwal-mengajar" data-dashboard-section data-section-label="Jadwal Mengajar">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Jadwal Mengajar Hari Ini</h2>
                            <p>{{ $todayLabel ?? 'Hari ini' }} — Ringkasan jadwal yang perlu dipantau pada sesi aktif.</p>
                        </div>
                    </div>

                    <div class="portal-schedule-list">
                        @forelse ($scheduleRows as $row)
                        <div class="portal-schedule-card-item" data-search-item>
                            <div class="portal-schedule-card-header">
                                <div class="portal-schedule-card-header-left">
                                    <span class="portal-schedule-card-time">{{ $row['time'] }}</span>
                                    <span class="portal-badge is-primary">Jam Ke-{{ $row['lesson_period'] }}</span>
                                    @if ($row['is_filled'])
                                        <span class="portal-badge is-success">Sudah Diisi</span>
                                    @else
                                        <span class="portal-badge is-warning">Belum Diisi</span>
                                    @endif
                                </div>
                                <span class="portal-badge is-{{ $row['status']['tone'] }}">{{ $row['status']['label'] }}</span>
                            </div>
                            <div class="portal-schedule-card-body">
                                <h4 class="portal-schedule-card-subject">{{ $row['subject'] }}</h4>
                                <div class="portal-schedule-card-meta">
                                    <div class="portal-schedule-card-meta-item">
                                        <span class="portal-schedule-card-meta-label">Kelas</span>
                                        <span class="portal-schedule-card-meta-value">{{ $row['class_name'] }}</span>
                                    </div>
                                    <div class="portal-schedule-card-meta-item">
                                        <span class="portal-schedule-card-meta-label">Ruang</span>
                                        <span class="portal-schedule-card-meta-value">{{ $row['room'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted">Tidak ada jadwal untuk hari ini.</div>
                        @endforelse
                    </div>
                </section>

                <section class="portal-panel portal-workbench-card portal-teacher-attendance-card" id="absensi-siswa" data-dashboard-section data-section-label="Presensi Belum Diisi">
                    <div class="portal-section-heading portal-teacher-attendance-card__head">
                        <div>
                            <h2>Presensi Belum Diisi</h2>
                            <p>Pilih jadwal, cek daftar siswa, lalu simpan status kehadiran.</p>
                        </div>
                        <span class="portal-teacher-attendance-card__count">{{ count($teacherStudents ?? []) }} siswa</span>
                    </div>

                    <form class="portal-attendance-form" data-subject-attendance-form>
                        <div class="portal-form-grid portal-teacher-attendance-controls">
                            <label>
                                <span>Jadwal</span>
                                <select class="form-select" data-assignment-select required>
                                    @forelse ($scheduleRows as $row)
                                    <option value="{{ $row['id'] }}" data-class-id="{{ $row['school_class_id'] }}">{{ $row['time'] }} | {{ $row['subject'] }} | {{ $row['class_name'] }}</option>
                                    @empty
                                    <option value="">Belum ada jadwal</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                <span>Tanggal</span>
                                <input class="form-control" type="date" value="{{ now()->toDateString() }}" data-attendance-date required>
                            </label>
                        </div>

                        <div class="portal-assignment-list" data-assignment-cards></div>

                        <div class="portal-teacher-attendance-toolbar">
                            <div>
                                <strong>Daftar siswa</strong>
                                <span data-roster-summary>Pilih jadwal untuk menampilkan siswa.</span>
                            </div>
                            <div class="portal-teacher-attendance-actions">
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="hadir">Hadir Semua</button>
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="izin">Izin Semua</button>
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="sakit">Sakit Semua</button>
                            </div>
                        </div>

                        <div class="portal-attendance-roster" data-attendance-students></div>
                        <div class="portal-form-feedback d-none" data-attendance-feedback></div>

                        <div class="portal-teacher-attendance-submit">
                            <div>
                                <strong>Siap disimpan?</strong>
                                <span>Pastikan status dan catatan siswa sudah benar.</span>
                            </div>
                            <button class="btn btn-primary portal-form-submit" type="submit">Simpan Absensi Mapel</button>
                        </div>
                    </form>
                </section>

                <section class="portal-overview-band" data-dashboard-section data-section-label="Ringkasan Guru Mapel">
                    @foreach ($summary as $item)
                    <article class="portal-panel portal-summary-card {{ $loop->first ? 'has-anchor' : '' }}" @if ($loop->first) id="ringkasan-guru" @endif data-search-item>
                        <strong>{{ $item['value'] }}</strong>
                        <span>{{ $item['label'] }}</span>
                        <small>Aktif hari ini</small>
                    </article>
                    @endforeach
                </section>

                @if (!empty($scheduleRows))
                <section class="portal-panel portal-table-card" data-dashboard-section data-section-label="Informasi Kelas">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Informasi Kelas Siswa</h2>
                            <p>Daftar kelas yang diajar hari ini beserta jumlah siswa.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table portal-table mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                    <th>Jam</th>
                                    <th>Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $classStudentCounts = collect($teacherStudents ?? [])
                                        ->groupBy('school_class_id')
                                        ->map(fn($group) => $group->count());
                                @endphp
                                @foreach ($scheduleRows as $index => $row)
                                <tr data-search-item>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $row['class_name'] }}</strong></td>
                                    <td>{{ $row['subject'] }}</td>
                                    <td>{{ $row['time'] }}</td>
                                    <td><span class="portal-badge is-primary">{{ $classStudentCounts[$row['school_class_id']] ?? 0 }} siswa</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif

                <section class="portal-admin-overview portal-admin-overview--full" data-dashboard-section data-section-label="Pintasan Guru Mapel">
                    <div class="portal-admin-actions">
                        <a class="portal-panel portal-action-card" href="{{ url('/guru-mapel/jadwal-mengajar') }}" data-note-preset="Cek jadwal mengajar hari ini.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'schedule'])
                            </span>
                            <strong>Jadwal Mengajar</strong>
                            <small>Lihat sesi aktif hari ini.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/guru-mapel/absensi-siswa/tambah') }}" data-note-preset="Isi absensi mapel sebelum jam terakhir.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'attendance'])
                            </span>
                            <strong>Tambah Absen</strong>
                            <small>Input absensi baru per jadwal.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/guru-mapel/absensi-siswa/daftar') }}" data-note-preset="Cek daftar absensi yang sudah diinput.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'recap'])
                            </span>
                            <strong>Daftar Absen</strong>
                            <small>Lihat absensi yang sudah tersimpan.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/guru-mapel/rekap-absensi') }}" data-note-preset="Review rekap absensi mapel.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'recap'])
                            </span>
                            <strong>Rekap Absen</strong>
                            <small>Pantau ringkasan kehadiran.</small>
                        </a>
                    </div>
                </section>

{{--
                <section class="portal-metric-grid" id="rekap-absensi" data-dashboard-section data-section-label="Rekap Absensi">
                    @forelse ($attendanceCards as $card)
                    <article class="portal-panel portal-metric-card" data-search-item>
                        <div class="portal-metric-card__header">
                            <div>
                                <h3>{{ $card['title'] }}</h3>
                                <p>{{ $card['subtitle'] }}</p>
                            </div>
                            <span class="portal-metric-card__badge">{{ collect($card['counts'])->sum('value') }}</span>
                        </div>

                        @foreach ($card['counts'] as $count)
                        <div class="portal-progress-row portal-progress-row--legend">
                            <span>{{ $count['label'] }}</span>
                            <strong>{{ $count['value'] }}</strong>
                        </div>
                        <div class="progress portal-progress">
                            <div class="progress-bar bg-{{ $count['color'] }}" style="width: {{ min(max(($count['value'] * 16), 12), 100) }}%"></div>
                        </div>
                        @endforeach
                    </article>
                    @empty
                    <article class="portal-panel portal-placeholder-card" id="absensi-siswa">
                        <h2>Absensi Siswa</h2>
                        <p>Belum ada rekap absensi mapel yang dapat ditampilkan.</p>
                    </article>
                    @endforelse
                </section>
                --}}

                @elseif ($portalKey === 'walikelas')
                <section class="portal-overview-band" data-dashboard-section data-section-label="Ringkasan Wali Kelas">
                    @foreach ($summary as $item)
                    <article class="portal-panel portal-summary-card" id="{{ $loop->first ? 'data-siswa' : '' }}" data-search-item>
                        <strong>{{ $item['value'] }}</strong>
                        <span>{{ $item['label'] }}</span>
                        <small>{{ $item['meta'] }}</small>
                    </article>
                    @endforeach
                </section>

                <section class="portal-panel portal-class-focus-card" data-dashboard-section data-section-label="Data Siswa Perwalian">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Kehadiran {{ $classes[0]['name'] ?? 'Kelas Perwalian' }} Hari Ini</h2>
                            <p>Ringkasan cepat siswa perwalian dan prioritas tindak lanjut.</p>
                        </div>
                    </div>

                    <div class="portal-metric-grid portal-metric-grid--homeroom">
                        @foreach ($summary as $item)
                        <article class="portal-panel portal-metric-card portal-metric-card--compact" data-search-item>
                            <div class="portal-metric-card__header">
                                <div>
                                    <h3>{{ $item['label'] }}</h3>
                                    <p>{{ $item['meta'] }}</p>
                                </div>
                                <span class="portal-metric-card__badge">{{ $item['value'] }}</span>
                            </div>

                            <div class="portal-strip-chart">
                                <span class="is-primary" style="width: {{ min(max(($item['value'] * 8), 24), 100) }}%"></span>
                                <span class="is-warning" style="width: {{ min(max(($item['value'] * 5), 18), 100) }}%"></span>
                                <span class="is-danger" style="width: {{ min(max(($item['value'] * 3), 12), 100) }}%"></span>
                            </div>
                        </article>
                        @endforeach
                    </div>

                    <div class="portal-class-chip-list">
                        @forelse (array_slice($classes, 0, 6) as $class)
                        <article class="portal-class-chip" data-search-item>
                            <strong>{{ $class['name'] }}</strong>
                            <span>{{ $class['students_count'] }} siswa</span>
                            <small>{{ $class['level'] ?: 'Tanpa tingkat' }}</small>
                        </article>
                        @empty
                        <div class="text-muted">Belum ada kelas perwalian.</div>
                        @endforelse
                    </div>
                </section>

                <section class="portal-admin-overview portal-admin-overview--full" data-dashboard-section data-section-label="Pintasan Wali Kelas">
                    <div class="portal-admin-actions">
                        <a class="portal-panel portal-action-card" href="{{ url('/walikelas/absensi-kelas') }}" data-note-preset="Isi absensi kelas hari ini.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'attendance'])
                            </span>
                            <strong>Isi Absensi Kelas</strong>
                            <small>Simpan Absensi Kelas di halaman khusus.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/walikelas/rekap-absensi') }}" data-note-preset="Review rekap absensi kelas.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'recap'])
                            </span>
                            <strong>Rekap Absensi</strong>
                            <small>Pantau ringkasan kehadiran kelas.</small>
                        </a>

                        <a class="portal-panel portal-action-card" href="{{ url('/walikelas/catatan-siswa') }}" data-note-preset="Tulis catatan siswa yang perlu ditindaklanjuti.">
                            <span class="portal-action-card__icon">
                                @include('dashboard.partials.icon', ['name' => 'note'])
                            </span>
                            <strong>Catatan Siswa</strong>
                            <small>Kelola catatan pembinaan siswa.</small>
                        </a>
                    </div>
                </section>


                <section class="portal-panel portal-workbench-card portal-teacher-attendance-card" id="absensi-kelas" data-dashboard-section data-section-label="Isi Absensi Kelas">
                    <div class="portal-section-heading portal-teacher-attendance-card__head">
                        <div>
                            <h2>Isi Absensi Kelas</h2>
                            <p>Pilih kelas perwalian, cek daftar siswa, lalu simpan status kehadiran.</p>
                        </div>
                        <span class="portal-teacher-attendance-card__count">{{ count($homeroomStudents ?? []) }} siswa</span>
                    </div>

                    <form class="portal-attendance-form" data-class-attendance-form>
                        <div class="portal-form-grid portal-teacher-attendance-controls">
                            <label>
                                <span>Kelas</span>
                                <select class="form-select" data-class-select required>
                                    @forelse ($classes as $class)
                                    <option value="{{ $class['id'] }}">{{ $class['name'] }} | {{ $class['students_count'] }} siswa</option>
                                    @empty
                                    <option value="">Belum ada kelas</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                <span>Tanggal</span>
                                <input class="form-control" type="date" value="{{ now()->toDateString() }}" data-attendance-date required>
                            </label>
                        </div>

                        <div class="portal-teacher-attendance-toolbar">
                            <div>
                                <strong>Daftar siswa</strong>
                                <span data-roster-summary>Pilih kelas untuk menampilkan siswa.</span>
                            </div>
                            <div class="portal-teacher-attendance-actions">
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="hadir">Hadir Semua</button>
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="izin">Izin Semua</button>
                                <button class="btn btn-light btn-sm" type="button" data-mark-status="sakit">Sakit Semua</button>
                            </div>
                        </div>

                        <div class="portal-attendance-roster" data-attendance-students></div>
                        <div class="portal-form-feedback d-none" data-attendance-feedback></div>

                        <div class="portal-teacher-attendance-submit">
                            <div>
                                <strong>Siap disimpan?</strong>
                                <span>Pastikan status dan catatan siswa sudah benar.</span>
                            </div>
                            <button class="btn btn-primary portal-form-submit" type="submit">Simpan Absensi Kelas</button>
                        </div>
                    </form>
                </section>

                <section class="portal-panel portal-teacher-recap" id="rekap-absensi" data-dashboard-section data-section-label="Rekap Absensi Kelas">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Rekap Absensi Kelas</h2>
                            <p>Data terakhir {{ $classAttendanceSummary['latest_date'] }} dengan persentase hadir {{ $classAttendanceSummary['present_rate'] }}%.</p>
                        </div>
                    </div>

                    <div class="portal-teacher-recap__summary">
                        <article>
                            <span>Total Catatan</span>
                            <strong>{{ $classAttendanceSummary['total'] }}</strong>
                        </article>
                        <article>
                            <span>Hadir</span>
                            <strong>{{ $classAttendanceSummary['hadir'] }}</strong>
                        </article>
                        <article>
                            <span>Izin</span>
                            <strong>{{ $classAttendanceSummary['izin'] }}</strong>
                        </article>
                        <article>
                            <span>Sakit</span>
                            <strong>{{ $classAttendanceSummary['sakit'] }}</strong>
                        </article>
                        <article>
                            <span>Alpha</span>
                            <strong>{{ $classAttendanceSummary['alpha'] }}</strong>
                        </article>
                    </div>

                    <div class="portal-report-card portal-report-card--wide">
                        <div class="portal-report-card__header">
                            <h3>Rekap Per Kelas</h3>
                            <span>{{ count($classAttendanceRecapRows) }} kelas</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table portal-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Pertemuan</th>
                                        <th>Hadir</th>
                                        <th>Izin</th>
                                        <th>Sakit</th>
                                        <th>Alpha</th>
                                        <th>% Hadir</th>
                                        <th>Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($classAttendanceRecapRows as $row)
                                    <tr data-search-item>
                                        <td>{{ $row['class_name'] }}</td>
                                        <td>{{ $row['dates_count'] }}</td>
                                        <td>{{ $row['hadir'] }}</td>
                                        <td>{{ $row['izin'] }}</td>
                                        <td>{{ $row['sakit'] }}</td>
                                        <td>{{ $row['alpha'] }}</td>
                                        <td><span class="portal-badge is-primary">{{ $row['present_rate'] }}%</span></td>
                                        <td>{{ $row['latest_date'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada data absensi kelas.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="portal-report-card portal-report-card--wide">
                        <div class="portal-report-card__header">
                            <h3>Detail Catatan Siswa</h3>
                            <span>{{ count($classAttendanceDetailRows) }} baris</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table portal-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Siswa</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($classAttendanceDetailRows as $row)
                                    <tr data-search-item>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ $row['class_name'] }}</td>
                                        <td>{{ $row['student'] }}</td>
                                        <td>{{ $row['status'] }}</td>
                                        <td>{{ $row['notes'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada detail absensi yang dapat ditampilkan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="portal-panel portal-journal-card" id="catatan-siswa" data-dashboard-section data-section-label="Catatan Siswa">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Catatan Siswa</h2>
                            <p>Ringkasan siswa yang perlu ditinjau lebih lanjut.</p>
                        </div>
                        <a class="portal-round-action" href="{{ url('/walikelas/catatan-siswa') }}" aria-label="Kelola catatan">
                            @include('dashboard.partials.icon', ['name' => 'edit'])
                        </a>
                    </div>

                    <div class="portal-journal-surface" data-dashboard-section data-section-label="Catatan Siswa">
                        @if (($studentNoteRows ?? []) !== [])
                        <ul class="portal-note-list portal-note-list--plain">
                            @foreach ($studentNoteRows as $note)
                            <li data-search-item>{{ $note }}</li>
                            @endforeach
                        </ul>
                        @elseif ($noteBox !== [])
                        <ul class="portal-note-list portal-note-list--plain">
                            @foreach ($noteBox as $note)
                            <li data-search-item>{{ $note }}</li>
                            @endforeach
                        </ul>
                        @else
                        <p>Tidak ada catatan khusus hari ini. Semua siswa tercatat hadir atau belum ada rekap terbaru.</p>
                        @endif
                    </div>
                </section>

                @elseif ($portalKey === 'orang-tua')
                @forelse ($children as $child)
                <section class="portal-panel mb-6" id="anak-{{ $child['id'] }}" data-dashboard-section data-section-label="Anak: {{ $child['name'] }}">
                    <div class="portal-section-heading">
                        <div>
                            <h2>{{ $child['name'] }}</h2>
                            <p class="text-muted">Kelas: <strong>{{ $child['class_name'] }}</strong> | Wali Kelas: <strong>{{ $child['homeroom_teacher'] }}</strong></p>
                        </div>
                    </div>

                    <div class="portal-overview-band mt-4">
                        <article class="portal-panel portal-summary-card">
                            <strong>{{ $child['attendance']['hadir'] }}</strong>
                            <span>Hadir</span>
                            <small>Kehadiran Harian</small>
                        </article>
                        <article class="portal-panel portal-summary-card">
                            <strong>{{ $child['attendance']['sakit'] }}</strong>
                            <span>Sakit</span>
                            <small>Kehadiran Harian</small>
                        </article>
                        <article class="portal-panel portal-summary-card">
                            <strong>{{ $child['attendance']['izin'] }}</strong>
                            <span>Izin</span>
                            <small>Kehadiran Harian</small>
                        </article>
                        <article class="portal-panel portal-summary-card">
                            <strong>{{ $child['attendance']['alpha'] }}</strong>
                            <span>Alpha</span>
                            <small>Kehadiran Harian</small>
                        </article>
                    </div>

                    <!-- Jadwal Pelajaran -->
                    <div class="portal-panel mt-6" style="padding: 20px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                        <h3 class="font-semibold text-lg mb-4">Jadwal Pelajaran Hari Ini</h3>
                        <div class="portal-schedule-list">
                            @forelse ($child['schedules'] as $row)
                            <div class="portal-schedule-card-item mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px; width: 100%;">
                                <div class="portal-schedule-card-header d-flex align-items-center gap-2" style="flex-wrap: wrap;">
                                    <span class="portal-schedule-card-time" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.9rem;">{{ $row['time'] }}</span>
                                    <span class="portal-badge is-primary ms-auto" style="font-size: 0.78rem;">🚪 {{ $row['room'] }}</span>
                                </div>
                                <div class="portal-schedule-card-body d-flex flex-column gap-1">
                                    <h4 class="portal-schedule-card-subject" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff; text-align: left;">{{ $row['subject'] }}</h4>
                                    <span class="portal-schedule-card-meta-item" style="font-size: 0.85rem; color: rgba(255,255,255,0.6); text-align: left;">
                                        <strong>Guru:</strong> {{ $row['teacher'] }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-muted text-sm">Tidak ada jadwal pelajaran hari ini.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Catatan & Pelanggaran -->
                    <div class="portal-panel mt-6" style="padding: 20px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                        <h3 class="font-semibold text-lg mb-4">Catatan Perkembangan & Pelanggaran</h3>
                        <div class="portal-journal-surface">
                            @if (count($child['notes']) > 0 || count($child['violations']) > 0)
                            <ul class="portal-note-list portal-note-list--plain" style="list-style: none; padding: 0;">
                                @foreach ($child['notes'] as $note)
                                <li class="mb-2" style="background: rgba(255, 235, 186, 0.05); border-left: 4px solid #f8b803; padding: 10px; border-radius: 4px;">
                                    <strong style="color: #f8b803;">[Catatan Wali Kelas]</strong> {{ $note['title'] }}: {{ $note['note'] }}
                                </li>
                                @endforeach
                                @foreach ($child['violations'] as $violation)
                                <li class="mb-2" style="background: rgba(245, 48, 3, 0.05); border-left: 4px solid #f53003; padding: 10px; border-radius: 4px;">
                                    <strong style="color: #f53003;">[Pelanggaran]</strong> {{ $violation['type'] }} (Poin: {{ $violation['points'] }}): {{ $violation['description'] }}
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-muted text-sm">Tidak ada catatan perkembangan atau pelanggaran khusus saat ini.</p>
                            @endif
                        </div>
                    </div>
                </section>
                @empty
                <section class="portal-panel">
                    <p class="text-muted">Akun Orang Tua ini belum dihubungkan dengan data siswa. Silakan hubungi Admin Sekolah untuk menautkan akun Anda dengan data anak Anda.</p>
                </section>
                @endforelse

                @else
                <section class="portal-panel portal-table-card" id="jadwal-sekolah" data-dashboard-section data-section-label="Jadwal Sekolah">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Jadwal Pelajaran Hari Ini</h2>
                            <p>{{ $todayLabel ?? 'Hari ini' }}@if (!empty($studentClassName)) - Kelas {{ $studentClassName }}@endif</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table portal-table portal-table--student mb-0">
                            <thead>
                                <tr>
                                    <th>Jam Ke-</th>
                                    <th>Jam Mapel</th>
                                    <th>Mapel</th>
                                    <th>Guru</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($scheduleRows as $row)
                                <tr data-search-item>
                                    <td><span class="portal-badge is-primary">{{ $row['lesson_period'] }}</span></td>
                                    <td>{{ $row['time'] }}</td>
                                    <td>{{ $row['subject'] }}</td>
                                    <td>{{ $row['teacher'] }}</td>
                                    <td><span class="portal-badge is-{{ $row['status']['tone'] }}">{{ $row['status']['label'] }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada jadwal untuk hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="portal-overview-band" data-dashboard-section data-section-label="Ringkasan Siswa">
                    @foreach ($summary as $item)
                    <article class="portal-panel portal-summary-card" data-search-item>
                        <strong>{{ $item['value'] }}</strong>
                        <span>{{ $item['label'] }}</span>
                        <small>{{ $item['meta'] }}</small>
                    </article>
                    @endforeach
                </section>

                <section class="portal-attendance-feed" id="absensi-siswa" data-dashboard-section data-section-label="Absensi Siswa">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Absensi Hari Ini</h2>
                            <p>Riwayat kehadiran terbaru yang sudah tercatat pada akunmu.</p>
                        </div>
                    </div>

                    <div class="portal-attendance-feed__list">
                        @forelse ($attendanceRows as $row)
                        <article class="portal-panel portal-attendance-feed__item" data-search-item>
                            <div class="portal-attendance-feed__top">
                                <span class="portal-chip portal-chip--time">{{ $row['date'] }}</span>
                                <span class="portal-chip">{{ $row['subject'] }}</span>
                                <span class="portal-badge is-{{ strtolower($row['status']) === 'hadir' ? 'success' : 'warning' }}">{{ $row['status'] }}</span>
                            </div>

                            <div class="portal-attendance-feed__note">{{ $row['notes'] }}</div>
                        </article>
                        @empty
                        <article class="portal-panel portal-placeholder-card">
                            <h2>Absensi Siswa</h2>
                            <p>Belum ada data absensi untuk akun ini.</p>
                        </article>
                        @endforelse
                    </div>
                </section>
                @endif

                @if ($portalKey !== 'guru-mapel')
                <section class="portal-panel portal-settings-card" id="pengaturan" data-dashboard-section data-section-label="Pengaturan">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Pengaturan</h2>
                            <p>Ringkasan preferensi dashboard dan modul yang siap dikembangkan berikutnya.</p>
                        </div>
                    </div>

                    <div class="portal-access-pills">
                        @foreach ($apiPayload['akses'] as $access)
                        <span class="portal-access-pill" data-search-item>{{ $access }}</span>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <aside class="portal-dashboard-rightbar">
                <section class="portal-panel portal-profile-card" data-dashboard-section data-section-label="Profil">
                    <div class="portal-profile-card__header">
                        <div class="portal-profile-card__identity">
                            <div>
                                <h3>{{ $profile['name'] }}</h3>
                                <p>{{ $profile['role'] }}</p>
                            </div>

                            <a href="{{ route('profile.edit') }}" class="portal-round-action portal-round-action--light" aria-label="Ubah profil">
                                @include('dashboard.partials.icon', ['name' => 'edit'])
                            </a>
                        </div>

                        @if ($profile['photo_url'])
                        <img src="{{ $profile['photo_url'] }}" alt="{{ $profile['name'] }}" class="portal-profile-card__avatar">
                        @else
                        <div class="portal-profile-card__avatar portal-profile-card__avatar--fallback">
                            {{ $profile['initials'] }}
                        </div>
                        @endif
                    </div>

                    <div class="portal-profile-card__body" data-profile-body>
                        <div class="portal-profile-card__line">
                            <span class="portal-profile-card__line-icon">
                                @include('dashboard.partials.icon', ['name' => 'mail'])
                            </span>
                            <strong>{{ $profile['email'] }}</strong>
                        </div>
                        <div class="portal-profile-card__line">
                            <span class="portal-profile-card__line-icon">
                                @include('dashboard.partials.icon', ['name' => 'phone'])
                            </span>
                            <strong>{{ $profile['phone'] }}</strong>
                        </div>
                        <div class="portal-profile-card__line portal-profile-card__line--address">
                            <span class="portal-profile-card__line-icon">
                                @include('dashboard.partials.icon', ['name' => 'pin'])
                            </span>
                            <strong>{{ $profile['address'] }}</strong>
                        </div>
                    </div>
                </section>

                @include('dashboard.partials.calendar', ['calendar' => $calendar])

                <section class="portal-panel portal-academic-agenda-card" id="kalender-akademik" data-dashboard-section data-section-label="Kalender Akademik">
                    <div class="portal-note-card__header">
                        <h3>Kalender Akademik</h3>
                        <span>{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                    </div>

                    <div class="portal-academic-agenda-list">
                        @forelse ($academicCalendarEvents as $event)
                        <article class="portal-academic-agenda-item {{ $event['is_holiday'] ? 'is-holiday' : '' }}" data-search-item>
                            <div>
                                <strong>{{ $event['title'] }}</strong>
                                <small>{{ $event['period'] }}</small>
                            </div>
                            <span>{{ $event['category'] }}</span>
                        </article>
                        @empty
                        <p class="portal-academic-agenda-empty">Belum ada agenda akademik aktif untuk semester ini.</p>
                        @endforelse
                    </div>
                </section>

                <section class="portal-panel portal-note-card" data-dashboard-section data-section-label="Catatan">
                    <div class="portal-note-card__header">
                        <h3>Catatan</h3>
                        <button type="button" aria-label="Tambah catatan" data-note-add>
                            @include('dashboard.partials.icon', ['name' => 'plus'])
                        </button>
                    </div>

                    <div class="portal-note-composer">
                        <input class="form-control" type="text" placeholder="Tambahkan catatan..." data-note-input>
                    </div>

                    <div class="portal-note-card__divider"></div>

                    <ul class="portal-task-list" data-note-list>
                        @foreach ($checklist as $item)
                        <li data-note-item data-search-item>
                            <span class="portal-task-list__check"></span>
                            <span>{{ $item }}</span>
                        </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection

@push('scripts')
@php
$teacherStudentsForJs = $teacherStudents ?? [];
$homeroomStudentsForJs = $homeroomStudents ?? [];
$attendanceStatusesForJs = $attendanceStatuses ?? ['hadir', 'izin', 'sakit', 'alpha'];
$scheduleRowsForJs = $scheduleRows ?? [];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dashboard = document.querySelector('[data-dashboard]');

        if (!dashboard) {
            return;
        }

        const portalKey = dashboard.dataset.dashboardPortal || 'portal';
        const portalPrefix = '/' + portalKey;
        const searchInput = dashboard.querySelector('#portal-dashboard-search');
        const searchButton = dashboard.querySelector('.portal-dashboard-search__button');
        const feedbackBox = dashboard.querySelector('[data-search-feedback]');
        const sections = Array.from(dashboard.querySelectorAll('[data-dashboard-section]'));
        const navLinks = Array.from(dashboard.querySelectorAll('[data-nav-link]'));
        const csrfToken = '{{ csrf_token() }}';
        const teacherStudents = @json($teacherStudentsForJs);
        const homeroomStudents = @json($homeroomStudentsForJs);
        const attendanceStatuses = @json($attendanceStatusesForJs);
        let scheduleRows = @json($scheduleRowsForJs);

        const setActiveNav = function(id) {
            navLinks.forEach(function(link) {
                const isActive = link.getAttribute('href') === '#' + id;
                link.classList.toggle('is-active', isActive);
            });
        };

        navLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                const targetSelector = link.getAttribute('href');

                if (!targetSelector || !targetSelector.startsWith('#')) {
                    return;
                }

                const target = dashboard.querySelector(targetSelector);

                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                setActiveNav(target.id);
            });
        });

        if ('IntersectionObserver' in window) {
            const observedSections = sections.filter(function(section) {
                return Boolean(section.id);
            });

            const observer = new IntersectionObserver(function(entries) {
                const visible = entries
                    .filter(function(entry) {
                        return entry.isIntersecting;
                    })
                    .sort(function(a, b) {
                        return b.intersectionRatio - a.intersectionRatio;
                    })[0];

                if (visible && visible.target.id) {
                    setActiveNav(visible.target.id);
                }
            }, {
                rootMargin: '-18% 0px -58% 0px',
                threshold: [0.2, 0.45, 0.7],
            });

            observedSections.forEach(function(section) {
                observer.observe(section);
            });
        }

        const updateSearchFeedback = function(query, matches) {
            if (!feedbackBox) {
                return;
            }

            if (query === '') {
                feedbackBox.textContent = '';
                feedbackBox.classList.add('d-none');

                return;
            }

            feedbackBox.textContent = matches > 0 ?
                'Menampilkan ' + matches + ' bagian untuk kata kunci "' + query + '".' :
                'Tidak ada bagian yang cocok untuk kata kunci "' + query + '".';
            feedbackBox.classList.remove('d-none');
        };

        const applySearch = function() {
            if (!searchInput) {
                return;
            }

            const query = searchInput.value.trim().toLowerCase();
            let visibleSections = 0;

            sections.forEach(function(section) {
                const nestedItems = Array.from(section.querySelectorAll('[data-search-item]')).filter(function(item) {
                    return item !== section;
                });

                nestedItems.forEach(function(item) {
                    item.classList.remove('is-search-hidden', 'is-search-hit');
                });

                if (query === '') {
                    section.classList.remove('is-search-hidden', 'is-search-hit');
                    visibleSections += 1;

                    return;
                }

                let matchedItemCount = 0;

                nestedItems.forEach(function(item) {
                    const match = item.textContent.toLowerCase().includes(query);
                    item.classList.toggle('is-search-hidden', !match);
                    item.classList.toggle('is-search-hit', match);

                    if (match) {
                        matchedItemCount += 1;
                    }
                });

                const sectionLabel = (section.dataset.sectionLabel || '').toLowerCase();
                const sectionMatch = section.textContent.toLowerCase().includes(query) || sectionLabel.includes(query) || matchedItemCount > 0;

                section.classList.toggle('is-search-hidden', !sectionMatch);
                section.classList.toggle('is-search-hit', sectionMatch);

                if (sectionMatch) {
                    visibleSections += 1;
                }
            });

            updateSearchFeedback(query, visibleSections);
        };

        if (searchButton && searchInput) {
            searchButton.addEventListener('click', function() {
                applySearch();
                searchInput.focus();
            });

            searchInput.addEventListener('input', applySearch);
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applySearch();
                }
            });
        }

        const tabButtons = Array.from(dashboard.querySelectorAll('[data-admin-panel-tab]'));
        const tabPanels = Array.from(dashboard.querySelectorAll('[data-admin-panel]'));

        tabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const selected = button.dataset.adminPanelTab;

                tabButtons.forEach(function(item) {
                    item.classList.toggle('is-active', item === button);
                });

                tabPanels.forEach(function(panel) {
                    panel.classList.toggle('d-none', panel.dataset.adminPanel !== selected);
                });
            });
        });

        const profileToggle = dashboard.querySelector('[data-profile-toggle]');
        const profileBody = dashboard.querySelector('[data-profile-body]');

        if (profileToggle && profileBody) {
            profileToggle.addEventListener('click', function() {
                profileBody.classList.toggle('is-collapsed');
                profileToggle.classList.toggle('is-active');
            });
        }

        const noteList = dashboard.querySelector('[data-note-list]');
        const noteInput = dashboard.querySelector('[data-note-input]');
        const noteAddButtons = Array.from(dashboard.querySelectorAll('[data-note-add]'));
        const notePresetButtons = Array.from(dashboard.querySelectorAll('[data-note-preset]'));
        const noteStorageKey = 'portal-dashboard-notes:' + portalKey;

        const readNotes = function() {
            try {
                const stored = window.localStorage.getItem(noteStorageKey);

                if (stored) {
                    return JSON.parse(stored);
                }
            } catch (error) {
                console.warn('Gagal membaca catatan dashboard.', error);
            }

            if (!noteList) {
                return [];
            }

            return Array.from(noteList.querySelectorAll('[data-note-item]')).map(function(item) {
                const textNode = item.querySelector('span:last-child');

                return {
                    text: (textNode ? textNode.textContent : item.textContent).trim(),
                    done: item.classList.contains('is-complete'),
                };
            });
        };

        let notes = readNotes();

        const persistNotes = function() {
            try {
                window.localStorage.setItem(noteStorageKey, JSON.stringify(notes));
            } catch (error) {
                console.warn('Gagal menyimpan catatan dashboard.', error);
            }
        };

        const renderNotes = function() {
            if (!noteList) {
                return;
            }

            if (notes.length === 0) {
                noteList.innerHTML = '<li class="portal-task-list__empty">Belum ada catatan. Tambahkan catatan baru dari tombol plus.</li>';

                return;
            }

            noteList.innerHTML = notes.map(function(note, index) {
                return '' +
                    '<li data-note-item data-search-item data-note-index="' + index + '" class="' + (note.done ? 'is-complete' : '') + '">' +
                    '<span class="portal-task-list__check"></span>' +
                    '<span>' + note.text.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' +
                    '</li>';
            }).join('');
        };

        const addNote = function(text) {
            const value = text.trim();

            if (value === '') {
                return;
            }

            notes.unshift({
                text: value,
                done: false,
            });
            persistNotes();
            renderNotes();
            applySearch();

            if (noteInput) {
                noteInput.value = '';
                noteInput.focus();
            }
        };

        noteAddButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                if (noteInput && noteInput.value.trim() !== '') {
                    addNote(noteInput.value);

                    return;
                }

                if (noteInput) {
                    noteInput.focus();
                }
            });
        });

        if (noteInput) {
            noteInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addNote(noteInput.value);
                }
            });
        }

        if (noteList) {
            noteList.addEventListener('click', function(event) {
                const item = event.target.closest('[data-note-item]');

                if (!item) {
                    return;
                }

                const index = Number(item.dataset.noteIndex);

                if (Number.isNaN(index) || !notes[index]) {
                    return;
                }

                notes[index].done = !notes[index].done;
                persistNotes();
                renderNotes();
                applySearch();
            });
        }

        notePresetButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const preset = button.dataset.notePreset || '';

                if (noteInput) {
                    noteInput.value = preset;
                    noteInput.focus();
                }
            });
        });

        renderNotes();

        const escapeHtml = function(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const statusOptions = function() {
            return attendanceStatuses.map(function(status) {
                const label = status.charAt(0).toUpperCase() + status.slice(1);

                return '<option value="' + escapeHtml(status) + '">' + escapeHtml(label) + '</option>';
            }).join('');
        };

        const scheduleOptionLabel = function(schedule) {
            return [schedule.time, schedule.subject, schedule.class_name].filter(Boolean).join(' | ');
        };

        const renderScheduleOptions = function(select, schedules, preferredId) {
            if (!select) {
                return;
            }

            if (!schedules || schedules.length === 0) {
                select.innerHTML = '<option value="">Belum ada jadwal</option>';
                return;
            }

            select.innerHTML = schedules.map(function(schedule) {
                return '<option value="' + escapeHtml(schedule.id) + '" data-class-id="' + escapeHtml(schedule.school_class_id) + '">' +
                    escapeHtml(scheduleOptionLabel(schedule)) +
                    '</option>';
            }).join('');

            const selectedExists = schedules.some(function(schedule) {
                return String(schedule.id) === String(preferredId || '');
            });

            select.value = selectedExists ? String(preferredId) : String(schedules[0].id);
        };

        const renderScheduleCards = function(form, schedules, selectedId) {
            const cards = form.querySelector('[data-assignment-cards]');

            if (!cards) {
                return;
            }

            if (!schedules || schedules.length === 0) {
                cards.innerHTML = '<div class="portal-assignment-empty">Belum ada jadwal mengajar pada tanggal ini.</div>';
                return;
            }

            cards.innerHTML = schedules.map(function(schedule) {
                const isActive = String(schedule.id) === String(selectedId || '');
                const status = schedule.status || {};

                return '' +
                    '<button class="portal-assignment-card' + (isActive ? ' is-active' : '') + '" type="button" data-assignment-card="' + escapeHtml(schedule.id) + '">' +
                    '<span class="portal-assignment-card__time">' + escapeHtml(schedule.time) + '</span>' +
                    '<span class="portal-assignment-card__main">' +
                    '<strong>' + escapeHtml(schedule.subject) + '</strong>' +
                    '<small>' + escapeHtml(schedule.class_name) + ' | ' + escapeHtml(schedule.students_count || 0) + ' siswa</small>' +
                    '</span>' +
                    '<span class="portal-assignment-card__meta">' +
                    '<small>' + escapeHtml(schedule.day_name || '') + '</small>' +
                    '<b>' + escapeHtml(schedule.room || '-') + '</b>' +
                    '</span>' +
                    '<span class="portal-assignment-card__status is-' + escapeHtml(status.tone || 'neutral') + '">' + escapeHtml(status.label || 'Terjadwal') + '</span>' +
                    '</button>';
            }).join('');
        };

        const fetchSchedulesForDate = async function(date) {
            if (!date || portalKey !== 'guru-mapel') {
                return [];
            }

            try {
                const response = await fetch('/guru-mapel/absensi-siswa/jadwal?' + new URLSearchParams({date}).toString(), {
                    headers: {
                        'Accept': 'application/json'
                    },
                });
                const payload = await response.json();

                if (!response.ok) {
                    return [];
                }

                return Array.isArray(payload?.data?.schedules) ? payload.data.schedules : [];
            } catch (error) {
                return [];
            }
        };

        const renderRoster = function(form, students, classId) {
            const roster = form.querySelector('[data-attendance-students]');
            const summary = form.querySelector('[data-roster-summary]');
            const filteredStudents = students.filter(function(student) {
                return String(student.school_class_id || '') === String(classId || '');
            });

            if (!roster) {
                return;
            }

            if (!classId) {
                roster.innerHTML = '<div class="portal-roster-empty">Pilih data terlebih dahulu.</div>';
                if (summary) {
                    summary.textContent = 'Pilih data untuk menampilkan siswa.';
                }

                return;
            }

            if (filteredStudents.length === 0) {
                roster.innerHTML = '<div class="portal-roster-empty">Belum ada siswa pada pilihan ini.</div>';
                if (summary) {
                    summary.textContent = 'Belum ada siswa pada pilihan ini.';
                }

                return;
            }

            if (summary) {
                summary.textContent = filteredStudents.length + ' siswa tampil pada pilihan ini.';
            }

            roster.innerHTML = filteredStudents.map(function(student, index) {
                return '' +
                    '<article class="portal-roster-row" data-student-id="' + escapeHtml(student.id) + '" data-search-item>' +
                    '<span class="portal-roster-row__number">' + (index + 1) + '</span>' +
                    '<div class="portal-roster-row__identity">' +
                    '<strong>' + escapeHtml(student.name) + '</strong>' +
                    '<small>NIK ' + escapeHtml(student.nik || '-') + ' | ' + escapeHtml(student.class_name || '-') + '</small>' +
                    '</div>' +
                    '<select class="form-select" data-student-status aria-label="Status ' + escapeHtml(student.name) + '">' + statusOptions() + '</select>' +
                    '<input class="form-control" type="text" data-student-notes placeholder="Catatan">' +
                    '</article>';
            }).join('');
        };

        const checkAttendanceStatus = async function(form, endpoint, date) {
            if (!date) {
                return true;
            }

            try {
                const response = await fetch(endpoint + '?date=' + encodeURIComponent(date), {
                    headers: {
                        'Accept': 'application/json'
                    },
                });
                const payload = await response.json();
                const allowed = Boolean(payload?.data?.allowed);
                const message = payload?.data?.message || '';
                const rows = Array.from(form.querySelectorAll('[data-student-id]'));
                const submitButton = form.querySelector('[type="submit"]');

                rows.forEach(function(row) {
                    row.querySelectorAll('select, input').forEach(function(field) {
                        field.disabled = !allowed;
                    });
                });

                form.querySelectorAll('[data-mark-status]').forEach(function(button) {
                    button.disabled = !allowed;
                });

                if (submitButton) {
                    submitButton.disabled = !allowed;
                }

                if (!allowed) {
                    setFormFeedback(form, message || 'Absensi tidak dapat diisi pada tanggal ini.', true);
                }

                return allowed;
            } catch (error) {
                return true;
            }
        };

        const setFormFeedback = function(form, message, isError) {
            const feedback = form.querySelector('[data-attendance-feedback]');

            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.classList.toggle('is-error', Boolean(isError));
            feedback.classList.remove('d-none');
        };

        const clearFormFeedback = function(form) {
            const feedback = form.querySelector('[data-attendance-feedback]');

            if (!feedback) {
                return;
            }

            feedback.textContent = '';
            feedback.classList.remove('is-error');
            feedback.classList.add('d-none');
        };

        const applyAttendanceRecords = function(form, records) {
            const recordsByStudent = new Map();

            (records || []).forEach(function(record) {
                if (record?.student_id) {
                    recordsByStudent.set(String(record.student_id), record);
                }
            });

            form.querySelectorAll('[data-student-id]').forEach(function(row) {
                const record = recordsByStudent.get(String(row.dataset.studentId));
                const statusField = row.querySelector('[data-student-status]');
                const notesField = row.querySelector('[data-student-notes]');

                if (statusField) {
                    statusField.value = record?.status || 'hadir';
                }

                if (notesField) {
                    notesField.value = record?.notes || '';
                }
            });
        };

        const fetchAttendanceRecords = async function(form, endpoint, params) {
            const query = new URLSearchParams(params);

            if (Array.from(query.values()).some(function(value) {
                    return value === '';
                })) {
                applyAttendanceRecords(form, []);

                return [];
            }

            try {
                const response = await fetch(endpoint + '?' + query.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    },
                });
                const payload = await response.json();

                if (!response.ok) {
                    return [];
                }

                const records = Array.isArray(payload?.data) ? payload.data : [];

                applyAttendanceRecords(form, records);

                return records;
            } catch (error) {
                return [];
            }
        };

        const submitAttendance = async function(form, endpoint, baseFields) {
            const rows = Array.from(form.querySelectorAll('[data-student-id]'));

            if (rows.length === 0) {
                setFormFeedback(form, 'Belum ada siswa yang bisa disimpan.', true);

                return;
            }

            const formData = new FormData();

            Object.entries(baseFields).forEach(function([key, value]) {
                formData.append(key, value);
            });

            rows.forEach(function(row, index) {
                formData.append('attendances[' + index + '][student_id]', row.dataset.studentId);
                formData.append('attendances[' + index + '][status]', row.querySelector('[data-student-status]')?.value || 'hadir');
                formData.append('attendances[' + index + '][notes]', row.querySelector('[data-student-notes]')?.value || '');
            });

            const submitButton = form.querySelector('[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const payload = await response.json();

                if (!response.ok) {
                    const messages = payload?.errors ?
                        Object.values(payload.errors).flat().join(' ') :
                        payload?.message || 'Absensi gagal disimpan.';

                    setFormFeedback(form, messages, true);

                    return;
                }

                setFormFeedback(form, payload?.message || 'Absensi berhasil disimpan.', false);
                applyAttendanceRecords(form, Array.isArray(payload?.data) ? payload.data : []);
            } catch (error) {
                setFormFeedback(form, 'Koneksi bermasalah saat menyimpan absensi.', true);
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        };

        dashboard.querySelectorAll('[data-subject-attendance-form]').forEach(function(form) {
            const assignmentSelect = form.querySelector('[data-assignment-select]');
            const dateInput = form.querySelector('[data-attendance-date]');
            let renderSequence = 0;

            const render = async function(options = {}) {
                const sequence = ++renderSequence;
                const shouldRefreshSchedules = Boolean(options.refreshSchedules);
                const date = dateInput?.value || '';
                const previousAssignmentId = assignmentSelect?.value || '';

                if (shouldRefreshSchedules) {
                    scheduleRows = await fetchSchedulesForDate(date);

                    if (sequence !== renderSequence) {
                        return;
                    }

                    renderScheduleOptions(assignmentSelect, scheduleRows, previousAssignmentId);
                }

                const selectedOption = assignmentSelect?.selectedOptions[0];
                const assignmentId = assignmentSelect?.value || '';

                renderRoster(form, teacherStudents, selectedOption?.dataset.classId || '');
                renderScheduleCards(form, scheduleRows, assignmentId);
                clearFormFeedback(form);
                await checkAttendanceStatus(form, '/guru-mapel/status-absensi', date);

                if (sequence !== renderSequence) {
                    return;
                }

                await fetchAttendanceRecords(form, '/guru-mapel/rekap-absensi-mapel', {
                    teaching_assignment_id: assignmentId,
                    attendance_date: date,
                });
            };

            renderScheduleOptions(assignmentSelect, scheduleRows, assignmentSelect?.value || '');
            assignmentSelect?.addEventListener('change', function() {
                render({
                    refreshSchedules: false
                });
            });
            dateInput?.addEventListener('change', function() {
                render({
                    refreshSchedules: true
                });
            });
            render();

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitAttendance(form, '/guru-mapel/absensi-mapel', {
                    teaching_assignment_id: assignmentSelect?.value || '',
                    attendance_date: dateInput?.value || '',
                });
            });
        });

        dashboard.querySelectorAll('[data-class-attendance-form]').forEach(function(form) {
            const classSelect = form.querySelector('[data-class-select]');
            const dateInput = form.querySelector('[data-attendance-date]');
            let renderSequence = 0;

            const render = async function() {
                const sequence = ++renderSequence;
                const classId = classSelect?.value || '';
                const date = dateInput?.value || '';

                renderRoster(form, homeroomStudents, classId);
                clearFormFeedback(form);
                await checkAttendanceStatus(form, portalPrefix + '/status-absensi', date);

                if (sequence !== renderSequence) {
                    return;
                }

                await fetchAttendanceRecords(form, portalPrefix + '/rekap-absensi-kelas', {
                    school_class_id: classId,
                    attendance_date: date,
                });
            };

            classSelect?.addEventListener('change', render);
            dateInput?.addEventListener('change', render);
            render();

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitAttendance(form, portalPrefix + '/absensi-kelas', {
                    school_class_id: classSelect?.value || '',
                    attendance_date: dateInput?.value || '',
                });
            });
        });

        dashboard.addEventListener('click', function(event) {
            const scheduleCard = event.target.closest('[data-assignment-card]');

            if (scheduleCard) {
                const form = scheduleCard.closest('form');
                const select = form?.querySelector('[data-assignment-select]');

                if (select) {
                    select.value = scheduleCard.dataset.assignmentCard || '';
                    select.dispatchEvent(new Event('change'));
                }

                return;
            }

            const button = event.target.closest('[data-mark-status]');

            if (!button) {
                return;
            }

            const form = button.closest('form');
            const status = button.dataset.markStatus || 'hadir';

            form?.querySelectorAll('[data-student-status]').forEach(function(select) {
                select.value = status;
            });
        });

        const calendarRoot = dashboard.querySelector('[data-calendar]');

        if (calendarRoot) {
            const label = calendarRoot.querySelector('[data-calendar-label]');
            const body = calendarRoot.querySelector('[data-calendar-body]');
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const today = new Date();
            const currentMonth = calendarRoot.dataset.currentMonth || new Date().toISOString().slice(0, 10);
            let activeMonth = new Date(currentMonth);

            const renderCalendar = function() {
                const firstDay = new Date(activeMonth.getFullYear(), activeMonth.getMonth(), 1);
                const startOffset = (firstDay.getDay() + 6) % 7;
                const cursor = new Date(firstDay);
                cursor.setDate(cursor.getDate() - startOffset);
                const cells = [];

                for (let index = 0; index < 42; index += 1) {
                    const isCurrentMonth = cursor.getMonth() === activeMonth.getMonth();
                    const isToday = cursor.toDateString() === today.toDateString();
                    cells.push('' +
                        '<button type="button" class="portal-calendar-day ' + (isCurrentMonth ? '' : 'is-outside ') + (isToday ? 'is-today' : '') + '" data-calendar-day="' + cursor.toISOString().slice(0, 10) + '">' +
                        cursor.getDate() +
                        '</button>');
                    cursor.setDate(cursor.getDate() + 1);
                }

                if (label) {
                    label.textContent = monthNames[activeMonth.getMonth()] + ' ' + activeMonth.getFullYear();
                }

                if (body) {
                    const rows = [];

                    for (let row = 0; row < cells.length; row += 7) {
                        rows.push('<div class="portal-calendar-grid">' + cells.slice(row, row + 7).join('') + '</div>');
                    }

                    body.innerHTML = rows.join('');
                }
            };

            calendarRoot.querySelectorAll('[data-calendar-nav]').forEach(function(button) {
                button.addEventListener('click', function() {
                    const step = Number(button.dataset.calendarNav || '0');
                    activeMonth = new Date(activeMonth.getFullYear(), activeMonth.getMonth() + step, 1);
                    renderCalendar();
                });
            });

            calendarRoot.addEventListener('click', function(event) {
                const dayButton = event.target.closest('[data-calendar-day]');

                if (!dayButton || !noteInput) {
                    return;
                }

                noteInput.value = 'Agenda untuk ' + dayButton.dataset.calendarDay + '.';
                noteInput.focus();
            });

            renderCalendar();
        }
    });
</script>
@endpush
