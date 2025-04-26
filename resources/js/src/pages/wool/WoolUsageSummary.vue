<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wool Usage Summary</h3>
          <p class="text-indigo-100 text-sm mt-1">Monthly wool usage summary and reports</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="exportSummary" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters Section -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.start_date"
              @change="fetchSummary"
            >
          </div>
          <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.end_date"
              @change="fetchSummary"
            >
          </div>
          <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button 
              class="w-full inline-flex items-center justify-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              @click="resetFilters"
            >
              <i class="fas fa-sync-alt mr-1"></i> Reset
            </button>
          </div>
        </div>

        <!-- Monthly Summary Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.N.</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Wool (KG)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Count</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average per Usage</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(summary, month, index) in monthlySummary" :key="month">
                <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatMonth(month) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ summary.total_kg }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ summary.usage_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ (summary.total_kg / summary.usage_count).toFixed(2) }}</td>
              </tr>
              <tr v-if="Object.keys(monthlySummary).length === 0">
                <td colspan="5" class="px-6 py-4 text-center">No data available</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const monthlySummary = ref({})

const filters = ref({
  start_date: '',
  end_date: ''
})

const fetchSummary = async () => {
  try {
    const response = await axios.get('/wool/usage/summary', {
      params: filters.value
    })
    if (response.data.success) {
      monthlySummary.value = response.data.data.monthly_summary
    }
  } catch (error) {
    console.error('Error fetching summary:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load summary data. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const exportSummary = async () => {
  try {
    const response = await axios.get('/wool/usage/summary/export', {
      params: filters.value,
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'wool-usage-summary.csv'
    
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
    console.error('Error exporting summary:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to export summary. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const resetFilters = () => {
  filters.value = {
    start_date: '',
    end_date: ''
  }
  fetchSummary()
}

const formatMonth = (month) => {
  const [year, monthNum] = month.split('-')
  const date = new Date(year, monthNum - 1)
  return date.toLocaleString('default', { month: 'long', year: 'numeric' })
}

onMounted(() => {
  fetchSummary()
})
</script> 