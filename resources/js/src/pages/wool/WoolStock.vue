<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Wool Stock</h1>
      <div class="flex space-x-4">
        <button @click="showFilterModal = true" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
          Filter
        </button>
        <button @click="exportStock" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
          Export
        </button>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wool Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="stock in filteredStock" :key="stock.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ stock.wool_type }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ stock.color }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ stock.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ stock.unit }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(stock.unit_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(stock.received_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="stock.orderItem?.order" class="text-blue-600 hover:text-blue-900 cursor-pointer"
                    @click="viewOrder(stock.orderItem.order)">
                {{ stock.orderItem.order.order_number }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="editStock(stock)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
              <button @click="deleteStock(stock.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Filter Modal -->
    <div v-if="showFilterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Filter Stock</h3>
          <form @submit.prevent="applyFilters" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="wool_type">Wool Type</label>
              <input v-model="filters.wool_type" type="text" id="wool_type"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="color">Color</label>
              <input v-model="filters.color" type="text" id="color"
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

    <!-- Edit Stock Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Stock</h3>
          <form @submit.prevent="updateStock" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="wool_type">Wool Type</label>
              <input v-model="editForm.wool_type" type="text" id="wool_type" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="color">Color</label>
              <input v-model="editForm.color" type="text" id="color" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="quantity">Quantity</label>
              <input v-model="editForm.quantity" type="number" step="0.01" id="quantity" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="unit">Unit</label>
              <input v-model="editForm.unit" type="text" id="unit" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="unit_price">Unit Price</label>
              <input v-model="editForm.unit_price" type="number" step="0.01" id="unit_price" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="received_date">Received Date</label>
              <input v-model="editForm.received_date" type="date" id="received_date" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="editForm.notes" id="notes"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeEditModal"
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                Cancel
              </button>
              <button type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
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

const stock = ref([])
const showFilterModal = ref(false)
const showEditModal = ref(false)
const editingId = ref(null)

const filters = ref({
  wool_type: '',
  color: '',
  date_from: '',
  date_to: ''
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

const fetchStock = async () => {
  try {
    const response = await axios.get('/wool/stock')
    stock.value = response.data
  } catch (error) {
    console.error('Error fetching stock:', error)
  }
}

const filteredStock = computed(() => {
  return stock.value.filter(item => {
    const matchesWoolType = !filters.value.wool_type || 
      item.wool_type.toLowerCase().includes(filters.value.wool_type.toLowerCase())
    const matchesColor = !filters.value.color || 
      item.color.toLowerCase().includes(filters.value.color.toLowerCase())
    const matchesDateFrom = !filters.value.date_from || 
      new Date(item.received_date) >= new Date(filters.value.date_from)
    const matchesDateTo = !filters.value.date_to || 
      new Date(item.received_date) <= new Date(filters.value.date_to)
    
    return matchesWoolType && matchesColor && matchesDateFrom && matchesDateTo
  })
})

const applyFilters = () => {
  showFilterModal.value = false
}

const resetFilters = () => {
  filters.value = {
    wool_type: '',
    color: '',
    date_from: '',
    date_to: ''
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
    
    // Create a blob from the response data
    const blob = new Blob([response.data], { type: 'text/csv' });
    
    // Create a temporary URL for the blob
    const url = window.URL.createObjectURL(blob);
    
    // Create a temporary link element
    const link = document.createElement('a');
    link.href = url;
    
    // Get the filename from the Content-Disposition header or use a default
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
    
    // Clean up
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