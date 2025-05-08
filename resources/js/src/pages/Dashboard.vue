<template>
  <div class="p-6">
    <!-- Date Range Filter -->
    <div class="mb-6 flex justify-end">
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">From:</label>
          <input
            type="date"
            v-model="startDate"
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            :max="endDate || today"
            @change="fetchDashboardData"
          />
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">To:</label>
          <input
            type="date"
            v-model="endDate"
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            :max="today"
            @change="fetchDashboardData"
          />
        </div>
        <div class="flex space-x-2">
          <button
            @click="setCurrentMonth"
            class="px-3 py-1 text-sm rounded-md bg-blue-500 text-white hover:bg-blue-600"
          >
            Current Month
          </button>
          <button
            @click="setCurrentDay"
            class="px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200"
          >
            Current Day
          </button>
        </div>
      </div>
    </div>

    <!-- Header Section -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-lg shadow-lg p-6">
        <div class="flex flex-col">
          <h1 class="text-3xl font-extrabold text-white">
            Dashboard <span class="text-indigo-200">Overview</span>
          </h1>
          <p class="text-indigo-100 text-sm mt-1">Welcome back! Here's an overview of your system.</p>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Orders -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalOrders }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500">Active Orders: {{ stats.activeOrders }}</span>
        </div>
      </div>

      <!-- Total Artisans -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Artisans</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalArtisans }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500">Active Artisans: {{ stats.activeArtisans }}</span>
        </div>
      </div>

      <!-- Total Products -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Products</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalProducts }}</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500">In Stock: {{ stats.inStockProducts }}</span>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900">Rs. {{ stats.totalRevenue }}</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500">This Month: Rs. {{ stats.monthlyRevenue }}</span>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Monthly Revenue Chart -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-700">Monthly Revenue</h3>
          <div class="flex space-x-2">
            <button
              v-for="year in availableYears"
              :key="year"
              @click="selectedYear = year; fetchDashboardData()"
              class="px-3 py-1 text-sm rounded-md"
              :class="selectedYear === year ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              {{ year }}
            </button>
          </div>
        </div>
        <div class="h-80">
          <canvas ref="monthlyChart"></canvas>
        </div>
      </div>

      <!-- Order Status Distribution Chart -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Order Status Distribution</h3>
        <div class="h-80">
          <canvas ref="statusChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Orders -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
        </div>
        <div class="p-6">
          <div v-if="recentOrders.length === 0" class="text-center text-gray-500 py-4">
            No recent orders
          </div>
          <div v-else class="space-y-4">
            <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">{{ order.product_name }}</p>
                <p class="text-sm text-gray-500">Order ID: {{ order.order_id }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium" :class="getStatusColor(order.status)">
                  {{ formatStatus(order.status) }}
                </p>
                <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Artisan Productivity -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Top Performing Artisans</h2>
        </div>
        <div class="p-6">
          <div v-if="topArtisans.length === 0" class="text-center text-gray-500 py-4">
            No artisan data available
          </div>
          <div v-else class="space-y-4">
            <div v-for="artisan in topArtisans" :key="artisan.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center">
                <img v-if="artisan.profile_photo" 
                     :src="'/storage/' + artisan.profile_photo" 
                     class="h-10 w-10 rounded-full object-cover mr-3"
                     :alt="artisan.name">
                <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                  <span class="text-gray-500 text-sm">{{ artisan.name.charAt(0) }}</span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ artisan.name }}</p>
                  <p class="text-sm text-gray-500">{{ artisan.department?.name }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-green-600">{{ artisan.orders.completion_rate }}% Completion</p>
                <p class="text-sm text-gray-500">{{ artisan.products.total_approved }} Products</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Inventory Status -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Inventory Status</h2>
        </div>
        <div class="p-6">
          <div v-if="inventoryStatus.length === 0" class="text-center text-gray-500 py-4">
            No inventory data available
          </div>
          <div v-else class="space-y-4">
            <div v-for="item in inventoryStatus" :key="item.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">{{ item.name }}</p>
                <p class="text-sm text-gray-500">{{ item.category }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium" :class="getStockLevelColor(item.stock_level)">
                  {{ item.quantity }} units
                </p>
                <p class="text-sm text-gray-500">Last updated: {{ formatDate(item.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Recent Activities</h2>
        </div>
        <div class="p-6">
          <div v-if="recentActivities.length === 0" class="text-center text-gray-500 py-4">
            No recent activities
          </div>
          <div v-else class="space-y-4">
            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start p-4 bg-gray-50 rounded-lg">
              <div class="flex-shrink-0">
                <div class="p-2 rounded-full" :class="getActivityIconBg(activity.type)">
                  <component :is="getActivityIcon(activity.type)" class="w-5 h-5" :class="getActivityIconColor(activity.type)" />
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">{{ activity.description }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(activity.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

// State
const stats = ref({
  totalOrders: 0,
  activeOrders: 0,
  totalArtisans: 0,
  activeArtisans: 0,
  totalProducts: 0,
  inStockProducts: 0,
  totalRevenue: 0,
  monthlyRevenue: 0
});

const recentOrders = ref([]);
const topArtisans = ref([]);
const inventoryStatus = ref([]);
const recentActivities = ref([]);
const monthlyChart = ref(null);
const statusChart = ref(null);
const monthlyChartInstance = ref(null);
const statusChartInstance = ref(null);

// Date Range State
const today = new Date().toISOString().split('T')[0];
const firstDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
const lastDayOfMonth = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0];
const startDate = ref(firstDayOfMonth);
const endDate = ref(lastDayOfMonth);
const selectedYear = ref(new Date().getFullYear());

// Computed
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  return Array.from({ length: 5 }, (_, i) => currentYear - i);
});

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    console.log('Fetching dashboard data with params:', {
      start_date: startDate.value,
      end_date: endDate.value,
      year: selectedYear.value
    });
    const response = await axios.get('/dashboard', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
        year: selectedYear.value
      }
    });
    console.log('Dashboard response:', response.data);
    stats.value = response.data.stats;
    recentOrders.value = response.data.recentOrders;
    topArtisans.value = response.data.topArtisans;
    inventoryStatus.value = response.data.inventoryStatus;
    recentActivities.value = response.data.recentActivities;

    // Initialize charts
    if (response.data.monthly_data) {
      initMonthlyChart(response.data.monthly_data);
    }
    if (response.data.status_distribution) {
      initStatusChart(response.data.status_distribution);
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    if (error.response) {
      console.error('Error response:', error.response.data);
    }
  }
};

