@extends('layouts.app')

@section('title', 'Xóa tài khoản - ' . ($appSettings['siteName'] ?? 'Sàn Đất'))
@section('description', 'Hướng dẫn xóa tài khoản và dữ liệu cá nhân trên ' . ($appSettings['siteName'] ?? 'Sàn Đất'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Xóa Tài Khoản & Dữ Liệu</h1>

        <div class="prose prose-lg max-w-none space-y-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h2 class="text-xl font-bold text-red-700 mb-3">⚠️ Lưu ý quan trọng</h2>
                <p class="text-red-600">Việc xóa tài khoản là <strong>không thể khôi phục</strong>. Toàn bộ dữ liệu của bạn
                    sẽ bị xóa vĩnh viễn, bao gồm:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1 text-red-600">
                    <li>Thông tin cá nhân (tên, email, số điện thoại)</li>
                    <li>Các tin đăng bất động sản đã ký gửi</li>
                    <li>Lịch sử giao dịch và số dư ví</li>
                    <li>Toàn bộ dữ liệu liên kết với tài khoản</li>
                </ul>
            </div>

            <section>
                <h2 class="text-xl font-bold mb-3">Cách xóa tài khoản</h2>

                <h3 class="text-lg font-semibold mt-4 mb-2">Cách 1: Xóa trực tiếp trên ứng dụng</h3>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Đăng nhập vào tài khoản tại <a href="https://app.khodat.com/login"
                            class="text-blue-600 underline">app.khodat.com</a></li>
                    <li>Vào <strong>Cài đặt tài khoản</strong> → <strong>Xóa tài khoản</strong></li>
                    <li>Xác nhận bằng mật khẩu (hoặc nhập <code>DELETE</code> nếu đăng nhập qua mạng xã hội)</li>
                    <li>Tài khoản sẽ được xóa ngay lập tức</li>
                </ol>

                <h3 class="text-lg font-semibold mt-4 mb-2">Cách 2: Gửi yêu cầu qua email</h3>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Gửi email đến <strong>{{ $appSettings['email'] ?? 'contact@sandat.vn' }}</strong></li>
                    <li>Tiêu đề: <em>"Yêu cầu xóa tài khoản"</em></li>
                    <li>Nội dung: Cung cấp email đăng ký hoặc thông tin tài khoản cần xóa</li>
                    <li>Chúng tôi sẽ xử lý trong vòng <strong>7 ngày làm việc</strong></li>
                </ol>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">Dữ liệu được xóa</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">Loại dữ liệu</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Xử lý</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">Thông tin cá nhân</td>
                                <td class="border border-gray-300 px-4 py-2">Xóa vĩnh viễn</td>
                                <td class="border border-gray-300 px-4 py-2">Ngay lập tức</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">Tin đăng bất động sản</td>
                                <td class="border border-gray-300 px-4 py-2">Xóa vĩnh viễn</td>
                                <td class="border border-gray-300 px-4 py-2">Ngay lập tức</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">Số dư ví</td>
                                <td class="border border-gray-300 px-4 py-2">Xóa vĩnh viễn</td>
                                <td class="border border-gray-300 px-4 py-2">Ngay lập tức</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">Lịch sử giao dịch</td>
                                <td class="border border-gray-300 px-4 py-2">Lưu trữ theo quy định</td>
                                <td class="border border-gray-300 px-4 py-2">Tối đa 10 năm*</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-sm text-gray-500 mt-2">* Theo quy định pháp luật Việt Nam về lưu trữ chứng từ tài chính</p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">Liên hệ hỗ trợ</h2>
                <p>Nếu bạn gặp khó khăn trong việc xóa tài khoản, vui lòng liên hệ:</p>
                <ul class="list-none pl-0 mt-2 space-y-1">
                    <li><strong>Email:</strong> {{ $appSettings['email'] ?? 'contact@sandat.vn' }}</li>
                    <li><strong>Điện thoại:</strong> {{ $appSettings['phone'] ?? '0123 456 789' }}</li>
                </ul>
            </section>
        </div>
    </div>
@endsection