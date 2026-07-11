@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="{{ $portalKey ?? 'guru-mapel' }}">
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => false])

        <main class="portal-dashboard-main portal-directory-main">
            <div class="portal-directory-header">
                <div>
                    <span class="portal-hero__badge">{{ $activeAcademicYear }} | {{ ucfirst($activeSemester) }}</span>
                    <h1>Jadwal Mengajar</h1>
                    <p>Agenda mengajar hari ini dan jadwal lengkap per hari dalam seminggu.</p>
                </div>
            </div>

            @include('dashboard.partials.teacher-schedule')

            @if (!empty($allScheduleRows))
                <section class="portal-panel portal-weekly-schedule" data-dashboard-section data-section-label="Jadwal Mengajar Lengkap">
                    <div class="portal-section-heading">
                        <div>
                            <h2>Jadwal Mengajar Lengkap</h2>
                            <p>Seluruh jadwal mengajar dalam seminggu, dikelompokkan per hari.</p>
                        </div>
                    </div>

                    @foreach ($allScheduleRows as $dayGroup)
                        <div class="portal-weekly-schedule__day">
                            <h3 class="portal-weekly-schedule__day-title">
                                <span>{{ mb_substr($dayGroup['day'], 0, 3) }}</span>
                                {{ $dayGroup['day'] }}
                            </h3>

                            <div class="portal-weekly-schedule__list">
                                @foreach ($dayGroup['items'] as $item)
                                    <article class="portal-weekly-schedule__item" data-search-item>
                                        <div class="portal-weekly-schedule__time">
                                            <span>Jam Mapel</span>
                                            <strong>{{ $item['time'] }}</strong>
                                        </div>
                                        <div class="portal-weekly-schedule__main">
                                            <strong>{{ $item['subject'] }}</strong>
                                            <span>{{ $item['class_name'] }}</span>
                                        </div>
                                        <div class="portal-weekly-schedule__period">
                                            <span>Jam Ke-</span>
                                            <strong>{{ $item['lesson_period'] }}</strong>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
@endsection
