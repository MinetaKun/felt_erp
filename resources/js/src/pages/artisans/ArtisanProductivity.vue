<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200">
        <h3 class="text-xl font-bold text-white">Artisan Productivity</h3>
        <p class="text-indigo-100 text-sm mt-1">Track artisan performance and productivity</p>
      </div>

      <div class="p-5">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-red-700">{{ errorMessage }}</p>
          </div>
          <button @click="fetchProductivity" class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium">
            Try Again
          </button>
        </div>

        <!-- Content -->
        <div v-else>
          <!-- Filters -->
          <div class="flex flex-wrap gap-4 mb-6">
            <div class="w-full md:w-1/4">
              <select 
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                v-model="selectedDepartment" 
                @change="fetchProductivity"
              >
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
            <div class="w-full md:w-1/4">
              <select 
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                v-model="dateRange" 
                @change="fetchProductivity"
              >
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
              </select>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h4 class="text-gray-500 text-sm font-medium">Total Artisans</h4>
              <p class="text-2xl font-bold text-gray-800">{{ totalArtisans }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h4 class="text-gray-500 text-sm font-medium">Total Products Made</h4>
              <p class="text-2xl font-bold text-gray-800">{{ totalProducts }}</p>
              <p class="text-sm text-gray-500">Approved Products</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h4 class="text-gray-500 text-sm font-medium">Average Attendance Rate</h4>
              <p class="text-2xl font-bold text-gray-800">{{ averageAttendanceRate }}%</p>
              <p class="text-sm text-gray-500">Overall Attendance</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
              <h4 class="text-gray-500 text-sm font-medium">Average Completion Rate</h4>
              <p class="text-2xl font-bold text-gray-800">{{ averageCompletionRate }}%</p>
              <p class="text-sm text-gray-500">Task Completion</p>
            </div>
          </div>

          <!-- Productivity Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
              <thead>
                <tr class="bg-gray-100">
                  <th class="p-3 text-left border-b-2 border-gray-200">Artisan</th>
                  <th class="p-3 text-left border-b-2 border-gray-200">Department</th>
                  <th class="p-3 text-left border-b-2 border-gray-200">Attendance</th>
                  <th class="p-3 text-left border-b-2 border-gray-200">Orders</th>
                  <th class="p-3 text-left border-b-2 border-gray-200">Products</th>
                  <th class="p-3 text-left border-b-2 border-gray-200">Efficiency</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="artisan in productivityData" :key="artisan.id" class="hover:bg-gray-50">
                  <td class="p-3 border-t">
                    <div class="flex items-center">
                      <img v-if="artisan.profile_photo" 
                        :src="'/storage/' + artisan.profile_photo" 
                        class="h-8 w-8 rounded-full object-cover mr-2"
                        :alt="artisan.name">
                      <div v-else class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                        <span class="text-gray-500 text-sm">{{ artisan.name.charAt(0) }}</span>
                      </div>
                      <span>{{ artisan.name }}</span>
                    </div>
                  </td>
                  <td class="p-3 border-t">
                    <span :class="[
                      'inline-block px-2 py-1 text-xs font-semibold rounded',
                      getDepartmentColor(artisan.department ? artisan.department.name : '')
                    ]">
                      {{ artisan.department ? artisan.department.name : 'N/A' }}
                    </span>
                  </td>
                  <td class="p-3 border-t">
                    <div class="flex flex-col">
                      <span class="text-sm">{{ artisan.attendance.present_days }}/{{ artisan.attendance.total_days }} days</span>
                      <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" 
                          :style="{ width: artisan.attendance.attendance_rate + '%' }"></div>
                      </div>
                      <span class="text-xs text-gray-600">{{ artisan.attendance.attendance_rate }}% Present</span>
                    </div>
                  </td>
                  <td class="p-3 border-t">
                    <div class="flex flex-col">
                      <div class="flex justify-between mb-1">
                        <span class="text-sm">Completed:</span>
                        <span class="text-sm font-medium">{{ artisan.orders.completed_assignments }}/{{ artisan.orders.total_assignments }}</span>
                      </div>
                      <div class="flex justify-between mb-1">
                        <span class="text-sm">Approved:</span>
                        <span class="text-sm font-medium">{{ artisan.orders.approved_assignments }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 border-t">
                    <div class="flex flex-col">
                      <div class="flex justify-between mb-1">
                        <span class="text-sm">Assigned:</span>
                        <span class="text-sm font-medium">{{ artisan.products.total_assigned }}</span>
                      </div>
                      <div class="flex justify-between mb-1">
                        <span class="text-sm">Completed:</span>
                        <span class="text-sm font-medium">{{ artisan.products.total_completed }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-sm">Approved:</span>
                        <span class="text-sm font-medium">{{ artisan.products.total_approved }}</span>
                      </div>
                      <div class="text-xs text-gray-600 mt-1">
                        {{ artisan.products.average_per_day }} per day
                      </div>
                    </div>
                  </td>
                  <td class="p-3 border-t">
                    <div class="flex flex-col space-y-2">
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-xs">Completion</span>
                          <span class="text-xs font-medium">{{ artisan.orders.completion_rate }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                          <div class="bg-blue-600 h-1.5 rounded-full" 
                            :style="{ width: artisan.orders.completion_rate + '%' }"></div>
                        </div>
                      </div>
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-xs">Approval</span>
                          <span class="text-xs font-medium">{{ artisan.orders.approval_rate }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                          <div class="bg-green-600 h-1.5 rounded-full" 
                            :style="{ width: artisan.orders.approval_rate + '%' }"></div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-if="!productivityData || productivityData.length === 0">
                  <td colspan="6" class="p-3 text-center border-t">No productivity data available</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-4 flex justify-center">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 border rounded-l bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <span class="px-3 py-1 border-t border-b bg-white">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 border rounded-r bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const productivityData = ref([]);
    const departments = ref([]);
    const selectedDepartment = ref('');
    const dateRange = ref('month');
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    const totalArtisans = ref(0);
    const totalProducts = ref(0);
    const averageAttendanceRate = ref(0);
    const averageCompletionRate = ref(0);
    const loading = ref(false);
    const error = ref(false);
    const errorMessage = ref('');

    const totalPages = computed(() => {
      return Math.ceil(productivityData.value.length / itemsPerPage.value);
    });

    const paginatedData = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      const end = start + itemsPerPage.value;
      return productivityData.value.slice(start, end);
    });

    async function fetchProductivity() {
      loading.value = true;
      error.value = false;
      errorMessage.value = '';
      
      try {
        const params = {
          department: selectedDepartment.value,
          date_range: dateRange.value,
          page: currentPage.value,
          per_page: itemsPerPage.value
        };

        const response = await axios.get('/artisans/productivity', { params });
        
        if (response.data && response.data.data) {
          productivityData.value = response.data.data;
          totalArtisans.value = response.data.meta.total_artisans;
          totalProducts.value = response.data.meta.total_products;
          averageAttendanceRate.value = response.data.meta.average_attendance_rate;
          averageCompletionRate.value = response.data.meta.average_completion_rate;
        } else {
          throw new Error('Invalid response format from server');
        }
      } catch (error) {
        console.error('Error fetching productivity data:', error);
        error.value = true;
        errorMessage.value = error.response?.data?.message || 'Failed to fetch productivity data. Please try again.';
      } finally {
        loading.value = false;
      }
    }

    async function fetchDepartments() {
      try {
        const response = await axios.get('/departments');
        departments.value = response.data;
      } catch (error) {
        console.error('Error fetching departments:', error);
      }
    }

    function getDepartmentColor(department) {
      const colors = {
        Pottery: "bg-amber-100 text-amber-800",
        Weaving: "bg-emerald-100 text-emerald-800",
        Woodwork: "bg-orange-100 text-orange-800",
        Textiles: "bg-blue-100 text-blue-800",
        Handicrafts: "bg-purple-100 text-purple-800",
        Jewelry: "bg-rose-100 text-rose-800",
      };

      return colors[department] || "bg-gray-100 text-gray-800";
    }

    onMounted(() => {
      fetchDepartments();
      fetchProductivity();
    });

    return {
      productivityData,
      departments,
      selectedDepartment,
      dateRange,
      currentPage,
      totalPages,
      paginatedData,
      totalArtisans,
      totalProducts,
      averageAttendanceRate,
      averageCompletionRate,
      loading,
      error,
      errorMessage,
      fetchProductivity,
      getDepartmentColor
    };
  }
};
</script> 