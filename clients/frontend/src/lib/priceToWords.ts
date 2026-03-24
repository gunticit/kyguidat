/**
 * Converts a numeric price to Vietnamese words.
 * VD: 5000000000 → "năm tỷ"
 *     50000 → "năm mươi nghìn"
 */
const digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
const units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

function readThreeDigits(n: number, hasHigherUnit: boolean): string {
    const h = Math.floor(n / 100);
    const t = Math.floor((n % 100) / 10);
    const u = n % 10;
    let result = '';
    if (h > 0) result += digits[h] + ' trăm';
    else if (hasHigherUnit && (t > 0 || u > 0)) result += 'không trăm';
    if (t > 1) result += ' ' + digits[t] + ' mươi';
    else if (t === 1) result += ' mười';
    else if (t === 0 && h > 0 && u > 0) result += ' lẻ';
    if (u > 0) {
        if (t >= 2 && u === 1) result += ' mốt';
        else if (t >= 1 && u === 5) result += ' lăm';
        else if (t >= 2 && u === 4) result += ' tư';
        else result += ' ' + digits[u];
    }
    return result.trim();
}

export function priceToWords(n: number | string): string {
    const num = typeof n === 'string' ? parseInt(n.replace(/\D/g, '')) : n;
    if (!num || num <= 0 || isNaN(num)) return '';

    // Shorthand for exact values
    if (num >= 1e9 && num % 1e9 === 0 && num / 1e9 <= 9) return digits[num / 1e9] + ' tỷ';
    if (num >= 1e6 && num % 1e6 === 0 && num / 1e6 <= 9) return digits[num / 1e6] + ' triệu';
    if (num >= 1e3 && num % 1e3 === 0 && num < 1e6 && num / 1e3 <= 9) return digits[num / 1e3] + ' nghìn';

    // Split number into 3-digit chunks from right to left
    const chunks: number[] = [];
    let remainder = num;
    while (remainder > 0) {
        chunks.push(remainder % 1000);
        remainder = Math.floor(remainder / 1000);
    }

    // Process chunks from highest to lowest (left to right)
    const parts: string[] = [];
    for (let i = chunks.length - 1; i >= 0; i--) {
        const chunk = chunks[i];
        if (chunk > 0) {
            // hasHigherUnit = true only if there are already parts before this chunk
            const chunkText = readThreeDigits(chunk, parts.length > 0);
            parts.push(chunkText + (units[i] ? ' ' + units[i] : ''));
        }
    }
    return parts.join(' ').trim();
}
