<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- Header with Navigation -->
    <div class="bg-white shadow-sm">
      <div class="container mx-auto px-6 py-4">
        <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Artisans
        </button>
      </div>
    </div>

    <div class="container mx-auto p-6">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8 space-y-6 animate-pulse">
        <div class="flex flex-col md:flex-row md:space-x-8">
          <div class="flex-shrink-0">
            <div class="h-36 w-36 bg-gray-200 rounded-full"></div>
          </div>
          <div class="mt-4 md:mt-0 space-y-3 w-full">
            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            <div class="h-6 bg-gray-200 rounded w-1/2"></div>
            <div class="h-6 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
        <div class="h-px bg-gray-200 my-6"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="h-10 bg-gray-200 rounded"></div>
          <div class="h-10 bg-gray-200 rounded"></div>
          <div class="h-10 bg-gray-200 rounded"></div>
          <div class="h-10 bg-gray-200 rounded"></div>
        </div>
        <div class="h-px bg-gray-200 my-6"></div>
        <div class="h-8 bg-gray-200 rounded w-1/4"></div>
        <div class="h-64 bg-gray-200 rounded"></div>
      </div>

      <!-- Error Message -->
      <div v-else-if="error" class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8 flex flex-col justify-center items-center py-16">
        <div class="text-red-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Unable to Load Profile</h2>
        <p class="text-gray-600 mb-6 text-center">{{ errorMessage }}</p>
        <button @click="fetchArtisan" class="px-6 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Retry
        </button>
      </div>

      <!-- Artisan Details -->
      <div v-else-if="artisan" class="max-w-4xl mx-auto">
        <!-- Profile Card -->
        <div class="bg-white shadow-md rounded-xl overflow-hidden mb-6">
          <!-- Cover Photo Background -->
          <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
          
          <!-- Profile Info -->
          <div class="px-6 py-8 relative">
            <!-- Profile Image -->
            <div class="absolute -top-16 left-6">
              <div v-if="artisan.profile_photo_url" class="relative">
                <img :src="artisan.profile_photo_url" alt="Profile Photo" 
                  class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-lg hover:scale-105 transition-transform" />
                <div class="absolute bottom-0 right-0 bg-green-500 w-5 h-5 rounded-full border-2 border-white"></div>
              </div>
              <div v-else class="w-32 h-32 rounded-full border-4 border-white bg-gray-200 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
            </div>
            
            <!-- Name and Department -->
            <div class="ml-40">
              <h1 class="text-3xl font-bold text-gray-800">{{ artisan.name || 'N/A' }}</h1>
              <div class="flex items-center text-gray-600 mt-1">
                <svg v-if="artisan.department?.name" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>{{ artisan.department?.name || 'Department Not Assigned' }}</span>
              </div>
              <div class="mt-3 flex items-center text-gray-800 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ formatCurrency(artisan.basic_salary) || 'Salary Not Specified' }}</span>
              </div>
            </div>
          </div>

          <!-- Contact Info Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 px-6 pb-6">
            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-center">
                <div class="bg-blue-100 rounded-full p-2 mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <p class="font-medium truncate" :title="artisan.email">{{ artisan.email || 'N/A' }}</p>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-2 mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Phone</p>
                  <p class="font-medium">{{ artisan.phone_number || 'N/A' }}</p>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition">
              <div class="flex items-center">
                <div class="bg-purple-100 rounded-full p-2 mr-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">PAN Number</p>
                  <p class="font-medium">{{ artisan.pan_number || 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Citizenship Card -->
        <div v-if="artisan.citizenship_photo_url" class="bg-white shadow-md rounded-xl p-6 mb-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            Citizenship Document
          </h2>
          <div class="flex justify-center">
            <div class="relative group cursor-pointer">
              <img :src="artisan.citizenship_photo_url" alt="Citizenship" 
                class="max-w-md w-full object-contain rounded-lg border border-gray-200 shadow-md group-hover:shadow-lg transition duration-300" />
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg flex items-center justify-center transition duration-300">
                <div class="opacity-0 group-hover:opacity-100 transition duration-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="bg-white shadow-md rounded-xl p-6 mb-6 flex flex-col items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <p class="text-gray-500">No citizenship document available</p>
        </div>
        
        <!-- Attendance Section -->
        <div class="bg-white shadow-md rounded-xl p-6">
          <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Attendance Record
          </h2>
          <AttendanceTable :artisanId="artisan.id" />
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.email }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.phone_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">PAN Number</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.pan_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Bank Account Number</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.bank_account_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Basic Salary</label>
                <p class="mt-1 text-sm text-gray-900">Rs. {{ artisan.basic_salary }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Department</label>
                <p class="mt-1 text-sm text-gray-900">{{ artisan.department?.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <p class="mt-1">
                  <span :class="artisan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ artisan.status }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Skills -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Skills</h3>
            <div class="flex flex-wrap gap-2">
              <span v-for="skill in artisan.skills" :key="skill"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-700">
                {{ skill }}
              </span>
              <span v-if="!artisan.skills?.length" class="text-gray-500 text-sm">No skills added</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found Message -->
      <div v-else class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8 flex flex-col justify-center items-center py-16">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Artisan Not Found</h2>
        <p class="text-gray-600 mb-6">The artisan you are looking for does not exist or has been removed.</p>
        <button @click="goBack" class="px-6 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Return to Artisans List
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import AttendanceTable from './AttendanceTable.vue';

export default {
  components: { AttendanceTable },
  data() {
    return {
      artisan: null,
      loading: false,
      error: false,
      errorMessage: '',
    };
  },
  mounted() {
    this.fetchArtisan();
  },
  methods: {
    async fetchArtisan() {
      this.loading = true;
      this.error = false;
      this.errorMessage = '';
      try {
        const response = await axios.get(`/artisans/${this.$route.params.id}`);
        if (response.data.success && response.data.data) {
          this.artisan = response.data.data;
        } else {
          this.artisan = null;
          this.errorMessage = 'No artisan data available.';
        }
      } catch (error) {
        console.error('Error fetching artisan:', error);
        this.error = true;
        this.artisan = null;
        this.errorMessage = error.response?.data?.message || 'Failed to fetch artisan details. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    goBack() {
      this.$router.go(-1);
    },
    formatCurrency(value) {
      return value
        ? new Intl.NumberFormat('ne-NP', { style: 'currency', currency: 'NPR' }).format(value)
        : 'N/A';
    },
  },
  watch: {
    artisan(newVal) {
      document.title = newVal ? `${newVal.name} - Artisan Profile` : 'Artisan Profile';
    },
  },
};
</script>