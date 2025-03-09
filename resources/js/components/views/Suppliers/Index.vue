<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Navbar at the top -->
      <Navbar />
      <div class="flex">
      <!-- Sidebar below Navbar -->
      <Sidebar class="h-screen" />
  
      <div class="flex-1 p-8">
        <h2 class="text-2xl font-bold mb-4">Supplier Inventory Management</h2>
        
        <!-- Search and Sort Controls -->
        <div class="flex mb-4 space-x-4">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search supplier..." 
            class="border p-2 rounded-md w-1/3" 
          />
          <select v-model="sortKey" class="border p-2 rounded-md">
            <option value="name">Sort by Name</option>
            <option value="stock">Sort by Stock</option>
            <option value="category">Sort by Category</option>
          </select>
        </div>
  
        <!-- Supplier Inventory Table -->
        <div v-if="filteredSuppliers.length">
          <table class="w-full border-collapse border border-gray-200">
            <thead>
              <tr class="bg-gray-200">
                <th class="border p-2">Supplier Name</th>
                <th class="border p-2">Supplier ID</th>
                <th class="border p-2">Category</th>
                <th class="border p-2">Stock Quantity</th>
                <th class="border p-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="supplier in sortedSuppliers" :key="supplier.id" class="border-b">
                <td class="border p-2">{{ supplier.name }}</td>
                <td class="border p-2">{{ supplier.id }}</td>
                <td class="border p-2">{{ supplier.category }}</td>
                <td class="border p-2">{{ supplier.stock }}</td>
                <td class="border p-2 space-x-2">
                  <button 
                    class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600"
                    @click="updateStock(supplier.id, 'increase')"
                  >
                    Increase
                  </button>
                  <button 
                    class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600"
                    @click="updateStock(supplier.id, 'decrease')"
                  >
                    Decrease
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <p v-else class="text-gray-500">No suppliers available.</p>
      </div>
    </div>
  </div>
  </template>
  
  <script>
  import Navbar from "../../Navbar.vue";
  import Sidebar from "../../Sidebar.vue";
  import axios from "axios";
  
  export default {
    name: "Suppliers",
    components: { Navbar, Sidebar },
    data() {
      return {
        suppliers: [],
        searchQuery: "",
        sortKey: "name",
      };
    },
    computed: {
      filteredSuppliers() {
        return this.suppliers.filter(supplier => 
          supplier.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      },
      sortedSuppliers() {
        return this.filteredSuppliers.sort((a, b) => {
          if (a[this.sortKey] < b[this.sortKey]) return -1;
          if (a[this.sortKey] > b[this.sortKey]) return 1;
          return 0;
        });
      }
    },
    methods: {
      async fetchSuppliers() {
        try {
          const response = await axios.get("/api/suppliers");
          this.suppliers = response.data;
        } catch (error) {
          console.error("Error fetching suppliers:", error);
        }
      },
      async updateStock(supplierId, action) {
        try {
          const supplier = this.suppliers.find(s => s.id === supplierId);
          if (!supplier) return;
          
          const newStock = action === "increase" ? supplier.stock + 1 : Math.max(0, supplier.stock - 1);
          await axios.put(`/api/suppliers/${supplierId}`, { stock: newStock });
          this.suppliers = this.suppliers.map(s =>
            s.id === supplierId ? { ...s, stock: newStock } : s
          );
        } catch (error) {
          console.error("Error updating stock:", error);
        }
      }
    },
    mounted() {
      this.fetchSuppliers();
    }
  };
  </script>
  