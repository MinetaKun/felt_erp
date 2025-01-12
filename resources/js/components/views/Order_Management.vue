<template>
  <div class="flex h-screen flex-col">
    <!-- Navbar -->
    <Navbar />
  
    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full" />
  
      <!-- Content -->
      <div class="flex-1 p-6 bg-gray-100">
        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white p-4 rounded shadow flex items-center">
            <div class="mr-4">
              <div class="text-3xl font-bold text-blue-500">100</div>
              <p class="text-gray-600">Total Artisans</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded shadow flex items-center">
            <div class="mr-4">
              <div class="text-3xl font-bold text-green-500">4</div>
              <p class="text-gray-600">Departments</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded shadow flex items-center">
            <div class="mr-4">
              <div class="text-3xl font-bold text-yellow-500">2500</div>
              <p class="text-gray-600">Daily Output</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded shadow flex items-center">
            <div class="mr-4">
              <div class="text-3xl font-bold text-red-500">10</div>
              <p class="text-gray-600">Top Performers</p>
            </div>
          </div>
        </div>
  
        <!-- Date Filter -->
        <div class="mb-6">
          <label for="dateFilter" class="block text-gray-600 font-semibold">Filter by Date</label>
          <select v-model="selectedDateFilter" id="dateFilter" class="p-2 border rounded">
            <option value="all">All</option>
            <option value="month">This Month</option>
            <option value="lastMonth">Last Month</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>

        <!-- Search -->
        <div class="mb-6">
          <label for="search" class="block text-gray-600 font-semibold">Search Orders</label>
          <input
            v-model="searchQuery"
            type="text"
            id="search"
            class="p-2 border rounded w-full"
            placeholder="Search by Client Name or Order ID"
          />
        </div>
  
        <!-- Add Order Button -->
        <div class="mb-6">
          <button 
            @click="openAddOrderPopup"
            class="px-6 py-3 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Add Order
          </button>
        </div>

        <!-- Orders Table Section -->
        <h1 class="text-2xl font-bold mb-4 mt-10">Order Management</h1>
        <div class="overflow-x-auto bg-white p-4 rounded shadow">
          <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2 text-left border border-gray-300" @click="sortTable('id')">Order ID</th>
                <th class="px-4 py-2 text-left border border-gray-300" @click="sortTable('clientName')">Client Name</th>
                <th class="px-4 py-2 text-left border border-gray-300">Assigned Artisan</th>
                <th class="px-4 py-2 text-left border border-gray-300">Product Name</th>
                <th class="px-4 py-2 text-left border border-gray-300">Size</th>
                <th class="px-4 py-2 text-left border border-gray-300">Color</th>
                <th class="px-4 py-2 text-left border border-gray-300">Quantity to Make</th>
                <th class="px-4 py-2 text-left border border-gray-300">Status</th>
                <th class="px-4 py-2 text-left border border-gray-300">Date Assigned</th>
                <th class="px-4 py-2 text-left border border-gray-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in paginatedOrders" :key="order.id">
                <td class="px-4 py-2 border border-gray-300">{{ order.id }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.clientName }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.assignedArtisan }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.productName }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.size }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.color }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.quantityToMake }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.status }}</td>
                <td class="px-4 py-2 border border-gray-300">{{ order.dateAssigned }}</td>
                <td class="px-4 py-2 border border-gray-300">
                  <button
                    @click="editOrder(order)"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteOrder(order.id)"
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 ml-2"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex justify-between items-center">
          <div class="text-gray-600">Total Orders: {{ filteredOrders.length }}</div>
          <div>
            <button 
              @click="changePage(-1)" 
              :disabled="currentPage <= 1" 
              class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
              Previous
            </button>
            <span class="mx-4">{{ currentPage }} / {{ totalPages }}</span>
            <button 
              @click="changePage(1)" 
              :disabled="currentPage >= totalPages" 
              class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Order Popup Form -->
    <div v-if="isAddOrderPopupOpen" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white p-6 rounded shadow-lg w-96">
        <h2 class="text-xl font-bold mb-4">Add Order</h2>
        <form @submit.prevent="addOrder">
          <div class="mb-4">
            <label for="clientName" class="block text-gray-600">Client Name</label>
            <input
              v-model="newOrder.clientName"
              type="text"
              id="clientName"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <label for="assignedArtisan" class="block text-gray-600">Assigned Artisan</label>
            <input
              v-model="newOrder.assignedArtisan"
              type="text"
              id="assignedArtisan"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <label for="productName" class="block text-gray-600">Product Name</label>
            <input
              v-model="newOrder.productName"
              type="text"
              id="productName"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <label for="size" class="block text-gray-600">Size</label>
            <input
              v-model="newOrder.size"
              type="text"
              id="size"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <label for="color" class="block text-gray-600">Color</label>
            <input
              v-model="newOrder.color"
              type="text"
              id="color"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <label for="quantityToMake" class="block text-gray-600">Quantity to Make</label>
            <input
              v-model="newOrder.quantityToMake"
              type="number"
              id="quantityToMake"
              class="p-2 border rounded w-full"
              required
            />
          </div>
          <div class="mb-4">
            <button
              type="submit"
              class="px-6 py-3 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Save Order
            </button>
          </div>
        </form>
        <button
          @click="closeAddOrderPopup"
          class="px-4 py-2 bg-gray-300 text-gray-600 rounded hover:bg-gray-400"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '../Navbar.vue';
