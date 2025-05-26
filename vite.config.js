import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import dotenv from 'dotenv';

export default defineConfig(() => {
    const isDevCommantRunOnClient = process.env.INIT_CWD?.includes('client');

    const envPath = path.join(
        process.env.INIT_CWD,
        `${isDevCommantRunOnClient ? '../' : ''}.env`
    );

    const env = dotenv.config({ path: envPath });

    const frontendEnvs = ['APP_URL'];

    const clientEnv = Object.entries(env.parsed || {}) // null check added here
        .filter(([key, _]) => frontendEnvs.includes(key))
        .reduce(
            (clientEnv, [key, value]) => ({ ...clientEnv, [key]: value }),
            {}
        );

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.js'],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        define: {
            'process.env': clientEnv,
        },
    };
});
