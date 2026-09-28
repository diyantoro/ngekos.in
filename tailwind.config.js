import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef6f3',
                    100: '#d7ebe3',
                    200: '#b0d7c9',
                    300: '#80bda9',
                    400: '#4d9d88',
                    500: '#2b8170',
                    600: '#14685c',
                    700: '#0f5450',
                    800: '#0e4341',
                    900: '#0c3835',
                    950: '#04201f',
                },
                paper: '#f6f4ef',
            },
            boxShadow: {
                'card': '0 1px 2px 0 rgb(15 23 42 / 0.05)',
                'card-hover': '0 4px 12px -2px rgb(15 23 42 / 0.08)',
            },
            animation: {
                'fade-in': 'fadeIn 0.4s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
        },
    },

    plugins: [forms],
};
