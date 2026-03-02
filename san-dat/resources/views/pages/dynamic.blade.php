@extends('layouts.app')

@section('title', ($page['title'] ?? 'Trang') . ' - ' . ($settings['siteName'] ?? 'SànĐất'))

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-4 text-sm">
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-green-400 transition">Trang chủ</a>
            <span class="text-gray-600 mx-2">›</span>
            <span class="text-gray-300">{{ $page['title'] }}</span>
        </nav>

        <div class="bg-navy-700 rounded-lg shadow-md overflow-hidden border border-navy-600">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-100 mb-6 pb-4 border-b border-navy-600">
                    {{ $page['title'] }}</h1>

                <!-- Page Content -->
                <div class="prose prose-invert prose-green max-w-none text-gray-300 leading-relaxed page-content">
                    {!! $page['content'] !!}
                </div>
            </div>
        </div>
    </div>

    <style>
        .page-content h1,
        .page-content h2,
        .page-content h3 {
            color: #f3f4f6;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }

        .page-content h1 {
            font-size: 1.75em;
            font-weight: 700;
        }

        .page-content h2 {
            font-size: 1.5em;
            font-weight: 600;
        }

        .page-content h3 {
            font-size: 1.25em;
            font-weight: 600;
        }

        .page-content p {
            margin-bottom: 1em;
        }

        .page-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1em 0;
        }

        .page-content a {
            color: #4ade80;
            text-decoration: underline;
        }

        .page-content a:hover {
            color: #86efac;
        }

        .page-content ul,
        .page-content ol {
            padding-left: 1.5em;
            margin-bottom: 1em;
        }

        .page-content li {
            margin-bottom: 0.5em;
        }

        .page-content blockquote {
            border-left: 3px solid #22c55e;
            padding-left: 1em;
            color: #9ca3af;
            font-style: italic;
            margin: 1em 0;
        }
    </style>
@endsection