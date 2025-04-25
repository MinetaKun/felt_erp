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
          <div class="w-full md:w-1/2">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.date_range" 
              @change="handleFilterChange"
            >
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>
          <div class="w-full md:w-1/2">
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

        <!-- Content when not loading and no error -->
        <div v-else>
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Artisans</h3>
              <p class="text-3xl font-bold text-blue-600">{{ reportData.reduce((total, item) => total + item.total_artisans, 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Orders</h3>
              <p class="text-3xl font-bold text-green-600">{{ reportData.reduce((total, item) => total + item.total_orders, 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Wages</h3>
              <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(reportData.reduce((total, item) => total + item.total_wages, 0)) }}</p>
            </div>
          </div>

          <!-- Artisan Monthly Wages Table -->
          <div v-if="artisanWages.length > 0" class="mt-8 bg-white rounded-lg shadow overflow-hidden">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const reportData = ref([])
const loading = ref(false)
const error = ref(null)

const filters = ref({
  date_range: '',
  group_by: 'monthly'
})

const artisanWages = ref([])

const handleFilterChange = () => {
  fetchReport()
}

const fetchReport = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/wages/report', {
      params: {
        date_range: filters.value.date_range,
        group_by: filters.value.group_by,
        include_artisans: true
      }
    })
    
    if (response.data.success) {
      reportData.value = response.data.data.report || []
      artisanWages.value = response.data.data.artisans || []
    } else {
      error.value = response.data.message || 'Failed to fetch report data'
    }
  } catch (err) {
    console.error('Error fetching reports:', err)
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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR',
    currencyDisplay: 'symbol'
  }).format(amount).replace('₹', 'Rs.')
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