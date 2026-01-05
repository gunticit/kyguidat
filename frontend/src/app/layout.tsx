import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import { Toaster } from 'react-hot-toast';
import './globals.css';

const inter = Inter({ subsets: ['vietnamese', 'latin'] });

export const metadata: Metadata = {
    title: 'Khodat - Nền tảng Ký gửi',
    description: 'Nền tảng ký gửi uy tín, an toàn và nhanh chóng',
};

export default function RootLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <html lang="vi">
            <body className={inter.className}>
                {children}
                <Toaster
                    position="top-right"
                    toastOptions={{
                        duration: 4000,
                        style: {
                            background: '#333',
                            color: '#fff',
                        },
                    }}
                />
            </body>
        </html>
    );
}
