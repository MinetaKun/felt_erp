<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wage Reports</h3>
          <p class="text-indigo-100 text-sm mt-1">View wage summaries and statistics</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="exportReport" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export Report
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/3">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.date_range" 
              @change="fetchReport"
            >
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>
          <div class="w-full md:w-1/3">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.group_by" 
              @change="fetchReport"
            >
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
          <div class="w-full md:w-1/3">
            <button 
              class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              @click="resetFilters"
            >
              <i class="fas fa-sync-alt mr-1"></i> Reset
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center my-8">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
          {{ error }}
        </div>

        <!-- Summary Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Wages</h3>
            <p class="text-3xl font-bold text-blue-600">{{ formatCurrency(totalWages) }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Artisans</h3>
            <p class="text-3xl font-bold text-green-600">{{ totalArtisans }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Orders</h3>
            <p class="text-3xl font-bold text-purple-600">{{ totalOrders }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Average Wage</h3>
            <p class="text-3xl font-bold text-amber-600">{{ formatCurrency(averageWage) }}</p>
          </div>
        </div>

        <!-- Charts Section -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <!-- Wages Trend Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Wages Trend</h3>
            <div class="h-64">
              <canvas ref="wagesTrendChart"></canvas>
            </div>
          </div>

          <!-- Artisan Distribution Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Artisan Distribution</h3>
            <div class="h-64">
              <canvas ref="artisanDistributionChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Detailed Report Table -->
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Wages</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artisans</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Wage</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="reportData.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No wage records found
                </td>
              </tr>
              <tr v-for="item in reportData" :key="item.period">
                <td class="px-6 py-4 whitespace-nowrap">{{ item.period }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(item.total_wages) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ item.total_artisans }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ item.total_orders }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(item.average_wage) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Artisan Monthly Wages Table -->
        <div v-else class="mt-8 bg-white rounded-lg shadow overflow-hidden">
          <h3 class="text-lg font-semibold text-gray-700 p-4 border-b">Artisan Monthly Wages</h3>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artisan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Wages</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="artisanWages.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No artisan wage records found
                </td>
              </tr>
              <tr v-for="wage in artisanWages" :key="`${wage.artisan_id}-${wage.month}`">
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.artisan_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatMonth(wage.month) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.total_orders }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.total_quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(wage.total_wages) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const reportData = ref([])
const loading = ref(false)
const error = ref(null)

const filters = ref({
  date_range: '',
  group_by: 'monthly'
})

const artisanWages = ref([])

const fetchReport = async () => {
  loading.value = true
  error.value = null
  try {
    await Promise.all([
      fetchReport(),
      fetchArtisanWages()
    ])
  } catch (error) {
    console.error('Error fetching reports:', error)
    error.value = 'Failed to fetch report data. Please try again.'
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    date_range: '',
    group_by: 'monthly'
  }
  fetchReport()
}

const exportReport = async () => {
  try {
    const response = await axios.get('/wages/report/export', {
      params: filters.value,
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'wages-report.csv'
    
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/)
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1]
      }
    }
    
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    
    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
  } catch (error) {
    console.error('Error exporting report:', error)
    alert('Failed to export report. Please try again.')
  }
}

const totalWages = computed(() => {
  return reportData.value.reduce((total, item) => total + item.total_wages, 0)
})

const totalArtisans = computed(() => {
  return reportData.value.reduce((total, item) => total + item.total_artisans, 0)
})

const totalOrders = computed(() => {
  return reportData.value.reduce((total, item) => total + item.total_orders, 0)
})

const averageWage = computed(() => {
  if (totalArtisans.value === 0) return 0
  return totalWages.value / totalArtisans.value
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR',
    currencyDisplay: 'symbol'
  }).format(amount).replace('₹', 'Rs.')
}

let wagesTrendChart = null
let artisanDistributionChart = null

const updateCharts = () => {
  // Destroy existing charts if they exist
  if (wagesTrendChart) {
    wagesTrendChart.destroy()
  }
  if (artisanDistributionChart) {
    artisanDistributionChart.destroy()
  }

  // Create Wages Trend Chart
  const wagesTrendCtx = document.querySelector('canvas').getContext('2d')
  wagesTrendChart = new Chart(wagesTrendCtx, {
    type: 'line',
    data: {
      labels: reportData.value.map(item => item.period),
      datasets: [{
        label: 'Total Wages',
        data: reportData.value.map(item => item.total_wages),
        borderColor: 'rgb(59, 130, 246)',
        tension: 0.1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  })

  // Create Artisan Distribution Chart
  const artisanDistributionCtx = document.querySelectorAll('canvas')[1].getContext('2d')
  artisanDistributionChart = new Chart(artisanDistributionCtx, {
    type: 'bar',
    data: {
      labels: reportData.value.map(item => item.period),
      datasets: [{
        label: 'Number of Artisans',
        data: reportData.value.map(item => item.total_artisans),
        backgroundColor: 'rgb(16, 185, 129)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  })
}

const fetchArtisanWages = async () => {
  try {
    const response = await axios.get('/wages/artisan-monthly', {
      params: {
        date_range: filters.value.date_range
      }
    })
    
    if (response.data.success) {
      artisanWages.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching artisan wages:', error)
  }
}

const formatMonth = (month) => {
  const [year, monthNum] = month.split('-')
  const date = new Date(year, monthNum - 1)
  return date.toLocaleString('default', { month: 'long', year: 'numeric' })
}

onMounted(() => {
  fetchReport()
})
</script> 