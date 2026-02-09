@extends('layouts.app')

@section('title', 'Bất động sản - Sàn Đất')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-100 mb-4 md:mb-0">Bất động sản</h1>

            <!-- Filter Form -->
            <form action="{{ route('consignments.index') }}" method="GET" class="flex flex-wrap gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm..."
                    class="px-4 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-200 placeholder-gray-500">

                <select name="province"
                    class="px-4 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-gray-200">
                    <option value="">Tất cả khu vực</option>
                    @foreach($locations as $location)
                        <option value="{{ $location['province'] }}" {{ request('province') == $location['province'] ? 'selected' : '' }}>
                            {{ $location['province'] }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold shadow-lg shadow-green-500/25">
                    Lọc
                </button>
            </form>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
@endsection