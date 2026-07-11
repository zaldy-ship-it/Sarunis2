@php
    $roleLabels = [
        'admin' => 'Admin',
        'guru_mapel' => 'Guru Mapel',
        'walikelas' => 'Wali Kelas',
        'siswa' => 'Siswa',
    ];
    $userRoles = collect(auth()->user()?->roles ?? [])
        ->map(fn (string $role): string => $roleLabels[$role] ?? str_replace('_', ' ', $role))
        ->values();
    $sectionLabels = [
        'Menu' => 'Akademik',
        'Menu Utama' => 'Akademik',
        'Utama' => 'Akademik',
        'Admin' => 'Administrasi',
        'Lainnya' => 'Sistem',
        'Informasi' => 'Informasi',
        'Guru Mapel' => 'Akademik',
        'Wali Kelas' => 'Akademik',
        'Orang Tua' => 'Informasi',
        'Siswa' => 'Akademik',
    ];

    $flatMenuItems = [];
    foreach ($menuSections as $section) {
        foreach ($section['items'] as $item) {
            $flatMenuItems[] = $item;
        }
    }

    // Find Home
    $homeItem = collect($flatMenuItems)->first(fn($item) => $item['icon'] === 'home' || str_contains(strtolower($item['label']), 'beranda')) 
        ?? ($flatMenuItems[0] ?? null);

    // Find Attendance/Absen
    $attendanceItem = collect($flatMenuItems)->first(fn($item) => in_array($item['icon'], ['attendance', 'recap'], true) || str_contains(strtolower($item['label']), 'absen'))
        ?? ($flatMenuItems[1] ?? null);

    // Find Schedule/Calendar
    $scheduleItem = collect($flatMenuItems)->first(fn($item) => in_array($item['icon'], ['schedule', 'calendar'], true) || str_contains(strtolower($item['label']), 'jadwal') || str_contains(strtolower($item['label']), 'kalender'))
        ?? ($flatMenuItems[2] ?? null);
@endphp

<header class="portal-mobile-shellbar" data-mobile-shellbar>
    <div class="portal-mobile-shellbar__brand" style="margin-left: 0.5rem;">
        <strong>{{ config('app.name', 'Sarunis') }}</strong>
        <span>{{ $userRoles->implode(' + ') ?: 'Portal Sekolah' }}</span>
    </div>
</header>

<div class="portal-sidebar-backdrop" data-sidebar-close></div>

<aside class="portal-dashboard-sidebar" aria-label="Navigasi dashboard">
    <div class="portal-dashboard-sidebar__rail"></div>

    <div class="portal-dashboard-sidebar__panel">
        <div class="portal-dashboard-sidebar__tools">
            <button class="portal-sidebar-icon-button portal-sidebar-close" type="button" aria-label="Tutup menu" data-sidebar-close>
                <span aria-hidden="true">×</span>
            </button>
            <button class="portal-sidebar-icon-button portal-sidebar-collapse" type="button" aria-label="Perkecil sidebar" aria-pressed="false" data-sidebar-collapse>
                <span aria-hidden="true">‹</span>
            </button>
        </div>

        <div class="portal-dashboard-brand">
            <span class="portal-dashboard-brand__mark">S</span>
            <span class="portal-dashboard-brand__copy">
                <span class="portal-dashboard-brand__name">{{ config('app.name', 'Sarunis') }}</span>
                <span class="portal-dashboard-brand__caption">Portal Sekolah</span>
            </span>
        </div>

        @if ($userRoles->isNotEmpty())
            <div class="portal-dashboard-role-badge" title="{{ $userRoles->implode(' + ') }}">
                {{ $userRoles->implode(' + ') }}
            </div>
        @endif

        <div class="portal-dashboard-menu-groups">
            @foreach ($menuSections as $section)
                @if (! empty($section['items']))
                    <div class="portal-dashboard-menu-group">
                        <div class="portal-dashboard-menu-section">{{ $sectionLabels[$section['title']] ?? $section['title'] }}</div>

                        <nav class="portal-dashboard-menu-list" aria-label="{{ $sectionLabels[$section['title']] ?? $section['title'] }}">
                            @foreach ($section['items'] as $item)
                                <a class="portal-dashboard-menu-item {{ $item['active'] ? 'is-active' : '' }}" href="{{ $item['href'] }}" title="{{ $item['label'] }}" @if (($interactiveSidebar ?? false) && str_starts_with($item['href'], '#')) data-nav-link @endif>
                                    <span class="portal-dashboard-menu-item__icon" aria-hidden="true">
                                        @include('dashboard.partials.icon', ['name' => $item['icon']])
                                    </span>
                                    <span class="portal-dashboard-menu-item__label">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif
            @endforeach
        </div>

        <form method="POST" action="{{ url('/logout') }}" class="mt-auto">
            @csrf
            <button class="portal-dashboard-menu-item portal-dashboard-menu-item--button" type="submit">
                <span class="portal-dashboard-menu-item__icon" aria-hidden="true">
                    @include('dashboard.partials.icon', ['name' => 'logout'])
                </span>
                <span class="portal-dashboard-menu-item__label">Keluar</span>
            </button>
        </form>
    </div>
