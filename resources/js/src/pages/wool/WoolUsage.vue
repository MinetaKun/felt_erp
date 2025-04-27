<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wool Usage</h3>
          <p class="text-indigo-100 text-sm mt-1">Track and manage wool usage records</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Record
          </button>
          <button @click="exportUsage" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters Section -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.start_date"
              @change="fetchUsage"
            >
          </div>
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.end_date"
              @change="fetchUsage"
            >
          </div>
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select v-model="filters.department" @change="fetchUsage"
                    class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200">
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.name">
                {{ dept.name }}
              </option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button 
              class="w-full inline-flex items-center justify-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              @click="resetFilters"
            >
              <i class="fas fa-sync-alt mr-1"></i> Reset
            </button>
          </div>
        </div>

        <!-- Usage Records Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.N.</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roll</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total KG</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Purpose</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(usage, index) in usages" :key="usage.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(usage.date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.bill_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.department }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.color_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.roll_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.total_kg }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ usage.usage_purpose }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button @click="editUsage(usage)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="deleteUsage(usage.id)" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="usages.length === 0">
                <td colspan="9" class="px-6 py-4 text-center">No records found</td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination -->
          <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Previous
              </button>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
                  of
                  <span class="font-medium">{{ pagination.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button 
                    @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Previous</span>
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  
                  <template v-for="page in pagination.last_page" :key="page">
                    <button 
                      @click="changePage(page)"
                      :class="[
                        page === pagination.current_page 
                          ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                      ]"
                    >
                      {{ page }}
                    </button>
                  </template>

                  <button 
                    @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
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

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ showEditModal ? 'Edit Record' : 'Add New Record' }}
          </h3>
          <form @submit.prevent="showEditModal ? updateUsage() : createUsage()" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Date</label>
              <input v-model="form.date" type="date" id="date" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="bill_number">Bill Number</label>
              <input v-model="form.bill_number" type="text" id="bill_number" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="department">Department</label>
              <select v-model="form.department" id="department" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.name">
                  {{ dept.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="color_number">Color Number</label>
              <input v-model="form.color_number" type="text" id="color_number" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="roll_count">Roll Count</label>
              <input v-model="form.roll_count" type="number" id="roll_count" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="total_kg">Total KG</label>
              <input v-model="form.total_kg" type="number" step="0.01" id="total_kg" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="usage_purpose">Usage Purpose</label>
              <input v-model="form.usage_purpose" type="text" id="usage_purpose" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="remarks">Remarks</label>
              <textarea v-model="form.remarks" id="remarks"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="supplier_id">Supplier</label>
              <select v-model="form.supplier_id" id="supplier_id" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModal"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                {{ showEditModal ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const usages = ref([])
const departments = ref([])
const suppliers = ref([])
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingId = ref(null)

const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1
})

const filters = ref({
  start_date: '',
  end_date: '',
  department: ''
})

const form = ref({
  date: '',
  bill_number: '',
  department: '',
  color_number: '',
  roll_count: '',
  total_kg: '',
  usage_purpose: '',
  remarks: '',
  supplier_id: ''
})

const fetchUsage = async () => {
  try {
    const response = await axios.get('/wool/usage', {
      params: {
        ...filters.value,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    
    if (response.data && response.data.success) {
      // Check if data exists in the response
      if (response.data.data && response.data.data.usages) {
        usages.value = response.data.data.usages
      } else {
        usages.value = []
      }
      
      // Update pagination if available
      if (response.data.meta) {
        pagination.value = {
          current_page: response.data.meta.current_page || 1,
          per_page: response.data.meta.per_page || 10,
          total: response.data.meta.total || 0,
          last_page: response.data.meta.last_page || 1
        }
      }
    } else {
      usages.value = []
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'No data available',
        timer: 3000,
        showConfirmButton: false
      })
    }
  } catch (error) {
    console.error('Error fetching usage:', error)
    usages.value = []
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load usage data. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/departments')
    departments.value = response.data
  } catch (error) {
    console.error('Error fetching departments:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load departments. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const fetchSuppliers = async () => {
  try {
    const response = await axios.get('/wool/suppliers')
    suppliers.value = response.data
  } catch (error) {
    console.error('Error fetching suppliers:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load suppliers. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const changePage = (page) => {
  pagination.value.current_page = page
  fetchUsage()
}

const createUsage = async () => {
  try {
    await axios.post('/wool/usage', form.value)
    closeModal()
    fetchUsage()
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Usage record created successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error creating usage:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to create usage record',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const editUsage = (usage) => {
  editingId.value = usage.id
  form.value = { ...usage }
  showEditModal.value = true
}

const updateUsage = async () => {
  try {
    await axios.put(`/wool/usage/${editingId.value}`, form.value)
    closeModal()
    fetchUsage()
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Usage record updated successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error updating usage:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to update usage record',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const deleteUsage = async (id) => {
  try {
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    })

    if (result.isConfirmed) {
      await axios.delete(`/wool/usage/${id}`)
      fetchUsage()
      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Usage record has been deleted.',
        timer: 2000,
        showConfirmButton: false
      })
    }
  } catch (error) {
    console.error('Error deleting usage:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to delete usage record',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const exportUsage = async () => {
  try {
    const response = await axios.get('/wool/usage/export', {
      params: filters.value,
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'wool-usage-export.csv'
    
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
    console.error('Error exporting usage:', error)
    let errorMessage = 'Failed to export usage data. Please try again.'
    
    if (error.response?.data) {
      const reader = new FileReader()
      reader.onload = () => {
        try {
          const errorData = JSON.parse(reader.result)
          errorMessage = errorData.message || errorMessage
        } catch (e) {
          console.error('Error parsing error response:', e)
        }
      }
      reader.readAsText(error.response.data)
    }
    
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: errorMessage,
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const resetFilters = () => {
  filters.value = {
    start_date: '',
    end_date: '',
    department: ''
  }
  fetchUsage()
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = {
    date: '',
    bill_number: '',
    department: '',
    color_number: '',
    roll_count: '',
    total_kg: '',
    usage_purpose: '',
    remarks: '',
    supplier_id: ''
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchUsage()
  fetchDepartments()
  fetchSuppliers()
})
</script> 