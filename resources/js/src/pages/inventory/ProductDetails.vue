<template>
  <div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-white">Product Details</h2>
          <div class="flex space-x-4">
            <button
              @click="printDetails"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
              </svg>
              Print
            </button>
            <button
              @click="goBack"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Back to List
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6" id="print-content">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Image -->
            <div class="flex justify-center">
              <div class="w-64 h-64 bg-gray-100 rounded-lg overflow-hidden">
                <img
                  v-if="product?.image_path"
                  :src="`/storage/${product.image_path}`"
                  :alt="product?.name"
                  class="w-full h-full object-cover"
                >
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                  <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Details -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">ID</label>
                <div class="mt-1 text-lg">{{ product?.id }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <div class="mt-1 text-lg">{{ product?.name }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Size</label>
                <div class="mt-1 text-lg">{{ product?.size }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Color</label>
                <div class="mt-1 text-lg">{{ product?.color }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <div class="mt-1 text-lg">{{ product?.quantity }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Price</label>
                <div class="mt-1 text-lg">{{ formatCurrency(product?.price) }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Details</label>
                <div class="mt-1 text-lg">{{ product?.details }}</div>
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

export default {
  name: 'ProductDetails',
  data() {
    return {
      product: null,
      loading: false
    };
  },
  created() {
    this.fetchProduct();
  },
  methods: {
    async fetchProduct() {
      try {
        this.loading = true;
        const response = await axios.get(`/inventory/products/${this.$route.params.id}`);
        if (response.data.success) {
          this.product = response.data.data;
        } else {
          throw new Error(response.data.message || 'Failed to fetch product');
        }
      } catch (error) {
        console.error('Error fetching product:', error);
        this.$toast.error('Failed to fetch product');
        this.goBack();
      } finally {
        this.loading = false;
      }
    },
    printDetails() {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
          <head>
            <title>Product Details - ${this.product.name}</title>
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
            ${this.product.image_path ? `<img src="/storage/${this.product.image_path}" alt="${this.product.name}" class="product-image">` : ''}
            <div class="details">
              <div class="detail-row"><span class="label">ID:</span> ${this.product.id}</div>
              <div class="detail-row"><span class="label">Name:</span> ${this.product.name}</div>
              <div class="detail-row"><span class="label">Size:</span> ${this.product.size}</div>
              <div class="detail-row"><span class="label">Color:</span> ${this.product.color}</div>
              <div class="detail-row"><span class="label">Quantity:</span> ${this.product.quantity}</div>
              <div class="detail-row"><span class="label">Price:</span> ${this.formatCurrency(this.product.price)}</div>
              <div class="detail-row"><span class="label">Details:</span> ${this.product.details}</div>
            </div>
            <div class="no-print" style="margin-top: 20px; text-align: center;">
              <button onclick="window.print()">Print</button>
              <button onclick="window.close()">Close</button>
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(value);
    },
    goBack() {
      this.$router.push('/inventory/products');
    }
  }
};
</script>

<style scoped>
@media print {
  .no-print {
    display: none;
  }
}
</style> 