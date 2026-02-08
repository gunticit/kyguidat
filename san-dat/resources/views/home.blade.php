@extends('layouts.app')

@section('title', 'Sàn Đất - Ký gửi Bất động sản')
@section('description', 'Tìm kiếm và ký gửi bất động sản uy tín')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Tìm Kiếm Bất Động Sản
            </h1>
            <p class="text-xl text-indigo-100 mb-8">
                Nền tảng ký gửi bất động sản uy tín hàng đầu Việt Nam
            </p>

            <!-- Search Box -->
            <form action="{{ route('search.results') }}" method="GET"
                class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-2 flex">
                <input type="text" name="q" placeholder="Tìm kiếm theo tên, địa chỉ..."
                    class="flex-1 px-4 py-3 text-gray-700 focus:outline-none rounded-l-lg">
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Tìm kiếm
                </button>
            </form>
        </div>
    </section>

    <!-- Featured Listings -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Bất động sản nổi bật</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($consignments as $item)
                    @include('components.consignment-card', ['consignment' => $item])
                @empty
                    <p class="col-span-full text-center text-gray-500 py-8">
                        Chưa có bất động sản nào
                    </p>
                @endforelse
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('consignments.index') }}"
                    class="inline-flex items-center px-6 py-3 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                    Xem tất cả
                </a>
            </div>
        </div>
    </section>

    <!-- Locations -->
    @if(count($locations) > 0)
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Khu vực nổi bật</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($locations as $location)
                        <a href="{{ route('consignments.index', ['province' => $location['province']]) }}"
                            class="p-4 bg-gray-50 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition text-center">
                            <p class="font-medium text-gray-900">{{ $location['province'] }}</p>
                            <p class="text-sm text-gray-500">{{ $location['count'] }} tin</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="py-16 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Bạn có bất động sản cần ký gửi?
            </h2>
            <p class="text-indigo-100 mb-8">
                Đăng ký ngay để đăng tin và tiếp cận hàng nghìn khách hàng tiềm năng
            </p>
            <a href="http://localhost:3015"
                class="inline-flex items-center px-8 py-4 bg-white text-indigo-600 rounded-lg hover:bg-gray-100 transition font-semibold">
                Đăng tin ngay
            </a>
        </div>
    </section>
@endsection