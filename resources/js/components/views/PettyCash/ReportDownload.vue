<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Navbar -->
      <Navbar />
  
      <div class="flex">
        <!-- Sidebar -->
        <Sidebar class="h-screen" />
  
        <!-- Main content -->
        <div class="container mx-auto p-6">
          <h1 class="text-2xl font-bold mb-4">Download Reports</h1>
  
          <!-- Buttons for downloading reports -->
          <div class="space-y-4">
            <button class="bg-green-500 text-white px-4 py-2 mt-4">Download Monthly Report</button>
            <button class="bg-blue-500 text-white px-4 py-2 mt-4">Download Yearly Report</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../../Navbar.vue';
  import Sidebar from '../../Sidebar.vue';
  
  export default {
    components: { Navbar, Sidebar },
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 900px;
  }
  </style>
  