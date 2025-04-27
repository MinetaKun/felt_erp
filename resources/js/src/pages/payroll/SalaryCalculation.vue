<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Salary Calculation</h3>
          <p class="text-indigo-100 text-sm mt-1">Calculate and manage salaries for all employees</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="saveCalculations" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-save mr-2"></i> Save
          </button>
          <button @click="exportCalculations" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.start_date"
              @change="fetchData"
            >
          </div>
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input 
              type="date" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.end_date"
              @change="fetchData"
            >
          </div>
          <div class="w-full md:w-1/4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.department"
              @change="fetchData"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
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
          <!-- Salary Table -->
          <div class="bg-white rounded-lg shadow overflow-x-auto">
            <div class="min-w-full">
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
                  <tr v-for="(employee, index) in employees" :key="employee.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ employee.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input 
                        type="number" 
                        class="w-32 p-1 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                        v-model="employee.salary"
                        @input="calculateNetSalary(employee)"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input 
                        type="number" 
                        class="w-32 p-1 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                        v-model="employee.food_allowance"
                        @input="calculateNetSalary(employee)"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(employee.wages) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input 
                        type="number" 
                        class="w-32 p-1 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                        v-model="employee.allowances"
                        @input="calculateNetSalary(employee)"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input 
                        type="number" 
                        class="w-32 p-1 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
                        v-model="employee.advance"
                        @input="calculateNetSalary(employee)"
                      >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(employee.net_salary) }}</td>
                  </tr>
                </tbody>
              </table>
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
import Swal from 'sweetalert2'

const employees = ref([])
const departments = ref([])
const loading = ref(false)
const error = ref(null)

const filters = ref({
  start_date: '',
  end_date: '',
  department: ''
})

const fetchData = async () => {
  loading.value = true
  error.value = null
  try {
    // Fetch employees with their wages from orders
    const response = await axios.get('/payroll/salary-calculation', {
      params: filters.value
    })
    
    if (response.data.success) {
      employees.value = response.data.data.map(employee => ({
        ...employee,
        salary: employee.salary || null,
        food_allowance: employee.food_allowance || null,
        allowances: employee.allowances || null,
        advance: employee.advance || null,
        net_salary: 0
      }))
      // Calculate initial net salary for each employee
      employees.value.forEach(employee => calculateNetSalary(employee))
    } else {
      error.value = response.data.message || 'Failed to fetch data'
    }
  } catch (err) {
    console.error('Error fetching data:', err)
    error.value = 'Failed to fetch data. Please try again.'
  } finally {
    loading.value = false
  }
}

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/departments')
    departments.value = response.data
  } catch (err) {
    console.error('Error fetching departments:', err)
  }
}

const resetFilters = () => {
  filters.value = {
    start_date: '',
    end_date: '',
    department: ''
  }
  fetchData()
}

const calculateNetSalary = (employee) => {
  // Calculate net salary: salary + allowances + food_allowance + wages - advance
  const netSalary = (employee.salary || 0) +
                   (employee.allowances || 0) +
                   (employee.food_allowance || 0) +
                   (employee.wages || 0) -
                   (employee.advance || 0)

  employee.net_salary = netSalary
}

const saveCalculations = async () => {
  try {
    // Calculate net salary for all employees before saving
    employees.value.forEach(employee => calculateNetSalary(employee))

    const response = await axios.post('/payroll/salary-calculation', {
      employees: employees.value.map(employee => ({
        artisan_id: employee.id,
        salary: employee.salary,
        food_allowance: employee.food_allowance,
        allowances: employee.allowances,
        advance: employee.advance,
        start_date: filters.value.start_date,
        end_date: filters.value.end_date
      }))
    })
    
    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Salary calculations saved successfully',
        timer: 2000,
        showConfirmButton: false
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: response.data.message || 'Failed to save salary calculations'
      })
    }
  } catch (error) {
    console.error('Error saving salary calculations:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save salary calculations. Please try again.'
    })
  }
}

const exportCalculations = async () => {
  try {
    const response = await axios.get('/payroll/salary-calculation/export', {
      params: filters.value,
      responseType: 'blob'
    })
    
    const blob = new Blob([response.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const contentDisposition = response.headers['content-disposition']
    let filename = 'salary-calculations.csv'
    
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

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Salary calculations exported successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error exporting salary calculations:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to export salary calculations. Please try again.'
    })
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
  fetchDepartments()
  fetchData()
})
</script> 