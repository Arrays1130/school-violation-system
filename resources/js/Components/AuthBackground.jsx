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
        >
            {/* Soft vignette keeps the campus photo present while focusing the form */}
            <div
                className="auth-bg-scrim"
                style={{
                    position: 'absolute',
                    inset: 0,
                    background:
                        'radial-gradient(ellipse 75% 65% at 50% 42%, rgba(15, 23, 42, 0.08) 0%, rgba(15, 23, 42, 0.35) 70%, rgba(15, 23, 42, 0.55) 100%), linear-gradient(180deg, rgba(15, 23, 42, 0.18) 0%, transparent 28%, transparent 62%, rgba(15, 23, 42, 0.45) 100%)',
                }}
            />
        </div>
    );
}

export { asset };
