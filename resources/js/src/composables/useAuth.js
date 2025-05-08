// resources/js/src/composables/useAuth.js
import useHttpRequest from './useHttpRequest';
import useUserStore from '../store/useUserStore';
import { useRouter } from 'vue-router';
import { axios, initializeCsrf } from '../utils/axios';

const useAuth = () => {
    const { index: verify } = useHttpRequest('/auth/verify');
    const { store: loginRequest } = useHttpRequest('/login');
    const userStore = useUserStore();
    const router = useRouter();

    const login = async (credentials) => {
        try {
            // Ensure CSRF token is set
            await initializeCsrf();
            const response = await loginRequest(credentials);
            console.log('useAuth login response:', response); // Debug log
            const user = response?.data?.user || response?.user || response;
            if (user?.id && (response?.token || user?.token)) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.token || user.token}`;
                userStore.setUser({ ...user, token: response.token || user.token });
                const isAuthenticated = await isUserAuthenticated();
                if (isAuthenticated) {
                    return user;
                }
                throw new Error('Authentication verification failed');
            }
            return null;
        } catch (error) {
            console.error('Login failed:', error);
            throw error;
        }
    };

    const isUserAuthenticated = async () => {
        try {
            const user = await verify();
            console.log('isUserAuthenticated response:', user); // Debug log
            if (!Array.isArray(user) && user?.id) {
                userStore.setUser(user);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Authentication check failed:', error);
            return false;
        }
    };

    const logout = async () => {
        try {
            // Ensure CSRF token is set
            await initializeCsrf();
            await axios.post('/logout');
            userStore.setUser(null);
            delete axios.defaults.headers.common['Authorization'];
            router.push('/login');
        } catch (error) {
            console.error('Logout failed:', error);
            throw error;
        }
    };

    return {
        login,
        isUserAuthenticated,
        logout,
    };
};

export default useAuth;