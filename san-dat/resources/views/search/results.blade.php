@extends('layouts.app')

@section('title', 'Tìm kiếm: ' . $searchQuery . ' - Sàn Đất')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Kết quả tìm kiếm</h1>

        @if($searchQuery)
            <p class="text-gray-500 mb-8">Tìm kiếm: "{{ $searchQuery }}"</p>
        @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($consignments as $item)
                @include('components.consignment-card', ['consignment' => $item])
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-gray-500">Không tìm thấy kết quả nào</p>
                    <a href="{{ route('consignments.index') }}" class="mt-4 inline-block text-indigo-600 hover:underline">
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
                            class="px-4 py-2 rounded-lg {{ $meta['current_page'] == $i ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition">
                            {{ $i }}
                        </a>
                    @endfor
                </nav>
            </div>
        @endif
    </div>
@endsection