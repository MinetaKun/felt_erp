<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="p-5">
      <div class="bg-white rounded-lg shadow">
        <!-- Header Section -->
        <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div class="text-white">
            <h3 class="text-xl font-bold">Finished Products</h3>
            <p class="text-indigo-100 text-sm mt-1">Manage your finished products inventory</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <router-link
              to="/inventory/products/create"
              class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
            >
              <i class="fas fa-plus mr-2"></i> Add Product
            </router-link>
          </div>
        </div>

        <!-- Main Content -->
        <div class="p-5">
          <!-- Filters -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Search</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-10 sm:text-sm border-gray-300 rounded-md"
                      v-model="filters.search"
                      placeholder="Search by name, ID, or details..."
                      @input="debouncedSearch"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Size</label>
                  <select 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    v-model="filters.size"
                    @change="fetchProducts"
                  >
                    <option value="">All Sizes</option>
                    <option v-for="size in sizes" :key="size" :value="size">
                      {{ size }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Color</label>
                  <select 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    v-model="filters.color"
                    @change="fetchProducts"
                  >
                    <option value="">All Colors</option>
                    <option v-for="color in colors" :key="color" :value="color">
                      {{ color }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Quantity Range</label>
                  <select 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    v-model="filters.quantityRange"
                    @change="fetchProducts"
                  >
                    <option value="">All Quantities</option>
                    <option value="low">Low Stock (&lt; 10)</option>
                    <option value="medium">Medium Stock (10-50)</option>
                    <option value="high">High Stock (&gt; 50)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Products Table -->
          <div class="bg-white shadow rounded-lg">
            <div class="p-6">
              <div v-if="loading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>

              <div v-else-if="error" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
                  </div>
                </div>
              </div>

              <div v-else>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="product in products" :key="product?.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <img 
                            v-if="product?.image_path" 
                            :src="`/storage/${product.image_path}`" 
                            alt="Product image" 
                            class="h-10 w-10 object-cover rounded-md"
                          >
                          <div v-else class="h-10 w-10 bg-gray-200 rounded-md flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.size }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.color }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(product?.price) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product?.details }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                          <div class="flex justify-end space-x-3">
                            <router-link
                              :to="`/inventory/products/${product?.id}`"
                              class="text-blue-600 hover:text-blue-900"
                              title="View Details"
                            >
                              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                              </svg>
                            </router-link>
                            <router-link
                              :to="`/inventory/products/${product?.id}/edit`"
                              class="text-indigo-600 hover:text-indigo-900"
                              title="Edit Product"
                            >
                              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                              </svg>
                            </router-link>
                            <button
                              @click="printProduct(product)"
                              class="text-green-600 hover:text-green-900"
                              title="Print Details"
                            >
                              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                              </svg>
                            </button>
                            <button
                              @click="deleteProduct(product)"
                              class="text-red-600 hover:text-red-900"
                              title="Delete Product"
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

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                  <div class="flex-1 flex justify-between sm:hidden">
                    <button
                      @click="changePage(pagination.current_page - 1)"
                      :disabled="pagination.current_page === 1"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                      Previous
                    </button>
                    <button
                      @click="changePage(pagination.current_page + 1)"
                      :disabled="pagination.current_page === pagination.last_page"
                      class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                      Next
                    </button>
                  </div>
                  <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                      <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ pagination.from }}</span>
                        to
                        <span class="font-medium">{{ pagination.to }}</span>
                        of
                        <span class="font-medium">{{ pagination.total }}</span>
                        results
                      </p>
                    </div>
                    <div>
                      <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button
                          @click="changePage(pagination.current_page - 1)"
                          :disabled="pagination.current_page === 1"
                          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                        >
                          <span class="sr-only">Previous</span>
                          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                          </svg>
                        </button>
                        <button
                          v-for="page in pagination.last_page"
                          :key="page"
                          @click="changePage(page)"
                          :class="[
                            page === pagination.current_page
                              ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                          ]"
                        >
                          {{ page }}
                        </button>
                        <button
                          @click="changePage(pagination.current_page + 1)"
                          :disabled="pagination.current_page === pagination.last_page"
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import debounce from 'lodash/debounce';

