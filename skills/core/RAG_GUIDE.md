# SKILL: RAG — Hướng Dẫn Tích Hợp Dữ Liệu Thực

## RAG là gì (nói ngắn gọn)

RAG = Retrieval-Augmented Generation.
Thay vì AI tự "bịa" sản phẩm → AI tra cứu database thực của bạn trước, rồi mới trả lời.
Kết quả: chatbot nói đúng giá, đúng diện tích, đúng địa chỉ — không hallucinate.

---

## Khi nào cần RAG?

| Số lượng tin đăng | Giải pháp | Phức tạp |
|---|---|---|
| < 50 tin | Nhét hết vào system prompt | Thấp |
| 50 - 500 tin | API inject theo filter | Trung bình |
| > 500 tin | Vector search (RAG thật sự) | Cao |

**Hiện tại Khodat (~19 tin Đồng Nai):** Dùng cách 1 hoặc 2 là đủ.

---

## Cách 1: Inject toàn bộ tin vào System Prompt (đơn giản nhất)

```javascript
// Gọi API lấy danh sách tin đăng của bạn
const listings = await fetch('https://api.khodat.com/listings?limit=50')
  .then(r => r.json());

// Format thành text ngắn gọn
const listingText = listings.map(l => 
  `[${l.id}] ${l.title} | ${l.price} | ${l.area}m² | ${l.location} | ${l.url}`
).join('\n');

// Đưa vào system prompt
const systemPrompt = `
${BASE_SYSTEM_PROMPT}

## Danh sách bất động sản hiện có (cập nhật realtime)
${listingText}

Chỉ tư vấn sản phẩm có trong danh sách trên. Không tự bịa thêm.
`;
```

**Ưu điểm:** Đơn giản, triển khai ngay.
**Nhược điểm:** Tốn token nếu nhiều tin. Giới hạn ~50 tin.

---

## Cách 2: API inject theo filter (khuyến nghị cho Khodat hiện tại)

Khi khách nói "đất Đồng Nai dưới 2 tỷ" → backend lọc tin phù hợp → inject vào context.

```javascript
// Bước 1: AI phân tích intent (lần gọi API đầu)
const intentResponse = await callClaude({
  system: "Trích xuất filter từ câu hỏi. Trả về JSON: {province, maxPrice, minPrice, type}",
  messages: [{ role: "user", content: userMessage }]
});
const filter = JSON.parse(intentResponse);

// Bước 2: Query database của bạn
const relevantListings = await db.query(`
  SELECT * FROM listings 
  WHERE province = $1 AND price <= $2
  LIMIT 5
`, [filter.province, filter.maxPrice]);

// Bước 3: Gọi AI lần 2 với dữ liệu thực
const finalResponse = await callClaude({
  system: FULL_SYSTEM_PROMPT,
  messages: [
    ...conversationHistory,
    { 
      role: "user", 
      content: `${userMessage}\n\n[DỮ LIỆU HỆ THỐNG - không đề cập với khách]\n${JSON.stringify(relevantListings)}`
    }
  ]
});
```

---

## Cách 3: Vector Search (khi có 500+ tin)

Dùng khi database lớn, cần tìm "đất gần sân bay" hoặc "yên tĩnh, có vườn".
Các từ khóa này không filter được bằng SQL thường.

**Stack gợi ý:**
- **Supabase** (có pgvector sẵn, free tier ổn) — phù hợp cho startup
- **Pinecone** — managed, dễ dùng hơn nhưng có phí
- **Weaviate** — open source, self-host được

**Flow:**
```
Tin đăng mới → Tạo embedding (text-embedding-3-small) → Lưu vào vector DB
                                    ↓
Câu hỏi khách → Tạo embedding → Tìm top-5 tin tương đồng → Inject vào prompt
```

**Code mẫu với Supabase:**
```javascript
import { createClient } from '@supabase/supabase-js';
import OpenAI from 'openai';

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);
const openai = new OpenAI();

async function searchListings(query) {
  // Tạo embedding cho câu hỏi
  const embedding = await openai.embeddings.create({
    model: 'text-embedding-3-small',
    input: query
  });

  // Tìm tin tương đồng
  const { data } = await supabase.rpc('match_listings', {
    query_embedding: embedding.data[0].embedding,
    match_count: 5
  });

  return data;
}
```

---

## Checklist triển khai RAG cho Khodat

- [ ] Bước 1: API endpoint trả về danh sách tin đăng (JSON)
- [ ] Bước 2: Inject top 10 tin mới nhất vào system prompt (cách 1)
- [ ] Bước 3: Thêm filter theo tỉnh/thành (cách 2)
- [ ] Bước 4: Thêm filter theo giá, loại đất
- [ ] Bước 5 (khi >200 tin): Xét chuyển sang vector search
- [ ] Bước 6: Sync realtime khi có tin mới/cũ