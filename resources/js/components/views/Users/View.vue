<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Navbar -->
      <Navbar />
      
      <!-- Main Content Section -->
      <div class="flex">
        <Sidebar class="h-screen" />
        
        <!-- Content -->
        <div class="flex-1 p-8">
          <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
              <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-bold text-indigo-900">Users</h1>
                <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-800 rounded-full">{{ users.length }} users</span>
              </div>
              
              <router-link to="/users/create">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                  Add New User
                </button>
              </router-link>
            </div>
  
            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
              <table class="w-full">
                <thead>
                  <tr class="border-b bg-gray-50">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">User Info</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Permissions</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id" class="border-b last:border-b-0 hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-600">{{ user.id }}</td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-3">
                        <img 
                          :src="user.photo || '/default-avatar.png'" 
                          :alt="user.full_name"
                          class="w-10 h-10 rounded-full object-cover"
                        >
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ user.full_name }}</div>
                          <div class="text-sm text-gray-500">@{{ user.username }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 text-sm rounded-full" 
                            :class="{
                              'bg-blue-100 text-blue-800': user.role === 'admin',
                              'bg-green-100 text-green-800': user.role === 'employee',
                              'bg-purple-100 text-purple-800': user.role === 'client'
                            }">
                        {{ user.role }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 text-sm rounded-full"
                            :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        {{ user.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex flex-wrap gap-2">
                        <span v-for="permission in user.permissions" 
                              :key="permission"
                              class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                          {{ permission }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="flex justify-end space-x-3">
                        <button v-if="user.role === 'employee'" 
                        @click="goToProfile(user.id)" 
                        class="text-indigo-600 hover:text-indigo-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          </svg>

                        </button>
                        <button class="text-indigo-600 hover:text-indigo-900">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                          </svg>
                        </button>
                        <button class="text-red-600 hover:text-red-900">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
              <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Previous
              </button>
              <span class="text-sm text-gray-600">Page 1 of 10</span>
              <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import Navbar from '../../Navbar.vue';
  import Sidebar from '../../Sidebar.vue';
  
  export default {
    name: 'View',
    components: {
      Navbar,
      Sidebar
    },
    data() {
      return {
        users: []
      }
    },
    methods: {
      async getUsers() {
        try {
          const response = await axios.get('/api/users');
          this.users = response.data.data;
        } catch (error) {
          console.error('Error fetching users:', error);
        }
      },
      goToProfile(userId) {
    // Navigate to Employee Profile page by employee ID
        this.$router.push(`/users/${userId}`);
     },
    },
    mounted() {
      this.getUsers();
    }
  }
  </script>