export default {
  name: 'Products',
  data() {
    return {
      products: [],
      sizes: [],
      colors: [],
      loading: true,
      error: null,
      filters: {
        search: '',
        size: '',
        color: '',
        quantityRange: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      },
      permissions: []
    };
  },
  created() {
    this.fetchProducts();
    this.fetchSizes();
    this.fetchColors();
    this.fetchPermissions();
  },
  methods: {
    async fetchPermissions() {
      try {
        const response = await axios.get('/user/permissions');
        this.permissions = response.data;
      } catch (error) {
        console.error('Error fetching permissions:', error);
      }
    },
    hasPermission(permission) {
      return this.permissions.includes(permission);
    },
    async fetchProducts() {
      try {
        this.loading = true;
        const response = await axios.get('/inventory/products', {
          params: {
            page: this.pagination.current_page,
            search: this.filters.search,
            size: this.filters.size,
            color: this.filters.color,
            quantity_range: this.filters.quantityRange
          }
        });
        
        if (response.data.success) {
          this.products = response.data.data.data || [];
          this.sizes = response.data.sizes || [];
          this.colors = response.data.colors || [];
          this.pagination = {
            current_page: response.data.data.current_page || 1,
            last_page: response.data.data.last_page || 1,
            from: response.data.data.from || 0,
            to: response.data.data.to || 0,
            total: response.data.data.total || 0
          };
        } else {
          this.error = 'Failed to fetch products. Please try again.';
          this.products = [];
        }
      } catch (error) {
        this.error = 'Failed to fetch products. Please try again.';
        console.error('Error fetching products:', error);
        this.products = [];
      } finally {
        this.loading = false;
      }
    },
    async fetchSizes() {
      try {
        const response = await axios.get('/inventory/sizes');
        this.sizes = response.data;
      } catch (error) {
        console.error('Error fetching sizes:', error);
      }
    },
    async fetchColors() {
      try {
        const response = await axios.get('/inventory/colors');
        this.colors = response.data;
      } catch (error) {
        console.error('Error fetching colors:', error);
      }
    },
    async deleteProduct(product) {
      try {
        const result = await Swal.fire({
          title: 'Are you sure?',
          text: `You are about to delete ${product.name}. This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
          await axios.delete(`/inventory/products/${product.id}`);
          this.fetchProducts();
          Swal.fire('Deleted!', 'The product has been deleted.', 'success');
        }
      } catch (error) {
        Swal.fire('Error!', 'Failed to delete the product.', 'error');
        console.error('Error deleting product:', error);
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page;
        this.fetchProducts();
      }
    },
    debouncedSearch: debounce(function() {
      this.pagination.current_page = 1;
      this.fetchProducts();
    }, 300),
    formatCurrency(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(value);
    },
    formatStatus(status) {
      if (!status) return 'Unknown';
      return status.charAt(0).toUpperCase() + status.slice(1);
    },
    printProduct(product) {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Product Details - ${product.name}</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .header { text-align: center; margin-bottom: 20px; }
              .product-image { max-width: 200px; margin: 0 auto; display: block; }
              .details { margin-top: 20px; }
              .detail-row { margin: 10px 0; }
              .label { font-weight: bold; }
              @media print {
                .no-print { display: none; }
                body { margin: 0; }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Product Details</h1>
            </div>
            ${product.image_path ? `<img src="/storage/${product.image_path}" alt="${product.name}" class="product-image">` : ''}
            <div class="details">
              <div class="detail-row"><span class="label">ID:</span> ${product.id}</div>
              <div class="detail-row"><span class="label">Name:</span> ${product.name}</div>
              <div class="detail-row"><span class="label">Size:</span> ${product.size}</div>
              <div class="detail-row"><span class="label">Color:</span> ${product.color}</div>
              <div class="detail-row"><span class="label">Quantity:</span> ${product.quantity}</div>
              <div class="detail-row"><span class="label">Price:</span> ${this.formatCurrency(product.price)}</div>
              <div class="detail-row"><span class="label">Details:</span> ${product.details}</div>
            </div>
            <div class="no-print" style="margin-top: 20px; text-align: center;">
              <button onclick="window.print()">Print</button>
              <button onclick="window.close()">Close</button>
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
    }
  },
  watch: {
    'filters.size': function() {
      this.pagination.current_page = 1;
      this.fetchProducts();
    },
    'filters.color': function() {
      this.pagination.current_page = 1;
      this.fetchProducts();
    },
    'filters.quantityRange': function() {
      this.pagination.current_page = 1;
      this.fetchProducts();
    }
  }
};
</script> 