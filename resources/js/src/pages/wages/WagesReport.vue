<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Wage Reports</h1>
      <div class="flex space-x-4">
        <button @click="showFilterModal = true" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
          Filter
        </button>
        <button @click="exportWages" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
          Export
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
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Artisans</h3>
        <p class="text-3xl font-bold text-blue-600">{{ uniqueArtisans }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Orders</h3>
        <p class="text-3xl font-bold text-green-600">{{ uniqueOrders }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Wages</h3>
        <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(totalWages) }}</p>
      </div>
    </div>

    <!-- Wages Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artisan</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wage per Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Wages</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispatch Date</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="wages.length === 0">
            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
              No wage records found
            </td>
          </tr>
          <tr v-for="wage in filteredWages" :key="wage.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ wage.artisan_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ wage.order_number }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ wage.product_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ wage.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(wage.wages_per_unit) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(wage.total_wages) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(wage.approval_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(wage.dispatch_date) }}</td>
          </tr>
        </tbody>
        <tfoot class="bg-gray-50">
          <tr>
            <td colspan="5" class="px-6 py-4 text-right font-bold">Total Wages:</td>
            <td class="px-6 py-4 font-bold">{{ formatCurrency(totalWages) }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Filter Modal -->
    <div v-if="showFilterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Filter Wages</h3>
          <form @submit.prevent="applyFilters" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="artisan">Artisan</label>
              <input v-model="filters.artisan" type="text" id="artisan"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="order">Order Number</label>
              <input v-model="filters.order" type="text" id="order"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="date_from">Date From</label>
              <input v-model="filters.date_from" type="date" id="date_from"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="date_to">Date To</label>
              <input v-model="filters.date_to" type="date" id="date_to"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="resetFilters"
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                Reset
              </button>
              <button type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                Apply Filters
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const wages = ref([])
const loading = ref(false)
const error = ref(null)
const showFilterModal = ref(false)

const filters = ref({
  artisan: '',
  order: '',
  date_from: '',
  date_to: ''
})

const fetchWages = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/wages/calculations', {
      params: filters.value
    })
    
    if (response.data.success) {
      wages.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to fetch wages'
    }
  } catch (error) {
    console.error('Error fetching wages:', error)
    error.value = 'Failed to fetch wages. Please try again.'
  } finally {
    loading.value = false
  }
}

const filteredWages = computed(() => {
  return wages.value.filter(wage => {
    const matchesArtisan = !filters.value.artisan || 
      wage.artisan_name.toLowerCase().includes(filters.value.artisan.toLowerCase())
    const matchesOrder = !filters.value.order || 
      wage.order_number.toLowerCase().includes(filters.value.order.toLowerCase())
    const matchesDateFrom = !filters.value.date_from || 
      new Date(wage.approval_date) >= new Date(filters.value.date_from)
    const matchesDateTo = !filters.value.date_to || 
      new Date(wage.approval_date) <= new Date(filters.value.date_to)
    
    return matchesArtisan && matchesOrder && matchesDateFrom && matchesDateTo
  })
})

const uniqueArtisans = computed(() => {
  return new Set(filteredWages.value.map(wage => wage.artisan_name)).size
})

const uniqueOrders = computed(() => {
  return new Set(filteredWages.value.map(wage => wage.order_number)).size
})

const totalWages = computed(() => {
  return filteredWages.value.reduce((total, wage) => total + wage.total_wages, 0)
})

const applyFilters = () => {
  showFilterModal.value = false
  fetchWages()
}

const resetFilters = () => {
  filters.value = {
    artisan: '',
    order: '',
    date_from: '',
    date_to: ''
  }
  fetchWages()
}

const exportWages = async () => {
  try {
    const response = await axios.post('/wages/export', filters.value, {
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
    console.error('Error exporting wages:', error)
    alert('Failed to export wages. Please try again.')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR',
    currencyDisplay: 'symbol'
  }).format(amount).replace('₹', 'Rs.')
}

onMounted(() => {
  fetchWages()
})
</script> 