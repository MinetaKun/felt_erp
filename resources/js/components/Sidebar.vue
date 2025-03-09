<template>
  <div class="flex">
    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'w-64' : 'w-20'" class="h-screen bg-gray-900 text-white transition-all duration-300">
      <div class="flex items-center justify-between p-4">
        <button @click="toggleSidebar" class="text-white focus:outline-none">
          <i :class="sidebarOpen ? 'fas fa-arrow-left' : 'fas fa-bars'"></i>
        </button>
      </div>
      <div class="flex items-center justify-between p-4">
        <a href="/">
          <i class="fas fa-users mr-3"></i>
          <span v-if="sidebarOpen" class="text-xl font-bold">Dashboard</span>
        </a>
      </div>
      <nav class="mt-4">
        <a href="/users" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-users mr-3"></i>
          <span v-if="sidebarOpen">Users</span>
        </a>
        <a href="/order-management" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-users mr-3"></i>
          <span v-if="sidebarOpen">Order Management</span>
        </a>
        <a href="/quality-control" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-users mr-3"></i>
          <span v-if="sidebarOpen">Quality Control</span>
        </a>
        <a href="/suppliers" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-users mr-3"></i>
          <span v-if="sidebarOpen">Suppliers</span>
        </a>

        <!-- 🔹 Petty Cash Section -->
        <div class="text-gray-400 text-sm px-3 mt-4 uppercase">Petty Cash</div>
        <a href="/petty-cash" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-wallet mr-3"></i>
          <span v-if="sidebarOpen">Dashboard</span>
        </a>
        <a href="/petty-cash/transactions" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-list mr-3"></i>
          <span v-if="sidebarOpen">Transactions</span>
        </a>
        <a href="/petty-cash/add" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-plus-circle mr-3"></i>
          <span v-if="sidebarOpen">Add Transaction</span>
        </a>
        <a href="/petty-cash/reports" class="flex items-center p-3 hover:bg-gray-700 rounded">
          <i class="fas fa-file-download mr-3"></i>
          <span v-if="sidebarOpen">Reports</span>
        </a>
      </nav>
    </div>

    <!-- Main Content -->
    
  </div>
</template>

<script>
export default {
data() {
  return {
    sidebarOpen: true,
  };
},
methods: {
  toggleSidebar() {
    this.sidebarOpen = !this.sidebarOpen;
  },
},
};
</script>
