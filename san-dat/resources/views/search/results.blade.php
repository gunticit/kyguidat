@extends('layouts.app')

@section('title', 'Tìm kiếm: ' . $searchQuery . ' - Sàn Đất')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-100 mb-2">Kết quả tìm kiếm</h1>

        @if($searchQuery)
            <p class="text-gray-400 mb-8">Tìm kiếm: "{{ $searchQuery }}"</p>
        @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($consignments as $item)
                @include('components.consignment-card', ['consignment' => $item])
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-gray-400">Không tìm thấy kết quả nào</p>
                    <a href="{{ route('consignments.index') }}" class="mt-4 inline-block text-green-400 hover:underline">
                        Xem tất cả bất động sản
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($meta && $meta['total_pages'] > 1)
            <div class="mt-8 flex justify-center">
                <nav class="flex items-center space-x-2">
                    @for($i = 1; $i <= $meta['total_pages']; $i++)
                        <a href="{{ route('search.results', array_merge(request()->query(), ['page' => $i])) }}"
                            class="px-4 py-2 rounded-lg {{ $meta['current_page'] == $i ? 'bg-green-500 text-white shadow-lg shadow-green-500/25' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600' }} transition">
                            {{ $i }}
                        </a>
                    @endfor
                </nav>
            </div>
        @endif
    </div>
@endsection