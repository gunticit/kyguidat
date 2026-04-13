# Khodat Chatbot Skills

Bộ tài liệu hướng dẫn xây dựng chatbot AI tư vấn BĐS cho khodat.com.

---

## Cấu trúc thư mục

```
skills/
│
├── core/
│   ├── SYSTEM_PROMPT.md        ← Prompt chính, copy vào API call
│   └── RAG_GUIDE.md            ← Hướng dẫn kết nối dữ liệu thực
│
├── persona/
│   └── PERSONA.md              ← Tính cách "Đất", giọng điệu, từ cấm
│
├── techniques/
│   └── CONVERSATION_TECHNIQUES.md  ← 5 kỹ thuật nói chuyện như người thật
│
└── examples/
    └── FEW_SHOT_EXAMPLES.md    ← 6 kịch bản hội thoại mẫu
```

---

## Cách sử dụng

### System prompt đầy đủ = ghép các file theo thứ tự:

```
1. core/SYSTEM_PROMPT.md        (bắt buộc)
2. persona/PERSONA.md           (bắt buộc)
3. techniques/CONVERSATION_TECHNIQUES.md  (khuyến nghị)
4. examples/FEW_SHOT_EXAMPLES.md          (khuyến nghị, chọn 3-4 case phù hợp)
```

### Lưu ý về token
- Toàn bộ 4 file = ~3,000 tokens system prompt
- Có thể cắt bớt examples nếu cần tiết kiệm token

---

## Thứ tự ưu tiên cải thiện

| Giai đoạn | Việc cần làm | Impact |
|---|---|---|
| Ngay bây giờ | Dùng SYSTEM_PROMPT + PERSONA + TECHNIQUES | Cao |
| Tuần 1-2 | Thêm FEW_SHOT_EXAMPLES | Cao |
| Tháng 1 | Inject dữ liệu tin đăng thực (RAG Cách 1) | Rất cao |
| Tháng 2-3 | RAG Cách 2 (filter thông minh) | Cao |
| Khi scale | RAG Cách 3 (vector search) | Trung bình |