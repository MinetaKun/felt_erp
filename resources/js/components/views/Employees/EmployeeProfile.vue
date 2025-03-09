<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="flex">
      <Sidebar class="h-screen" />
      <div class="container mx-auto p-6">
        <div v-if="selectedEmployee" class="bg-white rounded-xl shadow-lg p-8 max-w-4xl w-full">
          <!-- Header Section -->
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">User Profile</h2>
            <button
              @click="closeProfile"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Profile Header with User Info -->
          <div class="flex items-center mb-8 border-b pb-6">
            <img 
              v-if="selectedEmployee.user?.photo" 
              :src="selectedEmployee.user.photo" 
              alt="Profile Photo" 
              class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 mr-6"
            >
            <div>
              <h3 class="text-2xl font-semibold text-gray-800">{{ selectedEmployee.user?.full_name || 'N/A' }}</h3>
              <p class="text-gray-600">{{ selectedEmployee.user?.email || 'N/A' }}</p>
              <p class="text-gray-500 text-sm">{{ selectedEmployee.designation }}</p>
            </div>
          </div>

          <!-- Profile Content -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Employee ID:</span>
                <span class="text-gray-800">{{ selectedEmployee.id }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">User ID:</span>
                <span class="text-gray-800">{{ selectedEmployee.user_id }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Type:</span>
                <span class="text-gray-800">{{ selectedEmployee.employee_type }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Department:</span>
                <span class="text-gray-800">{{ selectedEmployee.department }}</span>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Join Date:</span>
                <span class="text-gray-800">{{ formatDate(selectedEmployee.join_date) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Mobile:</span>
                <span class="text-gray-800">{{ selectedEmployee.mobile }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Citizenship No:</span>
                <span class="text-gray-800">{{ selectedEmployee.citizenship_no }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">PAN No:</span>
                <span class="text-gray-800">{{ selectedEmployee.pan_no }}</span>
              </div>
            </div>
          </div>

          <!-- Skills Section -->
          <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Skills</h3>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="skill in selectedEmployee.skills.split(',')" 
                :key="skill"
                class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full"
              >
                {{ skill.trim() }}
              </span>
            </div>
          </div>

          <!-- Citizenship Photo Section -->
          <div v-if="selectedEmployee.citizenship_photo" class="mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Citizenship Photo</h3>
            <img 
              :src="selectedEmployee.citizenship_photo" 
              alt="Citizenship Photo" 
              class="w-48 h-48 object-cover rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
            />
          </div>

          <!-- Action Buttons -->
          <div class="mt-8 flex justify-end space-x-4">
            <button
              @click="closeProfile"
              class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '../../Navbar.vue';
import Sidebar from '../../Sidebar.vue';
import axios from 'axios';

export default {
  name: 'EmployeeProfile',
  components: {
    Navbar,
    Sidebar,
  },
  data() {
    return {
      selectedEmployee: null,
    };
  },
  methods: {
    async viewProfile() {
      try {
        const employeeId = this.$route.params.id;
        console.log(this.$route.params.id);
        console.log(employeeId);
        const response = await axios.get(`/api/users/${employeeId}`, {
          params: { include: 'user' } // Optional: If your API supports including related data
        });
        this.selectedEmployee = response.data;
        console.log(this.selectedEmployee);
        
        // If the API doesn't include user data, fetch it separately
        if (!this.selectedEmployee.user) {
          await this.fetchUserData();
        }
      } catch (error) {
        console.error('Error fetching employee profile:', error);
      }
    },
    async fetchUserData() {
      try {
        const userResponse = await axios.get(`/api/users/${employeeId}`);
        this.selectedEmployee.user = userResponse.data; // Add user data to employee object
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    },
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('en-US', options);
    },
    closeProfile() {
      this.selectedEmployee = null;
      this.$router.push('/users');
    },
  },
  mounted() {
    this.viewProfile();
  },
};
</script>

<style scoped>
/* Add any additional custom styles here if needed */
</style>