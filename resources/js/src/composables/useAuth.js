import useHttpRequest from './useHttpRequest';
import useUserStore from '../store/useUserStore';
import { useRouter } from 'vue-router';
import axios from 'axios';

const useAuth = () => {
    const { index: verify } = useHttpRequest('/auth/verify');
    const { store: loginRequest } = useHttpRequest('/login');
    const userStore = useUserStore();
    const router = useRouter();

    const login = async (credentials) => {
        try {
            const user = await loginRequest(credentials);
            if (user?.id) {
                userStore.setUser(user);
                return user;
            }
            return null;
        } catch (error) {
            console.error('Login failed:', error);
            throw error;
        }
    };

    const isUserAuthenticated = async () => {
        const user = await verify();
        if (!Array.isArray(user) && user?.id) {
            userStore.setUser(user);
            return true;
        }
        return false;
    };

    const logout = async () => {
        try {
            await axios.post('/logout');
            userStore.setUser(null);
            router.push('/login');
            // Force a page reload to clear any cached state
            window.location.reload();
        } catch (error) {
            console.error('Logout failed:', error);
        }
    };

    return {
        login,
        isUserAuthenticated,
        logout
    };
};

export default useAuth;
