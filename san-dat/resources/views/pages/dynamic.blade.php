@extends('layouts.app')

@section('title', ($page['title'] ?? 'Trang') . ' - ' . ($settings['siteName'] ?? 'SànĐất'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-4 text-sm">
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-green-400 transition">Trang chủ</a>
            <span class="text-gray-600 mx-2">›</span>
            <span class="text-gray-300">{{ $page['title'] }}</span>
        </nav>

        <div class="bg-navy-700 rounded-lg shadow-md overflow-hidden border border-navy-600">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-100 mb-6 pb-4 border-b border-navy-600">
                    {{ $page['title'] }}
                </h1>

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

        /* Quill editor output styles */
        .page-content strong {
            font-weight: 700;
            color: #f9fafb;
        }

        .page-content em {
            font-style: italic;
        }

        .page-content u {
            text-decoration: underline;
        }

        .page-content s {
            text-decoration: line-through;
        }

        .page-content pre.ql-syntax {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1em;
            border-radius: 8px;
            overflow-x: auto;
            font-family: monospace;
        }

        .page-content .ql-size-small {
            font-size: 0.75em;
        }

        .page-content .ql-size-large {
            font-size: 1.5em;
        }

        .page-content .ql-size-huge {
            font-size: 2em;
        }

        .page-content .ql-align-center {
            text-align: center;
        }

        .page-content .ql-align-right {
            text-align: right;
        }

        .page-content .ql-align-justify {
            text-align: justify;
        }

        .page-content .ql-indent-1 {
            padding-left: 3em;
        }

        .page-content .ql-indent-2 {
            padding-left: 6em;
        }

        .page-content .ql-indent-3 {
            padding-left: 9em;
        }

        .page-content .ql-video {
            width: 100%;
            max-width: 640px;
            height: 360px;
            border-radius: 8px;
            margin: 1em 0;
        }
    </style>
@endsection