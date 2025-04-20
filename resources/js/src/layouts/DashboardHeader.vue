<script setup>
import { inject } from 'vue';
import useHttpRequest from '../composables/useHttpRequest';
import useUserStore from '../store/useUserStore';
import useAppRouter from '../composables/useAppRouter';

const { index: logout } = useHttpRequest('/logout');
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        await pushToRoute({ name: 'login' }); // Navigate to login after logout
    }
};
</script>

<template>
    <header class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 py-3 px-6 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Logo and Title -->
            <RouterLink :to="{ name: 'users' }" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                <div class="bg-white p-2 rounded-full shadow-md">
                    <img src="../assets/logo.png" alt="Logo" class="h-10 w-10" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Maata Banasthali</h1>
                    <span class="text-sm tracking-wider text-white text-opacity-80">Handicrafts</span>
                </div>
            </RouterLink>

            <!-- Profile and Logout -->
            <div class="relative group">
                <button class="flex items-center gap-3 bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-full transition-all duration-200 text-white">
                    <div class="bg-white p-1 rounded-full shadow-sm">
                        <img 
                            src="../assets/logo.png" 
                            alt="Profile" 
                            class="h-7 w-7 rounded-full object-cover" 
                        />
                    </div>
                    <span class="font-medium">{{ userStore.user?.name || 'User' }}</span>
                    <svg 
                        width="16" 
                        height="16" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"
                        class="text-white"
                    >
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div 
                    class="absolute right-0 mt-2 w-48 overflow-hidden bg-white rounded-lg shadow-xl transition-all duration-300 transform origin-top-right opacity-0 invisible group-hover:opacity-100 group-hover:visible"
                >
                    <button
                        @click="onLogout"
                        class="flex items-center w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                        <svg 
                            class="w-5 h-5 mr-3 text-gray-500" 
                            viewBox="0 0 24 24" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="2" 
                            stroke-linecap="round" 
                            stroke-linejoin="round"
                        >
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>