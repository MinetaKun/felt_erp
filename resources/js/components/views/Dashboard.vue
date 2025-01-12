<template>
  <div class="flex h-screen flex-col bg-gray-50">
    <!-- Navbar -->
    <Navbar />

    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full" />

      <!-- Main Dashboard Content -->
      <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
        <!-- Stats Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
          <!-- Stats Cards -->
          <div class="bg-primary text-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold">Total Orders</div>
            <div class="text-3xl font-bold">320</div>
          </div>

          <div class="bg-secondary text-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold">Inventory Status</div>
            <div class="text-3xl font-bold">85%</div>
          </div>

          <div class="bg-black text-white p-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="text-xl font-semibold">Artisan Productivity</div>
            <div class="text-3xl font-bold">78%</div>
          </div>
        </div>

        <!-- Orders Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Pending Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-primary mb-4">Pending Orders</div>
            <table class="min-w-full bg-gray-50 border border-gray-200 rounded-lg">
              <thead>
                <tr class="bg-primary text-white">
                  <th class="px-4 py-2 text-left text-sm font-medium">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">001</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client A</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-25</td>
                  <td class="px-4 py-2 text-sm text-primary">Pending</td>
                </tr>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">002</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client B</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-20</td>
                  <td class="px-4 py-2 text-sm text-primary">Pending</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Processing Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-secondary mb-4">Processing Orders</div>
            <table class="min-w-full bg-gray-50 border border-gray-200 rounded-lg">
              <thead>
                <tr class="bg-secondary text-white">
                  <th class="px-4 py-2 text-left text-sm font-medium">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">003</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client C</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-22</td>
                  <td class="px-4 py-2 text-sm text-secondary">Processing</td>
                </tr>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">004</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client D</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-21</td>
                  <td class="px-4 py-2 text-sm text-secondary">Processing</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Shipped Orders Section -->
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="text-xl font-semibold text-accent mb-4">Shipped Orders</div>
            <table class="min-w-full bg-gray-50 border border-gray-200 rounded-lg">
              <thead>
                <tr class="bg-accent text-white">
                  <th class="px-4 py-2 text-left text-sm font-medium">Order No.</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Client</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Order Date</th>
                  <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">005</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client E</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-18</td>
                  <td class="px-4 py-2 text-sm text-accent">Shipped</td>
                </tr>
                <tr class="even:bg-gray-100">
                  <td class="px-4 py-2 text-sm text-gray-700">006</td>
                  <td class="px-4 py-2 text-sm text-gray-700">Client F</td>
                  <td class="px-4 py-2 text-sm text-gray-700">2024-12-19</td>
                  <td class="px-4 py-2 text-sm text-accent">Shipped</td>
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
</style>

