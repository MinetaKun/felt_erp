<template>
  <div class="flex h-screen flex-col">
    <!-- Navbar -->

<Navbar />
    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full" />

      <!-- Main Dashboard Content -->
      <div class="flex-1 p-6 bg-gray-100 overflow-y-auto">
        <!-- Stats Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
          <!-- Stats Cards -->
          <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold text-gray-700">Total Orders</div>
            <div class="text-3xl font-bold text-blue-600">320</div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold text-gray-700">Inventory Status</div>
            <div class="text-3xl font-bold text-green-600">85%</div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold text-gray-700">Artisan Productivity</div>
            <div class="text-3xl font-bold text-orange-600">78%</div>
          </div>
        </div>

        <!-- Orders Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Pending Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-gray-700 mb-4">Pending Orders</div>
            <table class="min-w-full bg-gray-100 border border-gray-200 rounded-lg">
              <thead>
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">001</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client A</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-25</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Pending</td>
                </tr>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">002</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client B</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-20</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Pending</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Processing Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-gray-700 mb-4">Processing Orders</div>
            <table class="min-w-full bg-gray-100 border border-gray-200 rounded-lg">
              <thead>
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">003</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client C</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-22</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Processing</td>
                </tr>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">004</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client D</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-21</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Processing</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Shipped Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-gray-700 mb-4">Shipped Orders</div>
            <table class="min-w-full bg-gray-100 border border-gray-200 rounded-lg">
              <thead>
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">005</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client E</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-18</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Shipped</td>
                </tr>
                <tr>
                  <td class="px-4 py-2 text-sm text-gray-600">006</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Client F</td>
                  <td class="px-4 py-2 text-sm text-gray-600">2024-12-19</td>
                  <td class="px-4 py-2 text-sm text-gray-600">Shipped</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '../Navbar.vue';
import Sidebar from '../Sidebar.vue';

export default {
  name: 'Dashboard',
  components: {
    Sidebar,
    Navbar
  },
  data() {
    return {
      message: 'Dashboard content goes here!',
    };
  },
  mounted() {
    console.log(this.message);
  },
};
</script>

<style scoped>
/* Additional custom styles for the dashboard can go here */
</style>
