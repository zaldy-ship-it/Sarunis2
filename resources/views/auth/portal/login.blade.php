@extends('layouts.admin-auth')

@section('title', 'Login '.$portalView['display'])
@section('content')
    <div class="text-center mb-4">
        <h1 class="portal-auth-title mb-2">Masuk</h1>
        <p class="portal-auth-subtitle">Masuk menggunakan email atau nama pengguna yang terdaftar. Sistem akan membuka dashboard sesuai role akun.</p>
    </div>

    @if (request('reset') === 'success')
        <div class="alert alert-success portal-auth-alert mb-3" role="alert">
            Kata sandi baru tersimpan. Silakan masuk kembali.
        </div>
    @endif

    @if (request('logout') === 'success' || session('status'))
        <div class="alert alert-success portal-auth-alert mb-3" role="alert">
            {{ session('status', 'Logout berhasil. Silakan masuk kembali.') }}
        </div>
    @endif

    <form class="portal-auth-form" method="POST" action="/login" data-portal-login>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="login">Email atau Nama Pengguna</label>
            <input
                class="form-control"
                id="login"
                name="login"
                type="text"
                autocomplete="username"
                value="{{ request('login', request('email')) }}"
                placeholder="contoh: admin@sarunis.test"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Kata Sandi</label>
            <input
                class="form-control"
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                required
            >
        </div>

        <p class="portal-auth-copy mb-3">
            Lupa kata sandi?
            <a class="portal-auth-link" href="{{ route('auth.page.verify-email', ['portal' => $portalKey]) }}">Klik disini</a>
        </p>

        <div class="alert alert-danger portal-auth-alert d-none mb-3" id="portal-login-error" role="alert"></div>

        <button class="btn btn-primary w-100" type="submit" data-default-label="Masuk">Masuk</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('[data-portal-login]');

            if (!form) {
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            const errorBox = document.getElementById('portal-login-error');

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const formData = new FormData(form);
                const defaultLabel = button.dataset.defaultLabel || button.textContent.trim();

                button.disabled = true;
                button.textContent = 'Memproses...';
                errorBox.textContent = '';
                errorBox.classList.add('d-none');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            login: formData.get('login'),
                            password: formData.get('password'),
                        }),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw payload;
                    }

                    window.location.assign(payload.data?.redirect_to ?? '/');
                } catch (error) {
                    const message =
                        error?.errors?.login?.[0] ??
                        error?.errors?.email?.[0] ??
                        error?.message ??
                        'Login gagal. Pastikan akun memiliki akses portal.';

                    errorBox.textContent = message;
                    errorBox.classList.remove('d-none');
                } finally {
                    button.disabled = false;
                    button.textContent = defaultLabel;
                }
            });
        });
    </script>
@endpush
