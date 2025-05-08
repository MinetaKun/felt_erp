// resources/js/src/utils/axios.js
import axios from 'axios';

axios.defaults.baseURL = '/api';
axios.defaults.withCredentials = true;

// Fetch CSRF token for stateful POST requests
const initializeCsrf = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie', {
            baseURL: '/', // Override baseURL for CSRF endpoint
        });
    } catch (error) {
        console.error('Failed to fetch CSRF token:', error);
    }
};

export { axios, initializeCsrf };