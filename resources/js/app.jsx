import './bootstrap';
import './echo';
import '../css/app.css';
import './accessibility'; // Accessibility enhancements
import { route } from 'ziggy-js';

window.route = route;

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        return resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx'));
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        try {
            root.render(<App {...props} />);
        } catch (error) {
            console.error(error);
            document.body.innerHTML = `<div style="padding: 20px; color: red;"><h1>React Error</h1><pre>${error.message}</pre><pre>${error.stack}</pre></div>`;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

window.addEventListener('error', (event) => {
    const errorContainer = document.getElementById('react-error-display') || document.createElement('div');
    errorContainer.id = 'react-error-display';
    errorContainer.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; z-index: 9999; background: #fee2e2; color: #b91c1c; padding: 20px; border-bottom: 2px solid #ef4444;';
    errorContainer.innerHTML = `<strong>Runtime Error:</strong> ${event.message} <br><small>${event.filename}:${event.lineno}:${event.colno}</small>`;
    document.body.appendChild(errorContainer);
});
