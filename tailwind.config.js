import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            {
                light: {
                    "primary": "#6366f1",
                    "primary-content": "#ffffff",
                    "secondary": "#8b5cf6",
                    "accent": "#06b6d4",
                    "neutral": "#374151",
                    "base-100": "#f8fafc",
                    "base-200": "#f1f5f9",
                    "base-300": "#e2e8f0",
                    "info": "#3b82f6",
                    "success": "#22c55e",
                    "warning": "#f59e0b",
                    "error": "#ef4444",
                },
                dark: {
                    "primary": "#818cf8",
                    "primary-content": "#ffffff",
                    "secondary": "#a78bfa",
                    "accent": "#22d3ee",
                    "neutral": "#1f2937",
                    "base-100": "#0f172a",
                    "base-200": "#1e293b",
                    "base-300": "#334155",
                    "info": "#60a5fa",
                    "success": "#4ade80",
                    "warning": "#fbbf24",
                    "error": "#f87171",
                },
            },
        ],
        darkTheme: "dark",
    },
};
