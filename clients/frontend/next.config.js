/** @type {import('next').NextConfig} */
const path = require('path');

const nextConfig = {
  reactStrictMode: true,
  images: {
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'localhost',
      },
      {
        protocol: 'https',
        hostname: 'lh3.googleusercontent.com',
      },
      {
        protocol: 'https',
        hostname: 'platform-lookaside.fbsbx.com',
      },
      {
        protocol: 'https',
        hostname: 'khodat.com',
      },
      {
        protocol: 'https',
        hostname: 'backend.khodat.com',
      },
      {
        protocol: 'https',
        hostname: '*.khodat.com',
      },
      {
        protocol: 'https',
        hostname: '*.zadn.vn',
      },
    ],
  },
  // Webpack configuration for path alias (fallback for non-Turbopack builds)
  webpack: (config) => {
    config.resolve.alias['@'] = path.join(__dirname, 'src');
    return config;
  },
  // Turbopack configuration for path alias (Next.js 16+)
  turbopack: {
    resolveAlias: {
      '@': path.join(__dirname, 'src'),
    },
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
