<script setup>
import { ref } from 'vue';
import FormInput from '../components/ui/FormInput.vue';
import Button from '../components/ui/Button.vue';
import useHttpRequest from '../composables/useHttpRequest';
import useValidation from '../composables/useValidation';
import useAppRouter from '../composables/useAppRouter';
import useUserStore from '../store/useUserStore';
const { store: login, saving: loggingIn } = useHttpRequest('/login');
const { runYupValidation } = useValidation();
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
import { string, object } from 'yup';

const formData = ref({
    email: null,
    password: null,
});

const formErrors = ref({});

const schema = object().shape({
    email: string().email().nullable().required(),
    password: string().nullable().required(),
});

const onSignIn = async () => {
    if (loggingIn.value) return;
    const { validated, data, errors } = await runYupValidation(
        schema,
        formData.value,
    );
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};
    const user = await login(data);
    if (user?.id) {
        userStore.setUser(user);
        await pushToRoute({ name: 'users' });
    }
};
</script>
<template>
    <section class="h-screen flex items-center justify-center">
        <div class="w-full max-w-3xl mx-auto shadow-lg rounded-lg overflow-hidden flex">
            <!-- Left side (earthy brown section) -->
            <div class="w-1/2 bg-earthy-brown p-8 flex flex-col justify-center">
                <img src="../assets/logo.png" alt="Company Logo" class="w-32 h-auto mb-6 mx-auto" />
                <h1 class="text-2xl font-semibold mb-4 text-white font-cormorant">Welcome to Maata Banasthali</h1>
                <p class="text-2xl font-semibold mb-4 text-white font-cormorant">"Artisans at Heart, Quality by Hand." </p>
                <p class="text-sm text-white opacity-90 font-poppins">Manage your handicraft production with ease. Track orders, materials, and artisans in one place.</p>
            </div>
            
            <!-- Right side (form) -->
            <div class="w-1/2 bg-white p-8 flex flex-col justify-center">
                <h2 class="text-xl font-semibold mb-6 text-charcoal font-cormorant">Sign in to your account</h2>
                
                <div class="space-y-4">
                    <div>
                        <FormInput
                            v-model="formData.email"
                            label="Email"
                            :error="formErrors?.email"
                            placeholder="Enter your email"
                            class="w-full text-sm font-poppins"
                        />
                    </div>
                    
                    <div>
                        <FormInput
                            v-model="formData.password"
                            label="Password"
                            type="password"
                            :error="formErrors?.password"
                            placeholder="Enter your password"
                            class="w-full text-sm font-poppins"
                        />
                    </div>
                    
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