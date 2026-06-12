import defaultTheme from 'tailwindcss/defaultTheme';
const colors = require("tailwindcss/colors");
const {
  default: flattenColorPalette,
} = require("tailwindcss/lib/util/flattenColorPalette");

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
        './resources/**/*.tsx',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                "body-md": ["Inter"],
                "label-caps": ["JetBrains Mono"],
                "headline-md": ["Manrope"],
                "headline-lg": ["Manrope"],
                "headline-lg-mobile": ["Manrope"],
                "body-lg": ["Inter"],
                "body-sm": ["Inter"],
                "headline-xl": ["Manrope"]
            },
            colors: {
                primary: {
                    ...colors.blue,
                    DEFAULT: colors.blue[600]
                },
                secondary: {
                    ...colors.yellow,
                    DEFAULT: colors.yellow[500]
                },
                "surface-variant": "#e1e3e4",
                "on-primary-fixed": "#001e2c",
                "primary-container": "#00bfff",
                "on-error": "#ffffff",
                "surface-bright": "#f8f9fa",
                "tertiary-container": "#93b2ef",
                "secondary-fixed-dim": "#e9c400",
                "inverse-surface": "#2e3132",
                "surface-tint": "#00668a",
                "tertiary": "#3e5e95",
                "on-tertiary-fixed": "#001b3f",
                "error-container": "#ffdad6",
                "primary-fixed-dim": "#7ad0ff",
                "on-surface": "#191c1d",
                "surface-dim": "#d9dadb",
                "on-secondary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "on-primary": "#ffffff",
                "inverse-on-surface": "#f0f1f2",
                "surface-container-highest": "#e1e3e4",
                "surface-container-high": "#e7e8e9",
                "surface-container": "#edeeef",
                "on-primary-fixed-variant": "#004c69",
                "secondary-container": "#fcd400",
                "on-tertiary": "#ffffff",
                "on-tertiary-fixed-variant": "#24467c",
                "on-background": "#191c1d",
                "surface-container-low": "#f3f4f5",
                "on-tertiary-container": "#214479",
                "outline": "#6d7981",
                "secondary-fixed": "#ffe16d",
                "primary-fixed": "#c3e8ff",
                "surface": "#f8f9fa",
                "on-secondary-fixed-variant": "#544600",
                "outline-variant": "#bcc8d1",
                "tertiary-fixed": "#d7e2ff",
                "tertiary-fixed-dim": "#abc7ff",
                "on-secondary-container": "#6e5c00",
                "on-error-container": "#93000a",
                "on-primary-container": "#004a65",
                "inverse-primary": "#7ad0ff",
                "on-surface-variant": "#3d4850",
                "error": "#ba1a1a",
                "on-secondary-fixed": "#221b00",
                "background": "#f8f9fa"
            },
            borderRadius: {
                "DEFAULT": "0.125rem",
                "lg": "0.25rem",
                "xl": "0.5rem",
                "full": "0.75rem"
            },
            spacing: {
                "unit": "8px",
                "margin-desktop": "40px",
                "margin-mobile": "16px",
                "gutter": "24px",
                "container-max": "1280px"
            },
            fontSize: {
                "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                "label-caps": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                "headline-lg-mobile": ["28px", {"lineHeight": "36px", "fontWeight": "700"}],
                "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                "headline-xl": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "800"}]
            },
            animation: {
                aurora: "aurora 60s linear infinite",
            },
            keyframes: {
                aurora: {
                    from: {
                        backgroundPosition: "50% 50%, 50% 50%",
                    },
                    to: {
                        backgroundPosition: "350% 50%, 350% 50%",
                    },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        addVariablesForColors
    ],
};

// This plugin adds each Tailwind color as a global CSS variable, e.g. var(--gray-200).
function addVariablesForColors({ addBase, theme }) {
  let allColors = flattenColorPalette(theme("colors"));
  let newVars = Object.fromEntries(
    Object.entries(allColors).map(([key, val]) => [`--${key}`, val])
  );
 
  addBase({
    ":root": {
        ...newVars,
        "--transparent": "transparent",
        "--black": "#000",
        "--white": "#fff",
    },
  });
}
