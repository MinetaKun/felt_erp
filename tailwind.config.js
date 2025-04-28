/** @type {import('tailwindcss').Config} */
import colors from 'tailwindcss/colors';
import animationDelay from 'tailwindcss-animation-delay';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            screens: {
                xs: '470px',
                mobile: '420px',
            },
            fontFamily: {
                'cormorant': ['Cormorant Garamond', 'serif'],
                'poppins': ['Poppins', 'sans-serif'],
            },
            fontSize: {
                xsm: '13px',
                '2xs': '11px',
                xxs: '10px',
            },
            colors: {
                'light-bg': '#F5F0E6',
                'light-color': '#213547',
                active: {
                    DEFAULT: colors.emerald[500],
                    light: colors.emerald[700],
                    dark: colors.emerald[500],
                    hover: colors.emerald[200],
                },
                'earthy-brown': '#8B5E3C',
                'earthy-brown-dark': '#6F4A2F',
                'saffron-gold': '#FFA500',
                'soft-beige': '#F5F0E6',
                'olive-green': '#6B8E23',
                'charcoal': '#333333',
                'warm-gray': '#666666',
            },
            transitionProperty: {
                width: 'width',
                height: 'height',
                'max-height': 'max-height',
            },
            boxShadow: {
                google: '0px 8px 10px 1px rgba(0, 0, 0, 0.14), 0px 3px 14px 2px rgba(0, 0, 0, 0.12), 0px 5px 5px -3px rgba(0, 0, 0, 0.2)',
                'google-sm': '0px 2px 2px 1px rgba(0, 0, 0, 0.14), 0px 2px 2px 2px rgba(0, 0, 0, 0.12), 0px 2px 2px 0px rgba(0, 0, 0, 0.2)',
            },
            borderRadius: {
                sm: '4px',
            },
            animationDelay: {
                100: '100ms',
                200: '200ms',
                300: '300ms',
                400: '400ms',
                500: '500ms',
                600: '600ms',
                700: '700ms',
                800: '800ms',
                900: '900ms',
                1000: '1000ms',
                1100: '1100ms',
                1200: '1200ms',
                1300: '1300ms',
                1400: '1400ms',
                1500: '1500ms',
                1600: '1600ms',
            },
        },
    },
    plugins: [animationDelay],
};
