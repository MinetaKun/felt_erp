<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />
    <div class="flex">
      <Sidebar class="h-screen" />
      <div class="container mx-auto p-6">
        <div v-if="userData" class="bg-white rounded-xl shadow-lg p-8 max-w-4xl w-full">
          <!-- Header Section -->
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">User Profile</h2>
            <div class="flex items-center">
              <span class="mr-3 px-3 py-1 rounded-lg text-white" :class="userTypeClass">
                {{ userData.user.role }}
              </span>
              <button
                @click="closeProfile"
                class="text-gray-500 hover:text-gray-700 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Profile Header with User Info -->
          <div class="flex items-center mb-8 border-b pb-6">
            <img 
              v-if="userData.user.photo" 
              :src="userData.user.photo" 
              alt="Profile Photo" 
              class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 mr-6"
            >
            <div v-else class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center mr-6">
              <span class="text-2xl text-gray-600">{{ getInitials(userData.user.full_name) }}</span>
            </div>
            <div>
              <h3 class="text-2xl font-semibold text-gray-800">{{ userData.user.full_name || 'N/A' }}</h3>
              <p class="text-gray-600">{{ userData.user.email || 'N/A' }}</p>
              <p v-if="userData.user.role === 'employee'" class="text-gray-500 text-sm">{{ userData.designation }}</p>
              <p v-else-if="userData.user_type === 'client'" class="text-gray-500 text-sm">{{ userData.company_name }}</p>
              <p v-else-if="userData.user_type === 'supplier'" class="text-gray-500 text-sm">{{ userData.supplier_type }}</p>
            </div>
          </div>
          
          <!-- Common User Information -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Account Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">User ID:</span>
                <span class="text-gray-800">{{ userData.id }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Status:</span>
                <span :class="userData.user.is_active === true ? 'text-green-600' : 'text-red-600'">
                   Active 
                </span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Phone:</span>
                <span class="text-gray-800">{{ userData.mobile || 'N/A' }}</span>
              </div>
            </div>
          </div>
          
          <!-- User Type Specific Information -->
          <div v-if="userData.user.role === 'employee'" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Employee Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Employee ID:</span>
                <span class="text-gray-800">{{ userData.id }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Department:</span>
                <span class="text-gray-800">{{ userData.department }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Designation:</span>
                <span class="text-gray-800">{{ userData.designation }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Type:</span>
                <span class="text-gray-800">{{ userData.employee_type }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Join Date:</span>
                <span class="text-gray-800">{{ formatDate(userData.join_date) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">PAN No:</span>
                <span class="text-gray-800">{{ userData.pan_no || 'N/A' }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Citizenship:</span>
                <span class="text-gray-800">{{ userData.citizenship_no || 'N/A' }}</span>
              </div>
            </div>
            
            <!-- Skills Section for Employee -->
            <div class="mt-4">
              <h4 class="text-md font-semibold text-gray-700 mb-2">Skills</h4>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="skill in getSkillsArray(userData.skills)" 
                  :key="skill"
                  class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full"
                >
                  {{ skill }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Client Specific Information -->
          <div v-else-if="userData.user_type === 'client'" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Client Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Company:</span>
                <span class="text-gray-800">{{ userData.company_name }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Industry:</span>
                <span class="text-gray-800">{{ userData.industry }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">VAT/PAN:</span>
                <span class="text-gray-800">{{ userData.vat_number || userData.pan_number || 'N/A' }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Address:</span>
                <span class="text-gray-800">{{ userData.address || 'N/A' }}</span>
              </div>
            </div>
          </div>
          
          <!-- Supplier Specific Information -->
          <div v-else-if="userData.user_type === 'supplier'" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Supplier Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Company:</span>
                <span class="text-gray-800">{{ userData.company_name }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Type:</span>
                <span class="text-gray-800">{{ userData.supplier_type }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">VAT/PAN:</span>
                <span class="text-gray-800">{{ userData.vat_number || userData.pan_number || 'N/A' }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Products:</span>
                <span class="text-gray-800">{{ userData.product_categories || 'N/A' }}</span>
              </div>
            </div>
          </div>
          
          <!-- Admin Specific Information -->
          <div v-else-if="userData.user_type === 'admin'" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Admin Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Role:</span>
                <span class="text-gray-800">{{ userData.admin_role }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-600 font-medium w-32">Permissions:</span>
                <span class="text-gray-800">{{ userData.permissions_level }}</span>
              </div>
            </div>
          </div>
          
          <!-- Documents Section (conditionally shown) -->
          <div v-if="hasDocuments" class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Documents</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              <div v-if="userData.citizenship_photo" class="group">
                <div class="border rounded-lg overflow-hidden h-48 bg-gray-100 relative group-hover:shadow-md transition-all">
                  <img 
                    :src="userData.citizenship_photo" 
                    alt="Citizenship Photo" 
                    class="w-full h-full object-cover"
                  />
                  <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                    <button class="text-white bg-blue-600 px-3 py-1 rounded shadow">View</button>
                  </div>
                </div>
                <p class="text-sm text-gray-600 mt-1">Citizenship Document</p>
              </div>
              
              <div v-if="userData.contract_document" class="group">
                <div class="border rounded-lg overflow-hidden h-48 bg-gray-100 relative group-hover:shadow-md transition-all">
                  <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                    <button class="text-white bg-blue-600 px-3 py-1 rounded shadow">View</button>
                  </div>
                </div>
                <p class="text-sm text-gray-600 mt-1">Contract Document</p>
              </div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="mt-8 flex justify-end space-x-4">
            <button
              v-if="canEdit"
              @click="editProfile"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Edit Profile
            </button>
            <button
              @click="closeProfile"
              class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors"
            >
              Close
            </button>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-else-if="loading" class="flex justify-center items-center h-64">
          <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-xl shadow-lg p-8 max-w-4xl w-full">
          <div class="flex flex-col items-center justify-center h-64">
            <svg class="w-16 h-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Error Loading Profile</h3>
            <p class="text-gray-600">{{ errorMessage }}</p>
            <button 
              @click="retryLoading" 
              class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Try Again
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
  name: 'UserProfile',
  components: {
    Navbar,
    Sidebar,
  },
  data() {
    return {
      userData: null,
      loading: true,
      error: false,
      errorMessage: '',
      canEdit: false,
    };
  },
  computed: {
    userTypeClass() {
      const classes = {
        'employee': 'bg-blue-600',
        'client': 'bg-green-600',
        'supplier': 'bg-purple-600',
        'admin': 'bg-red-600'
      };
      return classes[this.userData?.user_type] || 'bg-gray-600';
    },
    hasDocuments() {
      return this.userData && (
        this.userData.citizenship_photo || 
        this.userData.contract_document ||
        this.userData.other_documents
      );
    }
  },
  methods: {
    async fetchUserProfile() {
      this.loading = true;
      this.error = false;
      
      try {
        const userId = this.$route.params.id;
        const response = await axios.get(`/api/users/${userId}`);
        this.userData = response.data;
        console.log(this.userData);
                      
      } catch (error) {
        console.error('Error fetching user profile:', error);
        this.error = true;
        this.errorMessage = error.response?.data?.message || 'Failed to load user profile';
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('en-US', options);
    },
    getInitials(name) {
      if (!name) return '?';
      return name.split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .join('')
        .substring(0, 2);
    },
    getSkillsArray(skills) {
      if (!skills) return [];
      return skills.split(',').map(skill => skill.trim()).filter(Boolean);
    },
    closeProfile() {
      this.$router.push('/users');
    },
    editProfile() {
      this.$router.push(`/users/${this.$route.params.id}/edit`);
    },
    retryLoading() {
      this.fetchUserProfile();
    }
  },
  mounted() {
    this.fetchUserProfile();
  },
};
</script>

<style scoped>
/* Add any additional custom styles here if needed */
.group {
  transition: all 0.2s ease;
}
</style>