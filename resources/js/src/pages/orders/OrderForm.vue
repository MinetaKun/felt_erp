<template>
    <form @submit.prevent="saveOrder" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Product Information -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium border-b pb-2">Product Information</h3>
          
          <!-- Product Name -->
          <div>
            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Product Name *
            </label>
            <input
              id="product_name"
              v-model="formData.product_name"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.product_name }"
            />
            <p v-if="errors.product_name" class="mt-1 text-sm text-red-600">{{ errors.product_name[0] }}</p>
          </div>
          
          <!-- Size -->
          <div>
            <label for="size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Size
            </label>
            <input
              id="size"
              v-model="formData.size"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.size }"
            />
            <p v-if="errors.size" class="mt-1 text-sm text-red-600">{{ errors.size[0] }}</p>
          </div>
          
          <!-- Wool Color -->
          <div>
            <label for="wool_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Wool Color
            </label>
            <input
              id="wool_color"
              v-model="formData.wool_color"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.wool_color }"
            />
            <p v-if="errors.wool_color" class="mt-1 text-sm text-red-600">{{ errors.wool_color[0] }}</p>
          </div>
          
          <!-- Weight -->
          <div>
            <label for="weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Weight (kg)
            </label>
            <input
              id="weight"
              v-model="formData.weight"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.weight }"
            />
            <p v-if="errors.weight" class="mt-1 text-sm text-red-600">{{ errors.weight[0] }}</p>
          </div>
          
          <!-- Product Photo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Product Photo
            </label>
            <div class="mt-1 flex items-center">
              <div v-if="previewImage || (isEditMode && order?.product_photo)" class="mr-4">
                <img 
                  :src="previewImage || `/storage/${order.product_photo}`" 
                  class="h-24 w-24 object-cover rounded-md"
                  alt="Product preview"
                />
              </div>
              <label 
                class="cursor-pointer px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                <span>{{ previewImage || (isEditMode && order?.product_photo) ? 'Change Photo' : 'Upload Photo' }}</span>
                <input 
                  ref="fileInput"
                  type="file" 
                  @change="handleFileChange" 
                  accept="image/*" 
                  class="hidden"
                />
              </label>
              <button 
                v-if="previewImage || (isEditMode && order?.product_photo)" 
                type="button"
                @click="clearPhoto"
                class="ml-2 text-red-600 hover:text-red-900"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
            <p v-if="errors.product_photo" class="mt-1 text-sm text-red-600">{{ errors.product_photo[0] }}</p>
          </div>
        </div>
        
        <!-- Order Details -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium border-b pb-2">Order Details</h3>
          
          <!-- Total Quantity -->
          <div>
            <label for="total_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Total Quantity *
            </label>
            <input
              id="total_quantity"
              v-model="formData.total_quantity"
              type="number"
              min="1"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.total_quantity }"
            />
            <p v-if="errors.total_quantity" class="mt-1 text-sm text-red-600">{{ errors.total_quantity[0] }}</p>
          </div>
          
          <!-- Due Date -->
          <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Due Date *
            </label>
            <input
              id="due_date"
              v-model="formData.due_date"
              type="date"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.due_date }"
            />
            <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date[0] }}</p>
          </div>
          
          <!-- Wages Per Unit -->
          <div>
            <label for="wages_per_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Wages Per Unit (Rs) *
            </label>
            <input
              id="wages_per_unit"
              v-model="formData.wages_per_unit"
              type="number"
              step="0.01"
              min="0"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.wages_per_unit }"
            />
            <p v-if="errors.wages_per_unit" class="mt-1 text-sm text-red-600">{{ errors.wages_per_unit[0] }}</p>
          </div>
          
          <!-- Client Details -->
          <div>
            <label for="client_details" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Client Details
            </label>
            <textarea
              id="client_details"
              v-model="formData.client_details"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.client_details }"
              placeholder="Enter client name, contact info, etc."
            ></textarea>
            <p v-if="errors.client_details" class="mt-1 text-sm text-red-600">{{ errors.client_details[0] }}</p>
          </div>
          
          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="formData.notes"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
              :class="{ 'border-red-500': errors.notes }"
              placeholder="Any additional information about the order"
            ></textarea>
            <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
          </div>
        </div>
      </div>
      
      <!-- Form Actions -->
      <div class="flex justify-end space-x-3 pt-4 border-t">
        <button
          type="button"
          @click="$emit('cancelled')"
          class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="saving"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
          :class="{ 'opacity-75 cursor-not-allowed': saving }"
        >
          <span v-if="saving">
            <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
          </span>
          <span v-else>
            {{ isEditMode ? 'Update Order' : 'Create Order' }}
          </span>
        </button>
      </div>
    </form>
  </template>
  
  <script>
  import { ref, reactive, onMounted } from "vue";
  import axios from "axios";
  
  export default {
    props: {
      order: {
        type: Object,
        default: null
      },
      isEditMode: {
        type: Boolean,
        default: false
      }
    },
    emits: ['saved', 'cancelled'],
    setup(props, { emit }) {
      const fileInput = ref(null);
      const previewImage = ref(null);
      const saving = ref(false);
      const errors = ref({});
  
      // Form data
      const formData = reactive({
        product_name: '',
        size: '',
        wool_color: '',
        weight: '',
        total_quantity: '',
        due_date: '',
        wages_per_unit: '',
        client_details: '',
        notes: '',
        product_photo: null
      });
  
      // Initialize form with order data if in edit mode
      onMounted(() => {
        if (props.isEditMode && props.order) {
          Object.keys(formData).forEach(key => {
            if (key !== 'product_photo' && props.order[key] !== undefined) {
              formData[key] = props.order[key];
            }
          });
        } else {
          // Set default due date to 7 days from now for new orders
          const date = new Date();
          date.setDate(date.getDate() + 7);
          formData.due_date = date.toISOString().split('T')[0];
        }
      });
  
      // Handle file change
      const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
          formData.product_photo = file;
          previewImage.value = URL.createObjectURL(file);
        }
      };
  
      // Clear photo
      const clearPhoto = () => {
        formData.product_photo = null;
        previewImage.value = null;
        if (fileInput.value) {
          fileInput.value.value = '';
        }
      };
  
      // Save order
      const saveOrder = async () => {
        saving.value = true;
        errors.value = {};
  
        try {
          const formDataToSend = new FormData();
          
          // Append all form fields to FormData
          Object.keys(formData).forEach(key => {
            if (formData[key] !== null && formData[key] !== undefined) {
              if (key === 'product_photo' && typeof formData[key] !== 'object') {
                // Skip if product_photo is not a file object
                return;
              }
              formDataToSend.append(key, formData[key]);
            }
          });
  
          let response;
          if (props.isEditMode) {
            response = await axios.put(`/orders/${props.order.id}`, formDataToSend, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            });
          } else {
            response = await axios.post('/orders', formDataToSend, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            });
          }
  
          emit('saved', response.data);
        } catch (error) {
          console.error("Error saving order:", error);
          if (error.response && error.response.data && error.response.data.errors) {
            errors.value = error.response.data.errors;
          } else {
            alert(`Failed to ${props.isEditMode ? 'update' : 'create'} order. Please try again.`);
          }
        } finally {
          saving.value = false;
        }
      };
  
      return {
        formData,
        errors,
        saving,
        fileInput,
        previewImage,
        handleFileChange,
        clearPhoto,
        saveOrder
      };
    }
  };
  </script>
  