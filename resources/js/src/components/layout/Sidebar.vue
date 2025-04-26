<template>
  <div class="h-full bg-white border-r border-gray-200">
    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="flex items-center justify-center h-16 border-b border-gray-200">
        <img src="/logo.png" alt="Logo" class="h-8" />
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <router-link
          to="/dashboard"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-home mr-3"></i>
          Dashboard
        </router-link>

        <!-- Users -->
        <router-link
          to="/users"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-users mr-3"></i>
          Users
        </router-link>

        <!-- Roles -->
        <router-link
          to="/roles"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-user-tag mr-3"></i>
          Roles
        </router-link>

        <!-- Permissions -->
        <router-link
          to="/permissions"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-key mr-3"></i>
          Permissions
        </router-link>

        <!-- Artisans -->
        <router-link
          to="/artisans"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-users mr-3"></i>
          Artisans
        </router-link>

        <!-- Artisan Productivity -->
        <router-link
          to="/artisans/productivity"
          class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
        >
          <i class="fas fa-chart-line mr-3"></i>
          Artisan Productivity
        </router-link>

        <!-- Payroll Section -->
        <div class="mt-4">
          <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Payroll
          </h3>
          <div class="mt-2 space-y-1">
            <router-link
              to="/wages/calculations"
              class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
            >
              <i class="fas fa-calculator mr-3"></i>
              Wage Calculations
            </router-link>
            <router-link
              to="/wages/reports"
              class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md"
            >
              <i class="fas fa-file-invoice-dollar mr-3"></i>
              Wage Reports
            </router-link>
          </div>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
// No additional script needed for now
</script> 