</aside>

<!-- Bottom Navigation Bar for Mobile -->
<nav class="portal-bottom-nav d-lg-none" aria-label="Bottom Navigation">
    @if ($homeItem)
        <a href="{{ $homeItem['href'] }}" class="portal-bottom-nav-item {{ $homeItem['active'] ? 'is-active' : '' }}">
            <span class="portal-bottom-nav-icon">
                @include('dashboard.partials.icon', ['name' => $homeItem['icon']])
            </span>
            <span class="portal-bottom-nav-label">{{ $homeItem['label'] }}</span>
        </a>
    @endif

    @if ($attendanceItem && $attendanceItem !== $homeItem)
        <a href="{{ $attendanceItem['href'] }}" class="portal-bottom-nav-item {{ $attendanceItem['active'] ? 'is-active' : '' }}">
            <span class="portal-bottom-nav-icon">
                @include('dashboard.partials.icon', ['name' => $attendanceItem['icon']])
            </span>
            <span class="portal-bottom-nav-label">{{ $attendanceItem['label'] }}</span>
        </a>
    @endif

    @if ($scheduleItem && $scheduleItem !== $homeItem && $scheduleItem !== $attendanceItem)
        <a href="{{ $scheduleItem['href'] }}" class="portal-bottom-nav-item {{ $scheduleItem['active'] ? 'is-active' : '' }}">
            <span class="portal-bottom-nav-icon">
                @include('dashboard.partials.icon', ['name' => $scheduleItem['icon']])
            </span>
            <span class="portal-bottom-nav-label">{{ $scheduleItem['label'] }}</span>
        </a>
    @endif

    <button type="button" class="portal-bottom-nav-item" data-portal-menu-toggle>
        <span class="portal-bottom-nav-icon">
            @include('dashboard.partials.icon', ['name' => 'menu'])
        </span>
        <span class="portal-bottom-nav-label">Menu</span>
    </button>
</nav>

<!-- Mobile Menu Overlay (Grid Layout) -->
<div class="portal-mobile-menu-overlay d-none" id="mobileMenuOverlay">
    <div class="portal-mobile-menu-overlay-header">
        <h5>Semua Menu</h5>
        <button type="button" class="portal-mobile-menu-overlay-close" data-portal-menu-toggle>&times;</button>
    </div>
    <div class="portal-mobile-menu-overlay-body">
        <div class="portal-mobile-menu-grid">
            @foreach ($flatMenuItems as $item)
                <a href="{{ $item['href'] }}" class="portal-mobile-menu-grid-item {{ $item['active'] ? 'is-active' : '' }}">
                    <span class="portal-mobile-menu-grid-icon">
                        @include('dashboard.partials.icon', ['name' => $item['icon']])
                    </span>
                    <span class="portal-mobile-menu-grid-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
            
            <!-- Log out button -->
            <form method="POST" action="{{ url('/logout') }}" class="w-100 grid-col-span-all" style="grid-column: span 3;">
                @csrf
                <button type="submit" class="portal-mobile-menu-grid-item portal-mobile-menu-grid-item-logout" style="width: 100%; border: 0; background: transparent; padding-top: 1rem; color: #dc3545; display: flex; flex-direction: column; align-items: center;">
                    <span class="portal-mobile-menu-grid-icon text-danger" style="margin-bottom: 0.5rem;">
                        @include('dashboard.partials.icon', ['name' => 'logout'])
                    </span>
                    <span class="portal-mobile-menu-grid-label text-danger">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('[data-portal-menu-toggle]');
        const overlay = document.getElementById('mobileMenuOverlay');

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                if (overlay) {
                    overlay.classList.toggle('d-none');
                }
            });
        });
    });
</script>
