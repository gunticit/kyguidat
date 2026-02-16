<!DOCTYPE html>
<html lang="vi" data-theme="night">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        use Illuminate\Support\Facades\Storage;
        $appSettings = [];
        if (Storage::exists('settings.json')) {
            $appSettings = json_decode(Storage::get('settings.json'), true) ?? [];
        }
        $appFavicon = isset($appSettings['favicon']) ? preg_replace('#^https?://[^/]+#', '', $appSettings['favicon']) : '';
        $appSiteName = $appSettings['siteName'] ?? 'Sàn Đất';
    @endphp
    <title>@yield('title', $appSiteName . ' - Ký gửi Bất động sản')</title>
    <meta name="description" content="@yield('description', 'Nền tảng ký gửi bất động sản uy tín hàng đầu Việt Nam')">
    @if($appFavicon)
        <link rel="icon" type="image/png" href="{{ $appFavicon }}">
    @endif

    <!-- Theme Detection (runs before render to prevent flash) -->
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var theme;
            if (saved && saved !== 'auto') {
                theme = saved;
            } else {
                var hour = new Date().getHours();
                theme = (hour >= 6 && hour < 18) ? 'day' : 'night';
            }
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        /* ===== Theme CSS Variables ===== */
        :root,
        [data-theme="night"] {
            --navy-900: #0b1121;
            --navy-800: #111827;
            --navy-700: #1a2332;
            --navy-600: #1e293b;
            --navy-500: #263248;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --green-400: #4ade80;
        }

        [data-theme="day"] {
            --navy-900: #f8fafc;
            --navy-800: #f1f5f9;
            --navy-700: #ffffff;
            --navy-600: #e2e8f0;
            --navy-500: #cbd5e1;
            --gray-100: #0f172a;
            --gray-200: #1e293b;
            --gray-300: #334155;
            --gray-400: #475569;
            --gray-500: #64748b;
            --gray-600: #94a3b8;
            --green-400: #16a34a;
        }

        /* Smooth theme transitions */
        body,
        header,
        footer,
        main,
        section,
        nav,
        div,
        a,
        p,
        span,
        h1,
        h2,
        h3,
        h4,
        input,
        select,
        button,
        img {
            transition: background-color 0.4s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Day mode header shadow */
        [data-theme="day"] header {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* Day mode card shadow enhancement */
        [data-theme="day"] .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            900: 'var(--navy-900)',
                            800: 'var(--navy-800)',
                            700: 'var(--navy-700)',
                            600: 'var(--navy-600)',
                            500: 'var(--navy-500)',
                        },
                        gray: {
                            100: 'var(--gray-100)',
                            200: 'var(--gray-200)',
                            300: 'var(--gray-300)',
                            400: 'var(--gray-400)',
                            500: 'var(--gray-500)',
                            600: 'var(--gray-600)',
                        },
                        green: {
                            400: 'var(--green-400)',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body class="bg-navy-900 min-h-screen flex flex-col text-gray-100">
    <!-- Header -->
    @include('components.header')

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Theme Controller Script -->
    <script>
        function getAutoTheme() {
            var hour = new Date().getHours();
            return (hour >= 6 && hour < 18) ? 'day' : 'night';
        }

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            updateThemeIcon(theme);
        }

        function toggleTheme() {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'day' ? 'night' : 'day';
            localStorage.setItem('theme', next);
            setTheme(next);
        }

        function resetToAuto() {
            localStorage.setItem('theme', 'auto');
            setTheme(getAutoTheme());
        }

        function updateThemeIcon(theme) {
            var sunIcon = document.getElementById('sun-icon');
            var moonIcon = document.getElementById('moon-icon');
            if (sunIcon && moonIcon) {
                if (theme === 'day') {
                    sunIcon.classList.add('hidden');
                    moonIcon.classList.remove('hidden');
                } else {
                    sunIcon.classList.remove('hidden');
                    moonIcon.classList.add('hidden');
                }
            }
        }

        // Update icon on page load
        document.addEventListener('DOMContentLoaded', function () {
            var theme = document.documentElement.getAttribute('data-theme');
            updateThemeIcon(theme);
        });

        // Auto-switch check every minute (only if not manually set)
        setInterval(function () {
            var saved = localStorage.getItem('theme');
            if (!saved || saved === 'auto') {
                setTheme(getAutoTheme());
            }
        }, 60000);
    </script>

    @stack('scripts')
</body>

</html>