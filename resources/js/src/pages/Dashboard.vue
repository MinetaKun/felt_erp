<template>
  <div class="p-6">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-gray-600">Welcome back! Here's an overview of your system.</p>
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
            <p class="text-2xl font-bold text-gray-900">₹{{ stats.totalRevenue }}</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-4">
          <span class="text-sm text-gray-500">This Month: ₹{{ stats.monthlyRevenue }}</span>
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

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
})

const recentOrders = ref([])
const topArtisans = ref([])
const inventoryStatus = ref([])
const recentActivities = ref([])

// Fetch dashboard data
const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/dashboard')
    stats.value = response.data.stats
    recentOrders.value = response.data.recentOrders
    topArtisans.value = response.data.topArtisans
    inventoryStatus.value = response.data.inventoryStatus
    recentActivities.value = response.data.recentActivities
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  }
}

// Helper functions
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const formatStatus = (status) => {
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const getStatusColor = (status) => {
  const colors = {
    pending: 'text-yellow-600',
    in_production: 'text-blue-600',
    completed: 'text-green-600',
    dispatched: 'text-purple-600'
  }
  return colors[status] || 'text-gray-600'
}

const getStockLevelColor = (level) => {
  if (level === 'low') return 'text-red-600'
  if (level === 'medium') return 'text-yellow-600'
  return 'text-green-600'
}

const getActivityIcon = (type) => {
  const icons = {
    order: 'OrderIcon',
    artisan: 'UserIcon',
    inventory: 'PackageIcon',
    payment: 'CurrencyDollarIcon'
  }
  return icons[type] || 'InformationCircleIcon'
}

const getActivityIconBg = (type) => {
  const colors = {
    order: 'bg-blue-100',
    artisan: 'bg-green-100',
    inventory: 'bg-purple-100',
    payment: 'bg-yellow-100'
  }
  return colors[type] || 'bg-gray-100'
}

const getActivityIconColor = (type) => {
  const colors = {
    order: 'text-blue-600',
    artisan: 'text-green-600',
    inventory: 'text-purple-600',
    payment: 'text-yellow-600'
  }
  return colors[type] || 'text-gray-600'
}

onMounted(() => {
  fetchDashboardData()
})
</script>