<template>
    <div class="flex h-screen flex-col">
      <!-- Navbar -->
      <Navbar />
      <!-- Main Content Section (Sidebar and Content) -->
      <div class="flex flex-1">
        <!-- Sidebar Component -->
        <Sidebar class="h-full" />
<div class="mt-8">
    <h2 class="text-xl font-semibold mb-4">Users List</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <div
        v-for="user in users"
        :key="user.id"
        class="p-4 border rounded shadow"
      >
        <h3 class="text-lg font-bold">{{ user.name }}</h3>
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Phone:</strong> {{ user.phone_number }}</p>
        <p><strong>Address:</strong> {{ user.address }}</p>
        <p><strong>Role:</strong> {{ user.role }}</p>
      </div>
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
    fetchUsers() {
      axios
        .get('/users') // Ensure this matches the backend endpoint
        .then((response) => {
          this.users = response.data; // Populate users data
        })
        .catch((error) => {
          console.error('Error fetching users:', error);
        });
    },
  mounted() {
    this.fetchUsers(); // Fetch users when the component is mounted
  },
};
  </script>
  
  <style scoped>
  /* Additional custom styles for the form can go here */
  </style>
  