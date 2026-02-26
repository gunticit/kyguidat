@extends('layouts.app')

@section('title', 'Điều khoản sử dụng - ' . ($appSettings['siteName'] ?? 'Sàn Đất'))
@section('description', 'Điều khoản sử dụng của ' . ($appSettings['siteName'] ?? 'Sàn Đất') . ' - Nền tảng ký gửi bất động sản uy tín')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Điều Khoản Sử Dụng</h1>
        <p class="text-sm text-gray-500 mb-8 text-center">Cập nhật lần cuối: {{ date('d/m/Y') }}</p>

        <div class="prose prose-lg max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-bold mb-3">1. Giới thiệu và phạm vi áp dụng</h2>
                <p>Điều khoản sử dụng này (sau đây gọi tắt là "Điều khoản") quy định các điều kiện sử dụng website
                    <strong>{{ $appSettings['siteName'] ?? 'Sàn Đất' }}</strong> (sau đây gọi tắt là "Website" hoặc "Nền
                    tảng"). Bằng việc truy cập và sử dụng Website, bạn đồng ý tuân thủ các Điều khoản này.</p>
                <p>Điều khoản này được xây dựng theo quy định của pháp luật Việt Nam, bao gồm:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Bộ luật Dân sự 2015 (Luật số 91/2015/QH13)</li>
                    <li>Luật Thương mại 2005 (Luật số 36/2005/QH11)</li>
                    <li>Luật Giao dịch điện tử 2023 (Luật số 20/2023/QH15)</li>
                    <li>Luật Kinh doanh bất động sản 2023 (Luật số 29/2023/QH15)</li>
                    <li>Luật Bảo vệ quyền lợi người tiêu dùng 2023 (Luật số 19/2023/QH15)</li>
                    <li>Nghị định 52/2013/NĐ-CP về thương mại điện tử (sửa đổi bởi NĐ 85/2021/NĐ-CP)</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">2. Định nghĩa</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>"Người dùng":</strong> Bất kỳ cá nhân, tổ chức nào truy cập và sử dụng Website</li>
                    <li><strong>"Người bán/Người ký gửi":</strong> Người dùng đăng tin ký gửi bất động sản trên Website</li>
                    <li><strong>"Người mua":</strong> Người dùng tìm kiếm, xem và liên hệ mua bất động sản</li>
                    <li><strong>"Bất động sản":</strong> Đất đai, nhà ở, công trình xây dựng và các tài sản gắn liền với đất
                    </li>
                    <li><strong>"Dịch vụ":</strong> Các dịch vụ ký gửi, đăng tin, tìm kiếm bất động sản do Website cung cấp
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">3. Đăng ký tài khoản</h2>
                <h3 class="text-lg font-semibold mt-4 mb-2">3.1. Điều kiện đăng ký</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Người dùng phải từ đủ 18 tuổi trở lên và có đầy đủ năng lực hành vi dân sự</li>
                    <li>Cung cấp thông tin chính xác, đầy đủ và cập nhật</li>
                    <li>Mỗi cá nhân chỉ được đăng ký một tài khoản</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">3.2. Bảo mật tài khoản</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Bạn chịu trách nhiệm bảo mật thông tin đăng nhập của mình</li>
                    <li>Thông báo ngay cho chúng tôi khi phát hiện tài khoản bị truy cập trái phép</li>
                    <li>Chúng tôi không chịu trách nhiệm về thiệt hại do bạn không bảo mật tài khoản</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">4. Quy định về đăng tin bất động sản</h2>
                <h3 class="text-lg font-semibold mt-4 mb-2">4.1. Yêu cầu bắt buộc</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Thông tin bất động sản phải chính xác, trung thực, không gây nhầm lẫn</li>
                    <li>Hình ảnh phải phản ánh đúng thực tế bất động sản</li>
                    <li>Giá bán phải phù hợp với thị trường, không đưa giá ảo</li>
                    <li>Bất động sản phải có giấy tờ pháp lý hợp lệ theo quy định</li>
                    <li>Người đăng tin phải là chủ sở hữu hợp pháp hoặc được ủy quyền hợp pháp</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">4.2. Nội dung cấm</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Đăng tin bất động sản đang tranh chấp, bị kê biên, thế chấp mà không thông báo</li>
                    <li>Sử dụng hình ảnh giả mạo, chỉnh sửa gây hiểu lầm về bất động sản</li>
                    <li>Đăng tin trùng lặp nhiều lần cho cùng một bất động sản</li>
                    <li>Thông tin vi phạm pháp luật, xâm phạm quyền lợi bên thứ ba</li>
                    <li>Nội dung quảng cáo, spam, hoặc không liên quan đến bất động sản</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">5. Quyền và nghĩa vụ của người dùng</h2>
                <h3 class="text-lg font-semibold mt-4 mb-2">5.1. Quyền của người dùng</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Sử dụng các dịch vụ theo đúng chức năng của Website</li>
                    <li>Đăng tin, tìm kiếm, liên hệ về bất động sản</li>
                    <li>Được bảo vệ thông tin cá nhân theo Chính sách bảo mật</li>
                    <li>Khiếu nại, phản ánh về dịch vụ và nội dung trên Website</li>
                    <li>Yêu cầu gỡ bỏ thông tin cá nhân theo quy định</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">5.2. Nghĩa vụ của người dùng</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Tuân thủ Điều khoản sử dụng và pháp luật Việt Nam</li>
                    <li>Không sử dụng Website cho mục đích bất hợp pháp</li>
                    <li>Không can thiệp, phá hoại hệ thống kỹ thuật của Website</li>
                    <li>Không thu thập thông tin người dùng khác trái phép</li>
                    <li>Chịu trách nhiệm về tính chính xác của thông tin đăng tải</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">6. Quyền và nghĩa vụ của Website</h2>
                <h3 class="text-lg font-semibold mt-4 mb-2">6.1. Quyền của Website</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Kiểm duyệt, chỉnh sửa hoặc từ chối nội dung vi phạm</li>
                    <li>Tạm khóa hoặc xóa tài khoản vi phạm Điều khoản</li>
                    <li>Thay đổi, cập nhật tính năng và dịch vụ</li>
                    <li>Thu phí dịch vụ theo bảng giá công khai (nếu có)</li>
                </ul>

                <h3 class="text-lg font-semibold mt-4 mb-2">6.2. Nghĩa vụ của Website</h3>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Cung cấp dịch vụ theo đúng cam kết</li>
                    <li>Bảo mật thông tin cá nhân người dùng</li>
                    <li>Hỗ trợ giải quyết khiếu nại, tranh chấp</li>
                    <li>Tuân thủ các quy định pháp luật về thương mại điện tử và kinh doanh bất động sản</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">7. Miễn trừ trách nhiệm</h2>
                <p>Website <strong>không chịu trách nhiệm</strong> trong các trường hợp sau:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Giao dịch bất động sản diễn ra trực tiếp giữa người mua và người bán</li>
                    <li>Tính chính xác của thông tin do người dùng đăng tải</li>
                    <li>Thiệt hại phát sinh do sự cố kỹ thuật, thiên tai, sự kiện bất khả kháng</li>
                    <li>Tranh chấp pháp lý về quyền sở hữu bất động sản</li>
                    <li>Hành vi lừa đảo, gian lận của người dùng đối với bên thứ ba</li>
                    <li>Nội dung trên các website bên thứ ba được liên kết từ Website</li>
                </ul>
                <p class="mt-2">Website đóng vai trò là <strong>nền tảng trung gian</strong> kết nối, không tham gia vào
                    giao dịch thực tế giữa các bên. Người dùng cần tự thẩm định thông tin trước khi ra quyết định giao dịch.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">8. Sở hữu trí tuệ</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Toàn bộ nội dung, thiết kế, logo, giao diện của Website thuộc sở hữu của chúng tôi</li>
                    <li>Người dùng không được sao chép, phân phối, sửa đổi nội dung của Website mà không có sự đồng ý bằng
                        văn bản</li>
                    <li>Khi đăng tải nội dung (hình ảnh, mô tả bất động sản), người dùng cấp cho Website quyền sử dụng nội
                        dung đó để hiển thị trên nền tảng</li>
                    <li>Người dùng cam đoan nội dung đăng tải không vi phạm quyền sở hữu trí tuệ của bên thứ ba</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">9. Giải quyết tranh chấp</h2>
                <p>Tranh chấp phát sinh được giải quyết theo trình tự:</p>
                <ol class="list-decimal pl-6 space-y-1">
                    <li><strong>Thương lượng:</strong> Các bên thương lượng trực tiếp trong vòng 30 ngày</li>
                    <li><strong>Hòa giải:</strong> Nếu thương lượng không thành, các bên có thể thông qua hòa giải viên
                        thương mại</li>
                    <li><strong>Trọng tài/Tòa án:</strong> Tranh chấp được giải quyết tại Tòa án nhân dân có thẩm quyền tại
                        Việt Nam theo quy định pháp luật Việt Nam</li>
                </ol>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">10. Quy định về thanh toán (nếu có)</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Các khoản phí dịch vụ sẽ được niêm yết công khai trên Website</li>
                    <li>Thanh toán thông qua các phương thức: chuyển khoản ngân hàng, ví điện tử</li>
                    <li>Hóa đơn điện tử được cung cấp theo quy định của pháp luật</li>
                    <li>Chính sách hoàn tiền được áp dụng theo từng loại dịch vụ cụ thể</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">11. Chấm dứt sử dụng</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Người dùng có quyền ngừng sử dụng và yêu cầu xóa tài khoản bất cứ lúc nào</li>
                    <li>Website có quyền tạm khóa hoặc chấm dứt tài khoản khi người dùng vi phạm Điều khoản</li>
                    <li>Các nghĩa vụ phát sinh trước thời điểm chấm dứt vẫn có hiệu lực cho đến khi được hoàn thành</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">12. Điều khoản chung</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Điều khoản này có hiệu lực kể từ ngày bạn sử dụng Website</li>
                    <li>Chúng tôi có quyền sửa đổi Điều khoản và sẽ thông báo trước ít nhất 7 ngày</li>
                    <li>Nếu bất kỳ điều khoản nào bị coi là vô hiệu, các điều khoản còn lại vẫn có hiệu lực</li>
                    <li>Điều khoản này được điều chỉnh bởi pháp luật nước Cộng hòa Xã hội Chủ nghĩa Việt Nam</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold mb-3">13. Liên hệ</h2>
                <p>Mọi thắc mắc về Điều khoản sử dụng, vui lòng liên hệ:</p>
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