<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Artisan Advances Management</h3>
          <p class="text-indigo-100 text-sm mt-1">Track and manage artisan advance payments</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="saveAdvances" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-save mr-2"></i> Save Advances
          </button>
          <button @click="generateAdvanceReport" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-file-invoice mr-2"></i> Generate Report
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Date Range Selection -->
        <div class="mb-6">
          <div class="flex gap-4">
            <div class="w-1/3">
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input 
                type="date" 
                v-model="startDate"
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                @change="fetchAdvances"
              >
            </div>
            <div class="w-1/3">
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input 
                type="date" 
                v-model="endDate"
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                @change="fetchAdvances"
              >
            </div>
            <div class="w-1/3">
              <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
              <button 
                @click="resetDateRange"
                class="w-full inline-flex items-center justify-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              >
                <i class="fas fa-sync-alt mr-1"></i> Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Advances Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.N.</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advance Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(advance, index) in advances" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select 
                    v-model="advance.artisan_id"
                    class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                  >
                    <option value="">Select Artisan</option>
                    <option v-for="artisan in artisans" :key="artisan.id" :value="artisan.id">
                      {{ artisan.name }}
                    </option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="number" 
                    v-model="advance.amount"
                    class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="date" 
                    v-model="advance.date"
                    class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input 
                    type="text" 
                    v-model="advance.notes"
                    class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                    placeholder="Optional notes"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button 
                    @click="removeRow(index)"
                    class="text-red-600 hover:text-red-900"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50">
              <tr>
                <td colspan="2" class="px-6 py-4 text-right font-bold">Total Advances:</td>
                <td class="px-6 py-4 font-bold">{{ formatCurrency(totalAdvances) }}</td>
                <td colspan="3"></td>
              </tr>
            </tfoot>
          </table>
          <div class="p-4 border-t border-gray-200">
            <button 
              @click="addNewRow"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-200"
            >
              <i class="fas fa-plus mr-2"></i> Add New Row
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const artisans = ref([])
const advances = ref([])
const startDate = ref('')
const endDate = ref('')

const totalAdvances = computed(() => {
  return advances.value.reduce((total, advance) => total + (Number(advance.amount) || 0), 0)
})

const showSuccess = (message) => {
  Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  })
}

const showError = (message) => {
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  })
}

const showConfirm = async (title, text) => {
  const result = await Swal.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, proceed!',
    cancelButtonText: 'Cancel'
  })
  return result.isConfirmed
}

const fetchArtisans = async () => {
  try {
    const response = await axios.get('/artisans')
    artisans.value = response.data.data
  } catch (error) {
    console.error('Error fetching artisans:', error)
    showError('Failed to fetch artisans')
  }
}

const fetchAdvances = async () => {
  try {
    const response = await axios.get('/payroll/advances', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value
      }
    })
    
    if (response.data.success) {
      advances.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching advances:', error)
    showError('Failed to fetch advances')
  }
}

const addNewRow = () => {
  advances.value.push({
    id: null,
    artisan_id: '',
    amount: 0,
    date: new Date().toISOString().split('T')[0],
    notes: ''
  })
}

const removeRow = async (index) => {
  const confirmed = await showConfirm(
    'Remove Advance',
    'Are you sure you want to remove this advance?'
  )
  
  if (confirmed) {
    advances.value.splice(index, 1)
  }
}

const resetDateRange = () => {
  startDate.value = ''
  endDate.value = ''
  fetchAdvances()
}

const formatDate = (date) => {
  if (!date) return '';
  return date.split('T')[0];
}

const saveAdvances = async () => {
  // Validate required fields
  const invalidAdvances = advances.value.filter(advance => 
    !advance.artisan_id || !advance.amount || !advance.date
  )

  if (invalidAdvances.length > 0) {
    showError('Please fill in all required fields for each advance')
    return
  }

  try {
    // Format the data before sending
    const formattedAdvances = advances.value.map(advance => ({
      ...advance,
      date: formatDate(advance.date)
    }))

    const response = await axios.post('/payroll/advances', {
      advances: formattedAdvances
    })
    
    if (response.data.success) {
      showSuccess('Advances saved successfully')
      fetchAdvances()
    } else {
      showError(response.data.message || 'Failed to save advances')
    }
  } catch (error) {
    console.error('Error saving advances:', error)
    showError('Failed to save advances. Please try again.')
  }
}

const generateAdvanceReport = async () => {
  if (!startDate.value || !endDate.value) {
    showError('Please select both start and end dates')
    return
  }

  try {
    const { value: format } = await Swal.fire({
      title: 'Select Report Format',
      input: 'select',
      inputOptions: {
        pdf: 'PDF',
        csv: 'CSV'
      },
      inputPlaceholder: 'Select a format',
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) {
          return 'You need to select a format'
        }
      }
    })

    if (format) {
      const response = await axios.get('/payroll/advances', {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
          format: format
        },
        responseType: 'blob'
      })

      // Create a blob from the response
      const blob = new Blob([response.data], { 
        type: format === 'pdf' ? 'application/pdf' : 'text/csv' 
      })

      // Create a URL for the blob
      const url = window.URL.createObjectURL(blob)

      // Create a temporary link element
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `artisan-advances-report_${format === 'pdf' ? '.pdf' : '.csv'}`)
      document.body.appendChild(link)

      // Trigger the download
      link.click()

      // Clean up
      window.URL.revokeObjectURL(url)
      document.body.removeChild(link)

      showSuccess('Report generated successfully')
    }
  } catch (error) {
    console.error('Error generating advance report:', error)
    showError('Failed to generate advance report')
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'INR',
    currencyDisplay: 'symbol'
  }).format(amount).replace('₹', 'Rs.')
}

onMounted(() => {
  fetchArtisans()
  fetchAdvances()
})
</script> 