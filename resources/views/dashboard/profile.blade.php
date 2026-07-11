@extends('layouts.portal-dashboard')

@section('title', $pageTitle)

@section('content')
    <div class="portal-dashboard-shell" data-dashboard data-dashboard-portal="{{ $portalKey }}">
        @include('dashboard.partials.sidebar', ['menuSections' => $menuSections, 'interactiveSidebar' => true])

        <main class="portal-dashboard-main portal-directory-main">
            <div class="portal-directory-header">
                <div>
                    <h1>{{ $pageTitle }}</h1>
                    <p>Kelola informasi akun dan kata sandi Anda.</p>
                </div>
            </div>

            <div class="portal-profile-layout">
                <!-- Left Sidebar: User Avatar & Quick Info Card -->
                <div class="portal-profile-sidebar">
                    <div class="portal-panel portal-profile-card text-center">
                        <div class="portal-profile-avatar-wrapper mx-auto">
                            @php
                                $nameParts = preg_split('/\s+/', trim($user->name)) ?: [];
                                $initials = collect($nameParts)
                                    ->filter()
                                    ->map(static fn(string $part): string => strtoupper(substr($part, 0, 1)))
                                    ->take(2)
                                    ->implode('');
                                $initials = $initials !== '' ? $initials : 'US';
                            @endphp
                            <div class="portal-profile-avatar-fallback">{{ $initials }}</div>
                        </div>
                        
                        <h3 class="portal-profile-name mt-3">{{ $user->name }}</h3>
                        <span class="portal-badge is-primary mb-2" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                            {{ $portalKey === 'admin' ? 'Administrator' : ($portalKey === 'guru-mapel' ? 'Guru Mata Pelajaran' : ($portalKey === 'walikelas' ? 'Wali Kelas' : ($portalKey === 'siswa' ? 'Siswa' : 'Orang Tua'))) }}
                        </span>
                        
                        <div class="portal-profile-card-details mt-4 text-start">
                            <div class="portal-profile-detail-item">
                                <span class="text-muted">Username / NIP / NISN</span>
                                <strong>{{ $profile->nip ?? ($profile->nisn ?? ($user->email)) }}</strong>
                            </div>
                            <div class="portal-profile-detail-item mt-3">
                                <span class="text-muted">Status Akun</span>
                                <span class="text-success font-semibold">● Aktif</span>
                            </div>
                        </div>

                        <div class="portal-profile-actions mt-4">
                            <form method="POST" action="{{ url('/logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; font-weight: 700; padding: 0.65rem;">
                                    <span style="width: 1.2rem; height: 1.2rem; display: inline-flex; align-items: center; justify-content: center;">
                                        @include('dashboard.partials.icon', ['name' => 'logout'])
                                    </span>
                                    Keluar dari Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Forms -->
                <div class="portal-profile-forms">
                    @if (session('status') === 'profil-diperbarui')
                        <div class="alert alert-success mb-4" style="border-radius: 10px;">
                            Profil Anda berhasil diperbarui.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: 10px;">
                            Terdapat kesalahan pada input Anda. Silakan periksa kembali formulir di bawah.
                        </div>
                    @endif

                    <div class="portal-panel">
                        <div class="portal-section-heading">
                            <div>
                                <h2>Informasi Akun</h2>
                                <p>Perbarui informasi detail diri dan kontak Anda di bawah ini.</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('profile.update') }}" class="portal-form">
                            @csrf
                            @method('patch')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label font-bold">Nama Lengkap</label>
                                    <input id="name" name="name" type="text" class="form-control bg-light" value="{{ old('name', $profile->name ?? $user->name) }}" readonly disabled>
                                    <small class="text-muted" style="font-size: 0.76rem;">Nama tidak dapat diubah secara mandiri. Hubungi administrator jika ada kesalahan.</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label font-bold">Email</label>
                                    <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label font-bold">Nomor Telepon</label>
                                    <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $profile->phone ?? '') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label font-bold">Alamat Lengkap</label>
                                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $profile->address ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-5" style="border-color: rgba(34, 152, 207, 0.16);">

                            <div class="portal-section-heading">
                                <div>
                                    <h2>Ganti Kata Sandi</h2>
                                    <p>Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="current_password" class="form-label font-bold">Kata Sandi Saat Ini</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label font-bold">Kata Sandi Baru</label>
                                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label font-bold">Konfirmasi Kata Sandi Baru</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-5">
                                <button type="submit" class="btn btn-primary font-bold" style="padding: 0.65rem 1.75rem; border-radius: 8px;">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
