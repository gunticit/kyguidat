/**
 * Format số tiền VNĐ theo định dạng chuẩn
 * @param amount - Số tiền cần format
 * @param options - Tùy chọn format
 * @returns Chuỗi tiền đã format
 */
export function formatCurrency(
    amount: number | string | null | undefined,
    options: {
        showCurrency?: boolean;
        showBillion?: boolean;
    } = {}
): string {
    const { showCurrency = true, showBillion = false } = options;

    if (amount === null || amount === undefined || amount === '') {
        return showCurrency ? '0 đ' : '0';
    }

    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;

    if (isNaN(numAmount)) {
        return showCurrency ? '0 đ' : '0';
    }

    // Nếu số lớn hơn 1 tỷ và muốn hiển thị dạng tỷ
    if (showBillion && numAmount >= 1000000000) {
        const billions = numAmount / 1000000000;
        const formatted = billions % 1 === 0
            ? billions.toString()
            : billions.toFixed(1).replace('.0', '');
        return `${formatted} tỷ`;
    }

    // Nếu số lớn hơn 1 triệu và muốn hiển thị dạng ngắn
    if (showBillion && numAmount >= 1000000) {
        const millions = numAmount / 1000000;
        const formatted = millions % 1 === 0
            ? millions.toString()
            : millions.toFixed(1).replace('.0', '');
        return `${formatted} triệu`;
    }

    // Format thông thường với dấu chấm phân cách
    const formatted = new Intl.NumberFormat('vi-VN').format(numAmount);

    return showCurrency ? `${formatted} đ` : formatted;
}

/**
 * Parse chuỗi tiền đã format về số
 * @param formattedAmount - Chuỗi tiền đã format (ví dụ: "5.000.000")
 * @returns Số nguyên
 */
export function parseCurrency(formattedAmount: string): number {
    if (!formattedAmount) return 0;
    // Loại bỏ tất cả ký tự không phải số
    const numStr = formattedAmount.replace(/\D/g, '');
    return parseInt(numStr, 10) || 0;
}

/**
 * Format input tiền khi người dùng đang nhập
 * @param value - Giá trị từ input
 * @returns Chuỗi đã format
 */
export function formatCurrencyInput(value: string): string {
    // Loại bỏ tất cả ký tự không phải số
    const numStr = value.replace(/\D/g, '');
    if (!numStr) return '';

    // Format với dấu chấm phân cách
    return new Intl.NumberFormat('vi-VN').format(parseInt(numStr, 10));
}
