<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Raw Materials Inventory</h1>
      <div class="flex space-x-4">
        <button
          @click="printInventory"
          class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <span class="mr-2">🖨️</span>
          Print Inventory
        </button>
        <button
          @click="showAddModal = true"
          class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center"
        >
          <span class="mr-2">+</span>
          Add Raw Material
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="filters.type"
            @change="applyFilters"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option v-for="type in materialTypes" :key="type" :value="type">
              {{ type }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
          <select
            v-model="filters.color"
            @change="applyFilters"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Colors</option>
            <option v-for="color in materialColors" :key="color" :value="color">
              {{ color }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
          <select
            v-model="filters.stockStatus"
            @change="applyFilters"
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
            @input="debounceSearch"
            placeholder="Search by name, description, or supplier..."
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="material in materials" :key="material.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ material.name }}</div>
              <div class="text-sm text-gray-500">{{ material.description }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.type }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.color }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.unit }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.min_stock_level }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(material.price_per_unit) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.supplier }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ material.location }}</td>
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
              <div class="flex items-center space-x-2">
                <!-- Preview Button -->
                <button
                  @click="showMaterialPreview(material)"
                  class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50"
                  title="Preview"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>

                <!-- Edit Button -->
                <button
                  @click="editMaterial(material)"
                  class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50"
                  title="Edit"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>

                <!-- Update Stock Button -->
                <button
                  @click="updateStock(material)"
                  class="text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-50"
                  title="Update Stock"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </button>

                <!-- Delete Button -->
                <button
                  @click="deleteMaterial(material)"
                  class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-50"
                  title="Delete"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

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
  if (confirm(`Are you sure you want to delete "${material.name}"? This action cannot be undone.`)) {
    try {
      const response = await axios.delete(`/inventory/raw-materials/${material.id}`)
      if (response.data.success) {
        await fetchMaterials()
        await fetchLowStockMaterials()
      }
    } catch (error) {
      console.error('Failed to delete material:', error)
    }
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