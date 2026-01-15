/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Enable standalone output for Docker production
  output: 'standalone',
  images: {
    domains: [
      'localhost',
      'lh3.googleusercontent.com',
      'platform-lookaside.fbsbx.com',
      'khodat.com',
      'api.khodat.com',
    ],
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/:path*`,
      },
    ];
  },
};

module.exports = nextConfig;
