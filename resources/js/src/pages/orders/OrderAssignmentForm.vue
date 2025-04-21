<template>
    <div class="space-y-6">
      <!-- Order Summary -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <h3 class="text-lg font-medium mb-2">Order Summary</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Product:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.product_name }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Order ID:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.order_id }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Quantity:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.total_quantity }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Remaining Quantity:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ remainingQuantity }}</p>
          </div>
        </div>
      </div>
  
      <!-- Department Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Filter by Department
        </label>
        <select 
          v-model="selectedDepartment" 
          @change="loadArtisans"
          class="w-full p-2 border rounded-md"
        >
          <option value="">All Departments</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
            {{ dept.name }}
          </option>
        </select>
      </div>
  
      <!-- Artisan Selection -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-lg font-medium">Assign to Artisans</h3>
          <button 
            @click="addAssignment" 
            type="button"
            class="px-3 py-1 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm"
            :disabled="remainingQuantity <= 0"
            :class="{ 'opacity-50 cursor-not-allowed': remainingQuantity <= 0 }"
          >
            <i class="fas fa-plus mr-1"></i> Add Artisan
          </button>
        </div>
  
        <div v-if="loading" class="text-center py-4">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
          <p class="mt-2">Loading artisans...</p>
        </div>
  
        <div v-else-if="assignments.length === 0" class="text-center py-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <p class="text-gray-500 dark:text-gray-400">No artisans assigned yet</p>
          <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Click "Add Artisan" to start assigning</p>
        </div>
  
        <div v-else class="space-y-4">
          <div 
            v-for="(assignment, index) in assignments" 
            :key="index"
            class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg"
          >
            <div class="flex justify-between items-center mb-3">
              <h4 class="font-medium">Assignment #{{ index + 1 }}</h4>
              <button 
                @click="removeAssignment(index)" 
                type="button"
                class="text-red-600 hover:text-red-900"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
  
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Artisan *
                </label>
                <select 
                  v-model="assignment.artisan_id" 
                  class="w-full p-2 border rounded-md"
                  :class="{ 'border-red-500': errors[`assignments.${index}.artisan_id`] }"
                  required
                >
                  <option value="">Select an artisan</option>
                  <option v-for="artisan in availableArtisans" :key="artisan.id" :value="artisan.id">
                    {{ artisan.name }} ({{ artisan.department ? artisan.department.name : 'No Department' }})
                  </option>
                </select>
                <p v-if="errors[`assignments.${index}.artisan_id`]" class="mt-1 text-sm text-red-600">
                  {{ errors[`assignments.${index}.artisan_id`][0] }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Quantity *
                </label>
                <input 
                  v-model.number="assignment.quantity" 
                  type="number" 
                  min="1" 
                  :max="remainingQuantity + (assignment.original_quantity || 0)"
                  class="w-full p-2 border rounded-md"
                  :class="{ 'border-red-500': errors[`assignments.${index}.quantity`] }"
                  required
                />
                <p v-if="errors[`assignments.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                  {{ errors[`assignments.${index}.quantity`][0] }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Quantity Summary -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <h3 class="text-lg font-medium mb-2">Assignment Summary</h3>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Quantity:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.total_quantity }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Quantity:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ assignedQuantity }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Remaining Quantity:</p>
            <p 
              class="text-sm font-medium"
              :class="remainingQuantity < 0 ? 'text-red-600' : 'text-gray-900 dark:text-white'"
            >
              {{ remainingQuantity }}
            </p>
          </div>
        </div>
        <div v-if="remainingQuantity < 0" class="mt-2">
          <p class="text-sm text-red-600">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            You have assigned more than the available quantity
          </p>
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
          type="button"
          @click="saveAssignments"
          :disabled="saving || remainingQuantity < 0 || assignments.length === 0"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
          :class="{ 'opacity-75 cursor-not-allowed': saving || remainingQuantity < 0 || assignments.length === 0 }"
        >
          <span v-if="saving">
            <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
          </span>
          <span v-else>
            Save Assignments
          </span>
        </button>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from "vue";
  import axios from "axios";
  
  export default {
    props: {
      order: {
        type: Object,
        required: true
      }
    },
    emits: ['saved', 'cancelled'],
    setup(props, { emit }) {
      const loading = ref(false);
      const saving = ref(false);
      const errors = ref({});
      const availableArtisans = ref([]);
      const departments = ref([]);
      const selectedDepartment = ref('');
      const assignments = ref([]);
  
      // Computed properties
      const assignedQuantity = computed(() => {
        return assignments.value.reduce((total, assignment) => {
          return total + (assignment.quantity || 0);
        }, 0);
      });
  
      const remainingQuantity = computed(() => {
        const existingAssignments = props.order.assignments || [];
        const existingAssignedQuantity = existingAssignments.reduce((total, assignment) => {
          return total + assignment.assigned_quantity;
        }, 0);
        
        return props.order.total_quantity - existingAssignedQuantity - assignedQuantity.value;
      });
  
      // Load artisans
      const loadArtisans = async () => {
        loading.value = true;
        try {
          // First, load departments
          const deptResponse = await axios.get('/departments');
          departments.value = deptResponse.data;
          
          // Then load artisans, filtered by department if selected
          const url = selectedDepartment.value 
            ? `/orders/${props.order.id}/available-artisans?department_id=${selectedDepartment.value}`
            : `/orders/${props.order.id}/available-artisans`;
            
          const response = await axios.get(url);
          availableArtisans.value = response.data.data;
        } catch (error) {
          console.error("Error loading artisans:", error);
          alert("Failed to load artisans. Please try again.");
        } finally {
          loading.value = false;
        }
      };
  
      // Add a new assignment
      const addAssignment = () => {
        assignments.value.push({
          artisan_id: '',
          quantity: 1
        });
      };
  
      // Remove an assignment
      const removeAssignment = (index) => {
        assignments.value.splice(index, 1);
      };
  
      // Save assignments
      const saveAssignments = async () => {
        if (assignments.value.length === 0) return;
        if (remainingQuantity.value < 0) {
          alert("You have assigned more than the available quantity");
          return;
        }
        
        saving.value = true;
        errors.value = {};
        
        try {
          const assignmentsData = assignments.value.map(assignment => ({
            artisan_id: assignment.artisan_id,
            assigned_quantity: assignment.quantity
          }));
          
          const response = await axios.post(`/orders/${props.order.id}/assignments`, {
            assignments: assignmentsData
          });
          
          alert("Assignments saved successfully");
          emit('saved', response.data);
        } catch (error) {
          console.error("Error saving assignments:", error);
          
          if (error.response && error.response.data && error.response.data.errors) {
            errors.value = error.response.data.errors;
          } else {
            alert("Failed to save assignments. Please try again.");
          }
        } finally {
          saving.value = false;
        }
      };
  
      // Initialize
      onMounted(() => {
        loadArtisans();
      });
  
      return {
        loading,
        saving,
        errors,
        availableArtisans,
        departments,
        selectedDepartment,
        assignments,
        assignedQuantity,
        remainingQuantity,
        loadArtisans,
        addAssignment,
        removeAssignment,
        saveAssignments
      };
    }
  };
  </script>
  