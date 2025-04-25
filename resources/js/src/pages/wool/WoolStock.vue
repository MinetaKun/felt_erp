<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wool Stock</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your wool stock efficiently</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="exportStock" class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm">
            <i class="fas fa-download mr-2"></i> Export
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters Section -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <input 
              type="text" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              placeholder="Search by wool type or color..." 
              v-model="searchQuery"
              @input="debouncedFilterStock"
            >
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.wool_type" 
              @change="filterStock"
            >
              <option value="">All Wool Types</option>
              <option v-for="type in uniqueWoolTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.color" 
              @change="filterStock"
            >
              <option value="">All Colors</option>
              <option v-for="color in uniqueColors" :key="color" :value="color">
                {{ color }}
              </option>
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

        <!-- Stock Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-3 text-left border-b-2 border-gray-200">Wool Type</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Color</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Quantity</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Unit</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Unit Price</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Received Date</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Order Reference</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="stock in filteredStock" :key="stock.id" class="hover:bg-gray-50">
                <td class="p-3 border-t">{{ stock.wool_type }}</td>
                <td class="p-3 border-t">{{ stock.color }}</td>
                <td class="p-3 border-t">{{ stock.quantity }}</td>
                <td class="p-3 border-t">{{ stock.unit }}</td>
                <td class="p-3 border-t">{{ formatCurrency(stock.unit_price) }}</td>
                <td class="p-3 border-t">{{ formatDate(stock.received_date) }}</td>
                <td class="p-3 border-t">
                  <span v-if="stock.orderItem?.order" class="text-blue-600 hover:text-blue-900 cursor-pointer"
                        @click="viewOrder(stock.orderItem.order)">
                    {{ stock.orderItem.order.order_number }}
                  </span>
                </td>
                <td class="p-3 border-t">
                  <button @click="editStock(stock)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="deleteStock(stock.id)" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredStock.length === 0">
                <td colspan="8" class="p-3 text-center border-t">No stock items found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit Stock Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Stock</h3>
          <form @submit.prevent="updateStock" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="wool_type">Wool Type</label>
              <input v-model="editForm.wool_type" type="text" id="wool_type" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="color">Color</label>
              <input v-model="editForm.color" type="text" id="color" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="quantity">Quantity</label>
              <input v-model="editForm.quantity" type="number" step="0.01" id="quantity" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="unit">Unit</label>
              <input v-model="editForm.unit" type="text" id="unit" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="unit_price">Unit Price</label>
              <input v-model="editForm.unit_price" type="number" step="0.01" id="unit_price" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="received_date">Received Date</label>
              <input v-model="editForm.received_date" type="date" id="received_date" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="editForm.notes" id="notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeEditModal"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Update
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
import { debounce } from 'lodash'

const stock = ref([])
const showEditModal = ref(false)
const editingId = ref(null)
const searchQuery = ref('')

const filters = ref({
  wool_type: '',
  color: ''
})

const editForm = ref({
  wool_type: '',
  color: '',
  quantity: '',
  unit: '',
  unit_price: '',
  received_date: '',
  notes: ''
})

const uniqueWoolTypes = computed(() => {
  const types = new Set(stock.value.map(item => item.wool_type))
  return Array.from(types).sort()
})

const uniqueColors = computed(() => {
  const colors = new Set(stock.value.map(item => item.color))
  return Array.from(colors).sort()
})

const fetchStock = async () => {
  try {
    const response = await axios.get('/wool/stock')
    stock.value = response.data
  } catch (error) {
    console.error('Error fetching stock:', error)
  }
}

const filterStock = () => {
  // The filteredStock computed property will handle the filtering
}

const debouncedFilterStock = debounce(filterStock, 300)

const filteredStock = computed(() => {
  return stock.value.filter(item => {
    const matchesSearch = !searchQuery.value || 
      item.wool_type.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.color.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesWoolType = !filters.value.wool_type || 
      item.wool_type === filters.value.wool_type
    const matchesColor = !filters.value.color || 
      item.color === filters.value.color
    
    return matchesSearch && matchesWoolType && matchesColor
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  filters.value = {
    wool_type: '',
    color: ''
  }
}

const editStock = (stockItem) => {
  editingId.value = stockItem.id
  editForm.value = { ...stockItem }
  showEditModal.value = true
}

const updateStock = async () => {
  try {
    await axios.put(`/wool/stock/${editingId.value}`, editForm.value)
    closeEditModal()
    fetchStock()
  } catch (error) {
    console.error('Error updating stock:', error)
  }
}

const deleteStock = async (id) => {
  if (confirm('Are you sure you want to delete this stock entry?')) {
    try {
      await axios.delete(`/wool/stock/${id}`)
      fetchStock()
    } catch (error) {
      console.error('Error deleting stock:', error)
    }
  }
}

const viewOrder = (order) => {
  // Implement order viewing logic
  console.log('View order:', order)
}

const exportStock = async () => {
  try {
    const response = await axios.post('/wool/stock/export', {}, {
      responseType: 'blob'
    });
    
    const blob = new Blob([response.data], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'wool-stock-export.csv';
    
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+)"/);
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1];
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    
    window.URL.revokeObjectURL(url);
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error exporting stock:', error);
  }
}

const closeEditModal = () => {
  showEditModal.value = false
  editingId.value = null
  editForm.value = {
    wool_type: '',
    color: '',
    quantity: '',
    unit: '',
    unit_price: '',
    received_date: '',
    notes: ''
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
  }).format(amount).replace('₹', 'Rs.');
}

onMounted(() => {
  fetchStock()
})
</script> 