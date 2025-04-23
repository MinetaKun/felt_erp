<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Raw Materials</h3>
          <p class="mt-2 text-3xl font-bold text-blue-600">{{ rawMaterialsCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Finished Products</h3>
          <p class="mt-2 text-3xl font-bold text-green-600">{{ finishedProductsCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Low Stock Items</h3>
          <p class="mt-2 text-3xl font-bold text-red-600">{{ lowStockCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Value</h3>
          <p class="mt-2 text-3xl font-bold text-purple-600">{{ formatCurrency(totalValue) }}</p>
        </div>
      </div>

      <!-- Inventory Sections -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Raw Materials Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-gray-900">Raw Materials</h2>
              <router-link
                to="/inventory/raw-materials"
                class="text-blue-600 hover:text-blue-800"
              >
                View All
              </router-link>
            </div>
            <div class="space-y-4">
              <div v-for="material in recentRawMaterials" :key="material.id" class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                <div>
                  <h3 class="font-medium text-gray-900">{{ material.name }}</h3>
                  <p class="text-sm text-gray-500">{{ material.quantity }} {{ material.unit }}</p>
                </div>
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
              </div>
            </div>
          </div>
        </div>

        <!-- Finished Products Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-gray-900">Finished Products</h2>
              <router-link
                to="/inventory/products"
                class="text-blue-600 hover:text-blue-800"
              >
                View All
              </router-link>
            </div>
            <div class="space-y-4">
              <div v-for="product in recentFinishedProducts" :key="product.id" class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                <div>
                  <h3 class="font-medium text-gray-900">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500">{{ product.quantity }} units</p>
                </div>
                <span
                  :class="{
                    'bg-red-100 text-red-800': product.status === 'out_of_stock',
                    'bg-yellow-100 text-yellow-800': product.status === 'low_stock',
                    'bg-green-100 text-green-800': product.status === 'in_stock'
                  }"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ formatStatus(product.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Alerts -->
      <div v-if="lowStockItems.length > 0" class="mt-6">
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Low Stock Alerts</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="item in lowStockItems" :key="item.id">
                    {{ item.name }} ({{ item.quantity }} {{ item.unit || 'units' }} remaining)
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const rawMaterialsCount = ref(0);
const finishedProductsCount = ref(0);
const lowStockCount = ref(0);
const totalValue = ref(0);
const recentRawMaterials = ref([]);
const recentFinishedProducts = ref([]);
const lowStockItems = ref([]);

onMounted(async () => {
  await fetchInventoryData();
});

async function fetchInventoryData() {
  try {
    // Fetch raw materials summary
    const rawMaterialsResponse = await axios.get('/inventory/raw-materials');
    if (rawMaterialsResponse.data.success) {
      rawMaterialsCount.value = rawMaterialsResponse.data.data.total;
      recentRawMaterials.value = rawMaterialsResponse.data.data.data.slice(0, 5);
    }

    // Fetch finished products summary
    const finishedProductsResponse = await axios.get('/inventory/products');
    if (finishedProductsResponse.data.success) {
      finishedProductsCount.value = finishedProductsResponse.data.data.total;
      recentFinishedProducts.value = finishedProductsResponse.data.data.data.slice(0, 5);
    }

    // Fetch low stock items
    const lowStockResponse = await axios.get('/inventory/raw-materials/low-stock');
    if (lowStockResponse.data.success) {
      lowStockItems.value = lowStockResponse.data.data;
      lowStockCount.value = lowStockItems.value.length;
    }

    // Calculate total value
    totalValue.value = recentRawMaterials.value.reduce((sum, item) => sum + (item.quantity * item.price_per_unit), 0) +
                      recentFinishedProducts.value.reduce((sum, item) => sum + (item.quantity * item.price), 0);
  } catch (error) {
    console.error('Failed to fetch inventory data:', error);
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
}

function formatStatus(status) {
  const statuses = {
    in_stock: 'In Stock',
    low_stock: 'Low Stock',
    out_of_stock: 'Out of Stock'
  };
  return statuses[status] || status;
}
</script> 