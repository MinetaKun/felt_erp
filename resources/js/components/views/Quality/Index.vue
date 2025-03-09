<template>
    <div class="min-h-screen bg-gray-100">
        <Navbar />
    <div class="flex">
      <Sidebar class="h-screen w-64" />
      <div class="flex-1 min-h-screen bg-gray-100 p-6">
      
  
        <div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
          <h2 class="text-2xl font-bold mb-4">Quality Control</h2>
  
          <!-- Search & Sort Controls -->
          <div class="flex mb-4 space-x-4">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search product..." 
              class="border p-2 rounded-md w-1/3" 
            />
            <select v-model="sortKey" class="border p-2 rounded-md">
              <option value="name">Sort by Name</option>
              <option value="category">Sort by Category</option>
              <option value="status">Sort by Status</option>
            </select>
          </div>
  
          <!-- Product Table -->
          <div v-if="sortedProducts.length">
            <table class="w-full border-collapse border border-gray-200">
              <thead>
                <tr class="bg-gray-200">
                  <th class="border p-2 text-left">Product Name</th>
                  <th class="border p-2 text-left">Product ID</th>
                  <th class="border p-2 text-left">Category</th>
                  <th class="border p-2 text-left">Quality Status</th>
                  <th class="border p-2 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in sortedProducts" :key="product.id" class="border-b">
                  <td class="border p-2">{{ product.name }}</td>
                  <td class="border p-2">{{ product.id }}</td>
                  <td class="border p-2">{{ product.category }}</td>
                  <td class="border p-2" :class="{'text-green-500': product.status === 'Accepted', 'text-red-500': product.status === 'Rejected'}">
                    {{ product.status || 'Pending' }}
                  </td>
                  <td class="border p-2 space-x-2">
                    <button 
                      class="bg-green-500 text-white px-4 py-2 rounded-md shadow hover:bg-green-600"
                      @click="updateStatus(product.id, 'Accepted')"
                    >
                      Accept
                    </button>
                    <button 
                      class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600"
                      @click="updateStatus(product.id, 'Rejected')"
                    >
                      Reject
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <p v-else class="text-gray-500">No products available for review.</p>
        </div>
      </div>
    </div>
</div>
  </template>
  
  <script>
  import Navbar from "../../Navbar.vue";
  import Sidebar from "../../Sidebar.vue";
  import axios from "axios";
  
  export default {
    name: "QualityControl",
    components: { Navbar, Sidebar },
    data() {
      return {
        products: [],
        searchQuery: "",
        sortKey: "name",
      };
    },
    computed: {
      filteredProducts() {
        return this.products.filter(product => 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      },
      sortedProducts() {
        return this.filteredProducts.slice().sort((a, b) => {
          const valA = a[this.sortKey]?.toString().toLowerCase() || "";
          const valB = b[this.sortKey]?.toString().toLowerCase() || "";
          return valA.localeCompare(valB);
        });
      }
    },
    methods: {
      async fetchProducts() {
        try {
          const response = await axios.get("/api/products");
          this.products = response.data;
        } catch (error) {
          console.error("Error fetching products:", error);
        }
      },
      async updateStatus(productId, status) {
        try {
          await axios.put(`/api/products/${productId}`, { status });
          this.products = this.products.map(product =>
            product.id === productId ? { ...product, status } : product
          );
        } catch (error) {
          console.error("Error updating product status:", error);
        }
      }
    },
    mounted() {
      this.fetchProducts();
    }
  };
  </script>
  