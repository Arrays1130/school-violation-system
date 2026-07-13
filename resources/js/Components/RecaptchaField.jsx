import { forwardRef, useEffect, useImperativeHandle, useRef } from "react";

const RecaptchaField = forwardRef(function RecaptchaField({ siteKey, onChange, error }, ref) {
    const containerRef = useRef(null);
    const widgetIdRef = useRef(null);

    useImperativeHandle(ref, () => ({
        reset() {
            if (widgetIdRef.current !== null && window.grecaptcha?.reset) {
                window.grecaptcha.reset(widgetIdRef.current);
            }
            onChange("");
        },
    }));

    useEffect(() => {
        if (!siteKey) {
            return undefined;
        }

        const renderWidget = () => {
            if (!containerRef.current || widgetIdRef.current !== null) {
                return;
            }

            widgetIdRef.current = window.grecaptcha.render(containerRef.current, {
                sitekey: siteKey,
                callback: (token) => onChange(token),
                "expired-callback": () => onChange(""),
            });
        };

        if (window.grecaptcha?.render) {
            window.grecaptcha.ready(renderWidget);
            return undefined;
        }

        const existingScript = document.querySelector('script[src*="google.com/recaptcha/api.js"]');

        if (existingScript) {
            existingScript.addEventListener("load", () => window.grecaptcha.ready(renderWidget));
            return undefined;
        }

        const script = document.createElement("script");
        script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
        script.async = true;
        script.defer = true;
        script.onload = () => window.grecaptcha.ready(renderWidget);
        document.head.appendChild(script);

        return undefined;
    }, [siteKey, onChange]);

    if (!siteKey) {
        return null;
    }

    return (
        <div className="mt-5">
            <div ref={containerRef} className="flex justify-center" />
            {error && <div className="text-red-600 text-sm mt-1 text-center">{error}</div>}
        </div>
    );
});

export default RecaptchaField;
