<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wage Calculations</h3>
          <p class="text-indigo-100 text-sm mt-1">View and manage wage calculations</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="exportWages" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters and Search -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <input 
              type="text" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              placeholder="Search by artisan or order..." 
              v-model="searchQuery"
              @input="debouncedFetchWages"
            >
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.date_range" 
              @change="fetchWages"
            >
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="sortBy" 
              @change="fetchWages"
            >
              <option value="approval_date:desc">Newest First</option>
              <option value="approval_date:asc">Oldest First</option>
              <option value="artisan_name:asc">Artisan (A-Z)</option>
              <option value="artisan_name:desc">Artisan (Z-A)</option>
              <option value="total_wages:desc">Wages (High to Low)</option>
              <option value="total_wages:asc">Wages (Low to High)</option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
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
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
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
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="wages.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  No wage calculations found
                </td>
              </tr>
              <tr v-for="wage in paginatedWages" :key="wage.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.artisan_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.order_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.product_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ wage.quantity }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(wage.wages_per_unit) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(wage.total_wages) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(wage.approval_date) }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50">
              <tr>
                <td colspan="5" class="px-6 py-4 text-right font-bold">Total Wages:</td>
                <td class="px-6 py-4 font-bold">{{ formatCurrency(totalWages) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
          <!-- Pagination -->
          <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button 
                @click="currentPage--" 
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Previous
              </button>
              <button 
                @click="currentPage++" 
                :disabled="currentPage === totalPages"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredWages.length) }}</span>
                  of
                  <span class="font-medium">{{ filteredWages.length }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button 
                    @click="currentPage--" 
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Previous</span>
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <button 
                    v-for="page in totalPages" 
                    :key="page"
                    @click="currentPage = page"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      currentPage === page 
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button 
                    @click="currentPage++" 
                    :disabled="currentPage === totalPages"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Next</span>
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'

const wages = ref([])
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const sortBy = ref('approval_date:desc')
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  date_range: '',
})

const totalPages = computed(() => {
  return Math.ceil(filteredWages.value.length / itemsPerPage)
})

const paginatedWages = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredWages.value.slice(start, end)
})

const fetchWages = async () => {
  loading.value = true
  error.value = null
  try {
    const params = {
      search: searchQuery.value,
      date_range: filters.value.date_range,
      sort: sortBy.value,
    }

    const response = await axios.get('/wages/calculations', { params })
    
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

const debouncedFetchWages = debounce(fetchWages, 500)

const filteredWages = computed(() => {
  return wages.value.filter(wage => {
    const matchesSearch = !searchQuery.value || 
      wage.artisan_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      wage.order_number.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    return matchesSearch
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

const resetFilters = () => {
  searchQuery.value = ''
  filters.value.date_range = ''
  sortBy.value = 'approval_date:desc'
  fetchWages()
}

const exportWages = async () => {
  try {
    const response = await axios.get('/wages/export', {
      params: {
        search: searchQuery.value,
        date_range: filters.value.date_range,
        sort: sortBy.value,
      },
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'wages-export.csv'
    
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