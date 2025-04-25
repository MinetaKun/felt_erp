<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wool Orders</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your wool orders efficiently</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Create Order
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Orders Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-3 text-left border-b-2 border-gray-200">Order Number</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Supplier</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Order Date</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Expected Delivery</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Total Amount</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Status</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                <td class="p-3 border-t">{{ order.order_number }}</td>
                <td class="p-3 border-t">{{ order.supplier?.name }}</td>
                <td class="p-3 border-t">{{ formatDate(order.order_date) }}</td>
                <td class="p-3 border-t">{{ formatDate(order.expected_delivery_date) }}</td>
                <td class="p-3 border-t">{{ formatCurrency(order.total_amount) }}</td>
                <td class="p-3 border-t">
                  <span :class="getStatusClass(order.status)" 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ order.status }}
                  </span>
                </td>
                <td class="p-3 border-t">
                  <button @click="viewOrder(order)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="updateStatus(order)" class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 mr-1">
                    <i class="fas fa-sync"></i>
                  </button>
                  <button @click="deleteOrder(order.id)" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="orders.length === 0">
                <td colspan="7" class="p-3 text-center border-t">No orders found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Order Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Order</h3>
          <form @submit.prevent="createOrder" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="wool_supplier_id">Supplier</label>
              <select v-model="form.wool_supplier_id" id="wool_supplier_id" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="order_date">Order Date</label>
              <input v-model="form.order_date" type="date" id="order_date" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="expected_delivery_date">Expected Delivery Date</label>
              <input v-model="form.expected_delivery_date" type="date" id="expected_delivery_date" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="form.notes" id="notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            
            <!-- Order Items -->
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2">Order Items</label>
              <div v-for="(item, index) in form.items" :key="index" class="mb-4 p-4 border rounded">
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Wool Type</label>
                  <input v-model="item.wool_type" type="text" required
                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Color</label>
                  <input v-model="item.color" type="text" required
                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Quantity</label>
                  <input v-model="item.quantity" type="number" step="0.01" required
                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Unit</label>
                  <input v-model="item.unit" type="text" required
                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Unit Price</label>
                  <input v-model="item.unit_price" type="number" step="0.01" required
                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Specifications</label>
                  <textarea v-model="item.specifications"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                  Remove Item
                </button>
              </div>
              <button type="button" @click="addItem" class="mt-2 text-blue-600 hover:text-blue-900">
                Add Item
              </button>
            </div>

            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModal"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Create Order
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Order Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Order Details</h3>
          <div class="mt-4 text-left">
            <p><strong>Order Number:</strong> {{ selectedOrder.order_number }}</p>
            <p><strong>Supplier:</strong> {{ selectedOrder.supplier?.name }}</p>
            <p><strong>Order Date:</strong> {{ formatDate(selectedOrder.order_date) }}</p>
            <p><strong>Expected Delivery:</strong> {{ formatDate(selectedOrder.expected_delivery_date) }}</p>
            <p><strong>Total Amount:</strong> {{ formatCurrency(selectedOrder.total_amount) }}</p>
            <p><strong>Status:</strong> {{ selectedOrder.status }}</p>
            <p><strong>Notes:</strong> {{ selectedOrder.notes }}</p>
            
            <h4 class="mt-4 font-bold">Order Items:</h4>
            <div v-for="(item, index) in selectedOrder.items" :key="index" class="mt-2 p-2 border rounded">
              <p><strong>Wool Type:</strong> {{ item.wool_type }}</p>
              <p><strong>Color:</strong> {{ item.color }}</p>
              <p><strong>Quantity:</strong> {{ item.quantity }} {{ item.unit }}</p>
              <p><strong>Unit Price:</strong> {{ formatCurrency(item.unit_price) }}</p>
              <p><strong>Total Price:</strong> {{ formatCurrency(item.total_price) }}</p>
              <p v-if="item.specifications"><strong>Specifications:</strong> {{ item.specifications }}</p>
            </div>
          </div>
          <div class="mt-4">
            <button @click="closeViewModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Status Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Update Order Status</h3>
          <form @submit.prevent="submitStatusUpdate" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status</label>
              <select v-model="statusForm.status" id="status" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="statusForm.notes" id="notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeStatusModal"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Update Status
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

const orders = ref([])
const suppliers = ref([])
const showAddModal = ref(false)
const showViewModal = ref(false)
const showStatusModal = ref(false)
const selectedOrder = ref({})
const editingId = ref(null)

const form = ref({
  wool_supplier_id: '',
  order_date: '',
  expected_delivery_date: '',
  notes: '',
  items: [{
    wool_type: '',
    color: '',
    quantity: '',
    unit: '',
    unit_price: '',
    specifications: ''
  }]
})

const statusForm = ref({
  status: '',
  notes: ''
})

const fetchOrders = async () => {
  try {
    const response = await axios.get('/wool/orders')
    orders.value = response.data
  } catch (error) {
    console.error('Error fetching orders:', error)
  }
}

const fetchSuppliers = async () => {
  try {
    const response = await axios.get('/wool/suppliers')
    suppliers.value = response.data
  } catch (error) {
    console.error('Error fetching suppliers:', error)
  }
}

const createOrder = async () => {
  try {
    await axios.post('/wool/orders', form.value)
    closeModal()
    fetchOrders()
  } catch (error) {
    console.error('Error creating order:', error)
  }
}

const viewOrder = (order) => {
  selectedOrder.value = order
  showViewModal.value = true
}

const updateStatus = (order) => {
  editingId.value = order.id
  statusForm.value = {
    status: order.status,
    notes: order.notes
  }
  showStatusModal.value = true
}

const submitStatusUpdate = async () => {
  try {
    await axios.put(`/wool/orders/${editingId.value}`, statusForm.value)
    closeStatusModal()
    fetchOrders()
  } catch (error) {
    console.error('Error updating order status:', error)
  }
}

const deleteOrder = async (id) => {
  if (confirm('Are you sure you want to delete this order?')) {
    try {
      await axios.delete(`/wool/orders/${id}`)
      fetchOrders()
    } catch (error) {
      console.error('Error deleting order:', error)
    }
  }
}

const addItem = () => {
  form.value.items.push({
    wool_type: '',
    color: '',
    quantity: '',
    unit: '',
    unit_price: '',
    specifications: ''
  })
}

const removeItem = (index) => {
  form.value.items.splice(index, 1)
}

const closeModal = () => {
  showAddModal.value = false
  form.value = {
    wool_supplier_id: '',
    order_date: '',
    expected_delivery_date: '',
    notes: '',
    items: [{
      wool_type: '',
      color: '',
      quantity: '',
      unit: '',
      unit_price: '',
      specifications: ''
    }]
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedOrder.value = {}
}

const closeStatusModal = () => {
  showStatusModal.value = false
  editingId.value = null
  statusForm.value = {
    status: '',
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

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchOrders()
  fetchSuppliers()
})
</script> 