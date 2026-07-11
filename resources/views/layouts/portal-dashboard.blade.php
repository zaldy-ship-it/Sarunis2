<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Dashboard Portal') - {{ config('app.name', 'Sarunis') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
        >
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            {!! file_get_contents(resource_path('css/app.css')) !!}
            .portal-tooltip-sm {
                --bs-tooltip-font-size: 0.75rem;
                --bs-tooltip-padding-x: 0.5rem;
                --bs-tooltip-padding-y: 0.25rem;
            }
            body:not(.portal-sidebar-collapsed) .portal-dashboard-sidebar .tooltip {
                display: none !important;
            }
        </style>
    </head>
    <body class="portal-dashboard-body">
        @yield('content')

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"
        ></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const body = document.body;
                const openButton = document.querySelector('[data-sidebar-open]');
                const closeButtons = document.querySelectorAll('[data-sidebar-close]');
                const collapseButton = document.querySelector('[data-sidebar-collapse]');
                const collapsedKey = 'sarunis.sidebar.collapsed';

                if (localStorage.getItem(collapsedKey) === '1') {
                    body.classList.add('portal-sidebar-collapsed');
                    collapseButton?.setAttribute('aria-pressed', 'true');
                    collapseButton?.setAttribute('aria-label', 'Perbesar sidebar');
                }

                const setMobileDrawer = function (open) {
                    body.classList.toggle('portal-sidebar-open', open);
                    openButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                openButton?.addEventListener('click', function () {
                    setMobileDrawer(true);
                });

                closeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setMobileDrawer(false);
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        setMobileDrawer(false);
                    }
                });

                collapseButton?.addEventListener('click', function () {
                    const collapsed = !body.classList.contains('portal-sidebar-collapsed');
                    body.classList.toggle('portal-sidebar-collapsed', collapsed);
                    collapseButton.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                    collapseButton.setAttribute('aria-label', collapsed ? 'Perbesar sidebar' : 'Perkecil sidebar');
                    localStorage.setItem(collapsedKey, collapsed ? '1' : '0');
                });

                const labelTables = function (root) {
                    const tables = new Set();

                    if (root.matches?.('.portal-directory-table, .portal-table, table')) {
                        tables.add(root);
                    }

                    root.querySelectorAll?.('.portal-directory-table, .portal-table, table').forEach(function (table) {
                        tables.add(table);
                    });

                    root.closest?.('table') && tables.add(root.closest('table'));

                    tables.forEach(function (table) {
                        const headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
                            return th.textContent.trim();
                        });

                        if (headers.length === 0) return;

                        table.querySelectorAll('tbody tr').forEach(function (row) {
                            Array.from(row.children).forEach(function (cell, index) {
                                if (!cell.hasAttribute('data-label') && headers[index]) {
                                    cell.setAttribute('data-label', headers[index]);
                                }
                            });
                        });
                    });
                };

                const enhanceButtons = function (root) {
                    root.querySelectorAll('[aria-label]').forEach(function (element) {
                        // Skip adding title to structural elements like aside or nav to prevent giant tooltips
                        if (element.tagName === 'ASIDE' || element.tagName === 'NAV') return;
                        
                        if (!element.getAttribute('title')) {
                            element.setAttribute('title', element.getAttribute('aria-label'));
                        }
                    });

                    if (window.bootstrap?.Tooltip) {
                        root.querySelectorAll('[title]').forEach(function (element) {
                            if (element.dataset.tooltipBound === '1') return;
                            element.dataset.tooltipBound = '1';
                            
                            let placement = 'auto';
                            let container = false;
                            
                            const sidebar = element.closest('.portal-dashboard-sidebar');
                            if (sidebar) {
                                placement = 'right';
                                container = sidebar;
                            }
                            
                            new window.bootstrap.Tooltip(element, {
                                placement: placement,
                                container: container,
                                customClass: 'portal-tooltip-sm'
                            });
                        });
                    }
                };

                labelTables(document);
                enhanceButtons(document);

                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        mutation.addedNodes.forEach(function (node) {
                            if (node instanceof HTMLElement) {
                                labelTables(node);
                                enhanceButtons(node);
                            }
                        });
                    });
                });

                observer.observe(document.body, { childList: true, subtree: true });
            });
        </script>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: {!! json_encode(session('success')) !!},
                        confirmButtonColor: '#2298cf'
                    });
                });
            </script>
        @endif

        @if (session('status'))
            @php
                $statusMsg = session('status');
                $friendlyMsg = match($statusMsg) {
                    'profil-diperbarui' => 'Profil Anda berhasil diperbarui.',
                    'password-diperbarui' => 'Kata sandi Anda berhasil diperbarui.',
                    default => $statusMsg
                };
            @endphp
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: {!! json_encode($friendlyMsg) !!},
                        confirmButtonColor: '#2298cf'
                    });
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: {!! json_encode(session('error')) !!},
                        confirmButtonColor: '#2298cf'
                    });
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Validasi!',
                        html: {!! json_encode(implode("<br>", $errors->all())) !!},
                        confirmButtonColor: '#2298cf'
                    });
                });
            </script>
        @endif

        @stack('scripts')
    </body>
</html>
