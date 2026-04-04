@extends('layouts.app')

@section('title', ($article['title'] ?? 'Tin tức') . ' - ' . ($settings['siteName'] ?? 'SànĐất'))

@php
    // Prepare OG image from article's featured image
    $articleOgImage = $article['featured_image'] ?? '';

    // Prepare OG description: use excerpt or strip HTML from content
    $articleOgDescription = '';
    if (!empty($article['excerpt'])) {
        $articleOgDescription = strip_tags($article['excerpt']);
    } elseif (!empty($article['content'])) {
        $articleOgDescription = strip_tags(html_entity_decode($article['content']));
    }
    $articleOgDescription = \Illuminate\Support\Str::limit($articleOgDescription, 160);
    if (empty($articleOgDescription)) {
        $articleOgDescription = ($article['title'] ?? 'Tin tức') . ' - Tin tức bất động sản';
    }

    $articleOgTitle = ($article['title'] ?? 'Tin tức') . ' - ' . ($settings['siteName'] ?? 'SànĐất');
@endphp

@section('og_type', 'article')
@section('og_title', $articleOgTitle)
@section('description', $articleOgDescription)
@section('og_description', $articleOgDescription)
@if($articleOgImage)
    @section('og_image', $articleOgImage)
    @section('twitter_image', $articleOgImage)
@endif
@section('og_url', url()->current())
@section('twitter_title', $articleOgTitle)
@section('twitter_description', $articleOgDescription)

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-4 text-sm">
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-green-400 transition">Trang chủ</a>
            <span class="text-gray-600 mx-2">›</span>
            <a href="{{ route('articles.index') }}" class="text-gray-400 hover:text-green-400 transition">Tin
                tức</a>
            <span class="text-gray-600 mx-2">›</span>
            <span class="text-gray-300">{{ $article['title'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <article class="lg:col-span-3">

                <div class="bg-navy-700 rounded-lg shadow-md overflow-hidden border border-navy-600">
                    <!-- Featured Image -->
                    @if(!empty($article['featured_image']))
                        <img src="{{ $article['featured_image'] }}" alt="{{ $article['title'] }}"
                            class="w-full h-64 md:h-80 object-cover" onerror="this.style.display='none'">
                    @endif

                    <div class="p-6 md:p-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-100 mb-4">{{ $article['title'] }}</h1>

                        <div class="flex items-center gap-4 text-sm text-gray-400 mb-6 pb-6 border-b border-navy-600">
                            @if(!empty($article['author']['name']))
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $article['author']['name'] }}
                                </span>
                            @endif
                            @if(!empty($article['published_at']))
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($article['published_at'])->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>

                        <!-- Article Content -->
                        <div
                            class="prose prose-invert prose-green max-w-none text-gray-300 leading-relaxed article-content">
                            @php
                                $rawContent = html_entity_decode($article['content'] ?? '', ENT_QUOTES, 'UTF-8');
                                $hasHtml = preg_match('/<[a-zA-Z][\s\S]*>/', $rawContent);
                            @endphp
                            @if($hasHtml)
                                {!! $rawContent !!}
                            @else
                                {!! nl2br(e($rawContent)) !!}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Back button -->
                <div class="mt-6">
                    <a href="{{ route('articles.index') }}"
                        class="inline-flex items-center gap-2 text-green-400 hover:text-green-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Quay lại danh sách tin tức
                    </a>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <div class="bg-navy-700 rounded-lg shadow-md p-6 border border-navy-600 sticky top-20">
                    <h3 class="text-lg font-bold text-gray-100 mb-4 pb-3 border-b border-navy-600">Bài viết mới nhất</h3>
                    @if(count($recentArticles) > 0)
                        <div class="space-y-4">
                            @foreach($recentArticles as $recent)
                                @if(($recent['slug'] ?? '') !== ($article['slug'] ?? ''))
                                    <a href="{{ route('articles.show', $recent['slug']) }}" class="flex gap-3 group">
                                        @if(!empty($recent['featured_image']))
                                            <img src="{{ $recent['featured_image'] }}" class="w-16 h-12 object-cover rounded flex-shrink-0"
                                                onerror="this.src='/images/placeholder.jpg'">
                                        @else
                                            <div class="w-16 h-12 bg-navy-800 rounded flex-shrink-0 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm text-gray-300 group-hover:text-green-400 transition line-clamp-2">
                                                {{ $recent['title'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ \Carbon\Carbon::parse($recent['published_at'])->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Chưa có bài viết khác</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>

    <style>
        .article-content h1,
        .article-content h2,
        .article-content h3 {
            color: #f3f4f6;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }

        .article-content h1 {
            font-size: 1.75em;
            font-weight: 700;
        }

        .article-content h2 {
            font-size: 1.5em;
            font-weight: 600;
        }

        .article-content h3 {
            font-size: 1.25em;
            font-weight: 600;
        }

        .article-content p {
            margin-bottom: 1em;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1em 0;
        }

        .article-content a {
            color: #4ade80;
            text-decoration: underline;
        }

        .article-content a:hover {
            color: #86efac;
        }

        .article-content ul,
        .article-content ol {
            padding-left: 1.5em;
            margin-bottom: 1em;
        }

        .article-content li {
            margin-bottom: 0.5em;
        }

        .article-content blockquote {
            border-left: 3px solid #22c55e;
            padding-left: 1em;
            color: #9ca3af;
            font-style: italic;
            margin: 1em 0;
        }

        /* Quill editor output styles */
        .article-content strong {
            font-weight: 700;
            color: #f9fafb;
        }

        .article-content em {
            font-style: italic;
        }

        .article-content u {
            text-decoration: underline;
        }

        .article-content s {
            text-decoration: line-through;
        }

        .article-content pre.ql-syntax {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1em;
            border-radius: 8px;
            overflow-x: auto;
            font-family: monospace;
        }

        .article-content .ql-size-small {
            font-size: 0.75em;
        }

        .article-content .ql-size-large {
            font-size: 1.5em;
        }

        .article-content .ql-size-huge {
            font-size: 2em;
        }

        .article-content .ql-align-center {
            text-align: center;
        }

        .article-content .ql-align-right {
            text-align: right;
        }

        .article-content .ql-align-justify {
            text-align: justify;
        }

        .article-content .ql-indent-1 {
            padding-left: 3em;
        }

        .article-content .ql-indent-2 {
            padding-left: 6em;
        }

        .article-content .ql-indent-3 {
            padding-left: 9em;
        }

        .article-content .ql-video {
            width: 100%;
            max-width: 640px;
            height: 360px;
            border-radius: 8px;
            margin: 1em 0;
        }
    </style>
@endsection