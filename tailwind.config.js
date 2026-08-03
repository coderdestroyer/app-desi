import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'green-dark': '#255d3e',
                'green-main': '#2f6b48',
                'green-soft': '#eaf7ef',
                'green-pale': '#f6fbf7',
                'yellow-main': '#f4cf63',
                'yellow-soft': '#fff8e6',
                'navy': '#14213d',
                'text-dark': '#243042',
                'text-soft': '#667085',
                'border-soft': '#e5e7eb',
                'bg-main': '#f8faf8',
            },
            keyframes: {
                modalAppear: {
                    'from': {
                        opacity: '0',
                        transform: 'translateY(10px) scale(0.97)',
                    },
                    'to': {
                        opacity: '1',
                        transform: 'translateY(0) scale(1)',
                    },
                },
                kbliCategoryFocus: {
                    '0%, 100%': {
                        boxShadow: 'inset 0 0 0 0 rgba(5, 150, 105, 0)',
                        background: 'rgba(248, 250, 252, 0.8)',
                    },
                    '35%, 70%': {
                        boxShadow: 'inset 5px 0 0 0 rgb(5, 150, 105)',
                        background: 'rgba(209, 250, 229, 0.75)',
                    },
                },
                kbkiSectionFocus: {
                    '0%, 100%': {
                        boxShadow: 'inset 0 0 0 0 rgba(5, 150, 105, 0)',
                        background: 'rgba(248, 250, 252, 0.8)',
                    },
                    '35%, 70%': {
                        boxShadow: 'inset 5px 0 0 0 rgb(5, 150, 105)',
                        background: 'rgba(209, 250, 229, 0.75)',
                    },
                },
            },
            animation: {
                modalAppear: 'modalAppear 0.2s ease-out',
                kbliCategoryFocus: 'kbliCategoryFocus 1.6s ease',
                kbkiSectionFocus: 'kbkiSectionFocus 1.6s ease',
            },
        },
    },

    plugins: [forms],
};