const initMonthlyChart = (data) => {
  if (!data || !Array.isArray(data)) {
    console.warn('Invalid monthly data received');
    return;
  }
  if (monthlyChartInstance.value) {
    monthlyChartInstance.value.destroy();
  }

  const ctx = monthlyChart.value.getContext('2d');
  monthlyChartInstance.value = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.map(item => item.month),
      datasets: [{
        label: 'Revenue',
        data: data.map(item => parseFloat(item.revenue) || 0),
        backgroundColor: 'rgba(59, 130, 246, 0.5)',
        borderColor: 'rgb(59, 130, 246)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'Rs. ' + value.toLocaleString();
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.parsed.y !== null) {
                label += 'Rs. ' + context.parsed.y.toLocaleString();
              }
              return label;
            }
          }
        }
      }
    }
  });
};

const initStatusChart = (data) => {
  if (!data || !Array.isArray(data)) {
    console.warn('Invalid status distribution data received');
    return;
  }
  if (statusChartInstance.value) {
    statusChartInstance.value.destroy();
  }

  const ctx = statusChart.value.getContext('2d');
  statusChartInstance.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.map(item => item.status),
      datasets: [{
        data: data.map(item => item.count),
        backgroundColor: [
          'rgba(234, 179, 8, 0.7)',   // Pending - Yellow
          'rgba(59, 130, 246, 0.7)',  // In Production - Blue
          'rgba(34, 197, 94, 0.7)',   // Completed - Green
          'rgba(168, 85, 247, 0.7)',  // Dispatched - Purple
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: {
            boxWidth: 12,
            padding: 15
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
};

// Helper functions
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(date);
};

const formatStatus = (status) => {
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'text-yellow-600',
    in_production: 'text-blue-600',
    completed: 'text-green-600',
    dispatched: 'text-purple-600'
  };
  return colors[status] || 'text-gray-600';
};

const getStockLevelColor = (level) => {
  if (level === 'low') return 'text-red-600';
  if (level === 'medium') return 'text-yellow-600';
  return 'text-green-600';
};

const getActivityIcon = (type) => {
  const icons = {
    order: 'OrderIcon',
    artisan: 'UserIcon',
    inventory: 'PackageIcon',
    payment: 'CurrencyDollarIcon'
  };
  return icons[type] || 'InformationCircleIcon';
};

const getActivityIconBg = (type) => {
  const colors = {
    order: 'bg-blue-100',
    artisan: 'bg-green-100',
    inventory: 'bg-purple-100',
    payment: 'bg-yellow-100'
  };
  return colors[type] || 'bg-gray-100';
};

const getActivityIconColor = (type) => {
  const colors = {
    order: 'text-blue-600',
    artisan: 'text-green-600',
    inventory: 'text-purple-600',
    payment: 'text-yellow-600'
  };
  return colors[type] || 'text-gray-600';
};

// Set default date ranges
const setCurrentMonth = () => {
  startDate.value = firstDayOfMonth;
  endDate.value = lastDayOfMonth;
  fetchDashboardData();
};

const setCurrentDay = () => {
  startDate.value = today;
  endDate.value = today;
  fetchDashboardData();
};

onMounted(() => {
  fetchDashboardData();
});
</script>