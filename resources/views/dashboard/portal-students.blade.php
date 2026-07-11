@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
<div class="portal-dashboard-shell portal-directory-shell" data-portal-students-page>
    @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => false])

    <main class="portal-dashboard-main portal-directory-main">
        <div class="portal-directory-stack">
            <section class="portal-panel portal-directory-banner">
                <div class="portal-directory-banner__bar"></div>
                <div class="portal-directory-banner__copy">
                    <h1>{{ $directoryTitle }}</h1>
                    <p>{{ $directorySubtitle }}</p>
                </div>
                <div class="portal-directory-banner__count">{{ $totalStudents }} siswa</div>
            </section>

            <section class="portal-student-summary-grid" aria-label="Ringkasan data siswa">
                @foreach ($studentSummary as $summary)
                <article class="portal-student-summary-card">
                    <span>{{ $summary['label'] }}</span>
                    <strong>{{ $summary['value'] }}</strong>
                </article>
                @endforeach
            </section>

            <section class="portal-directory-toolbar">
                <label class="portal-directory-search" for="portal-student-search">
                    <span class="portal-directory-search__icon">
                        @include('dashboard.partials.icon', ['name' => 'search'])
                    </span>
                    <input id="portal-student-search" type="search" placeholder="Pencarian..." data-student-search>
                </label>

                <label class="portal-directory-filter" for="portal-student-class-filter">
                    <span class="portal-directory-filter__icon">
                        @include('dashboard.partials.icon', ['name' => 'filter'])
                    </span>
                    <select id="portal-student-class-filter" data-class-filter>
                        <option value="">Semua kelas</option>
                        @foreach ($classOptions as $className)
                        <option value="{{ $className }}">{{ $className }}</option>
                        @endforeach
                    </select>
                    <span class="portal-directory-filter__arrow">
                        @include('dashboard.partials.icon', ['name' => 'chevron-down'])
                    </span>
                </label>
            </section>

            <div class="portal-directory-feedback d-none" data-student-feedback></div>

            @if ($portalKey === 'guru-walikelas')
            <section class="portal-tabs-container" style="margin-bottom: 24px;">
                <div style="display: flex; gap: 12px; border-bottom: 2px solid #eef8fd; padding-bottom: 12px;">
                    <button class="portal-tab-button is-active" data-tab="taught-students" style="background: none; border: none; cursor: pointer; padding: 8px 16px; font-weight: 600; color: #0066cc; border-bottom: 3px solid #0066cc; margin-bottom: -14px;">
                        Siswa yang Diajar
                    </button>
                    <button class="portal-tab-button" data-tab="homeroom-students" style="background: none; border: none; cursor: pointer; padding: 8px 16px; font-weight: 600; color: #5c7183;">
                        Siswa Perwalian
                    </button>
                </div>
            </section>

            <!-- Taught Students Section -->
            <section class="portal-teacher-student-groups" id="taught-students" data-tab-content="taught-students" aria-label="Siswa yang diajar">
                @if (!empty($taughtStudentGroups) && count($taughtStudentGroups) > 0)
                @foreach ($taughtStudentGroups as $group)
                <article class="portal-panel portal-teacher-student-group" data-student-class-section>
                    <div class="portal-teacher-student-group__head">
                        <div>
                            <span style="color: #0066cc; font-weight: 600;">Diajar</span>
                            <h2>{{ $group['class_name'] }}</h2>
                        </div>
                        <div class="portal-teacher-student-group__meta">
                            <strong>{{ $group['total'] }} siswa</strong>
                            <small>{{ $group['male'] }} Laki-laki | {{ $group['female'] }} Perempuan</small>
                        </div>
                        <a class="btn btn-primary btn-sm" href="{{ url('/guru-mapel/absensi-siswa/tambah') }}">
                            Isi Absensi Mapel
                        </a>
                    </div>

                    <div class="portal-teacher-student-list">
                        @foreach ($group['students'] as $student)
                        <article class="portal-teacher-student-card" data-student-row data-class-name="{{ $student['class_name'] }}" data-search-text="{{ $student['search_text'] }}">
                            <div class="portal-teacher-student-card__avatar">{{ $student['initials'] }}</div>
                            <div class="portal-teacher-student-card__body">
                                <div class="portal-teacher-student-card__top">
                                    <div>
                                        <h3>{{ $student['name'] }}</h3>
                                        <p>{{ $student['gender'] }} | Lahir {{ $student['birth_date'] }}</p>
                                    </div>
                                    <span>{{ $student['nisn'] }}</span>
                                </div>
                                <dl>
                                    <div>
                                        <dt>NIK</dt>
                                        <dd>{{ $student['nik'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Kontak</dt>
                                        <dd>
                                            @if ($student['phone'] !== '-')
                                            <a href="tel:{{ $student['phone'] }}">{{ $student['phone'] }}</a>
                                            @else
                                            -
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Alamat</dt>
                                        <dd>{{ $student['address'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </article>
                @endforeach
                @else
                <article class="portal-panel portal-directory-empty">
                    <h2>Belum ada siswa yang diajar</h2>
                    <p>Data siswa akan tampil setelah jadwal mengajar Anda ditetapkan.</p>
                </article>
                @endif
            </section>

            <!-- Homeroom Students Section -->
            <section class="portal-teacher-student-groups" id="homeroom-students" data-tab-content="homeroom-students" style="display: none;" aria-label="Siswa perwalian">
                @if (!empty($homeroomStudentGroups) && count($homeroomStudentGroups) > 0)
                @foreach ($homeroomStudentGroups as $group)
                <article class="portal-panel portal-teacher-student-group" data-student-class-section>
                    <div class="portal-teacher-student-group__head">
                        <div>
                            <span style="color: #d9534f; font-weight: 600;">Perwalian</span>
                            <h2>{{ $group['class_name'] }}</h2>
                        </div>
                        <div class="portal-teacher-student-group__meta">
                            <strong>{{ $group['total'] }} siswa</strong>
                            <small>{{ $group['male'] }} Laki-laki | {{ $group['female'] }} Perempuan</small>
                        </div>
                        <a class="btn btn-primary btn-sm" href="{{ url('/walikelas/dashboard#absensi-kelas') }}">
                            Absensi Kelas
                        </a>
                    </div>

                    <div class="portal-teacher-student-list">
                        @foreach ($group['students'] as $student)
                        <article class="portal-teacher-student-card" data-student-row data-class-name="{{ $student['class_name'] }}" data-search-text="{{ $student['search_text'] }}">
                            <div class="portal-teacher-student-card__avatar">{{ $student['initials'] }}</div>
                            <div class="portal-teacher-student-card__body">
                                <div class="portal-teacher-student-card__top">
                                    <div>
                                        <h3>{{ $student['name'] }}</h3>
                                        <p>{{ $student['gender'] }} | Lahir {{ $student['birth_date'] }}</p>
                                    </div>
                                    <span>{{ $student['nisn'] }}</span>
                                </div>
                                <dl>
                                    <div>
                                        <dt>NIK</dt>
                                        <dd>{{ $student['nik'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Kontak</dt>
                                        <dd>
                                            @if ($student['phone'] !== '-')
                                            <a href="tel:{{ $student['phone'] }}">{{ $student['phone'] }}</a>
                                            @else
                                            -
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Alamat</dt>
                                        <dd>{{ $student['address'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </article>
                @endforeach
                @else
                <article class="portal-panel portal-directory-empty">
                    <h2>Belum ada siswa perwalian</h2>
                    <p>Data siswa akan tampil setelah Anda ditugaskan sebagai wali kelas.</p>
                </article>
                @endif
            </section>
            @elseif (in_array($portalKey, ['guru-mapel', 'walikelas'], true))
            <section class="portal-teacher-student-groups" aria-label="Daftar siswa per kelas">
                @forelse ($studentGroups as $group)
                <article class="portal-panel portal-teacher-student-group" data-student-class-section>
                    <div class="portal-teacher-student-group__head">
                        <div>
                            <span>{{ implode(' / ', $group['contexts']) }}</span>
                            <h2>{{ $group['class_name'] }}</h2>
                        </div>
                        <div class="portal-teacher-student-group__meta">
                            <strong>{{ $group['total'] }} siswa</strong>
                            <small>{{ $group['male'] }} Laki-laki | {{ $group['female'] }} Perempuan</small>
                        </div>
                        <a class="btn btn-primary btn-sm" href="{{ $portalKey === 'guru-mapel' ? url('/guru-mapel/absensi-siswa/tambah') : url('/walikelas/dashboard#absensi-kelas') }}">
                            {{ $portalKey === 'guru-mapel' ? 'Isi Absensi' : 'Absensi Kelas' }}
                        </a>
                    </div>

                    <div class="portal-teacher-student-list">
                        @foreach ($group['students'] as $student)
                        <article class="portal-teacher-student-card" data-student-row data-class-name="{{ $student['class_name'] }}" data-search-text="{{ $student['search_text'] }}">
                            <div class="portal-teacher-student-card__avatar">{{ $student['initials'] }}</div>
                            <div class="portal-teacher-student-card__body">
                                <div class="portal-teacher-student-card__top">
                                    <div>
                                        <h3>{{ $student['name'] }}</h3>
                                        <p>{{ $student['gender'] }} | Lahir {{ $student['birth_date'] }}</p>
                                    </div>
                                    <span>{{ $student['nisn'] }}</span>
                                </div>
                                <dl>
                                    <div>
                                        <dt>NIK</dt>
                                        <dd>{{ $student['nik'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Kontak</dt>
                                        <dd>
                                            @if ($student['phone'] !== '-')
                                            <a href="tel:{{ $student['phone'] }}">{{ $student['phone'] }}</a>
                                            @else
                                            -
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Alamat</dt>
                                        <dd>{{ $student['address'] }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </article>
                @empty
                <section class="portal-panel portal-directory-empty">
                    <h2>Belum ada siswa</h2>
                    <p>Data siswa akan tampil setelah jadwal mengajar terhubung dengan kelas.</p>
                </section>
                @endforelse
            </section>
            @else
            <section class="portal-panel portal-table-card portal-students-table-card">
                <div class="table-responsive">
                    <table class="table portal-table portal-directory-table portal-students-table mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th>NISN</th>
                                <th>Kelas</th>
                                <th>Gender</th>
                                <th>Tanggal Lahir</th>
                                <th>Kontak</th>
                                <th>Konteks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($studentRows as $index => $student)
                            <tr data-student-row data-class-name="{{ $student['class_name'] }}" data-search-text="{{ $student['search_text'] }}">
                                <td data-label="No">{{ $index + 1 }}</td>
                                <td data-label="Nama">
                                    <div class="portal-directory-name">{{ $student['name'] }}</div>
                                    <div class="portal-directory-meta">{{ $student['address'] }}</div>
                                </td>
                                <td data-label="NIK">{{ $student['nik'] }}</td>
                                <td data-label="NISN">{{ $student['nisn'] }}</td>
                                <td data-label="Kelas">{{ $student['class_name'] }}</td>
                                <td data-label="Gender">{{ $student['gender'] }}</td>
                                <td data-label="Tanggal lahir">{{ $student['birth_date'] }}</td>
                                <td data-label="Kontak">{{ $student['phone'] }}</td>
                                <td data-label="Konteks"><span class="portal-directory-status">{{ $student['context'] }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada siswa untuk ditampilkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

            @if ($totalStudents > 0)
            <section class="portal-panel portal-directory-empty d-none" data-student-empty>
                <h2>Tidak ada hasil</h2>
                <p>Ubah kata kunci atau filter kelas untuk melihat data siswa lain.</p>
            </section>
            @endif
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const page = document.querySelector('[data-portal-students-page]');

        if (!page) {
            return;
        }

        const searchInput = page.querySelector('[data-student-search]');
        const classFilter = page.querySelector('[data-class-filter]');
        const feedback = page.querySelector('[data-student-feedback]');
        const emptyState = page.querySelector('[data-student-empty]');
        const rows = Array.from(page.querySelectorAll('[data-student-row]'));

        const applyFilters = function() {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const className = classFilter?.value || '';
            let visibleCount = 0;

            rows.forEach(function(row) {
                const matchesQuery = query === '' || (row.dataset.searchText || '').includes(query);
                const matchesClass = className === '' || row.dataset.className === className;
                const visible = matchesQuery && matchesClass;

                row.classList.toggle('d-none', !visible);

                if (visible) {
                    visibleCount += 1;
                }
            });

            if (feedback) {
                feedback.textContent = query === '' && className === '' ?
                    '' :
                    'Menampilkan ' + visibleCount + ' siswa sesuai filter.';
                feedback.classList.toggle('d-none', query === '' && className === '');
            }

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }

            page.querySelectorAll('[data-student-class-section]').forEach(function(section) {
                const hasVisibleStudent = Array.from(section.querySelectorAll('[data-student-row]')).some(function(row) {
                    return !row.classList.contains('d-none');
                });

                section.classList.toggle('d-none', !hasVisibleStudent);
            });
        };

        searchInput?.addEventListener('input', applyFilters);
        classFilter?.addEventListener('change', applyFilters);

        // Tab switching for guru-walikelas
        const tabButtons = page.querySelectorAll('.portal-tab-button');
        const tabContents = page.querySelectorAll('[data-tab-content]');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.dataset.tab;

                // Hide all tabs
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });

                // Remove active class from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('is-active');
                    btn.style.color = '#5c7183';
                    btn.style.borderBottom = '3px solid transparent';
                });

                // Show selected tab and activate button
                document.getElementById(tabName)?.style.display = '';
                this.classList.add('is-active');
                this.style.color = '#0066cc';
                this.style.borderBottom = '3px solid #0066cc';
            });
        });
    });
</script>
@endpush
