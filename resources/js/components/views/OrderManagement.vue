<template>
  <div class="flex h-screen flex-col bg-gray-50">
    <!-- Navbar -->
    <Navbar />

    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full" />

  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Order Management</h1>
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
    Navbar,
  },
};


</script>