import Sidebar from '../Sidebar.vue';
export default {
  name: 'OrderManagement',
  components: {
    Navbar,
    Sidebar,
  },
  data() {
    return {
      artisans: [], // Your data for artisans
      orders: [
        {
          id: 1,
          clientName: 'John Doe',
          assignedArtisan: 'Artisan 1',
          productName: 'Felt Slippers',
          size: '40 EU',
          color: 'Red',
          quantityToMake: 10,
          status: 'Pending',
          dateAssigned: '2025-01-01',
        },
        {
          id: 2,
          clientName: 'Jane Smith',
          assignedArtisan: 'Artisan 2',
          productName: 'Felt Cat Cave',
          size: 'L',
          color: 'Blue',
          quantityToMake: 5,
          status: 'In Progress',
          dateAssigned: '2025-01-05',
        },
      ], // Add mock order data here
      isAddOrderPopupOpen: false,
      newOrder: {
        clientName: '',
        assignedArtisan: '',
        productName: '',
        size: '',
        color: '',
        quantityToMake: 0,
      },
      currentPage: 1,
      perPage: 10,
      searchQuery: '',
      selectedDateFilter: 'all',
    };
  },
  computed: {
    filteredOrders() {
      let filteredOrders = this.orders;

      // Filter by search query
      if (this.searchQuery) {
        filteredOrders = filteredOrders.filter((order) => 
          order.clientName.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
          order.id.toString().includes(this.searchQuery)
        );
      }

      // Filter by date range (if applicable)
      if (this.selectedDateFilter === 'month') {
        filteredOrders = filteredOrders.filter((order) => {
          const orderDate = new Date(order.dateAssigned);
          const today = new Date();
          return orderDate.getMonth() === today.getMonth() && orderDate.getFullYear() === today.getFullYear();
        });
      }

      if (this.selectedDateFilter === 'lastMonth') {
        filteredOrders = filteredOrders.filter((order) => {
          const orderDate = new Date(order.dateAssigned);
          const lastMonth = new Date();
          lastMonth.setMonth(lastMonth.getMonth() - 1);
          return orderDate.getMonth() === lastMonth.getMonth() && orderDate.getFullYear() === lastMonth.getFullYear();
        });
      }

      return filteredOrders;
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.perPage);
    },
    paginatedOrders() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.filteredOrders.slice(start, end);
    },
  },
  methods: {
    openAddOrderPopup() {
      this.isAddOrderPopupOpen = true;
    },
    closeAddOrderPopup() {
      this.isAddOrderPopupOpen = false;
    },
    addOrder() {
      this.orders.push({
        ...this.newOrder,
        id: this.orders.length + 1,
        status: 'New',
        dateAssigned: new Date().toISOString(),
      });
      this.closeAddOrderPopup();
    },
    deleteOrder(orderId) {
      this.orders = this.orders.filter((order) => order.id !== orderId);
    },
    editOrder(order) {
      // You can add logic here to edit an order
      console.log('Edit Order', order);
    },
    sortTable(column) {
      // Implement sorting logic here based on column name
    },
    changePage(direction) {
      this.currentPage += direction;
    },
  },
};
</script>

<style scoped>
/* Add your custom styles here */
</style>
