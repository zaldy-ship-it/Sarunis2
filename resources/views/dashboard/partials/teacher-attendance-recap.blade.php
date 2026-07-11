@php
    $recapSectionId = $recapSectionId ?? 'rekap-absensi';
    $recapSectionLabel = $recapSectionLabel ?? 'Rekap Absensi Mapel';
    $recapTitle = $recapTitle ?? 'Ringkasan Absensi Mapel';
    $recapDescription = $recapDescription ?? 'Data absensi dari kelas yang diajar sebagai guru mapel.';
    $recapExportBaseUrl = $recapExportBaseUrl ?? url('/guru-mapel/rekap-absensi');
    $recapFilters = $recapFilters ?? ($attendanceFilters ?? []);
    $exportQuery = request()->getQueryString();
    $withExportQuery = fn (string $url): string => $exportQuery ? $url . '?' . $exportQuery : $url;
    $recapExportXlsUrl = $recapExportXlsUrl ?? $recapExportBaseUrl . '/export/xls';
    $recapExportCsvUrl = $recapExportCsvUrl ?? $recapExportBaseUrl . '/export/csv';
    $recapExportPdfUrl = $recapExportPdfUrl ?? $recapExportBaseUrl . '/export/pdf';
    $recapPrintUrl = $recapPrintUrl ?? $recapExportBaseUrl . '/print';
    $showExportActions = $showExportActions ?? true;
    $showAttendanceSummary = $showAttendanceSummary ?? true;
    $showAttendanceMeetingTable = $showAttendanceMeetingTable ?? false;
    $showAttendanceRecapTable = $showAttendanceRecapTable ?? true;
    $showAttendanceDetailTable = $showAttendanceDetailTable ?? true;
    $attendanceDetailTitle = $attendanceDetailTitle ?? 'Detail Catatan Siswa';
    $attendanceMeetingRows = $attendanceMeetingRows ?? [];
@endphp

