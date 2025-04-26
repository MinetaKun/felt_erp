<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Raw Materials</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your raw materials inventory</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            @click="showAddModal = true"
            class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
          >
            <i class="fas fa-plus mr-2"></i> Add Material
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <input 
              type="text" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              placeholder="Search by name, type, or supplier..." 
              v-model="filters.search"
              @input="debouncedFetchMaterials"
            >
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.type" 
              @change="fetchMaterials"
            >
              <option value="">All Types</option>
              <option v-for="type in materialTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="filters.color" 
              @change="fetchMaterials"
            >
              <option value="">All Colors</option>
              <option v-for="color in materialColors" :key="color" :value="color">
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
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="material in materials" :key="material.id">
                    <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white">{{ material.id }}</td>
                    <td class="px-6 py-4 sticky left-0 bg-white">
                      <div class="text-sm font-medium text-gray-900">{{ material.name }}</div>
                      <div class="text-sm text-gray-500">{{ material.description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.color }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.unit }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.min_stock_level }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(material.price_per_unit) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.supplier }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ material.location }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="{
                          'bg-red-100 text-red-800': material.stock_status === 'low',
                          'bg-yellow-100 text-yellow-800': material.stock_status === 'warning',
                          'bg-green-100 text-green-800': material.stock_status === 'good'
                        }"
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                      >
                        {{ material.stock_status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium sticky right-0 bg-white">
                      <div class="flex space-x-2">
                        <button 
                          @click="editMaterial(material)"
                          class="text-blue-600 hover:text-blue-900"
                          title="Edit Material"
                        >
                          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                          </svg>
                        </button>
                        <button 
                          @click="deleteMaterial(material)"
                          class="text-red-600 hover:text-red-900"
                          title="Delete Material"
                        >
                          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination -->
          <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="currentPage > 1 ? currentPage-- : null"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Previous
              </button>
              <button
                @click="currentPage < lastPage ? currentPage++ : null"
                :disabled="currentPage === lastPage"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(currentPage * perPage, total) }}</span>
                  of
                  <span class="font-medium">{{ total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="currentPage > 1 ? currentPage-- : null"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    v-for="page in pages"
                    :key="page"
                    @click="currentPage = page"
                    :class="[
                      currentPage === page
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="currentPage < lastPage ? currentPage++ : null"
                    :disabled="currentPage === lastPage"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-model="showAddModal" :title="editingMaterial ? 'Edit Inventory Item' : 'Add Inventory Item'" class="max-w-2xl">
      <form @submit.prevent="saveMaterial" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            <label class="block text-sm font-medium text-gray-700">Color</label>
            <input
              type="text"
              v-model="form.color"
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
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier</label>
            <input
              type="text"
              v-model="form.supplier"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Location</label>
            <input
              type="text"
              v-model="form.location"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            v-model="form.description"
            rows="2"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
        </div>
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="showAddModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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

    <!-- Add this modal for preview -->
    <Modal v-model="showPreviewModal" title="Material Details">
      <div v-if="previewMaterial" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <h3 class="text-sm font-medium text-gray-500">Name</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.name }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Type</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.type }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Color</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.color }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Quantity</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.quantity }} {{ previewMaterial.unit }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Min Stock Level</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.min_stock_level }} {{ previewMaterial.unit }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Price per Unit</h3>
            <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(previewMaterial.price_per_unit) }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Supplier</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.supplier || 'Not specified' }}</p>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Location</h3>
            <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.location || 'Not specified' }}</p>
          </div>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500">Description</h3>
          <p class="mt-1 text-sm text-gray-900">{{ previewMaterial.description || 'No description available' }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500">Stock Status</h3>
          <span
            :class="{
              'bg-red-100 text-red-800': previewMaterial.stock_status === 'low',
              'bg-yellow-100 text-yellow-800': previewMaterial.stock_status === 'warning',
              'bg-green-100 text-green-800': previewMaterial.stock_status === 'good'
            }"
            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
          >
            {{ previewMaterial.stock_status }}
          </span>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import Modal from '../../components/Modal.vue'
import axios from 'axios'
import debounce from 'lodash/debounce'
import { useToast } from 'vue-toastification'
import Swal from 'sweetalert2'

const toast = useToast()

