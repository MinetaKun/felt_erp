// resources/js/src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import useAuth from '../composables/useAuth';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const { isUserAuthenticated } = useAuth();

    if (to.name === from.name && to.path === from.path) {
        return next();
    }

    const isAuthenticated = await isUserAuthenticated();
    console.log('Navigation guard - isAuthenticated:', isAuthenticated); // Debug log

    if (isAuthenticated && to.name === 'login') {
        return next({ name: 'dashboard' });
    }

    if (!isAuthenticated && to.name !== 'login') {
        return next({ name: 'login' });
    }

    return next();
});

export default router;