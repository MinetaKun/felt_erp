<template>
    <div class="sidebar bg-gray-800 text-white h-screen fixed top-0 left-0 overflow-y-auto transition-all duration-300"
    :class="{ 'w-16': isCollapsed, 'w-64': !isCollapsed }">
      <div class="p-4 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center space-x-2" :class="{ 'justify-center': isCollapsed }">
          <img src="/logo.png" alt="Logo" class="h-8 w-8" v-if="false">
          <span v-if="!isCollapsed" class="text-xl font-bold">FPMS</span>
        </div>
        <button @click="toggleCollapse" class="text-gray-400 hover:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  :d="isCollapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'" />
          </svg>
        </button>
      </div>
      
      <nav class="mt-4">
        <ul>
          <!-- Dashboard -->
          <li class="mb-2">
            <router-link to="/dashboard" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                        :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/dashboard') }">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span v-if="!isCollapsed" class="ml-3">Dashboard</span>
            </router-link>
          </li>

          <!-- User Management Section -->
          <li v-if="hasPermission(['users-all', 'users-view', 'roles-all', 'roles-view', 'permissions-all', 'permissions-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('userManagement')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/users') || isActive('/roles') || isActive('/permissions') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">User Management</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.userManagement || shouldOpenDropdown.userManagement }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.userManagement || shouldOpenDropdown.userManagement))" class="ml-8 mt-2 space-y-2">
                <router-link v-if="hasPermission(['users-all', 'users-view'])" to="/users" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Users
                </router-link>
                <router-link v-if="hasPermission(['roles-all', 'roles-view'])" to="/roles" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Roles
                </router-link>
                <router-link v-if="hasPermission(['permissions-all', 'permissions-view'])" to="/permissions" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Permissions
                </router-link>
              </div>
            </div>
          </li>

          <!-- Inventory Section -->
          <li v-if="hasPermission(['inventory-all', 'inventory-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('inventory')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/inventory') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Inventory</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.inventory || shouldOpenDropdown.inventory }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.inventory || shouldOpenDropdown.inventory))" class="ml-8 mt-2 space-y-2">
                <router-link to="/inventory" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Overview
                </router-link>
                <router-link to="/inventory/raw-materials" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Raw Materials
                </router-link>
                <router-link to="/inventory/products" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Products
                </router-link>
              </div>
            </div>
          </li>

          <!-- Departments Section -->
          <li v-if="hasPermission(['departments-all', 'departments-view'])" class="mb-2">
            <router-link to="/departments" class="flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                        :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/departments') }">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span v-if="!isCollapsed" class="ml-3">Departments</span>
            </router-link>
          </li>
          
          <!-- Artisans Section -->
          <li v-if="hasPermission(['artisans-all', 'artisans-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('artisans')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/artisans') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Artisans</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.artisans || shouldOpenDropdown.artisans }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.artisans || shouldOpenDropdown.artisans))" class="ml-8 mt-2 space-y-2">
                <router-link to="/artisans" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  All Artisans
                </router-link>
                
                <router-link to="/departments" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Departments
                </router-link>
                <router-link
                  to="/artisans/productivity"
                  class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors"
                >
                  Artisan Productivity
                </router-link>
              </div>
            </div>
          </li>
          
          <!-- Petty Cash Section -->
          <li v-if="hasPermission(['petty-cash-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('pettyCash')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/petty-cash') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Petty Cash</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.pettyCash || shouldOpenDropdown.pettyCash }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.pettyCash || shouldOpenDropdown.pettyCash))" class="ml-8 mt-2 space-y-2">
                <router-link to="/petty-cash" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Dashboard
                </router-link>
                <router-link to="/petty-cash/transactions" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Transactions
                </router-link>
                <router-link to="/petty-cash/categories" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Categories
                </router-link>
              </div>
            </div>
          </li>
          
          <!-- Orders Section -->
          <li v-if="hasPermission(['orders-all', 'orders-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('orders')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/orders') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Orders</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.orders || shouldOpenDropdown.orders }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.orders || shouldOpenDropdown.orders))" class="ml-8 mt-2 space-y-2">
                <router-link to="/orders" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  All Orders
                </router-link>
                
                <router-link to="/orders/assignments" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Assignments
                </router-link>
                <router-link to="/orders/dispatch" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Dispatch
                </router-link>
              </div>
            </div>
          </li>

          <!-- Attendance Section -->
          <li v-if="hasPermission(['attendance-all', 'attendance-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('attendance')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/attendance') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Attendance</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.attendance || shouldOpenDropdown.attendance }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.attendance || shouldOpenDropdown.attendance))" class="ml-8 mt-2 space-y-2">
                <router-link to="/attendance" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Mark Attendance
                </router-link>
                <router-link to="/attendance/report" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Attendance Report
                </router-link>
              </div>
            </div>
          </li>

          <li v-if="hasPermission(['payroll-all', 'payroll-view'])" class="mb-2">
            <div class="space-y-1">
              <button
                @click="dropdownOpen.payroll = !dropdownOpen.payroll"
                class="flex items-center justify-between w-full px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                :class="{ 'bg-gray-700': dropdownOpen.payroll }"
              >
                <div class="flex items-center">
                  <i class="fas fa-money-bill-wave mr-3"></i>
                  <span>Payroll</span>
                </div>
                <i
                  class="fas fa-chevron-down text-sm transition-transform"
                  :class="{ 'transform rotate-180': dropdownOpen.payroll }"
                ></i>
              </button>

              <div v-show="dropdownOpen.payroll" class="pl-4 space-y-1">
                <router-link
                  to="/payroll"
                  class="block px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                  :class="{ 'bg-gray-700': isActive('/payroll') }"
                >
                  Payroll Management
                </router-link>
                <router-link
                  to="/payroll/salary-calculation"
                  class="block px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                  :class="{ 'bg-gray-700': isActive('/payroll/salary-calculation') }"
                >
                  Salary Calculation
                </router-link>
                <router-link
                  to="/payroll/advances"
                  class="block px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                  :class="{ 'bg-gray-700': isActive('/payroll/advances') }"
                >
                  Artisan Advances
                </router-link>
              </div>
            </div>
          </li>
                    
          <!-- Wool Management Section -->
          <li v-if="hasPermission(['wool-all', 'wool-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('wool')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/wool') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Wool Management</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                    :class="{ 'rotate-180': dropdownOpen.wool || shouldOpenDropdown.wool }"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.wool || shouldOpenDropdown.wool))" class="ml-8 mt-2 space-y-2">
                <router-link to="/wool/suppliers" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Suppliers
                </router-link>
                <router-link to="/wool/usage" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Usage Records
                </router-link>
                <router-link to="/wool/usage/summary" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Monthly Summary
                </router-link>
              </div>
            </div>
          </li>

          <!-- Wages Section -->
          <li v-if="hasPermission(['wages-all', 'wages-view'])" class="mb-2">
            <div>
              <button @click="toggleDropdown('wages')" 
                      class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                      :class="{ 'justify-center': isCollapsed, 'bg-gray-700': isActive('/wages') }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span v-if="!isCollapsed" class="ml-3 flex-1 text-left">Wages</span>
                <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                     :class="{ 'rotate-180': dropdownOpen.wages || shouldOpenDropdown.wages }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div v-if="(!isCollapsed && (dropdownOpen.wages || shouldOpenDropdown.wages))" class="ml-8 mt-2 space-y-2">
                <router-link to="/wages/calculations" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Wage Calculations
                </router-link>
                <router-link to="/wages/reports" class="block p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                  Wage Report
                </router-link>
              </div>
            </div>
          </li>

          <!-- Logout Button -->
          <li class="mt-8">
            <button @click="logout" class="w-full flex items-center p-3 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors"
                    :class="{ 'justify-center': isCollapsed }">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span v-if="!isCollapsed" class="ml-3">Logout</span>
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, watch } from "vue"
import { useRouter } from "vue-router"
import useUserStore from "../store/useUserStore"
import axios from "axios"

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: true, // Change default to true to match LayoutDashboard
  },
})

