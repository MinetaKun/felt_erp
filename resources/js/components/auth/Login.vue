<template>
  <div class="min-h-screen flex justify-center items-center bg-gray-100">
    <!-- Main Container -->
    <div class="flex w-full max-w-4xl bg-white shadow-xl rounded-lg">
      <!-- Left Section (Company Description) -->
      <div class="w-1/2 p-8 flex flex-col justify-center bg-blue-600 text-white">
        <img src="" alt="Company Logo" class="mb-6 mx-auto w-24" />
        <h2 class="text-3xl font-semibold">Welcome to Our Platform</h2>
        <p class="mt-4 text-lg">Sign in to manage your dashboard, view reports, and track performance.</p>
      </div>

      <!-- Right Section (Login Form) -->
      <div class="w-1/2 p-8 flex flex-col justify-center">
        <h2 class="text-2xl font-semibold mb-4 text-gray-900">Sign in to your account</h2>
        
        <!-- Login Form -->
        <form @submit.prevent="loginUser" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input 
              type="email" 
              id="email" 
              v-model="email" 
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" 
              required 
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input 
              type="password" 
              id="password" 
              v-model="password" 
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" 
              required 
            />
          </div>
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input 
              type="password" 
              id="confirm_password" 
              v-model="confirm_password" 
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" 
              required 
            />
          </div>
          
          <div class="flex justify-between items-center">
            <a href="#" class="text-sm text-blue-600 hover:underline">Forgot Password?</a>
            <button 
              type="submit" 
              class="px-6 py-2 bg-blue-600 text-white rounded-lg focus:outline-none hover:bg-blue-700 transition duration-200"
              :disabled="loading"
            >
              {{ loading ? 'Logging in...' : 'Sign In' }}
            </button>
          </div>
        </form>

        <!-- Error Message -->
        <div v-if="error" class="mt-4 text-red-600 text-sm">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      loading: false, // Track loading state
      error: '', // Track error messages
    };
  },
  methods: {
    async loginUser() {
      this.loading = true;
      this.error = ''; // Reset any previous error message

      try {
        // Make API call to login
        const response = await axios.post('/login', { // Use '/login' route for session-based login
          email: this.email,
          password: this.password,
        });

        if (response.status === 200) {
          // Successful login, store the user data (optional)
          localStorage.setItem('user', JSON.stringify(response.data.user));

          // Redirect to dashboard
          this.$router.push('/');
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Invalid credentials, please try again.';
        console.error('Login failed', error.response?.data);
      } finally {
        this.loading = false; // Reset loading state
      }
    },
  },
};
</script>

<style scoped>
/* Custom styles can be added here if needed */
</style>
