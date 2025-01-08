<template>
    <div class="h-full bg-gray-800 text-white">
      <ul class="space-y-4">
        <li>
          <a href="/" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Dashboard Icon -->
            <i class="fas fa-tachometer-alt mr-3"></i>
            Dashboard
          </a>
        </li>
        <li>
          <a href="/order-management" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Orders Icon -->
            <i class="fas fa-box mr-3"></i>
            Orders
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Inventory Icon -->
            <i class="fas fa-cogs mr-3"></i>
            Inventory
          </a>
        </li>
        
        <li>
        <router-link to="/user-registration" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
    <!-- Reports Icon -->
          <i class="fas fa-chart-line mr-3"></i>
          User Management
        </router-link>
      </li>
        <li>
          <a href="/artisan-management" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Artisan Management Icon -->
            <i class="fas fa-users-cog mr-3"></i>
            Artisan Management
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Quality Control Icon -->
            <i class="fas fa-check-circle mr-3"></i>
            Quality Control
          </a>
        </li>
        <li>
          <a href="/payroll-wages" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Payroll & Wages Icon -->
            <i class="fas fa-wallet mr-3"></i>
            Payroll & Wages
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Reports Icon -->
            <i class="fas fa-chart-line mr-3"></i>
            Reports
          </a>
        </li>
        <li>
          <a href="/petty-cash" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Petty Cash Icon -->
            <i class="fas fa-money-bill-wave mr-3"></i>
            Petty Cash
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Suppliers Icon -->
            <i class="fas fa-truck mr-3"></i>
            Suppliers
          </a>
        </li>
        <li>
          <a href="#" class="flex items-center hover:bg-blue-700 px-4 py-2 rounded-lg">
            <!-- Settings Icon -->
            <i class="fas fa-cogs mr-3"></i>
            Settings
          </a>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Sidebar',
  };
  </script>
  
  <style scoped>
  /* Additional styles for the sidebar can go here */
  </style>
  