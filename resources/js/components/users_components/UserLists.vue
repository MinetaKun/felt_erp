<template>
  <div class="flex h-screen flex-col">
    <Navbar />
    <div class="flex flex-1">
      <Sidebar class="h-full" />
      <div class="mt-8 w-full px-4">
        <h2 class="text-xl font-semibold mb-4">Users List</h2>

        <!-- Debug info: Show raw response data -->
        <div v-if="debug" class="mb-4 p-4 bg-gray-100">
          Raw data: {{ JSON.stringify(users, null, 2) }}
        </div>

        <!-- Loading and error handling -->
        <div v-if="loading" class="text-center">
          Loading users...
        </div>
        <div v-else-if="error" class="text-red-500">
          {{ error }}
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Navbar from '../Navbar.vue';
import Sidebar from '../Sidebar.vue';

export default {
  name: 'Users_List',
  components: {
    Navbar,
    Sidebar,
  },
  data() {
    return {
      users: [],
      loading: false,
      error: null,
      debug: true,  // Set to true for debugging
    };
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;
      try {
        console.log('Starting fetch...');
        const response = await axios.get('http://localhost:8000/users'); // Full URL to your backend API
        console.log('Raw response data:', response.data);  // Log the raw data

        this.users = response.data;  // Directly assign the response data to users

        if (!this.users || !this.users.length) {
          this.error = 'No users found';
        }
      } catch (error) {
        console.error('Fetch error:', error);
        this.error = 'Failed to fetch users: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    console.log('Component mounted');
    this.fetchUsers();  // Fetch users when the component is mounted
  },
};
</script>
