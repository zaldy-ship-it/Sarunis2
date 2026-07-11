@php
    $scheduleCount = count($scheduleRows);
    $classCount = collect($scheduleRows)->pluck('class_name')->filter()->unique()->count();
    $roomCount = collect($scheduleRows)->pluck('room')->filter(fn ($room) => $room !== '-')->unique()->count();
    $actionUrls = array_merge([
        'attendance' => url('/guru-mapel/absensi-siswa/tambah'),
        'students' => null,
        'recap' => url('/guru-mapel/rekap-absensi'),
    ], $scheduleActionUrls ?? []);
@endphp

<section class="portal-panel portal-schedule-card portal-teacher-schedule-board" id="jadwal-mengajar" data-dashboard-section data-section-label="Jadwal Mengajar">
    <div class="portal-section-heading portal-teacher-schedule-board__head">
        <div>
            <h2>Jadwal Mengajar Hari Ini</h2>
            <p>Urutan sesi mengajar yang perlu dipantau dari awal sampai akhir hari.</p>
        </div>
        <div class="portal-teacher-schedule-board__stats" aria-label="Ringkasan jadwal">
            <span><strong>{{ $scheduleCount }}</strong> sesi</span>
            <span><strong>{{ $classCount }}</strong> kelas</span>
            <span><strong>{{ $roomCount }}</strong> ruang</span>
        </div>
    </div>

    <div class="portal-teacher-schedule-actions">
        <a class="btn btn-primary btn-sm" href="{{ $actionUrls['attendance'] }}">Isi Absensi</a>
        @if (!empty($actionUrls['students']))
        <a class="btn btn-outline-primary btn-sm" href="{{ $actionUrls['students'] }}">Lihat Siswa</a>
        @endif
        <a class="btn btn-outline-primary btn-sm" href="{{ $actionUrls['recap'] }}">Rekap Absensi</a>
    </div>

    <div class="portal-teacher-schedule-list">
        @forelse ($scheduleRows as $row)
            <article class="portal-teacher-schedule-card" data-search-item>
                <div class="portal-teacher-schedule-card__time">
                    <span>Sesi {{ $loop->iteration }}</span>
                    <strong>{{ $row['time'] }}</strong>
                </div>
                <div class="portal-teacher-schedule-card__body">
                    <div class="portal-teacher-schedule-card__top">
                        <div>
                            <h3>{{ $row['subject'] }}</h3>
                            <p>{{ $row['class_name'] }}</p>
                        </div>
                    </div>
                    <div class="portal-teacher-schedule-card__meta">
                        <span>{{ $row['room'] }}</span>
                        <span>{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                        @if ($row['is_filled'])
                            <span class="portal-badge is-success">Sudah Diisi</span>
                        @else
                            <span class="portal-badge is-warning">Belum Diisi</span>
                        @endif
                    </div>
                </div>
                <div class="portal-teacher-schedule-card__actions">
                    <span class="portal-badge is-{{ $row['status']['tone'] }}">{{ $row['status']['label'] }}</span>
                    <a class="portal-teacher-schedule-card__link" href="{{ $actionUrls['attendance'] }}">Absensi</a>
                </div>
            </article>
        @empty
            <div class="portal-teacher-schedule-empty">
                <strong>Belum ada jadwal hari ini.</strong>
                <span>Gunakan waktu kosong untuk meninjau data siswa atau rekap absensi terakhir.</span>
            </div>
        @endforelse
    </div>
</section>
