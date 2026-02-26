@extends('layouts.app')

@section('title', 'Bất động sản - Sàn Đất')

@push('styles')
    <style>
        @keyframes skeleton-shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .skeleton-pulse {
            background: linear-gradient(90deg, var(--navy-700) 25%, var(--navy-600) 50%, var(--navy-700) 75%);
            background-size: 200% 100%;
            animation: skeleton-shimmer 1.5s ease-in-out infinite;
        }

        #skeleton-grid {
            transition: opacity 0.3s ease;
        }

        #real-content {
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        #real-content.loaded {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-100 mb-2">Bất động sản</h1>
                <!-- Location Status -->
                <div id="location-status" class="flex items-center text-sm">
                    @if($userLat && $userLng)
                        <span class="inline-flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Đang hiển thị theo vị trí gần bạn
                        </span>
                        <a href="{{ route('consignments.index', request()->except(['lat', 'lng'])) }}"
                            class="ml-3 text-gray-400 hover:text-gray-200 underline text-xs">Bỏ lọc vị trí</a>
                    @else
                        <button onclick="requestLocation()"
                            class="inline-flex items-center text-gray-400 hover:text-green-400 transition cursor-pointer">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Hiển thị theo vị trí gần tôi
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filter Form -->
            <form action="{{ route('consignments.index') }}" method="GET" class="flex flex-wrap gap-4 mt-4 md:mt-0">
                @if($userLat && $userLng)
                    <input type="hidden" name="lat" value="{{ $userLat }}">
                    <input type="hidden" name="lng" value="{{ $userLng }}">
                @endif

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm..."
                    class="px-4 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-200 placeholder-gray-500">

                <select name="province"
                    class="px-4 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-200">
                    <option value="">Tất cả khu vực</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province['name'] }}" {{ request('province') == $province['name'] ? 'selected' : '' }}>
                            {{ $province['name'] }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold shadow-lg shadow-green-500/25">
                    Lọc
                </button>
            </form>

            <!-- View Toggle -->
            <div class="flex gap-1 mt-4 md:mt-0 ml-0 md:ml-4">
                <button onclick="setView('grid')" id="btn-grid" class="p-2 rounded-lg border border-navy-600 transition"
                    title="Dạng lưới">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button onclick="setView('list')" id="btn-list" class="p-2 rounded-lg border border-navy-600 transition"
                    title="Dạng danh sách">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Skeleton Loading Grid -->
        <div id="skeleton-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($s = 0; $s < 8; $s++)
                <div class="bg-navy-700 rounded-lg shadow-md overflow-hidden border border-navy-600">
                    <!-- Skeleton Image -->
                    <div class="aspect-video skeleton-pulse"></div>
                    <!-- Skeleton Content -->
                    <div class="p-4 space-y-3">
                        <div class="h-4 skeleton-pulse rounded w-full"></div>
                        <div class="h-4 skeleton-pulse rounded w-3/4"></div>
                        <div class="h-5 skeleton-pulse rounded w-1/2 mt-1"></div>
                        <div class="flex space-x-4 mt-2">
                            <div class="h-3 skeleton-pulse rounded w-16"></div>
                            <div class="h-3 skeleton-pulse rounded w-20"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <!-- Real Content (hidden initially) -->
        <div id="real-content">
            <!-- Grid View -->
            <div id="view-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($consignments as $item)
                    @include('components.consignment-card', ['consignment' => $item])
                @empty
                    <div class="col-span-full text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-gray-400">Không tìm thấy bất động sản nào</p>
                    </div>
                @endforelse
            </div>

            <!-- List View -->
            <div id="view-list" class="flex flex-col gap-4 hidden">
                @forelse($consignments as $item)
                    @include('components.consignment-card-list', ['consignment' => $item])
                @empty
                    <div class="text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-gray-400">Không tìm thấy bất động sản nào</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($meta && $meta['total_pages'] > 1)
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        @for($i = 1; $i <= $meta['total_pages']; $i++)
                            <a href="{{ route('consignments.index', array_merge(request()->query(), ['page' => $i])) }}"
                                class="px-4 py-2 rounded-lg {{ $meta['current_page'] == $i ? 'bg-green-500 text-white shadow-lg shadow-green-500/25' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600' }} transition">
                                {{ $i }}
                            </a>
                        @endfor
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <script>
        // View toggle
        function setView(mode) {
            const grid = document.getElementById('view-grid');
            const list = document.getElementById('view-list');
            const btnGrid = document.getElementById('btn-grid');
            const btnList = document.getElementById('btn-list');

            if (mode === 'list') {
                grid.classList.add('hidden');
                list.classList.remove('hidden');
                btnGrid.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnGrid.classList.add('text-gray-400');
                btnList.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnList.classList.remove('text-gray-400');
            } else {
                list.classList.add('hidden');
                grid.classList.remove('hidden');
                btnList.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnList.classList.add('text-gray-400');
                btnGrid.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnGrid.classList.remove('text-gray-400');
            }
            localStorage.setItem('viewMode', mode);
        }

        // Reveal real content when page fully loads
        document.addEventListener('DOMContentLoaded', function () {
            // Restore saved view mode
            var savedView = localStorage.getItem('viewMode') || 'grid';
            setView(savedView);

            var skeleton = document.getElementById('skeleton-grid');
            var content = document.getElementById('real-content');

            // Small delay for smooth transition
            setTimeout(function () {
                skeleton.style.opacity = '0';
                content.classList.add('loaded');

                // Remove skeleton from DOM after animation
                setTimeout(function () {
                    skeleton.style.display = 'none';
                }, 300);
            }, 400);
        });

        function requestLocation() {
            if (!navigator.geolocation) {
                alert('Trình duyệt của bạn không hỗ trợ định vị.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const url = new URL(window.location.href);
                    url.searchParams.set('lat', lat.toFixed(6));
                    url.searchParams.set('lng', lng.toFixed(6));
                    url.searchParams.set('max_distance', '15');
                    url.searchParams.set('page', '1');
                    window.location.href = url.toString();
                },
                function (error) {
                    if (error.code === error.PERMISSION_DENIED) {
                        alert('Bạn đã từ chối quyền truy cập vị trí. Vui lòng cho phép trong cài đặt trình duyệt.');
                    } else {
                        alert('Không thể lấy vị trí. Vui lòng thử lại.');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    </script>
@endsection