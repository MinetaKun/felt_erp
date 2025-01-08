<template>
  <div class="flex h-screen flex-col bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100">
    <!-- Navbar -->
    <Navbar />
    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full bg-gray-800 text-white" />

      <!-- Content -->
      <div class="flex-1 p-6 overflow-auto bg-white">
        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 rounded-lg shadow-lg text-white">
            <div class="mr-4">
              <div class="text-4xl font-semibold">{{ artisans.length }}</div>
              <p class="text-lg">Total Artisans</p>
            </div>
          </div>
          <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 rounded-lg shadow-lg text-white">
            <div class="mr-4">
              <div class="text-4xl font-semibold">{{ totalDepartments }}</div>
              <p class="text-lg">Departments</p>
            </div>
          </div>
          <div class="bg-gradient-to-r from-yellow-500 to-orange-600 p-6 rounded-lg shadow-lg text-white">
            <div class="mr-4">
              <div class="text-4xl font-semibold">{{ totalDailyOutput }}</div>
              <p class="text-lg">Daily Output</p>
            </div>
          </div>
          <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6 rounded-lg shadow-lg text-white">
            <div class="mr-4">
              <div class="text-4xl font-semibold">{{ topPerformers }}</div>
              <p class="text-lg">Top Performers</p>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Daily Output by Department</h2>
            <BarChart :data="departmentOutput" />
          </div>
          <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Skill Level Distribution</h2>
            <PieChart :data="skillDistribution" />
          </div>
        </div>

        <!-- Artisan Cards -->
        <h1 class="text-3xl font-semibold text-gray-900 mb-6">Artisan List</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="artisan in artisans"
            :key="artisan.id"
            class="bg-white p-6 rounded-lg shadow-xl transition-transform transform hover:scale-105 hover:shadow-2xl"
          >
            <h2 class="text-xl font-semibold text-gray-800">{{ artisan.name }}</h2>
            <p class="text-gray-600">Department: {{ artisan.department }}</p>
            <p class="text-gray-600">Skill Level: {{ artisan.skillLevel }}</p>
            <p class="text-gray-600">Daily Output: {{ artisan.dailyOutput }}</p>
            <div class="mt-4 flex space-x-2">
              <button
                class="px-6 py-3 bg-blue-500 text-white rounded-lg transition duration-200 hover:bg-blue-600 hover:scale-105"
              >
                View Details
              </button>
              <button
                class="px-6 py-3 bg-yellow-500 text-white rounded-lg transition duration-200 hover:bg-yellow-600 hover:scale-105"
              >
                Edit
              </button>
              <button
                class="px-6 py-3 bg-red-500 text-white rounded-lg transition duration-200 hover:bg-red-600 hover:scale-105"
              >
                Delete
              </button>
            </div>
          </div>
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
  name: 'Artisan_Management',
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
    };
  },
};
</script>

<style scoped>
/* Additional custom styles for the dashboard */
</style>