const materials = ref([])
const lowStockMaterials = ref([])
const showAddModal = ref(false)
const showStockModal = ref(false)
const editingMaterial = ref(null)
const selectedMaterial = ref(null)

// Pagination state
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const filters = reactive({
  type: '',
  color: '',
  stockStatus: '',
  search: ''
})

const form = reactive({
  name: '',
  type: '',
  color: '',
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

const materialColors = computed(() => {
  return [...new Set(materials.value.map(m => m.color))]
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

// Computed properties for pagination
const pages = computed(() => {
  const range = 2 // Number of pages to show before and after current page
  const pages = []
  
  for (let i = Math.max(1, currentPage.value - range); i <= Math.min(lastPage.value, currentPage.value + range); i++) {
    pages.push(i)
  }
  
  return pages
})

// Watch for changes in filters or pagination
watch([currentPage, perPage, () => ({ ...filters })], async () => {
  await fetchMaterials()
}, { deep: true })

onMounted(async () => {
  await fetchMaterials()
  await fetchLowStockMaterials()
})

async function fetchMaterials() {
  try {
    const response = await axios.get('/inventory/raw-materials', {
      params: {
        ...filters,
        page: currentPage.value,
        per_page: perPage.value
      }
    })
    
    if (response.data.success) {
      materials.value = response.data.data.data
      total.value = response.data.data.total
      lastPage.value = response.data.data.last_page
    }
  } catch (error) {
    console.error('Failed to fetch materials:', error)
  }
}

async function fetchLowStockMaterials() {
  try {
    const response = await axios.get('/inventory/raw-materials/low-stock')
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
      ? `/inventory/raw-materials/${editingMaterial.value.id}`
      : '/inventory/raw-materials'
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
      `/inventory/raw-materials/${selectedMaterial.value.id}/update-stock`,
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
    currency: 'INR',
    currencyDisplay: 'code'
  }).format(amount).replace('INR', 'Rs.');
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
  const printWindow = window.open('', '_blank')
  const materialsData = materials.value

  const printContent = `
    <html>
      <head>
        <title>Raw Materials Inventory Report</title>
        <style>
          body { font-family: Arial, sans-serif; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          th { background-color: #f5f5f5; }
          .header { text-align: center; margin-bottom: 20px; }
          .timestamp { text-align: right; margin-bottom: 20px; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Raw Materials Inventory Report</h1>
        </div>
        <div class="timestamp">
          Generated on: ${new Date().toLocaleString()}
        </div>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Type</th>
              <th>Color</th>
              <th>Quantity</th>
              <th>Unit</th>
              <th>Min Stock</th>
              <th>Price/Unit</th>
              <th>Supplier</th>
              <th>Location</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${materialsData.map(material => `
              <tr>
                <td>${material.id}</td>
                <td>${material.name}</td>
                <td>${material.type}</td>
                <td>${material.color}</td>
                <td>${material.quantity}</td>
                <td>${material.unit}</td>
                <td>${material.min_stock_level}</td>
                <td>${formatCurrency(material.price_per_unit)}</td>
                <td>${material.supplier}</td>
                <td>${material.location}</td>
                <td>${material.stock_status}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </body>
    </html>
  `

  printWindow.document.write(printContent)
  printWindow.document.close()
  printWindow.print()
}

async function applyFilters() {
  try {
    const response = await axios.get('/inventory/raw-materials', { params: filters })
    if (response.data.success) {
      materials.value = response.data.data.data
    }
  } catch (error) {
    console.error('Failed to apply filters:', error)
  }
}

async function deleteMaterial(material) {
  try {
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: `You are about to delete ${material.name}. This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      reverseButtons: true
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`/inventory/raw-materials/${material.id}`);
      
      if (response.data.success) {
        await Swal.fire({
          title: 'Deleted!',
          text: 'The material has been deleted successfully.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
        await fetchMaterials();
      } else {
        throw new Error(response.data.message || 'Failed to delete material');
      }
    }
  } catch (error) {
    console.error('Error deleting material:', error);
    Swal.fire({
      title: 'Error!',
      text: 'Failed to delete the material. Please try again.',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }
}

// Add preview modal state
const showPreviewModal = ref(false)
const previewMaterial = ref(null)

// Add preview function
function showMaterialPreview(material) {
  previewMaterial.value = material
  showPreviewModal.value = true
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