import { ReactNode } from 'react';
import styles from './Badge.module.css';

type BadgeVariant = 'default' | 'success' | 'warning' | 'error' | 'info' | 'secondary';

interface BadgeProps {
    children: ReactNode;
    variant?: BadgeVariant;
    size?: 'sm' | 'md';
    className?: string;
}

export function Badge({ children, variant = 'default', size = 'md', className = '' }: BadgeProps) {
    return (
        <span className={`${styles.badge} ${styles[variant]} ${styles[size]} ${className}`}>
            {children}
        </span>
    );
}

export default Badge;
