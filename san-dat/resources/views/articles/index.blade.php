@extends('layouts.app')

@section('title', 'Tin tức - ' . ($settings['siteName'] ?? 'SànĐất'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-100 mb-2">Tin tức</h1>
            <p class="text-gray-400">Cập nhật tin tức bất động sản mới nhất</p>
        </div>

        @if(count($articles) > 0)
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    <a href="{{ route('articles.show', $article['slug']) }}"
                        class="bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
                        <!-- Image -->
                        <div class="aspect-video bg-navy-800 relative overflow-hidden">
                            @if(!empty($article['featured_image']))
                                <img src="{{ preg_replace('#^https?://[^/]+#', '', $article['featured_image']) }}"
                                    alt="{{ $article['title'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    onerror="this.src='/images/placeholder.jpg'">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-100 line-clamp-2 mb-2 group-hover:text-green-400 transition">
                                {{ $article['title'] }}
                            </h3>
                            @if(!empty($article['excerpt']))
                                <p class="text-gray-400 text-sm line-clamp-3 mb-3">{{ $article['excerpt'] }}</p>
                            @endif
                            <p class="text-gray-500 text-xs">
                                {{ \Carbon\Carbon::parse($article['published_at'])->format('d/m/Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="bg-navy-700 rounded-lg p-12 text-center border border-navy-600">
                <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <p class="text-gray-400 text-lg">Chưa có tin tức nào</p>
            </div>
        @endif
    </div>
@endsection