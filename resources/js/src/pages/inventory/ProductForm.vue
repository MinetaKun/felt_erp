<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
          <h2 class="text-2xl font-bold text-white">
            {{ isEditing ? 'Edit Product' : 'Create New Product' }}
          </h2>
        </div>

        <!-- Form -->
        <div class="p-6">
          <form @submit.prevent="saveProduct" class="space-y-6">
            <!-- Name and Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input 
                  type="text" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  v-model="form.name" 
                  required
                  placeholder="Enter product name"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <div class="relative rounded-md shadow-sm">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input 
                    type="number" 
                    class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    v-model="form.price" 
                    required
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                  >
                </div>
              </div>
            </div>

            <!-- Quantity and Size -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input 
                  type="number" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  v-model="form.quantity" 
                  required
                  min="0"
                  placeholder="Enter quantity"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                <input 
                  type="text" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  v-model="form.size" 
                  required
                  placeholder="Enter size"
                >
              </div>
            </div>

            <!-- Color and Image -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                <input 
                  type="text" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  v-model="form.color" 
                  required
                  placeholder="Enter color"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-500 transition-colors">
                  <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                      <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                      <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                        <span>Upload an image</span>
                        <input 
                          type="file" 
                          class="sr-only"
                          @change="handleImageUpload"
                          accept="image/*"
                        >
                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Details -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
              <textarea 
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                v-model="form.details" 
                rows="4"
                required
                placeholder="Enter product details"
              ></textarea>
            </div>

            <!-- Preview Image -->
            <div v-if="imagePreview" class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
              <div class="mt-1">
                <img :src="imagePreview" alt="Product preview" class="h-32 w-32 object-cover rounded-md">
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6">
              <button 
                type="button" 
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                @click="goBack"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :disabled="loading"
              >
                <span v-if="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></span>
                {{ isEditing ? 'Update' : 'Create' }} Product
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
  name: 'ProductForm',
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      form: {
        name: '',
        price: 0,
        quantity: 0,
        size: '',
        color: '',
        details: '',
        image_path: ''
      },
      imagePreview: null,
      loading: false,
      isEditing: false
    };
  },
  created() {
    if (this.$route.params.id) {
      this.isEditing = true;
      this.fetchProduct();
    }
  },
  methods: {
    async fetchProduct() {
      try {
        const response = await axios.get(`/inventory/products/${this.$route.params.id}`);
        if (response.data.success) {
          this.form = response.data.data;
          if (this.form.image_path) {
            this.imagePreview = `/storage/${this.form.image_path}`;
          }
        } else {
          throw new Error(response.data.message || 'Failed to fetch product');
        }
      } catch (error) {
        console.error('Error fetching product:', error);
        this.toast.error('Failed to fetch product');
        this.goBack();
      }
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target.result;
          this.form.image_path = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    async saveProduct() {
      this.loading = true;
      try {
        const url = this.isEditing
          ? `/inventory/products/${this.$route.params.id}`
          : '/inventory/products';
        
        const method = this.isEditing ? 'put' : 'post';
        
        const response = await axios[method](url, this.form);
        
        // Check if we have a valid response
        if (response && response.status >= 200 && response.status < 300) {
          // Show success message
          this.toast.success(`Product ${this.isEditing ? 'updated' : 'created'} successfully`);
          this.goBack();
        } else {
          throw new Error('Failed to save product: Invalid response from server');
        }
      } catch (error) {
        console.error('Error saving product:', error);
        // Show error message
        const errorMessage = error.response?.data?.message || 
                           error.message || 
                           'Failed to save product. Please try again.';
        this.toast.error(errorMessage);
      } finally {
        this.loading = false;
      }
    },
    goBack() {
      this.$router.push('/inventory/products');
    }
  }
};
</script>


