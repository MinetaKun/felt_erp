<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Wool Orders</h1>
      <button @click="showAddModal = true" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
        Create Order
      </button>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="order in orders" :key="order.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ order.order_number }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ order.supplier?.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.order_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(order.expected_delivery_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(order.total_amount) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(order.status)" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ order.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="viewOrder(order)" class="text-indigo-600 hover:text-indigo-900 mr-4">View</button>
              <button @click="updateStatus(order)" class="text-green-600 hover:text-green-900 mr-4">Update Status</button>
              <button @click="deleteOrder(order.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
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
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="order_date">Order Date</label>
              <input v-model="form.order_date" type="date" id="order_date" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="expected_delivery_date">Expected Delivery Date</label>
              <input v-model="form.expected_delivery_date" type="date" id="expected_delivery_date" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="form.notes" id="notes"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            
            <!-- Order Items -->
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2">Order Items</label>
              <div v-for="(item, index) in form.items" :key="index" class="mb-4 p-4 border rounded">
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Wool Type</label>
                  <input v-model="item.wool_type" type="text" required
                         class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Color</label>
                  <input v-model="item.color" type="text" required
                         class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Quantity</label>
                  <input v-model="item.quantity" type="number" step="0.01" required
                         class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Unit</label>
                  <input v-model="item.unit" type="text" required
                         class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Unit Price</label>
                  <input v-model="item.unit_price" type="number" step="0.01" required
                         class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-2">
                  <label class="block text-gray-700 text-sm font-bold mb-2">Specifications</label>
                  <textarea v-model="item.specifications"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
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
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                Cancel
              </button>
              <button type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
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
            <button @click="closeViewModal" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
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
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="statusForm.notes" id="notes"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeStatusModal"
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                Cancel
              </button>
              <button type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
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