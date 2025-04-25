<template>
  <div class="fixed top-4 right-4 z-50">
    <div v-for="notification in notifications" :key="notification.id" 
         class="mb-2 p-4 rounded-lg shadow-lg"
         :class="getNotificationClass(notification.type)">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <i :class="getNotificationIcon(notification.type)" class="h-6 w-6"></i>
        </div>
        <div class="ml-3 w-0 flex-1">
          <p class="text-sm font-medium text-gray-900">
            {{ notification.message }}
          </p>
          <p class="mt-1 text-xs text-gray-500">
            {{ formatDate(notification.created_at) }}
          </p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
          <button @click="removeNotification(notification.id)" 
                  class="inline-flex text-gray-400 hover:text-gray-500">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default {
  setup() {
    const notifications = ref([]);

    const getNotificationClass = (type) => {
      switch (type) {
        case 'new_order':
          return 'bg-blue-50 border-l-4 border-blue-400';
        case 'order_assigned':
          return 'bg-green-50 border-l-4 border-green-400';
        case 'order_dispatched':
          return 'bg-purple-50 border-l-4 border-purple-400';
        case 'status_change':
          return 'bg-yellow-50 border-l-4 border-yellow-400';
        default:
          return 'bg-gray-50 border-l-4 border-gray-400';
      }
    };

    const getNotificationIcon = (type) => {
      switch (type) {
        case 'new_order':
          return 'fas fa-shopping-cart text-blue-400';
        case 'order_assigned':
          return 'fas fa-user-tie text-green-400';
        case 'order_dispatched':
          return 'fas fa-truck text-purple-400';
        case 'status_change':
          return 'fas fa-exchange-alt text-yellow-400';
        default:
          return 'fas fa-bell text-gray-400';
      }
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleTimeString();
    };

    const removeNotification = (id) => {
      notifications.value = notifications.value.filter(n => n.id !== id);
    };

    onMounted(() => {
      // Initialize Laravel Echo
      window.Pusher = Pusher;
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true
      });

      // Listen for order notifications
      window.Echo.private(`App.Models.User.${window.authUser.id}`)
        .notification((notification) => {
          notifications.value.push({
            id: Date.now(),
            ...notification
          });

          // Remove notification after 5 seconds
          setTimeout(() => {
            removeNotification(notification.id);
          }, 5000);
        });
    });

    onUnmounted(() => {
      // Clean up Echo instance
      if (window.Echo) {
        window.Echo.leave(`App.Models.User.${window.authUser.id}`);
      }
    });

    return {
      notifications,
      getNotificationClass,
      getNotificationIcon,
      formatDate,
      removeNotification
    };
  }
};
</script> 