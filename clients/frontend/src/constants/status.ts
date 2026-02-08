// Status constants with Vietnamese labels
export const CONSIGNMENT_STATUS = {
    pending: { label: 'Chờ duyệt', color: '#f59e0b', bgColor: 'rgba(245, 158, 11, 0.15)' },
    approved: { label: 'Đã duyệt', color: '#3b82f6', bgColor: 'rgba(59, 130, 246, 0.15)' },
    rejected: { label: 'Từ chối', color: '#ef4444', bgColor: 'rgba(239, 68, 68, 0.15)' },
    selling: { label: 'Đang bán', color: '#6366f1', bgColor: 'rgba(99, 102, 241, 0.15)' },
    sold: { label: 'Đã bán', color: '#22c55e', bgColor: 'rgba(34, 197, 94, 0.15)' },
    cancelled: { label: 'Đã hủy', color: '#6b7280', bgColor: 'rgba(107, 114, 128, 0.15)' },
} as const;

export const PAYMENT_STATUS = {
    pending: { label: 'Chờ thanh toán', color: '#f59e0b', bgColor: 'rgba(245, 158, 11, 0.15)' },
    processing: { label: 'Đang xử lý', color: '#3b82f6', bgColor: 'rgba(59, 130, 246, 0.15)' },
    completed: { label: 'Hoàn thành', color: '#22c55e', bgColor: 'rgba(34, 197, 94, 0.15)' },
    failed: { label: 'Thất bại', color: '#ef4444', bgColor: 'rgba(239, 68, 68, 0.15)' },
    cancelled: { label: 'Đã hủy', color: '#6b7280', bgColor: 'rgba(107, 114, 128, 0.15)' },
    expired: { label: 'Hết hạn', color: '#6b7280', bgColor: 'rgba(107, 114, 128, 0.15)' },
} as const;

export const PAYMENT_METHOD = {
    vnpay: { label: 'VNPay', icon: 'vnpay' },
    momo: { label: 'Momo', icon: 'momo' },
    bank_transfer: { label: 'Chuyển khoản', icon: 'bank' },
} as const;

export const SUPPORT_STATUS = {
    open: { label: 'Đang mở', color: '#3b82f6', bgColor: 'rgba(59, 130, 246, 0.15)' },
    in_progress: { label: 'Đang xử lý', color: '#f59e0b', bgColor: 'rgba(245, 158, 11, 0.15)' },
    waiting_reply: { label: 'Chờ phản hồi', color: '#8b5cf6', bgColor: 'rgba(139, 92, 246, 0.15)' },
    resolved: { label: 'Đã giải quyết', color: '#22c55e', bgColor: 'rgba(34, 197, 94, 0.15)' },
    closed: { label: 'Đã đóng', color: '#6b7280', bgColor: 'rgba(107, 114, 128, 0.15)' },
} as const;

export const SUPPORT_PRIORITY = {
    low: { label: 'Thấp', color: '#6b7280' },
    medium: { label: 'Trung bình', color: '#f59e0b' },
    high: { label: 'Cao', color: '#ef4444' },
    urgent: { label: 'Khẩn cấp', color: '#dc2626' },
} as const;

export const SUPPORT_CATEGORY = {
    general: { label: 'Chung' },
    payment: { label: 'Thanh toán' },
    consignment: { label: 'Ký gửi' },
    account: { label: 'Tài khoản' },
    other: { label: 'Khác' },
} as const;

export const PACKAGE_STATUS = {
    active: { label: 'Đang hoạt động', color: '#22c55e', bgColor: 'rgba(34, 197, 94, 0.15)' },
    expired: { label: 'Đã hết hạn', color: '#ef4444', bgColor: 'rgba(239, 68, 68, 0.15)' },
    cancelled: { label: 'Đã hủy', color: '#6b7280', bgColor: 'rgba(107, 114, 128, 0.15)' },
} as const;
