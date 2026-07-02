import { useEffect } from 'react';

const asset = (path) => (window.assetUrl ? `${window.assetUrl}${path}` : `/${path}`);

export default function AuthBackground() {
    const loginBg = asset('images/login-campus-bg.png');

    useEffect(() => {
        document.documentElement.classList.add('auth-page');
        document.body.classList.add('auth-page');
        document.documentElement.style.backgroundColor = '#7eb8e6';
        document.body.style.backgroundColor = '#7eb8e6';

        return () => {
            document.documentElement.classList.remove('auth-page');
            document.body.classList.remove('auth-page');
            document.documentElement.style.backgroundColor = '';
            document.body.style.backgroundColor = '';
        };
    }, []);

    return (
        <div
            className="auth-bg-layer"
            aria-hidden="true"
            style={{
                position: 'fixed',
                inset: 0,
                width: '100vw',
                height: '100dvh',
                zIndex: 0,
                overflow: 'hidden',
                pointerEvents: 'none',
                backgroundColor: '#7eb8e6',
                backgroundImage: `url(${loginBg})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundRepeat: 'no-repeat',
            }}
        />
    );
}

export { asset };
