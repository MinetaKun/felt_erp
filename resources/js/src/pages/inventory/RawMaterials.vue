<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Raw Materials Inventory</h1>
      <button
        @click="showAddModal = true"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <span class="mr-2">+</span>
        Add Raw Material
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filters.category"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Categories</option>
            <option value="raw_material">Raw Materials</option>
            <option value="machine">Machines & Equipment</option>
            <option value="gadget">Gadgets & Electronics</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="filters.type"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option v-for="type in materialTypes" :key="type" :value="type">
              {{ type }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
          <select
            v-model="filters.stockStatus"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="low">Low Stock</option>
            <option value="warning">Warning</option>
            <option value="good">Good</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            type="text"
            v-model="filters.search"
            placeholder="Search inventory..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>
    </div>

    <!-- Inventory Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Items</h3>
        <p class="mt-2 text-3xl font-bold text-blue-600">{{ totalItems }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Low Stock Items</h3>
        <p class="mt-2 text-3xl font-bold text-red-600">{{ lowStockCount }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Value</h3>
        <p class="mt-2 text-3xl font-bold text-green-600">{{ formatCurrency(totalValue) }}</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Machines & Equipment</h3>
        <p class="mt-2 text-3xl font-bold text-purple-600">{{ machineCount }}</p>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockMaterials.length > 0" class="mb-6">
      <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            !
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Low Stock Alert</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>The following items are running low on stock:</p>
              <ul class="list-disc pl-5 mt-2">
                <li v-for="material in lowStockMaterials" :key="material.id">
                  {{ material.name }} ({{ material.quantity }} {{ material.unit }} remaining)
                  <button
                    @click="updateStock(material)"
                    class="ml-2 text-blue-600 hover:text-blue-800 text-sm"
                  >
                    Restock
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Materials Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-medium text-gray-900">Inventory Items</h2>
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
              Category
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Quantity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Unit
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Price/Unit
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
          <tr v-for="material in materials" :key="material.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ material.name }}</div>
              <div class="text-sm text-gray-500">{{ material.description }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatCategory(material.category) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ material.type }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ material.quantity }} {{ material.unit }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ material.unit }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatCurrency(material.price_per_unit) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'bg-red-100 text-red-800': material.stock_status === 'low',
                  'bg-yellow-100 text-yellow-800': material.stock_status === 'warning',
                  'bg-green-100 text-green-800': material.stock_status === 'good'
                }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ material.stock_status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="editMaterial(material)"
                class="text-blue-600 hover:text-blue-900 mr-4"
              >
                Edit
              </button>
              <button
                @click="updateStock(material)"
                class="text-green-600 hover:text-green-900"
              >
                Update Stock
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showAddModal" :title="editingMaterial ? 'Edit Inventory Item' : 'Add Inventory Item'">
      <form @submit.prevent="saveMaterial" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Category</label>
          <select
            v-model="form.category"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="raw_material">Raw Materials</option>
            <option value="machine">Machines & Equipment</option>
            <option value="gadget">Gadgets & Electronics</option>
            <option value="other">Other</option>
          </select>
        </div>
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
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input
            type="number"
            v-model="form.quantity"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Unit</label>
          <input
            type="text"
            v-model="form.unit"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Min Stock Level</label>
          <input
            type="number"
            v-model="form.min_stock_level"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Price per Unit</label>
          <input
            type="number"
            v-model="form.price_per_unit"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Supplier</label>
          <input
            type="text"
            v-model="form.supplier"
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
            {{ editingMaterial ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Update Stock Modal -->
    <Modal v-model="showStockModal" title="Update Stock">
      <form @submit.prevent="saveStockUpdate" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Current Stock</label>
          <p class="mt-1 text-sm text-gray-500">
            {{ selectedMaterial.quantity }} {{ selectedMaterial.unit }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Operation</label>
          <select
            v-model="stockForm.operation"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="add">Add Stock</option>
            <option value="subtract">Subtract Stock</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <input
            type="number"
            v-model="stockForm.quantity"
            required
            min="0"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="button"
            @click="showStockModal = false"
            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Update Stock
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

const materials = ref([])
const lowStockMaterials = ref([])
const showAddModal = ref(false)
const showStockModal = ref(false)
const editingMaterial = ref(null)
const selectedMaterial = ref(null)

const filters = reactive({
  category: '',
  type: '',
  stockStatus: '',
  search: ''
})

const form = reactive({
  name: '',
  category: 'raw_material',
  type: '',
  quantity: 0,
  unit: '',
  min_stock_level: 0,
  price_per_unit: 0,
  supplier: '',
  description: '',
  location: ''
})

const stockForm = reactive({
  operation: 'add',
  quantity: 0
})

const materialTypes = computed(() => {
  return [...new Set(materials.value.map(m => m.type))]
})

const totalItems = computed(() => {
  return materials.value.length
})

const lowStockCount = computed(() => {
  return lowStockMaterials.value.length
})

const machineCount = computed(() => {
  return materials.value.filter(m => m.category === 'machine').length
})

const totalValue = computed(() => {
  return materials.value.reduce((sum, material) => {
    return sum + (material.quantity * material.price_per_unit)
  }, 0)
})

onMounted(async () => {
  await fetchMaterials()
  await fetchLowStockMaterials()
})

async function fetchMaterials() {
  try {
    const response = await axios.get('/raw-materials', { params: filters })
    if (response.data.success) {
      materials.value = response.data.data.data
    }
  } catch (error) {
    console.error('Failed to fetch materials:', error)
  }
}

async function fetchLowStockMaterials() {
  try {
    const response = await axios.get('/raw-materials/low-stock')
    if (response.data.success) {
      lowStockMaterials.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch low stock materials:', error)
  }
}

function editMaterial(material) {
  editingMaterial.value = material
  Object.assign(form, material)
  showAddModal.value = true
}

function updateStock(material) {
  selectedMaterial.value = material
  stockForm.operation = 'add'
  stockForm.quantity = 0
  showStockModal.value = true
}

async function saveMaterial() {
  try {
    const url = editingMaterial.value
      ? `/raw-materials/${editingMaterial.value.id}`
      : '/raw-materials'
    const method = editingMaterial.value ? 'put' : 'post'
    
    const response = await axios[method](url, form)
    if (response.data.success) {
      showAddModal.value = false
      await fetchMaterials()
      await fetchLowStockMaterials()
      resetForm()
    }
  } catch (error) {
    console.error('Failed to save material:', error)
  }
}

async function saveStockUpdate() {
  try {
    const response = await axios.post(
      `/raw-materials/${selectedMaterial.value.id}/update-stock`,
      stockForm
    )
    if (response.data.success) {
      showStockModal.value = false
      await fetchMaterials()
      await fetchLowStockMaterials()
    }
  } catch (error) {
    console.error('Failed to update stock:', error)
  }
}

function resetForm() {
  editingMaterial.value = null
  Object.keys(form).forEach(key => {
    form[key] = ''
  })
  form.category = 'raw_material'
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

function formatCategory(category) {
  const categories = {
    raw_material: 'Raw Materials',
    machine: 'Machines & Equipment',
    gadget: 'Gadgets & Electronics',
    other: 'Other'
  }
  return categories[category] || category
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