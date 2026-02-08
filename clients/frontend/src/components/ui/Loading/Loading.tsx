import styles from './Loading.module.css';

interface LoadingProps {
    size?: 'sm' | 'md' | 'lg';
    message?: string;
    fullScreen?: boolean;
}

export function Loading({ size = 'md', message, fullScreen = false }: LoadingProps) {
    const sizeClass = {
        sm: styles.spinnerSm,
        md: styles.spinnerMd,
        lg: styles.spinnerLg,
    };

    const content = (
        <div className={styles.loadingContainer}>
            <div className={`${styles.spinner} ${sizeClass[size]}`}></div>
            {message && <p className={styles.message}>{message}</p>}
        </div>
    );

    if (fullScreen) {
        return <div className={styles.fullScreen}>{content}</div>;
    }

    return content;
}

export default Loading;
