<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Payroll Management</h3>
          <p class="text-indigo-100 text-sm mt-1">View and manage monthly payrolls</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="generateBankTransferSheet" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-university mr-2"></i> Bank Transfer Sheet
          </button>
          <button @click="generateCashPaymentSheet" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-money-bill-wave mr-2"></i> Cash Payment Sheet
          </button>
          <button @click="exportPayroll" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Date Range Selection -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
              v-model="startDate"
              @change="fetchPayrolls"
            >
          </div>
          <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
              v-model="endDate"
              @change="fetchPayrolls"
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
              <p class="text-3xl font-bold text-blue-600">{{ payrolls.length }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Wages</h3>
              <p class="text-3xl font-bold text-green-600">{{ formatCurrency(totalWages) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Advances</h3>
              <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(totalAdvances) }}</p>
            </div>
          </div>

          <!-- Payroll Table -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.N.</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Food Allowance</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wages</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advance</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(payroll, index) in payrolls" :key="payroll.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ payroll.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.salary) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.food_allowance) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.wages) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.allowances) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.advance) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(payroll.net_salary) }}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="4" class="px-6 py-4 text-right font-bold">Total:</td>
                  <td class="px-6 py-4 font-bold">{{ formatCurrency(totalWages) }}</td>
                  <td class="px-6 py-4 font-bold">{{ formatCurrency(totalAllowances) }}</td>
                  <td class="px-6 py-4 font-bold">{{ formatCurrency(totalAdvances) }}</td>
                  <td class="px-6 py-4 font-bold">{{ formatCurrency(totalNetSalary) }}</td>
                </tr>
              </tfoot>
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

const payrolls = ref([])
const loading = ref(false)
const error = ref(null)
const startDate = ref('')
const endDate = ref('')

const totalWages = computed(() => {
  return payrolls.value.reduce((total, payroll) => total + (payroll.wages || 0), 0)
})

const totalAllowances = computed(() => {
  return payrolls.value.reduce((total, payroll) => total + (payroll.allowances || 0), 0)
})

const totalAdvances = computed(() => {
  return payrolls.value.reduce((total, payroll) => total + (payroll.advance || 0), 0)
})

const totalNetSalary = computed(() => {
  return payrolls.value.reduce((total, payroll) => total + (payroll.net_salary || 0), 0)
})

const fetchPayrolls = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/payroll/salary-calculation', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      }
    })
    
    if (response.data.success) {
      payrolls.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to fetch payrolls'
    }
  } catch (err) {
    console.error('Error fetching payrolls:', err)
    error.value = 'Failed to fetch payrolls. Please try again.'
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  startDate.value = ''
  endDate.value = ''
  fetchPayrolls()
}

const exportPayroll = async () => {
  try {
    const response = await axios.get('/payroll/salary-calculation/export', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      },
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'payroll-report.csv'
    
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
    console.error('Error exporting payroll:', error)
    alert('Failed to export payroll. Please try again.')
  }
}

const generateBankTransferSheet = async () => {
  if (!startDate.value || !endDate.value) {
    alert('Please select both start and end dates')
    return
  }

  try {
    const response = await axios.get('/payroll/bank-transfer-sheet', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      },
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'bank-transfer-sheet.pdf'
    
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
    console.error('Error generating bank transfer sheet:', error)
    alert('Failed to generate bank transfer sheet. Please try again.')
  }
}

const generateCashPaymentSheet = async () => {
  if (!startDate.value || !endDate.value) {
    alert('Please select both start and end dates')
    return
  }

  try {
    const response = await axios.get('/payroll/cash-payment-sheet', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      },
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'cash-payment-sheet.pdf'
    
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
    console.error('Error generating cash payment sheet:', error)
    alert('Failed to generate cash payment sheet. Please try again.')
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR',
    currencyDisplay: 'symbol'
  }).format(amount || 0).replace('₹', 'Rs.')
}

onMounted(() => {
  fetchPayrolls()
})
</script> 