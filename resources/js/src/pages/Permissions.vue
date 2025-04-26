<script setup>
import { ref, computed } from 'vue';
import usePermissionStore from '../store/usePermissionStore';
import useSlider from '../composables/useSlider';
import useModalToast from '../composables/useModalToast';
import useHttpRequest from '../composables/useHttpRequest';
import CreateButton from '../components/ui/CreateButton.vue';
import PermissionSlider from '../components/page/PermissionSlider.vue';

const permissionStore = usePermissionStore();
const searchQuery = ref('');

// Load permissions if not already loaded
if (!permissionStore.permissions?.length) await permissionStore.loadPermissions();

const { slider, sliderData, showSlider, hideSlider } = useSlider('permission-crud');
const { showConfirmModal, showToast } = useModalToast();
const { destroy: deletePermission, deleting } = useHttpRequest('/permissions');

// Filtered permissions based on search query
const filteredPermissions = computed(() => {
  if (!searchQuery.value) return permissionStore.permissions;
  
  const query = searchQuery.value.toLowerCase();
  return permissionStore.permissions.filter(permission => 
    permission.name?.toLowerCase().includes(query) || 
    permission.description?.toLowerCase().includes(query)
  );
});

const onDelete = (permission) => {
  if (deleting.value) return;
  
  showConfirmModal({
    title: 'Delete Permission',
    message: `Are you sure you want to delete "${permission?.name}"?`,
    confirmText: 'Delete',
    cancelText: 'Cancel',
    confirmVariant: 'danger'
  }, async (confirmed) => {
    if (!confirmed) return;
    
    const isDeleted = await deletePermission(permission?.id);
    if (isDeleted) {
      showToast({
        type: 'success',
        message: `"${permission?.name}" deleted successfully.`,
        duration: 3000
      });
      permissionStore.loadPermissions();
    }
  });
};
</script>

<template>
  <div class="w-full space-y-6 py-6 px-8">
    <!-- Header with Add New Button and Search -->
    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-lg shadow-lg p-6">
      <div class="flex flex-wrap justify-between items-center gap-4">
        <div class="flex flex-col">
          <h2 class="text-2xl font-bold text-white">
            <span class="text-indigo-200">Permissions</span> Management
          </h2>
          <p class="text-indigo-100 text-sm mt-1">Manage system permissions and access controls</p>
        </div>
        
        <div class="flex items-center gap-4">
          <!-- Search Box -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search permissions..."
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
            label="Add Permission"
            icon="Key"
            class="bg-emerald-500 hover:bg-emerald-600 text-white"
          />
        </div>
      </div>
    </div>

    <!-- Permissions Count Card -->
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex items-center gap-3">
        <div class="bg-blue-100 p-2 rounded-full">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <span class="text-gray-500 text-sm">Total Permissions</span>
          <p class="text-xl font-bold text-gray-800">{{ permissionStore.permissions.length }}</p>
        </div>
      </div>
    </div>

    <!-- Permissions Table -->
    <div class="w-full overflow-hidden rounded-xl shadow-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 to-blue-400 text-white">
              <th class="py-4 px-6 text-left font-semibold">ID</th>
              <th class="py-4 px-6 text-left font-semibold">Name</th>
              <th class="py-4 px-6 text-center font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="permission in filteredPermissions" 
              :key="permission.id"
              class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-150"
            >
              <!-- ID -->
              <td class="py-4 px-6">
                <span class="text-sm font-semibold text-gray-700 bg-gray-100 py-1 px-3 rounded-full">#{{ permission?.id }}</span>
              </td>
              
              <!-- Name -->
              <td class="py-4 px-6">
                <div class="font-medium text-gray-800">{{ permission?.name }}</div>
              </td>
              
              <!-- Description -->
              
              
              <!-- Actions -->
              <td class="py-4 px-6">
                <div class="flex justify-center gap-2">
                  <button 
                    @click="showSlider(true, permission)"
                    class="bg-amber-500 hover:bg-amber-600 text-white p-2 rounded-lg transition-all duration-150 flex items-center"
                    title="Edit Permission"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="onDelete(permission)"
                    class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition-all duration-150 flex items-center"
                    title="Delete Permission"
                    :disabled="deleting"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            
            <!-- Empty state when no permissions found -->
            <tr v-if="filteredPermissions.length === 0">
              <td colspan="4" class="py-8 text-center">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <p class="text-gray-500 text-lg font-medium">No permissions found</p>
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

    <!-- Permission Slider for Create/Edit -->
    <PermissionSlider
      :show="slider"
      :permission="sliderData"
      @hide="hideSlider"
      @permission-saved="permissionStore.loadPermissions()"
    />
  </div>
</template>

<style scoped>
/* Reuse the same styles as the users page */
</style>