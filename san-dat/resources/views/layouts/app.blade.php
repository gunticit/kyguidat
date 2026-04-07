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
        $appLogo = isset($appSettings['logo']) ? preg_replace('#^https?://[^/]+#', '', $appSettings['logo']) : '/logo.jpg';
        $appSiteName = $appSettings['siteName'] ?? 'Sàn Đất';

        // Load SEO settings from admin
        $seoSettings = [];
        if (Storage::exists('seo.json')) {
            $seoSettings = json_decode(Storage::get('seo.json'), true) ?? [];
        }
        $seoTitle = $seoSettings['metaTitle'] ?? '';
        $seoDescription = $seoSettings['metaDescription'] ?? '';
        $seoKeywords = $seoSettings['metaKeywords'] ?? '';
        $seoCanonical = $seoSettings['canonicalUrl'] ?? '';
        $seoOgTitle = $seoSettings['ogTitle'] ?? '';
        $seoOgDescription = $seoSettings['ogDescription'] ?? '';
        $seoOgImage = $seoSettings['ogImage'] ?? '';
        $seoTwitterTitle = $seoSettings['twitterTitle'] ?? '';
        $seoTwitterDescription = $seoSettings['twitterDescription'] ?? '';
        $seoRobots = $seoSettings['robotsMeta'] ?? 'index, follow';
        $seoGoogleVerification = $seoSettings['googleVerification'] ?? '';
        $seoSchemaOrgName = $seoSettings['schemaOrgName'] ?? '';
        $seoSchemaOrgLogo = $seoSettings['schemaOrgLogo'] ?? '';
        $seoSchemaCustom = $seoSettings['schemaCustom'] ?? '';

        // Default title fallback: SEO title from admin → siteName
        $defaultTitle = $seoTitle ?: ($appSiteName . ' - Ký gửi Bất động sản');
        $defaultDescription = $seoDescription ?: 'Nền tảng ký gửi bất động sản uy tín hàng đầu Việt Nam';
    @endphp
    <title>@yield('title', $defaultTitle)</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    @if($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    @if($seoRobots)
        <meta name="robots" content="{{ $seoRobots }}">
    @endif
    @if($seoCanonical)
        <link rel="canonical" href="{{ $seoCanonical }}">
    @endif
    @if($seoGoogleVerification)
        <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', $seoOgTitle ?: $defaultTitle)">
    <meta property="og:description" content="@yield('og_description', $seoOgDescription ?: $defaultDescription)">
    <meta property="og:image" content="@yield('og_image', $seoOgImage ?: url($appLogo))">
    <meta property="og:url" content="@yield('og_url', $seoCanonical ?: url()->current())">
    <meta property="og:site_name" content="{{ $appSiteName }}">
    <meta property="fb:app_id" content="{{ env('FACEBOOK_APP_ID', '966242223397117') }}">

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $seoTwitterTitle ?: $defaultTitle)">
    <meta name="twitter:description" content="@yield('twitter_description', $seoTwitterDescription ?: $defaultDescription)">
    <meta name="twitter:image" content="@yield('twitter_image', $seoOgImage ?: url($appLogo))">

    @if($appFavicon)
        <link rel="icon" type="image/png" href="{{ $appFavicon }}">
    @endif

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Khodat">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <!-- Theme Detection (runs before render to prevent flash) -->
    <script>
        (function () {
            var saved = null;
            try { saved = localStorage.getItem('theme'); } catch(e) {}
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

    {{-- JSON-LD Schema Markup --}}
    @if($seoSchemaOrgName || $seoSchemaOrgLogo)
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $seoSchemaOrgName }}",
        "url": "{{ $seoCanonical ?: url('/') }}"
        @if($seoSchemaOrgLogo)
        ,"logo": "{{ $seoSchemaOrgLogo }}"
        @endif
    }
    </script>
    @endif
    @if($seoSchemaCustom)
    <script type="application/ld+json">
    {!! $seoSchemaCustom !!}
    </script>
    @endif
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
            try { localStorage.setItem('theme', next); } catch(e) {}
            setTheme(next);
        }

        function resetToAuto() {
            try { localStorage.setItem('theme', 'auto'); } catch(e) {}
            setTheme(getAutoTheme());
        }

        function updateThemeIcon(theme) {
            var sunIcon = document.getElementById('sun-icon');
            var moonIcon = document.getElementById('moon-icon');
            var mobileSun = document.getElementById('mobile-sun-icon');
            var mobileMoon = document.getElementById('mobile-moon-icon');
            if (theme === 'day') {
                if (sunIcon) sunIcon.classList.add('hidden');
                if (moonIcon) moonIcon.classList.remove('hidden');
                if (mobileSun) mobileSun.classList.add('hidden');
                if (mobileMoon) mobileMoon.classList.remove('hidden');
            } else {
                if (sunIcon) sunIcon.classList.remove('hidden');
                if (moonIcon) moonIcon.classList.add('hidden');
                if (mobileSun) mobileSun.classList.remove('hidden');
                if (mobileMoon) mobileMoon.classList.add('hidden');
            }
        }

        // Update icon on page load
        document.addEventListener('DOMContentLoaded', function () {
            var theme = document.documentElement.getAttribute('data-theme');
            updateThemeIcon(theme);
        });

        // Auto-switch check every minute (only if not manually set)
        setInterval(function () {
            var saved = null;
            try { saved = localStorage.getItem('theme'); } catch(e) {}
            if (!saved || saved === 'auto') {
                setTheme(getAutoTheme());
            }
        }, 60000);
    </script>

    <!-- Mobile Bottom Navigation -->
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-navy-800 border-t border-navy-600 shadow-[0_-2px_10px_rgba(0,0,0,0.1)]">
        <div class="flex items-center h-16 w-full">
            <!-- Giới thiệu -->
            <a href="{{ url('/gioi-thieu') }}"
                class="flex-1 flex flex-col items-center gap-1 text-gray-500 hover:text-green-600 transition"
                style="color: #6b7280;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-medium">Giới thiệu</span>
            </a>

            <!-- Trang chủ (highlighted, centered) -->
            <a href="{{ url('/') }}" class="flex-1 flex flex-col items-center -mt-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center shadow-lg"
                    style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="text-xs font-medium mt-1" style="color: #f59e0b;">Trang chủ</span>
            </a>

            <!-- Ký gửi -->
            <a href="{{ env('APP_URL_SANDAT', '#') }}"
                class="flex-1 flex flex-col items-center gap-1 text-gray-500 hover:text-green-600 transition"
                style="color: #6b7280;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-xs font-medium">Ký gửi</span>
            </a>
        </div>
    </nav>

    <!-- Spacer for mobile bottom nav -->
    <style>
        @media (max-width: 767px) {
            body {
                padding-bottom: 64px;
            }
        }
    </style>

    @stack('scripts')

    <!-- Telegram Chat Widget -->
    @include('components.telegram-chat')

    <!-- PWA Service Worker & Install Prompt -->
    <script>
        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function () { });
        }
        // Capture install prompt for download button
        window.deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            window.deferredPrompt = e;
            // Show download buttons
            document.querySelectorAll('.pwa-install-btn').forEach(function (btn) {
                btn.style.display = 'inline-flex';
            });
        });
        window.addEventListener('appinstalled', function () {
            window.deferredPrompt = null;
            document.querySelectorAll('.pwa-install-btn').forEach(function (btn) {
                btn.style.display = 'none';
            });
        });
    </script>
</body>

</html>