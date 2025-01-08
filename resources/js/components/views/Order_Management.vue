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
                <div class="text-3xl font-bold text-blue-500">{{ artisans.length }}</div>
                <p class="text-gray-600">Total Artisans</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
              <div class="mr-4">
                <div class="text-3xl font-bold text-green-500">{{ totalDepartments }}</div>
                <p class="text-gray-600">Departments</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
              <div class="mr-4">
                <div class="text-3xl font-bold text-yellow-500">{{ totalDailyOutput }}</div>
                <p class="text-gray-600">Daily Output</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
              <div class="mr-4">
                <div class="text-3xl font-bold text-red-500">{{ topPerformers }}</div>
                <p class="text-gray-600">Top Performers</p>
              </div>
            </div>
          </div>
  
          <!-- Charts Section -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded shadow">
              <h2 class="text-lg font-bold mb-4">Daily Output by Department</h2>
              <BarChart :data="departmentOutput" />
            </div>
            <div class="bg-white p-4 rounded shadow">
              <h2 class="text-lg font-bold mb-4">Skill Level Distribution</h2>
              <PieChart :data="skillDistribution" />
            </div>
          </div>
  
          <!-- Artisan Cards -->
          <h1 class="text-2xl font-bold mb-4">Artisan List</h1>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="artisan in artisans"
              :key="artisan.id"
              class="bg-white p-4 rounded shadow"
            >
              <h2 class="text-lg font-semibold text-gray-800">{{ artisan.name }}</h2>
              <p class="text-gray-600">Department: {{ artisan.department }}</p>
              <p class="text-gray-600">Skill Level: {{ artisan.skillLevel }}</p>
              <p class="text-gray-600">Daily Output: {{ artisan.dailyOutput }}</p>
              <div class="mt-4 flex space-x-2">
                <button
                  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                  View Details
                </button>
                <button
                  class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                >
                  Edit
                </button>
                <button
                  class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
  
          <!-- Orders Table Section -->
          <h1 class="text-2xl font-bold mb-4 mt-10">Order Management</h1>
          <div class="overflow-x-auto bg-white p-4 rounded shadow">
            <table class="min-w-full table-auto">
              <thead class="bg-gray-200">
                <tr>
                  <th class="px-4 py-2 text-left">Order ID</th>
                  <th class="px-4 py-2 text-left">Client Name</th>
                  <th class="px-4 py-2 text-left">Assigned Artisan</th>
                  <th class="px-4 py-2 text-left">Status</th>
                  <th class="px-4 py-2 text-left">Date Assigned</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td class="px-4 py-2">{{ order.id }}</td>
                  <td class="px-4 py-2">{{ order.clientName }}</td>
                  <td class="px-4 py-2">{{ order.assignedArtisan }}</td>
                  <td class="px-4 py-2">{{ order.status }}</td>
                  <td class="px-4 py-2">{{ order.dateAssigned }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-4 flex justify-between items-center">
            <div class="text-gray-600">Total Orders: {{ orders.length }}</div>
            <div class="text-gray-600">Pending: {{ pendingOrders }}</div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../Navbar.vue';
  import Sidebar from '../Sidebar.vue';
  // import BarChart from './components/BarChart.vue';
  // import PieChart from './components/PieChart.vue';
  
  export default {
    name: 'OrderManagement',
    components: {
      Sidebar,
      Navbar, 
      // BarChart, 
      // PieChart
    },
    data() {
      return {
        artisans: [
          { id: 1, name: 'John Doe', department: 'Felting', skillLevel: 'Expert', dailyOutput: 20 },
          { id: 2, name: 'Jane Smith', department: 'Sewing', skillLevel: 'Intermediate', dailyOutput: 15 },
        ],
        totalDepartments: 3,
        totalDailyOutput: 200,
        topPerformers: 5,
        departmentOutput: {
          labels: ['Felting', 'Sewing', 'Dyeing'],
          datasets: [
            {
              label: 'Daily Output',
              data: [70, 50, 80],
              backgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
            },
          ],
        },
        skillDistribution: {
          labels: ['Beginner', 'Intermediate', 'Expert'],
          datasets: [
            {
              data: [20, 50, 30],
              backgroundColor: ['#2196F3', '#FFC107', '#4CAF50'],
            },
          ],
        },
        orders: [
          { id: 101, clientName: 'Acme Corp', assignedArtisan: 'John Doe', status: 'In Progress', dateAssigned: '2025-01-01' },
          { id: 102, clientName: 'Beta Ltd.', assignedArtisan: 'Jane Smith', status: 'Pending', dateAssigned: '2025-01-02' },
          { id: 103, clientName: 'Gamma Inc.', assignedArtisan: 'John Doe', status: 'Completed', dateAssigned: '2025-01-03' },
        ],
      };
    },
    computed: {
      pendingOrders() {
        return this.orders.filter(order => order.status === 'Pending').length;
      },
    },
  };
  </script>
  
  <style scoped>
  /* Additional custom styles for the dashboard can go here */
  </style>
  