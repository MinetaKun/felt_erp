<script setup>
import { ref, computed } from 'vue';
import useUserStore from '../store/useUserStore';
import useRoleStore from '../store/useRoleStore';
import usePermissionStore from '../store/usePermissionStore';
import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';
import CreateButton from '../components/ui/CreateButton.vue';
import RoleSlider from '../components/page/RoleSlider.vue';

const userStore = useUserStore();
const roleStore = useRoleStore();
const permissionStore = usePermissionStore();
const searchQuery = ref('');

// Load initial data
if (!permissionStore.permissions.length) {
  await permissionStore.loadPermissions();
}
if (!roleStore.roles?.length) {
  await roleStore.loadRoles();
}

const { slider, sliderData, showSlider, hideSlider } = useSlider('role-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deleteRole, deleting } = useHttpRequest('/roles');

// Filtered roles based on search query
const filteredRoles = computed(() => {
  if (!searchQuery.value) return roleStore.roles;
  
  const query = searchQuery.value.toLowerCase();
  return roleStore.roles.filter(role => 
    role.name?.toLowerCase().includes(query)
  );
});

const onDelete = (role) => {
  if (deleting.value) return;
  
  showConfirmModal({
    title: 'Delete Role',
    message: `Are you sure you want to delete "${role?.name}"?`,
    confirmText: 'Delete',
    cancelText: 'Cancel',
    confirmVariant: 'danger'
  }, async (confirmed) => {
    if (!confirmed) return;
    
    const isDeleted = await deleteRole(role?.id);
    if (isDeleted) {
      showToast({
        type: 'success',
        message: `"${role?.name}" deleted successfully.`,
        duration: 3000
      });
      roleStore.loadRoles();
      userStore.loadUsers();
    }
  });
};

// Get random color for role badge
const getRoleColor = (roleId) => {
  const colors = [
    'bg-blue-500', 'bg-purple-500', 'bg-green-500', 
    'bg-indigo-500', 'bg-rose-500', 'bg-amber-500'
  ];
  return colors[roleId % colors.length];
};

// Get the first character for role badge
const getRoleInitial = (name) => {
  if (!name) return 'R';
  return name.charAt(0).toUpperCase();
};

// Count users with a specific role
const getUserCountForRole = (roleId) => {
  return userStore.users.filter(user => 
    user.roles.some(role => role.id === roleId)
  ).length;
};
</script>

<template>
  <div class="w-full space-y-6 py-6 px-8">
    <!-- Header with Add New Button and Search -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-lg shadow-lg p-6">
      <div class="flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-col">
          <h2 class="text-2xl font-bold text-white">
            <span class="text-indigo-200">Roles</span> Management
          </h2>
          <p class="text-indigo-100 text-sm mt-1">Manage system roles and their permissions</p>
        </div>
        
        <div class="flex items-center gap-4">
          <!-- Search Box -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search roles..."
              class="pl-10 pr-4 py-2 rounded-lg border border-indigo-300 bg-white/10 backdrop-blur-sm text-white placeholder-indigo-200 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition-all duration-200 w-64"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
          
          <CreateButton 
            @click="showSlider(true)" 
            label="Add Role"
            icon="ShieldPlus"
            class="bg-emerald-500 hover:bg-emerald-600 text-white"
          />
        </div>
      </div>
    </div>

    <!-- Roles Count Card -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex items-center gap-3">
        <div class="bg-blue-100 p-2 rounded-full">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <div>
          <span class="text-gray-500 text-sm">Total Roles</span>
          <p class="text-xl font-bold text-gray-800">{{ roleStore.roles.length }}</p>
        </div>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="w-full overflow-hidden rounded-xl shadow-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 to-blue-400 text-white">
              <th class="py-4 px-6 text-left font-semibold">ID</th>
              <th class="py-4 px-6 text-left font-semibold">Role</th>
              <th class="py-4 px-6 text-left font-semibold">Name</th>
              <th class="py-4 px-6 text-left font-semibold">Assigned Users</th>
              <th class="py-4 px-6 text-left font-semibold">Permissions</th>
              <th class="py-4 px-6 text-center font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="role in filteredRoles" 
              :key="role.id"
              class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-150"
            >
              <!-- ID -->
              <td class="py-4 px-6">
                <span class="text-sm font-semibold text-gray-700 bg-gray-100 py-1 px-3 rounded-full">#{{ role?.id }}</span>
              </td>
              
              <!-- Role Badge -->
              <td class="py-4 px-6">
                <div class="flex items-center justify-center">
                  <div 
                    :class="[getRoleColor(role.id), 'w-10 h-10 rounded-full flex items-center justify-center']"
                  >
                    <span class="text-white font-medium text-lg">{{ getRoleInitial(role?.name) }}</span>
                  </div>
                </div>
              </td>
              
              <!-- Name -->
              <td class="py-4 px-6">
                <div class="font-medium text-gray-800">{{ role?.name }}</div>
              </td>
              
              <!-- Assigned Users Count -->
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  <span class="text-gray-600 text-sm">{{ getUserCountForRole(role.id) }} users</span>
                </div>
              </td>
              
              <!-- Permissions -->
              <td class="py-4 px-6">
                <div class="flex flex-wrap gap-1.5 max-w-sm">
                  <span 
                    v-for="permission in role.permissions" 
                    :key="permission.id"
                    class="inline-flex items-center bg-gray-100 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full"
                  >
                    {{ permission?.name }}
                  </span>
                  <span v-if="!role.permissions?.length" class="text-gray-400 text-xs italic">No permissions</span>
                </div>
              </td>
              
              <!-- Actions -->
              <td class="py-4 px-6">
                <div class="flex justify-center gap-2">
                  <button 
                    @click="showSlider(true, role)"
                    class="bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-lg transition-all duration-150 flex items-center"
                    title="Edit Role"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="onDelete(role)"
                    class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-all duration-150 flex items-center"
                    title="Delete Role"
                    :disabled="deleting"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            
            <!-- Empty state when no roles found -->
            <tr v-if="filteredRoles.length === 0">
              <td colspan="6" class="py-8 text-center">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                  <p class="text-gray-500 text-lg font-medium">No roles found</p>
                  <p v-if="searchQuery" class="text-gray-400 text-sm mt-1">
                    Try adjusting your search criteria
                  </p>
                  <button 
                    v-if="searchQuery" 
                    @click="searchQuery = ''"
                    class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Clear search
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Role Slider for Create/Edit -->
    <RoleSlider
      :show="slider"
      :role="sliderData"
      @hide="hideSlider"
    />
  </div>
</template>

<style scoped>
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}
thead {
  position: sticky;
  top: 0;
  z-index: 10;
}
th {
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 13px;
}
tr:last-child td:first-child {
  border-bottom-left-radius: 8px;
}
tr:last-child td:last-child {
  border-bottom-right-radius: 8px;
}
/* Smooth hover transition for buttons */
button {
  transition: all 0.2s ease;
}
/* Scrollbar styling */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
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