const emit = defineEmits(["toggle-collapse"])
const router = useRouter()
const userStore = useUserStore()

const isCollapsed = ref(props.collapsed)

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
  emit("toggle-collapse", isCollapsed.value)
}

const isActive = (path) => {
  return router.currentRoute.value.path.startsWith(path)
}

const hasPermission = (permissions) => {
  if (!permissions || !permissions.length) return true
  return (userStore.user?.permissions || []).some((permission) => permissions.includes(permission?.name))
}

// Update the dropdownOpen ref to include wool
const dropdownOpen = ref({
  pettyCash: false,
  orders: false,
  artisans: false,
  payroll: false,
  attendance: false,
  wool: false,
  userManagement: false,
  inventory: false
})

const toggleDropdown = (section) => {
  dropdownOpen.value[section] = !dropdownOpen.value[section]
}

// Update the shouldOpenDropdown computed property to include userManagement and inventory
const shouldOpenDropdown = computed(() => {
  return {
    pettyCash: isActive("/petty-cash"),
    orders: isActive("/orders"),
    artisans: isActive("/artisans"),
    payroll: isActive("/payroll"),
    attendance: isActive("/attendance"),
    wool: isActive("/wool"),
    userManagement: isActive("/users") || isActive("/roles") || isActive("/permissions"),
    inventory: isActive("/inventory")
  }
})

// Add logout method
const logout = async () => {
  try {
    await axios.get("/logout")
    userStore.setUser(null)
    router.push("/login")
  } catch (error) {
    console.error("Logout failed:", error)
  }
}

// Update the watch function to include userManagement and inventory
watch(
  () => router.currentRoute.value.path,
  () => {
    // Auto-open dropdown based on current route
    dropdownOpen.value = {
      pettyCash: isActive("/petty-cash"),
      orders: isActive("/orders"),
      artisans: isActive("/artisans"),
      payroll: isActive("/payroll"),
      attendance: isActive("/attendance"),
      wool: isActive("/wool"),
      userManagement: isActive("/users") || isActive("/roles") || isActive("/permissions"),
      inventory: isActive("/inventory")
    }
  },
)

// Update the onMounted function to include userManagement and inventory
onMounted(() => {
  dropdownOpen.value = {
    pettyCash: isActive("/petty-cash"),
    orders: isActive("/orders"),
    artisans: isActive("/artisans"),
    payroll: isActive("/payroll"),
    attendance: isActive("/attendance"),
    wool: isActive("/wool"),
    userManagement: isActive("/users") || isActive("/roles") || isActive("/permissions"),
    inventory: isActive("/inventory")
  }
})
</script>
  