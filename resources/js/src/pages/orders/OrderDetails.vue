<template>
    <div v-if="order" class="space-y-6">
      <!-- Order Header -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div class="text-white">
            <h3 class="text-xl font-bold">{{ order.product_name }}</h3>
            <p class="text-indigo-100 text-sm mt-1">Order ID: {{ order.order_id }}</p>
          </div>
          <div class="mt-2 md:mt-0">
            <span 
              class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
              :class="getStatusClass(order.status)"
            >
              {{ formatStatus(order.status) }}
            </span>
          </div>
        </div>
      </div>
  
      <!-- Order Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Product Information -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="text-lg font-medium mb-4 border-b pb-2">Product Information</h4>
          
          <div class="flex mb-4">
            <div class="mr-4">
              <img 
                v-if="order.product_photo" 
                :src="`/storage/${order.product_photo}`" 
                class="h-32 w-32 object-cover rounded-md"
                alt="Product"
              />
              <div 
                v-else 
                class="h-32 w-32 bg-gray-200 dark:bg-gray-600 rounded-md flex items-center justify-center"
              >
                <i class="fas fa-box text-gray-400 text-4xl"></i>
              </div>
            </div>
            <div class="flex-1">
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Size:</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ order.size || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Color:</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ order.wool_color || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Weight:</p>
                  <p class="text-sm text-gray-900 dark:text-white">{{ order.weight ? `${order.weight} kg` : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Wages Per Unit:</p>
                  <p class="text-sm text-gray-900 dark:text-white">Rs. {{ order.wages_per_unit }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="order.notes" class="mt-4">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.notes }}</p>
          </div>
        </div>
  
        <!-- Order Details -->
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="text-lg font-medium mb-4 border-b pb-2">Order Details</h4>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Quantity:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ order.total_quantity }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Quantity:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ order.total_assigned_quantity || 0 }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed Quantity:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ order.total_completed_quantity || 0 }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved Quantity:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ order.total_approved_quantity || 0 }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(order.due_date) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Days Remaining:</p>
              <p class="text-sm text-gray-900 dark:text-white">{{ getDaysRemaining(order.due_date) }}</p>
            </div>
          </div>
          
          <div class="mt-4">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Progress:</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
              <div 
                class="bg-emerald-500 h-2.5 rounded-full" 
                :style="`width: ${getProgressPercentage(order)}%`"
              ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ getProgressPercentage(order) }}% complete
            </p>
          </div>
          
          <div v-if="order.client_name" class="mt-4">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Client Name:</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ order.client_name }}</p>
          </div>
        </div>
      </div>
  
      <!-- Assignments Section -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <h4 class="text-lg font-medium mb-4 border-b pb-2">Assignments</h4>
        
        <div v-if="!order.assignments || order.assignments.length === 0" class="text-center py-4">
          <p class="text-gray-500 dark:text-gray-400">No assignments yet</p>
          <button 
            @click="$emit('assign')" 
            class="mt-2 px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700"
          >
            Assign to Artisans
          </button>
        </div>
        
        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Artisan
                  </th>
                  <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Department
                  </th> -->
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Assigned
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Completed
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Approved
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="assignment in order.assignments" :key="assignment.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ assignment.artisan.name }}
                      </div>
                    </div>
                  </td>
                  <!-- <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ assignment.artisan.department ? assignment.artisan.department.name : 'N/A' }}
                    </div>
                  </td> -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ assignment.assigned_quantity }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ assignment.completed_quantity }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ assignment.approved_quantity }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getAssignmentStatusClass(assignment.status)"
                    >
                      {{ formatStatus(assignment.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="mt-4 flex justify-end">
            <button 
              v-if="!order.is_fully_assigned"
              @click="$emit('assign')" 
              class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700"
            >
              Assign More
            </button>
          </div>
        </div>
      </div>
  
      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3 pt-4 border-t">
        <button
          @click="$emit('close')"
          class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Close
        </button>
        <button
          @click="$emit('edit')"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700"
        >
          Edit Order
        </button>
        <button
          v-if="!order.is_fully_assigned"
          @click="$emit('assign')"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
        >
          Assign to Artisans
        </button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      order: {
        type: Object,
        required: true
      }
    },
    emits: ['close', 'edit', 'assign'],
    setup() {
      // Format status for display
      const formatStatus = (status) => {
        switch (status) {
          case "pending":
            return "Pending";
          case "in_production":
            return "In Production";
          case "approved":
            return "Approved";
          case "dispatched":
            return "Dispatched";
          case "completed":
            return "Completed";
          default:
            return status;
        }
      };
  
      // Get status class for styling
      const getStatusClass = (status) => {
        switch (status) {
          case "pending":
            return "bg-yellow-100 text-yellow-800";
          case "in_production":
            return "bg-blue-100 text-blue-800";
          case "approved":
            return "bg-green-100 text-green-800";
          case "dispatched":
            return "bg-purple-100 text-purple-800";
          default:
            return "bg-gray-100 text-gray-800";
        }
      };
  
      // Get assignment status class
      const getAssignmentStatusClass = (status) => {
        switch (status) {
          case "pending":
            return "bg-yellow-100 text-yellow-800";
          case "in_production":
            return "bg-blue-100 text-blue-800";
          case "completed":
            return "bg-orange-100 text-orange-800";
          case "approved":
            return "bg-green-100 text-green-800";
          case "dispatched":
            return "bg-purple-100 text-purple-800";
          default:
            return "bg-gray-100 text-gray-800";
        }
      };
  
      // Format date
      const formatDate = (dateString) => {
        if (!dateString) return "";
        const date = new Date(dateString);
        return date.toLocaleDateString();
      };
  
      // Get days remaining until due date
      const getDaysRemaining = (dateString) => {
        if (!dateString) return "";
        const dueDate = new Date(dateString);
        const today = new Date();
        const diffTime = dueDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays < 0) {
          return `Overdue by ${Math.abs(diffDays)} days`;
        } else if (diffDays === 0) {
          return "Due today";
        } else {
          return `${diffDays} days remaining`;
        }
      };
  
      // Calculate progress percentage
      const getProgressPercentage = (order) => {
        if (!order.total_quantity) return 0;
        
        // If order is dispatched, return 100%
        if (order.status === 'dispatched') return 100;
        
        // Calculate based on approved quantity
        const approved = order.total_approved_quantity || 0;
        return Math.round((approved / order.total_quantity) * 100);
      };
  
      return {
        formatStatus,
        getStatusClass,
        getAssignmentStatusClass,
        formatDate,
        getDaysRemaining,
        getProgressPercentage
      };
    }
  };
  </script>
  