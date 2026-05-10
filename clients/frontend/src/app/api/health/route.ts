// Healthcheck endpoint for Docker healthcheck (Dockerfile.prod).
// Trả 200 ngay khi Next.js process sẵn sàng nhận request.
export const dynamic = 'force-static';

export function GET() {
    return new Response(JSON.stringify({ status: 'ok' }), {
        status: 200,
        headers: { 'content-type': 'application/json' },
    });
}
