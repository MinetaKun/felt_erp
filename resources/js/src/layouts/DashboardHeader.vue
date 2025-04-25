<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { inject } from 'vue';
import useHttpRequest from '../composables/useHttpRequest';
import useUserStore from '../store/useUserStore';
import useAppRouter from '../composables/useAppRouter';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const { index: logout } = useHttpRequest('/logout');
const { pushToRoute } = useAppRouter();
const userStore = useUserStore();
const authStore = useAuthStore();

// Notification state
const notifications = ref([]);
const unreadCount = ref(0);
const showNotifications = ref(false);

// Fetch notifications
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = Array.isArray(response.data.data) ? response.data.data : [];
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error('Error fetching notifications:', error);
        notifications.value = [];
        unreadCount.value = 0;
    }
};

// Handle new notification
const handleNewNotification = (notification) => {
    // Format the notification data to match our expected structure
    const formattedNotification = {
        id: notification.id,
        data: notification.data || {},
        read_at: null,
        created_at: notification.created_at || new Date().toISOString(),
        title: notification.title || 'New Notification',
        message: notification.message || '',
        type: notification.type || 'default'
    };

    // Add the new notification to the beginning of the array
    notifications.value.unshift(formattedNotification);
    unreadCount.value++;
};

// Mark notification as read
const markAsRead = async (notification) => {
    try {
        await axios.post(`/notifications/${notification.id}/mark-as-read`);
        notification.read_at = new Date().toISOString();
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

// Mark all notifications as read
const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-as-read');
        notifications.value.forEach(notification => {
            notification.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

// Initialize auth store and notification listener
onMounted(() => {
    fetchNotifications();
    
    if (window.Echo && authStore.user?.id) {
        // Listen for private notifications on the user's channel
        window.Echo.private(`App.Models.User.${authStore.user.id}`)
            .notification((notification) => {
                handleNewNotification(notification);
            });
    }
});

onUnmounted(() => {
    if (window.Echo && authStore.user?.id) {
        window.Echo.leave(`App.Models.User.${authStore.user.id}`);
    }
});

const onLogout = async () => {
    const isLoggedOut = await logout();
    if (isLoggedOut) {
        userStore.setUser(null);
        await pushToRoute({ name: 'login' });
    }
};

// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString();
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

            <!-- Right side items -->
            <div class="flex items-center gap-4">
                <!-- Notifications -->
                <div class="relative">
                    <button 
                        @click="showNotifications = !showNotifications"
                        class="relative p-2 text-white hover:bg-white/10 rounded-full transition-colors"
                    >
                        <svg 
                            class="w-6 h-6" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        <span 
                            v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ unreadCount }}
                        </span>
                    </button>

                    <!-- Notifications dropdown -->
                    <div 
                        v-if="showNotifications"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50"
                    >
                        <div class="p-4 border-b">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-gray-900">Notifications</h3>
                                <button 
                                    v-if="unreadCount > 0"
                                    @click="markAllAsRead"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    Mark all as read
                                </button>
                            </div>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto">
                            <div 
                                v-if="notifications.length === 0"
                                class="p-4 text-center text-gray-500"
                            >
                                No notifications
                            </div>
                            
                            <div 
                                v-for="notification in notifications" 
                                :key="notification.id"
                                class="p-4 border-b hover:bg-gray-50 transition-colors cursor-pointer"
                                :class="{ 'bg-gray-50': !notification.read_at }"
                                @click="!notification.read_at && markAsRead(notification)"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">{{ notification.data.message }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <p class="text-xs text-gray-500">
                                                {{ formatDate(notification.created_at) }}
                                            </p>
                                            <span 
                                                v-if="!notification.read_at"
                                                class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full"
                                            >
                                                New
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
        </div>
    </header>
</template>