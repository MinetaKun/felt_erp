<template>
    <div class="flex h-screen flex-col">
      <!-- Navbar -->
      <Navbar />
      <!-- Main Content Section (Sidebar and Content) -->
      <div class="flex flex-1">
        <!-- Sidebar Component -->
        <Sidebar class="h-full" />
        
        <!-- Content -->
        <div class="flex-1 p-6 bg-gray-100">
          <a href="/users-list" class="">
            <!-- Payroll & Wages Icon -->
            <i class="fas fa-wallet mr-3"></i>
            <span>Users List</span>
          </a>
          <!-- Form Section -->
          <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
            <h1 class="text-2xl font-bold mb-6">Add New Artisan</h1>
            <form @submit.prevent="submitForm">
              <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                />
              </div>
  
              <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                />
              </div>
  
              <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                />
              </div>
  
              <div class="mb-4">
                <label for="role" class="block text-gray-700">Role</label>
                <select
                  id="role"
                  v-model="form.role"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                >
                  <option value="artisan">Artisan</option>
                  <option value="accountant">Accountant</option>
                  <option value="manager">Manager</option>
                  <option value="suppliers">Suppliers</option>
                  <option value="clients">Client</option>
                </select>
              </div>
  
              <div class="mb-4">
                <label for="phone" class="block text-gray-700">Phone Number</label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                />
              </div>
  
              <div class="mb-4">
                <label for="address" class="block text-gray-700">Address</label>
                <input
                  id="address"
                  v-model="form.address"
                  type="text"
                  class="w-full p-2 mt-1 border border-gray-300 rounded"
                  required
                />
              </div>
              <!-- Submit Button -->
              <button
                type="submit"
                class="w-full p-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                Add User
              </button>
            </form>

  
            <!-- Success Message -->
            <div v-if="formSubmitted" class="mt-6 text-green-500">
              <p>Form submitted successfully!</p>
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
    name: 'AddArtisan',
    components: {
      Navbar,
      Sidebar,
    },
    data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        role: 'artisan',
        phone: '',
        address: '',
      },
      users: [], // To store the list of users
    };
  },
  methods: {
    submitForm() {
      if (!this.form.name || !this.form.email || !this.form.password || !this.form.phone || !this.form.address) {
        alert('All fields are required!');
        return;
      }

      axios
        .post('/user-registration', this.form)
        .then(() => {
          this.resetForm();
          this.fetchUsers(); // Refresh the user list after adding a user
        })
        .catch((error) => {
          console.error('There was an error!', error);
        });
    },
    resetForm() {
      this.form = {
        name: '',
        email: '',
        password: '',
        role: 'artisan',
        phone: '',
        address: '',
      };
    },
  },
};

  </script>
  
  <style scoped>
  /* Additional custom styles for the form can go here */
  </style>
  