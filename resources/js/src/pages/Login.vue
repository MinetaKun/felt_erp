<!-- resources/js/src/pages/Login.vue -->
<script setup>
import { ref } from 'vue';
import FormInput from '../components/ui/FormInput.vue';
import Button from '../components/ui/Button.vue';
import useHttpRequest from '../composables/useHttpRequest';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';
import { string, object } from 'yup';
import useModalToast from '../composables/useModalToast';
import useAuth from '../composables/useAuth';

const { store: login } = useHttpRequest('/login');
const { runYupValidation } = useValidation();
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const { showToast } = useModalToast();
const { isUserAuthenticated } = useAuth();

const formData = ref({
    email: null,
    password: null,
});

const formErrors = ref({});
const loggingIn = ref(false);

const schema = object().shape({
    email: string().email('Invalid email').nullable().required('Email is required'),
    password: string().nullable().required('Password is required'),
});

const onSignIn = async () => {
    if (loggingIn.value) return;
    loggingIn.value = true;
    const { validated, data, errors } = await runYupValidation(schema, formData.value);
    if (!validated) {
        formErrors.value = errors;
        loggingIn.value = false;
        return;
    }
    formErrors.value = {};
    try {
        const response = await login(data);
        console.log('Login response:', response); // Debug log
        // Handle different response formats
        const user = response?.data?.user || response?.user || response;
        if (user?.id && (response?.token || user?.token)) {
            userStore.setUser({ ...user, token: response.token || user.token });
            showToast('Login successful!');
            const isAuthenticated = await isUserAuthenticated();
            if (isAuthenticated) {
                await pushToRoute({ name: 'dashboard' });
            } else {
                showToast('Authentication state not updated', 'error');
            }
        } else {
            showToast('Login failed: Invalid response format', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('Login failed: Invalid credentials', 'error');
    } finally {
        loggingIn.value = false;
    }
};
</script>

<template>
    <section class="h-screen flex items-center justify-center">
        <div class="w-full max-w-3xl mx-auto shadow-lg rounded-lg overflow-hidden flex">
            <div class="w-1/2 bg-earthy-brown p-8 flex flex-col justify-center">
                <img src="../assets/logo.png" alt="Company Logo" class="w-32 h-auto mb-6 mx-auto" />
                <h1 class="text-2xl font-semibold mb-4 text-white font-cormorant">Welcome to Maata Banasthali</h1>
                <p class="text-2xl font-semibold mb-4 text-white font-cormorant">"Artisans at Heart, Quality by Hand."</p>
                <p class="text-sm text-white opacity-90 font-poppins">Manage your handicraft production with ease. Track orders, materials, and artisans in one place.</p>
            </div>
            <div class="w-1/2 bg-white p-8 flex flex-col justify-center">
                <h2 class="text-xl font-semibold mb-6 text-charcoal font-cormorant">Sign in to your account</h2>
                <div class="space-y-4">
                    <FormInput
                        v-model="formData.email"
                        label="Email"
                        :error="formErrors?.email"
                        placeholder="Enter your email"
                        class="w-full text-sm font-poppins"
                    />
                    <FormInput
                        v-model="formData.password"
                        label="Password"
                        type="password"
                        :error="formErrors?.password"
                        placeholder="Enter your password"
                        class="w-full text-sm font-poppins"
                    />
                    <Button
                        title="Sign In"
                        class="!w-full !bg-earthy-brown hover:!bg-earthy-brown-dark text-white py-2 rounded text-sm font-medium font-poppins transition duration-300"
                        loading-title="Signing in..."
                        :loading="loggingIn"
                        @click="onSignIn"
                    />
                </div>
            </div>
        </div>
    </section>
</template>