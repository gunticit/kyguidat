@extends('layouts.app')

@section('title', 'Chính sách bảo mật - ' . ($appSettings['siteName'] ?? 'Sàn Đất'))
@section('description', 'Chính sách bảo mật của ' . ($appSettings['siteName'] ?? 'Sàn Đất') . ' - Nền tảng ký gửi bất động sản uy tín')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Chính Sách Bảo Mật</h1>
        <p class="text-sm text-gray-500 mb-8 text-center">Cập nhật lần cuối: {{ date('d/m/Y') }}</p>

        <div class="prose prose-lg max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-bold mb-3">1. Giới thiệu</h2>
                <p>Chào mừng bạn đến với <strong>{{ $appSettings['siteName'] ?? 'Sàn Đất' }}</strong> (sau đây gọi tắt là
                    "Chúng tôi", "Website" hoặc "Nền tảng"). Chúng tôi cam kết bảo vệ quyền riêng tư và thông tin cá nhân
                    của bạn theo quy định của pháp luật Việt Nam, bao gồm:</p>
                <ul class="list-disc pl-6 mt-2 space-y-1">
                    <li>Luật An ninh mạng 2018 (Luật số 24/2018/QH14)</li>
                    <li>Luật Bảo vệ quyền lợi người tiêu dùng 2023 (Luật số 19/2023/QH15)</li>
                    <li>Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân</li>
                    <li>Luật Giao dịch điện tử 2023 (Luật số 20/2023/QH15)</li>
                    <li>Luật Kinh doanh bất động sản 2023 (Luật số 29/2023/QH15)</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">2. Thông tin chúng tôi thu thập</h2>
                <p>Khi sử dụng Website, chúng tôi có thể thu thập các loại thông tin sau:</p>

                <h3 class="text-lg font-semibold mt-4 mb-2">2.1. Thông tin cá nhân bạn cung cấp trực tiếp</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Họ và tên</li>
                    <li>Số điện thoại</li>
                    <li>Địa chỉ email</li>
                    <li>Ảnh đại diện (khi đăng nhập qua mạng xã hội)</li>
                    <li>Thông tin đăng nhập (tài khoản Google, Facebook, Zalo)</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">2.2. Thông tin về bất động sản</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Thông tin mô tả, hình ảnh, video về bất động sản ký gửi</li>
                    <li>Địa chỉ, vị trí, diện tích, giá bán của bất động sản</li>
                    <li>Giấy tờ pháp lý liên quan (nếu được cung cấp)</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">2.3. Thông tin tự động thu thập</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Địa chỉ IP, loại trình duyệt, hệ điều hành</li>
                    <li>Thời gian truy cập, các trang đã xem</li>
                    <li>Vị trí địa lý (nếu bạn cho phép)</li>
                    <li>Cookies và các công nghệ theo dõi tương tự</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">3. Mục đích sử dụng thông tin</h2>
                <p>Chúng tôi sử dụng thông tin cá nhân của bạn cho các mục đích sau:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Cung cấp, duy trì và cải thiện dịch vụ ký gửi bất động sản</li>
                    <li>Xác minh danh tính và quản lý tài khoản người dùng</li>
                    <li>Kết nối người mua và người bán bất động sản</li>
                    <li>Gửi thông báo về giao dịch, cập nhật dịch vụ</li>
                    <li>Hỗ trợ khách hàng và xử lý khiếu nại</li>
                    <li>Phân tích, thống kê nhằm nâng cao trải nghiệm người dùng</li>
                    <li>Phòng chống gian lận, bảo đảm an toàn hệ thống</li>
                    <li>Tuân thủ các nghĩa vụ pháp lý theo quy định của pháp luật Việt Nam</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">4. Chia sẻ thông tin</h2>
                <p>Chúng tôi <strong>không bán, trao đổi hoặc cho thuê</strong> thông tin cá nhân của bạn cho bên thứ ba vì
                    mục đích thương mại. Thông tin chỉ được chia sẻ trong các trường hợp sau:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Với sự đồng ý của bạn:</strong> Khi bạn cho phép chia sẻ thông tin cho bên thứ ba cụ thể
                    </li>
                    <li><strong>Đối tác dịch vụ:</strong> Các đơn vị hỗ trợ kỹ thuật, thanh toán, vận hành có ký cam kết bảo
                        mật</li>
                    <li><strong>Yêu cầu pháp lý:</strong> Khi có yêu cầu từ cơ quan nhà nước có thẩm quyền theo quy định
                        pháp luật</li>
                    <li><strong>Bảo vệ quyền lợi:</strong> Để bảo vệ quyền, tài sản hoặc an toàn của chúng tôi và người dùng
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">5. Bảo mật thông tin</h2>
                <p>Chúng tôi áp dụng các biện pháp kỹ thuật và tổ chức phù hợp để bảo vệ thông tin cá nhân, bao gồm:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Mã hóa dữ liệu truyền tải bằng giao thức SSL/TLS</li>
                    <li>Mã hóa mật khẩu bằng thuật toán bcrypt</li>
                    <li>Kiểm soát truy cập theo nguyên tắc phân quyền tối thiểu</li>
                    <li>Giám sát và ghi nhật ký hoạt động hệ thống</li>
                    <li>Sao lưu dữ liệu định kỳ</li>
                    <li>Đào tạo nhân viên về bảo mật thông tin</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">6. Quyền của chủ thể dữ liệu</h2>
                <p>Theo Nghị định 13/2023/NĐ-CP, bạn có các quyền sau đối với dữ liệu cá nhân của mình:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Quyền được biết:</strong> Được thông báo về việc xử lý dữ liệu cá nhân</li>
                    <li><strong>Quyền đồng ý:</strong> Đồng ý hoặc không đồng ý cho phép xử lý dữ liệu</li>
                    <li><strong>Quyền truy cập:</strong> Yêu cầu xem, sao chép dữ liệu cá nhân của mình</li>
                    <li><strong>Quyền chỉnh sửa:</strong> Yêu cầu chỉnh sửa thông tin không chính xác</li>
                    <li><strong>Quyền xóa:</strong> Yêu cầu xóa dữ liệu cá nhân trong một số trường hợp</li>
                    <li><strong>Quyền hạn chế xử lý:</strong> Yêu cầu hạn chế việc xử lý dữ liệu</li>
                    <li><strong>Quyền rút lại sự đồng ý:</strong> Rút lại sự đồng ý đã cấp trước đó</li>
                    <li><strong>Quyền khiếu nại:</strong> Khiếu nại lên cơ quan có thẩm quyền khi quyền bị vi phạm</li>
                </ul>
                <p class="mt-2">Để thực hiện các quyền trên, vui lòng liên hệ với chúng tôi qua thông tin phía dưới.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">7. Cookies</h2>
                <p>Website sử dụng cookies và các công nghệ tương tự nhằm:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Ghi nhớ tùy chọn và cài đặt của bạn (ví dụ: chế độ sáng/tối)</li>
                    <li>Duy trì phiên đăng nhập</li>
                    <li>Phân tích lưu lượng truy cập và hành vi sử dụng</li>
                    <li>Cải thiện hiệu suất và trải nghiệm người dùng</li>
                </ul>
                <p class="mt-2">Bạn có thể tắt cookies thông qua cài đặt trình duyệt, tuy nhiên điều này có thể ảnh hưởng
                    đến một số chức năng của Website.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">8. Lưu trữ và thời gian lưu giữ</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Dữ liệu được lưu trữ trên hệ thống máy chủ đặt tại Việt Nam</li>
                    <li>Thông tin tài khoản được lưu giữ trong suốt thời gian tài khoản hoạt động</li>
                    <li>Thông tin giao dịch được lưu giữ tối thiểu 10 năm theo quy định pháp luật</li>
                    <li>Khi bạn yêu cầu xóa tài khoản, dữ liệu sẽ được xóa trong vòng 30 ngày, trừ các thông tin phải lưu
                        giữ theo quy định pháp luật</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">9. Bảo vệ thông tin trẻ em</h2>
                <p>Website không hướng đến đối tượng dưới 16 tuổi. Chúng tôi không cố ý thu thập thông tin cá nhân của trẻ
                    em. Nếu phát hiện đã thu thập thông tin của người dưới 16 tuổi mà không có sự đồng ý của cha mẹ/người
                    giám hộ, chúng tôi sẽ xóa thông tin đó ngay lập tức.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">10. Thay đổi chính sách</h2>
                <p>Chúng tôi có quyền cập nhật Chính sách bảo mật này theo thời gian. Mọi thay đổi sẽ được thông báo trên
                    Website và/hoặc qua email. Việc tiếp tục sử dụng dịch vụ sau khi có thay đổi đồng nghĩa với việc bạn
                    chấp nhận chính sách mới.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">11. Liên hệ</h2>
                <p>Nếu bạn có bất kỳ câu hỏi nào về Chính sách bảo mật, vui lòng liên hệ:</p>
                <ul class="list-none pl-0 mt-2 space-y-1">
                    <li><strong>Website:</strong> {{ $appSettings['siteName'] ?? 'Sàn Đất' }}</li>
                    <li><strong>Email:</strong> {{ $appSettings['email'] ?? 'contact@sandat.vn' }}</li>
                    <li><strong>Điện thoại:</strong> {{ $appSettings['phone'] ?? '0123 456 789' }}</li>
                    <li><strong>Địa chỉ:</strong> {{ $appSettings['address'] ?? 'TP. Hồ Chí Minh' }}</li>
                </ul>
            </section>
        </div>
    </div>
@endsection