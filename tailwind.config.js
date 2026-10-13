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
                serif: ['Lora', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // UML Muhammadiyah green (primary)
                brand: {
                    50: '#eff8f0',
                    100: '#d7eeda',
                    200: '#b0ddb7',
                    300: '#7fc58b',
                    400: '#4dad62',
                    500: '#2a9244',
                    600: '#1d7635',
                    700: '#195e2d',
                    800: '#174b27',
                    900: '#143e22',
                    950: '#08230f',
                },
                gold: {
                    50: '#fbf8f1',
                    100: '#f5edd9',
                    200: '#ead8b0',
                    300: '#dcbd7e',
                    400: '#cfa257',
                    500: '#c8a45c',
                    600: '#b08842',
                    700: '#926d37',
                    800: '#785931',
                    900: '#654b2c',
                },
            },
        },
    },

    plugins: [forms],
};
