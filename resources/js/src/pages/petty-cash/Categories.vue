<template>
  <div class="container mx-auto py-4">
    <div class="bg-white shadow-md rounded-lg">
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Petty Cash Categories</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your petty cash categories efficiently</p>
        </div>
        <button 
          @click="openCreateModal" 
          class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
        >
          <i class="fas fa-plus mr-2"></i> Add Category
        </button>
      </div>
      <div class="p-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gray-200 text-gray-700 uppercase text-xs font-semibold">
              <tr>
                <th class="p-3">Name</th>
                <th class="p-3">Type</th>
                <th class="p-3">Description</th>
                <th class="p-3">Status</th>
                <th class="p-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 transition duration-200">
                <td class="p-3">{{ category.name }}</td>
                <td class="p-3">
                  <span :class="['inline-block px-2 py-1 text-xs font-semibold rounded-full', getTypeClass(category.type)]">
                    {{ formatType(category.type) }}
                  </span>
                </td>
                <td class="p-3">{{ category.description || '-' }}</td>
                <td class="p-3">
                  <span :class="['inline-block px-2 py-1 text-xs font-semibold rounded-full', category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                    {{ category.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="p-3">
                  <div class="flex space-x-2">
                    <button @click="openEditModal(category)" class="text-blue-600 hover:text-blue-800 transition duration-300">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button @click="confirmDelete(category.id)" class="text-red-600 hover:text-red-800 transition duration-300">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-5 4v6m4-6v6"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!categories.length">
                <td colspan="5" class="text-center py-4 text-gray-500">No categories found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal unchanged except for submit handling -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h5 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Edit Category' : 'Create Category' }}</h5>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <form @submit.prevent="handleSubmit">
            <!-- Form fields unchanged -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input
                type="text"
                v-model="formData.name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.name }"
                required
              >
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Type *</label>
              <select
                v-model="formData.type"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.type }"
                required
              >
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
              <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type[0] }}</p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                v-model="formData.description"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                rows="3"
              ></textarea>
            </div>
            <div class="mb-4 flex items-center">
              <input
                type="checkbox"
                v-model="formData.is_active"
                id="activeCheck"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="activeCheck" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="closeModal" 
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="isSubmitting"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting" class="inline-block animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                {{ isEditing ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2'

export default {
  setup() {
    const categories = ref([]);
    const showModal = ref(false);
    const isEditing = ref(false);
    const isSubmitting = ref(false);
    const errors = ref({});
    
    const formData = ref({
      name: '',
      type: 'expense',
      description: '',
      is_active: true
    });

    const currentCategoryId = ref(null);

    const fetchCategories = async () => {
      try {
        console.log('Fetching categories from /petty-cash/categories...');
        const response = await axios.get('/petty-cash/categories', {
          headers: {
            'Accept': 'application/json'
          }
        });
        console.log('Response status:', response.status);
        console.log('Response data:', response.data);
        if (Array.isArray(response.data)) {
          categories.value = response.data;
          console.log('Categories assigned:', categories.value);
        } else {
          console.error('Response data is not an array:', response.data);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Unexpected response format from server.',
            timer: 3000,
            showConfirmButton: false
          });
        }
      } catch (error) {
        console.error('Error fetching categories:', error);
        if (error.response) {
          console.error('Status:', error.response.status);
          console.error('Response data:', error.response.data);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.response.data.message || 'Failed to load categories. Please try again.',
            timer: 3000,
            showConfirmButton: false
          });
        } else if (error.request) {
          console.error('No response received:', error.request);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to load categories: No response from server',
            timer: 3000,
            showConfirmButton: false
          });
        } else {
          console.error('Error details:', error.message);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message,
            timer: 3000,
            showConfirmButton: false
          });
        }
      }
    };

    const getTypeClass = (type) => {
      if (!type) return 'bg-gray-500 text-white';
      return type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    };

    const formatType = (type) => {
      if (!type) return 'UNKNOWN';
      return type.toUpperCase();
    };

    const openCreateModal = () => {
      isEditing.value = false;
      resetForm();
      showModal.value = true;
    };

    const openEditModal = (category) => {
      isEditing.value = true;
      currentCategoryId.value = category.id;
      formData.value = { ...category };
      showModal.value = true;
    };

    const handleSubmit = async () => {
      isSubmitting.value = true;
      errors.value = {};

      try {
        if (isEditing.value) {
          await axios.put(`/petty-cash/categories/${currentCategoryId.value}`, formData.value);
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Category updated successfully',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          await axios.post('/petty-cash/categories', formData.value);
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Category created successfully',
            timer: 2000,
            showConfirmButton: false
          });
        }
        await fetchCategories();
        closeModal();
      } catch (error) {
        if (error.response?.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred while processing your request.',
            timer: 3000,
            showConfirmButton: false
          });
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    const confirmDelete = async (id) => {
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/petty-cash/categories/${id}`);
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Category has been deleted.',
            timer: 2000,
            showConfirmButton: false
          });
          await fetchCategories();
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to delete category.',
            timer: 3000,
            showConfirmButton: false
          });
        }
      }
    };

    const resetForm = () => {
      formData.value = {
        name: '',
        type: 'expense',
        description: '',
        is_active: true
      };
      currentCategoryId.value = null;
      errors.value = {};
    };

    const closeModal = () => {
      showModal.value = false;
      resetForm();
    };

    onMounted(fetchCategories);

    return {
      categories,
      showModal,
      isEditing,
      isSubmitting,
      formData,
      errors,
      getTypeClass,
      formatType,
      openCreateModal,
      openEditModal,
      handleSubmit,
      confirmDelete,
      closeModal
    };
  }
};
</script>

<style scoped>
/* Optional custom styles if needed */
</style>