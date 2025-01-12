import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Arial', 'sans-serif'],
                heading: ['Montserrat', 'Arial', 'sans-serif'],
            },
            colors: {
                primary: {
                  DEFAULT: "#6A994E", // Earthy Green
                  dark: "#2F5233", // Deep Forest Green
                },
                secondary: {
                  DEFAULT: "#FFB563", // Warm Orange
                },
                neutral: {
                  light: "#F3E9DC", // Neutral Beige
                  DEFAULT: "#8D99AE", // Soft Grey
                },
                accent: {
                  yellow: "#F0C808", // Muted Yellow
                },
                text: {
                  DEFAULT: "#333333", // Dark Charcoal for typography
                },
         },
     },
    },
    plugins: [],
};
