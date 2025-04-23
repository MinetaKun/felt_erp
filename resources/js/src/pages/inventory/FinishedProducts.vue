<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Finished Products Inventory</h1>
      <button
        @click="showAddModal = true"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <span class="mr-2">+</span>
        Add Finished Product
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Product Type</label>
          <select
            v-model="filters.type"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option v-for="type in productTypes" :key="type" :value="type">
              {{ type }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
          <select
            v-model="filters.size"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Sizes</option>
            <option v-for="size in productSizes" :key="size" :value="size">
              {{ size }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            type="text"
            v-model="filters.search"
            placeholder="Search products..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Inventory Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Products</h3>
        <p class="mt-2 text-3xl font-bold text-blue-600">{{ totalProducts }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Quantity</h3>
        <p class="mt-2 text-3xl font-bold text-green-600">{{ totalQuantity }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Value</h3>
        <p class="mt-2 text-3xl font-bold text-purple-600">{{ formatCurrency(totalValue) }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Active Orders</h3>
        <p class="mt-2 text-3xl font-bold text-orange-600">{{ activeOrders }}</p>
      </div>
    </div>

    <!-- Active Orders Timeline -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-lg font-medium text-gray-900 mb-4">Active Orders Timeline</h2>
      <div class="space-y-4">
        <div v-for="order in activeOrderTimeline" :key="order.id" class="border-l-4 border-blue-500 pl-4 py-2">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-sm font-medium text-gray-900">Order #{{ order.order_number }}</h3>
              <p class="text-sm text-gray-500">{{ order.product_name }} ({{ order.quantity }} units)</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Due: {{ formatDate(order.due_date) }}</p>
              <div class="mt-1">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-500 h-2 rounded-full"
                    :style="{ width: `${order.progress}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ order.progress }}% Complete</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-medium text-gray-900">Finished Products</h2>
        <button
          @click="printInventory"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center"
        >
          <span class="mr-2">🖨️</span>
          Print Inventory
        </button>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Size
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Quantity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Price
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Wage/Unit
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="product in products" :key="product.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
              <div class="text-sm text-gray-500">{{ product.description }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ product.type }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ product.size }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ product.quantity }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatCurrency(product.price) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatCurrency(product.wage_per_unit) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'bg-red-100 text-red-800': product.status === 'out_of_stock',
                  'bg-yellow-100 text-yellow-800': product.status === 'low_stock',
                  'bg-green-100 text-green-800': product.status === 'in_stock'
                }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ formatStatus(product.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="editProduct(product)"
                class="text-blue-600 hover:text-blue-900 mr-4"
              >
                Edit
              </button>
              <button
                @click="updateQuantity(product)"
                class="text-green-600 hover:text-green-900"
              >
                Update Quantity
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showAddModal" :title="editingProduct ? 'Edit Finished Product' : 'Add Finished Product'">
      <form @submit.prevent="saveProduct" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input
            type="text"
            v-model="form.name"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Type</label>
          <input
            type="text"
            v-model="form.type"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Size</label>
          <input
            type="text"
            v-model="form.size"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input
            type="number"
            v-model="form.quantity"
            required
            min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Price</label>
          <input
            type="number"
            v-model="form.price"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Wage per Unit</label>
          <input
            type="number"
            v-model="form.wage_per_unit"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Location</label>
          <input
            type="text"
            v-model="form.location"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="button"
            @click="showAddModal = false"
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ editingProduct ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Update Quantity Modal -->
    <Modal v-model="showQuantityModal" title="Update Quantity">
      <form @submit.prevent="saveQuantityUpdate" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Current Quantity</label>
          <p class="mt-1 text-sm text-gray-500">
            {{ selectedProduct.quantity }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Operation</label>
          <select
            v-model="quantityForm.operation"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="add">Add Quantity</option>
            <option value="subtract">Subtract Quantity</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input
            type="number"
            v-model="quantityForm.quantity"
            required
            min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="button"
            @click="showQuantityModal = false"
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Update Quantity
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Modal from '../../components/Modal.vue'
import axios from 'axios'

const products = ref([])
const activeOrderTimeline = ref([])
const showAddModal = ref(false)
const showQuantityModal = ref(false)
const editingProduct = ref(null)
const selectedProduct = ref(null)

const filters = reactive({
  type: '',
  size: '',
  status: '',
  search: ''
})

const form = reactive({
  name: '',
  type: '',
  size: '',
  quantity: 0,
  price: 0,
  wage_per_unit: 0,
  description: '',
  location: ''
})

const quantityForm = reactive({
  operation: 'add',
  quantity: 0
})

const productTypes = computed(() => {
  return [...new Set(products.value.map(p => p.type))]
})

const productSizes = computed(() => {
  return [...new Set(products.value.map(p => p.size))]
})

const totalProducts = computed(() => {
  return products.value.length
})

const totalQuantity = computed(() => {
  return products.value.reduce((sum, product) => sum + product.quantity, 0)
})

const totalValue = computed(() => {
  return products.value.reduce((sum, product) => sum + (product.quantity * product.price), 0)
})

const activeOrders = computed(() => {
  return activeOrderTimeline.value.length
})

onMounted(async () => {
  await fetchProducts()
  await fetchActiveOrders()
})

async function fetchProducts() {
  try {
    const response = await axios.get('/finished-products', { params: filters })
    if (response.data.success) {
      products.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch products:', error)
  }
}

async function fetchActiveOrders() {
  try {
    const response = await axios.get('/orders/active')
    if (response.data.success) {
      activeOrderTimeline.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch active orders:', error)
  }
}

function editProduct(product) {
  editingProduct.value = product
  Object.assign(form, product)
  showAddModal.value = true
}

function updateQuantity(product) {
  selectedProduct.value = product
  quantityForm.operation = 'add'
  quantityForm.quantity = 0
  showQuantityModal.value = true
}

async function saveProduct() {
  try {
    const url = editingProduct.value
      ? `/finished-products/${editingProduct.value.id}`
      : '/finished-products'
    const method = editingProduct.value ? 'put' : 'post'
    
    const response = await axios[method](url, form)
    if (response.data.success) {
      showAddModal.value = false
      await fetchProducts()
      resetForm()
    }
  } catch (error) {
    console.error('Failed to save product:', error)
  }
}

async function saveQuantityUpdate() {
  try {
    const response = await axios.post(
      `/finished-products/${selectedProduct.value.id}/update-quantity`,
      quantityForm
    )
    if (response.data.success) {
      showQuantityModal.value = false
      await fetchProducts()
    }
  } catch (error) {
    console.error('Failed to update quantity:', error)
  }
}

function resetForm() {
  editingProduct.value = null
  Object.keys(form).forEach(key => {
    form[key] = ''
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString()
}

function formatStatus(status) {
  const statuses = {
    in_stock: 'In Stock',
    low_stock: 'Low Stock',
    out_of_stock: 'Out of Stock'
  }
  return statuses[status] || status
}

function printInventory() {
  window.print()
}
</script>

<style>
@media print {
  .no-print {
    display: none;
  }
  .print-only {
    display: block;
  }
}
</style> 