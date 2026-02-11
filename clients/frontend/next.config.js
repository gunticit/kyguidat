/** @type {import('next').NextConfig} */
const path = require('path');

const nextConfig = {
  reactStrictMode: true,
  // Enable standalone output for Docker production
  output: 'standalone',
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
        hostname: 'kyguidatvuon.com',
      },
      {
        protocol: 'https',
        hostname: 'backend.kyguidatvuon.com',
      },
      {
        protocol: 'https',
        hostname: '*.kyguidatvuon.com',
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
