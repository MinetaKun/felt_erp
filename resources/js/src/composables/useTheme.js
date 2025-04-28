import { ref } from 'vue';

export function useTheme() {
    const theme = ref('light');

    const setTheme = (newTheme) => {
        theme.value = newTheme;
        document.documentElement.classList.remove('dark', 'light');
        document.documentElement.classList.add(newTheme);
        localStorage.setItem('theme', newTheme);
    };

    const initTheme = () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);
    };

    return {
        theme,
        setTheme,
        initTheme,
    };
}
