<script setup>
import { ref, computed } from 'vue';
import { Search, UserPlus, Edit, Trash2, Phone, Mail, User } from 'lucide-vue-next';
import useUserStore from '../store/useUserStore';
import useRoleStore from '../store/useRoleStore';
import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';
import CreateButton from '../components/ui/CreateButton.vue';
import UserSlider from '../components/page/UserSlider.vue';

const userStore = useUserStore();
const roleStore = useRoleStore();
const searchQuery = ref('');

// Load users and roles if not already loaded
if (!userStore.users?.length) await userStore.loadUsers();
if (!roleStore.roles?.length) await roleStore.loadRoles();

const { slider, sliderData, showSlider, hideSlider } = useSlider('user-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteUser, deleting } = useHttpRequest('/users');

// Filtered users based on search query
const filteredUsers = computed(() => {
  if (!searchQuery.value) return userStore.users;
  
  const query = searchQuery.value.toLowerCase();
  return userStore.users.filter(user => 
    user.name?.toLowerCase().includes(query) || 
    user.email?.toLowerCase().includes(query)
  );
});

const onDelete = (user) => {
  if (deleting.value) return;
  
  showConfirmModal({
    title: 'Delete User',
    message: `Are you sure you want to delete "${user?.name}"?`,
    confirmText: 'Delete',
    cancelText: 'Cancel',
    confirmVariant: 'danger'
  }, async (confirmed) => {
    if (!confirmed) return;
    
    const isDeleted = await deleteUser(user?.id);
    if (isDeleted) {
      showToast({
        type: 'success',
        message: `"${user?.name}" deleted successfully.`,
        duration: 3000
      });
      userStore.loadUsers();
    }
  });
};

// Get initials from name
const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

// Get random color for user avatar
const getAvatarColor = (userId) => {
  const colors = [
    'bg-blue-500', 'bg-green-500', 'bg-purple-500', 
    'bg-pink-500', 'bg-yellow-500', 'bg-indigo-500'
  ];
  return colors[userId % colors.length];
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <header class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-lg shadow-lg p-6">
          <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div class="flex flex-col">
              <h1 class="text-3xl font-extrabold text-white">
                User <span class="text-indigo-200">Management</span>
              </h1>
              <p class="text-indigo-100 text-sm mt-1">Manage system users and their roles</p>
            </div>
            
            <div class="flex flex-col md:flex-row items-center space-y-3 md:space-y-0 md:space-x-4 w-full md:w-auto">
              <!-- Search Input with Enhanced Styling -->
              <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search class="w-5 h-5 text-indigo-300" />
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search users by name or email..."
                  class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-indigo-300 bg-white/10 backdrop-blur-sm text-white placeholder-indigo-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-all duration-300 text-sm"
                />
              </div>
              
              <!-- Add User Button -->
              <CreateButton 
                @click="showSlider(true)" 
                label="Add User"
                :icon="UserPlus"
                class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg transition-colors duration-300"
              />
            </div>
          </div>
        </div>
      </header>

      <!-- Users Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center space-x-4">
            <div class="bg-blue-100 p-3 rounded-full">
              <User class="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <p class="text-gray-500 text-sm font-medium">Total Users</p>
              <p class="text-2xl font-bold text-gray-800">{{ userStore.users.length }}</p>
            </div>
          </div>
        </div>

        <!-- Active Users Card -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center space-x-4">
            <div class="bg-green-100 p-3 rounded-full">
              <User class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p class="text-gray-500 text-sm font-medium">Active Users</p>
              <p class="text-2xl font-bold text-gray-800">
                {{ userStore.users.filter(u => u.is_active).length }}
              </p>
            </div>
          </div>
        </div>

        <!-- Roles Overview Card -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
          <div class="flex items-center space-x-4">
            <div class="bg-purple-100 p-3 rounded-full">
              <User class="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p class="text-gray-500 text-sm font-medium">Total Roles</p>
              <p class="text-2xl font-bold text-gray-800">{{ roleStore.roles.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr 
                v-for="user in filteredUsers" 
                :key="user.id"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- User Profile -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div v-if="user?.profile_photo" class="relative">
                        <img 
                          v-if="user?.profile_photo_url" 
                          :src="user.profile_photo_url" 
                          alt="Profile" 
                          class="h-10 w-10 rounded-full object-cover border-2 border-blue-200"
                        />
                        <div class="absolute -bottom-1 -right-1 bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>
                      </div>
                      <div 
                        v-else 
                        :class="[getAvatarColor(user.id), 'h-10 w-10 rounded-full flex items-center justify-center']"
                      >
                        <span class="text-white font-medium text-sm">{{ getInitials(user?.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user?.name }}</div>
                      <div class="text-sm text-gray-500">{{ user?.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Contact Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-2">
                    <Mail class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ user?.email }}</span>
                  </div>
                  <div v-if="user?.phone_number" class="flex items-center space-x-2 mt-1">
                    <Phone class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">{{ user?.phone_number }}</span>
                  </div>
                </td>

                <!-- Roles -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1.5">
                    <span 
                      v-for="role in user.roles" 
                      :key="role.id"
                      class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full"
                    >
                      {{ role?.name }}
                    </span>
                    <span v-if="!user.roles?.length" class="text-gray-400 text-xs italic">No roles</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex justify-center space-x-2">
                    <button 
                      @click="showSlider(true, user)"
                      class="text-amber-500 hover:bg-amber-50 p-2 rounded-lg transition-all duration-150"
                      title="Edit User"
                    >
                      <Edit class="w-5 h-5" />
                    </button>
                    <button 
                      @click="onDelete(user)"
                      class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-all duration-150"
                      title="Delete User"
                      :disabled="deleting"
                    >
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="filteredUsers.length === 0">
                <td colspan="5" class="px-6 py-20 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No users found</p>
                    <p v-if="searchQuery" class="text-gray-400 text-sm mt-1">
                      Try adjusting your search criteria
                    </p>
                    <button 
                      v-if="searchQuery" 
                      @click="searchQuery = ''"
                      class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                    >
                      Clear search
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- User Slider for Create/Edit -->
      <UserSlider
        :show="slider"
        :user="sliderData"
        @hide="hideSlider"
        @user-saved="userStore.loadUsers()"
      />
    </div>
  </div>
</template>

<style scoped>
/* Smooth scrollbar for table */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #d1d5db #f3f4f6;
}

.overflow-x-auto::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>