<section class="portal-panel portal-teacher-recap" id="{{ $recapSectionId }}" data-dashboard-section data-section-label="{{ $recapSectionLabel }}">
    <div class="portal-section-heading">
        <div>
            <h2>{{ $recapTitle }}</h2>
            <p>{{ $recapDescription }} Data terakhir {{ $attendanceSummary['latest_date'] }} dengan persentase hadir {{ $attendanceSummary['present_rate'] }}%.</p>
        </div>
        @if ($showExportActions)
            <div class="portal-report-actions">
                <a class="btn btn-outline-primary btn-sm" href="{{ $withExportQuery($recapExportXlsUrl) }}">Excel Mapel</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ $withExportQuery($recapExportCsvUrl) }}">CSV Mapel</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ $withExportQuery($recapExportPdfUrl) }}" target="_blank" rel="noopener">PDF Mapel</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ $withExportQuery($recapPrintUrl) }}" target="_blank" rel="noopener">Print</a>
            </div>
        @endif
    </div>

    <form class="portal-report-card portal-report-card--wide" method="GET" action="{{ url()->current() }}">
        <div class="portal-report-card__header">
            <h3>Filter Export Mapel</h3>
            <span>{{ count($attendanceDetailRows) }} detail</span>
        </div>
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label" for="{{ $recapSectionId }}-subject">Mapel</label>
                <select class="form-control" id="{{ $recapSectionId }}-subject" name="subject_id">
                    <option value="">Semua mapel</option>
                    @foreach (($teacherExportSubjects ?? []) as $subjectOption)
                        <option value="{{ $subjectOption['id'] }}" @selected((string) ($recapFilters['subject_id'] ?? '') === (string) $subjectOption['id'])>{{ $subjectOption['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="{{ $recapSectionId }}-class">Kelas Ajar</label>
                <select class="form-control" id="{{ $recapSectionId }}-class" name="school_class_id">
                    <option value="">Semua kelas ajar</option>
                    @foreach (($teacherExportClasses ?? []) as $classOption)
                        <option value="{{ $classOption['id'] }}" @selected((string) ($recapFilters['school_class_id'] ?? '') === (string) $classOption['id'])>{{ $classOption['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="{{ $recapSectionId }}-meeting">Pertemuan</label>
                <select class="form-control" id="{{ $recapSectionId }}-meeting" name="meeting">
                    <option value="">Semua pertemuan</option>
                    @for ($i = 1; $i <= 16; $i++)
                        <option value="Pertemuan {{ $i }}" @selected(($recapFilters['meeting'] ?? '') === "Pertemuan {$i}")>Pertemuan {{ $i }}</option>
                    @endfor
                    <option value="UTS" @selected(($recapFilters['meeting'] ?? '') === 'UTS')>UTS</option>
                    <option value="UAS" @selected(($recapFilters['meeting'] ?? '') === 'UAS')>UAS</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="{{ $recapSectionId }}-from">Dari</label>
                <input class="form-control" id="{{ $recapSectionId }}-from" type="date" name="date_from" value="{{ $recapFilters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="{{ $recapSectionId }}-to">Sampai</label>
                <input class="form-control" id="{{ $recapSectionId }}-to" type="date" name="date_to" value="{{ $recapFilters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm w-100" type="submit">Terapkan</button>
                <a class="btn btn-outline-primary btn-sm w-100 text-center" href="{{ url()->current() }}">Reset</a>
            </div>
        </div>
    </form>

    @if ($showAttendanceSummary)
        <div class="portal-teacher-recap__summary">
            <article>
                <span>Total Catatan</span>
                <strong>{{ $attendanceSummary['total'] }}</strong>
            </article>
            <article>
                <span>Hadir</span>
                <strong>{{ $attendanceSummary['hadir'] }}</strong>
            </article>
            <article>
                <span>Izin</span>
                <strong>{{ $attendanceSummary['izin'] }}</strong>
            </article>
            <article>
                <span>Sakit</span>
                <strong>{{ $attendanceSummary['sakit'] }}</strong>
            </article>
            <article>
                <span>Alpha</span>
                <strong>{{ $attendanceSummary['alpha'] }}</strong>
            </article>
        </div>
    @endif

    @if ($showAttendanceRecapTable)
        <div class="portal-report-card portal-report-card--wide">
        <div class="portal-report-card__header">
            <h3>Rekap Per Mapel dan Kelas</h3>
            <span>{{ count($attendanceRecapRows) }} jadwal</span>
        </div>

        <div class="table-responsive">
            <table class="table portal-table mb-0">
                <thead>
                    <tr>
                        <th>Mapel</th>
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
                    @forelse ($attendanceRecapRows as $row)
                        <tr data-search-item>
                            <td data-label="Mapel">{{ $row['subject'] }}</td>
                            <td data-label="Kelas">{{ $row['class_name'] }}</td>
                            <td data-label="Pertemuan">{{ $row['dates_count'] }}</td>
                            <td data-label="Hadir">{{ $row['hadir'] }}</td>
                            <td data-label="Izin">{{ $row['izin'] }}</td>
                            <td data-label="Sakit">{{ $row['sakit'] }}</td>
                            <td data-label="Alpha">{{ $row['alpha'] }}</td>
                            <td data-label="% Hadir"><span class="portal-badge is-primary">{{ $row['present_rate'] }}%</span></td>
                            <td data-label="Terakhir">{{ $row['latest_date'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada data absensi mapel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    @endif

    @if ($showAttendanceMeetingTable)
        <div class="portal-report-card portal-report-card--wide">
        <div class="portal-report-card__header">
            <h3>Daftar Per Pertemuan</h3>
            <span>{{ count($attendanceMeetingRows) }} pertemuan</span>
        </div>

        <div class="table-responsive">
            <table class="table portal-table portal-attendance-meeting-table mb-0">
                <thead>
                    <tr>
                        <th>Pertemuan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Mapel</th>
                        <th>Kelas</th>
                        <th>Siswa</th>
                        <th>Ringkasan</th>
                        <th>% Hadir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceMeetingRows as $row)
                        <tr data-search-item>
                            <td data-label="Pertemuan">
                                @if (str_starts_with($row['meeting_label'], 'Pertemuan'))
                                    <span class="portal-badge is-primary">{{ $row['meeting_label'] }}</span>
                                @else
                                    <span class="portal-badge is-warning">{{ $row['meeting_label'] }}</span>
                                @endif
                            </td>
                            <td data-label="Tanggal">{{ $row['date'] }}</td>
                            <td data-label="Jam">{{ $row['time'] }}</td>
                            <td data-label="Mapel">{{ $row['subject'] }}</td>
                            <td data-label="Kelas">{{ $row['class_name'] }}</td>
                            <td data-label="Siswa">{{ $row['student_count'] }}</td>
                            <td data-label="Ringkasan">
                                <div class="portal-attendance-status-summary">
                                    <span>H {{ $row['hadir'] }}</span>
                                    <span>I {{ $row['izin'] }}</span>
                                    <span>S {{ $row['sakit'] }}</span>
                                    <span>A {{ $row['alpha'] }}</span>
                                </div>
                            </td>
                            <td data-label="% Hadir"><span class="portal-badge is-primary">{{ $row['present_rate'] }}%</span></td>
                            <td data-label="Aksi">
                                <button
                                    class="btn btn-outline-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $row['key'] }}"
                                    aria-expanded="false"
                                    aria-controls="{{ $row['key'] }}"
                                >Detail</button>
                            </td>
                        </tr>
                        <tr class="portal-attendance-meeting-detail-row">
                            <td colspan="9">
                                <div class="collapse" id="{{ $row['key'] }}">
                                    <div class="portal-attendance-meeting-detail">
                                        <div class="portal-report-card__header">
                                            <h3>Daftar Siswa Diabsen</h3>
                                            <span>{{ count($row['details']) }} siswa</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table portal-table portal-attendance-detail-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Nama Siswa</th>
                                                        <th>NISN/NIK</th>
                                                        <th>Status</th>
                                                        <th>Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($row['details'] as $detail)
                                                        <tr>
                                                            <td data-label="Nama">{{ $detail['student'] }}</td>
                                                            <td data-label="NISN/NIK">{{ $detail['identifier'] }}</td>
                                                            <td data-label="Status">{{ $detail['status'] }}</td>
                                                            <td data-label="Catatan">{{ $detail['notes'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada pertemuan absensi yang dapat ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    @endif

    @if ($showAttendanceDetailTable)
        <div class="portal-report-card portal-report-card--wide">
        <div class="portal-report-card__header">
            <h3>{{ $attendanceDetailTitle }}</h3>
            <span>{{ count($attendanceDetailRows) }} baris</span>
        </div>

        <div class="table-responsive">
            <table class="table portal-table mb-0">
                <thead>
                    <tr>
                        <th>Pertemuan</th>
                        <th>Tanggal</th>
                        <th>Mapel</th>
                        <th>Kelas</th>
                        <th>Siswa</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceDetailRows as $row)
                        <tr data-search-item>
                            <td data-label="Pertemuan">
                                @if (str_starts_with($row['meeting_label'], 'Pertemuan'))
                                    <span class="portal-badge is-primary">{{ $row['meeting_label'] }}</span>
                                @else
                                    <span class="portal-badge is-warning">{{ $row['meeting_label'] }}</span>
                                @endif
                            </td>
                            <td data-label="Tanggal">{{ $row['date'] }}</td>
                            <td data-label="Mapel">{{ $row['subject'] }}</td>
                            <td data-label="Kelas">{{ $row['class_name'] }}</td>
                            <td data-label="Siswa">{{ $row['student'] }}</td>
                            <td data-label="Status">{{ $row['status'] }}</td>
                            <td data-label="Catatan">{{ $row['notes'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada detail absensi yang dapat ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    @endif
</section>
