import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(null);
    const isAuthenticated = ref(false);

    const setUser = (userData) => {
        user.value = userData;
        isAuthenticated.value = !!userData;
    };

    const setToken = (newToken) => {
        token.value = newToken;
        if (newToken) {
            localStorage.setItem('token', newToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
        } else {
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    const logout = () => {
        setUser(null);
        setToken(null);
        isAuthenticated.value = false;
    };

    const initialize = async () => {
        const storedToken = localStorage.getItem('token');
        if (storedToken) {
            setToken(storedToken);
            try {
                const response = await axios.get('/api/user');
                setUser(response.data);
            } catch (error) {
                logout();
            }
        }
    };

    return {
        user,
        token,
        isAuthenticated,
        setUser,
        setToken,
        logout,
        initialize
    };
}); 