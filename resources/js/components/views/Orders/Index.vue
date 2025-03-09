<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="flex">
      <Sidebar class="h-screen" />
      <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
          <router-link to="/orders/create">
            <button class="bg-green-500 text-white px-4 py-2 rounded-md shadow hover:bg-green-600">
              Add Order
            </button>
          </router-link>
          
          <div class="relative">
            <input type="text" v-model="searchQuery" @input="searchOrders" class="px-4 py-2 border border-gray-300 rounded-md w-80" placeholder="Search orders..." />
          </div>
          
          <select v-model="selectedStatus" @change="filterOrders" class="px-4 py-2 border border-gray-300 rounded-md">
            <option value="">All</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Overdue">Overdue</option>
          </select>
        </div>

        <div class="overflow-x-auto shadow-md rounded-lg">
          <table class="min-w-full bg-white border border-gray-200">
            <thead>
              <tr class="bg-gray-100">
                <th v-for="column in columns" :key="column.key" class="py-2 px-4 text-left font-semibold text-gray-700 cursor-pointer" @click="sortTable(column.key)">
                  {{ column.label }}
                </th>
                <th class="py-2 px-4 text-left font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in filteredOrders" :key="order.id" class="border-t">
                <td class="py-2 px-4 text-gray-600">{{ order.id }}</td>
                <td class="py-2 px-4 text-gray-600">{{ order.product_name }}</td>
                <td class="py-2 px-4 text-gray-600">{{ order.quantity }}</td>
                <td class="py-2 px-4 text-gray-600">{{ order.wool_color }}</td>
                <td class="py-2 px-4 text-gray-600">{{ order.size_cm }} cm</td>
                <td class="py-2 px-4 text-gray-600">{{ order.wage_per_piece }}</td>
                <td class="py-2 px-4 text-gray-600">{{ getStatus(order) }}</td>
                <td class="py-2 px-4 text-gray-600">{{ formatDate(order.created_at) }}</td>
                <td class="py-2 px-4 text-gray-600">{{ formatDate(order.updated_at) }}</td>
                <td class="py-2 px-4">
                  <button class="bg-yellow-500 text-white px-4 py-2 rounded-md shadow hover:bg-yellow-600 mr-2" @click="editOrder(order)">Edit</button>
                  <button class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600" @click="deleteOrder(order.id)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '../../Navbar.vue';
import Sidebar from '../../Sidebar.vue';
import axios from 'axios';

export default {
  name: 'Orders',
  components: { Navbar, Sidebar },
  data() {
    return {
      orders: [],
      searchQuery: '',
      selectedStatus: '',
      sortedBy: '',
      sortOrder: 'asc',
      columns: [
        { key: 'id', label: 'Order ID' },
        { key: 'product_name', label: 'Product Name' },
        { key: 'quantity', label: 'Quantity' },
        { key: 'wool_color', label: 'Wool Color' },
        { key: 'size_cm', label: 'Size (cm)' },
        { key: 'wage_per_piece', label: 'Wage per Piece' },
        { key: 'status', label: 'Status' },
        { key: 'created_at', label: 'Created At' },
        { key: 'updated_at', label: 'Updated At' }
      ]
    };
  },
  computed: {
    filteredOrders() {
      return this.orders
        .filter(order => 
          order.product_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          order.wool_color.toLowerCase().includes(this.searchQuery.toLowerCase())
        )
        .filter(order => this.selectedStatus ? this.getStatus(order) === this.selectedStatus : true)
        .sort((a, b) => this.sortFunction(a, b));
    }
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    },
    sortFunction(a, b) {
      if (!this.sortedBy) return 0;
      return this.sortOrder === 'asc' ? (a[this.sortedBy] > b[this.sortedBy] ? 1 : -1) : (a[this.sortedBy] < b[this.sortedBy] ? 1 : -1);
    },
    sortTable(field) {
      this.sortedBy = field;
      this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
    },
    getStatus(order) {
      const currentDate = new Date();
      const createdDate = new Date(order.created_at);
      return currentDate < createdDate ? 'Pending' : (order.status !== 'Done' ? 'Overdue' : 'Done');
    },
    searchOrders() {
      console.log('Searching:', this.searchQuery);
    },
    filterOrders() {
      console.log('Filtering by status:', this.selectedStatus);
    },
    editOrder(order) {
      console.log('Editing order:', order);
    },
    deleteOrder(id) {
      this.orders = this.orders.filter(order => order.id !== id);
    },
    fetchOrders() {
      axios.get('http://127.0.0.1:8000/api/orders')
        .then(response => {
          this.orders = response.data; // Populate orders with data from API
        })
        .catch(error => {
          console.error('There was an error fetching the orders:', error);
        });
    }
  },
  mounted() {
    this.fetchOrders(); // Call the fetchOrders method when the component is mounted
  }
};
</script>