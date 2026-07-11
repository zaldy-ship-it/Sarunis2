<section class="portal-panel portal-workbench-card portal-teacher-attendance-card" id="absensi-siswa" data-dashboard-section data-section-label="Isi Absensi Mapel">
    <div class="portal-section-heading portal-teacher-attendance-card__head">
        <div>
            <h2>Panel Absensi</h2>
            <p>Pilih sesi mengajar, cek daftar siswa, lalu simpan status kehadiran.</p>
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
