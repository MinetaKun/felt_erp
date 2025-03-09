<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Navbar -->
      <Navbar />
      
      <!-- Main Content Section -->
      <div class="flex">
        <Sidebar class="h-screen" />
        <div class="container mx-auto p-4">
          <div class="overflow-x-auto shadow-md rounded-lg">
            <table class="min-w-full bg-white border border-gray-200">
              <thead>
                <tr class="bg-gray-100">
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">ID</th>
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">User Info</th>
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">Email</th>
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">Role</th>
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">Status</th>
                  <th class="py-2 px-4 text-left font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="employee in employees" :key="employee.id" class="border-t">
                  <td class="py-2 px-4 text-gray-600">{{ employee.id }}</td>
                  <td class="py-2 px-4 text-gray-600">{{ employee.user.name }}</td>
                  <td class="py-2 px-4 text-gray-600">{{ employee.user.email }}</td>
                  <td class="py-2 px-4 text-gray-600">{{ employee.role }}</td>
                  <td class="py-2 px-4 text-gray-600">{{ employee.status }}</td>
                  <td class="py-2 px-4">
                    <button
                      class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600"
                      @click="viewProfile(employee.id)"
                    >
                      View Profile
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <!-- Modal for Employee Profile -->
          <div v-if="selectedEmployee" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-6 rounded-lg max-w-2xl w-full">
              <h2 class="text-2xl font-bold mb-4">Employee Profile</h2>
              <p><strong>ID:</strong> {{ selectedEmployee.id }}</p>
              <p><strong>User ID:</strong> {{ selectedEmployee.user_id }}</p>
              <p><strong>Employee Type:</strong> {{ selectedEmployee.employee_type }}</p>
              <p><strong>Department:</strong> {{ selectedEmployee.department }}</p>
              <p><strong>Designation:</strong> {{ selectedEmployee.designation }}</p>
              <p><strong>Join Date:</strong> {{ formatDate(selectedEmployee.join_date) }}</p>
              <p><strong>Mobile:</strong> {{ selectedEmployee.mobile }}</p>
              <p><strong>Skills:</strong> {{ selectedEmployee.skills }}</p>
              <p><strong>Citizenship No:</strong> {{ selectedEmployee.citizenship_no }}</p>
              <div v-if="selectedEmployee.citizenship_photo">
                <p><strong>Citizenship Photo:</strong></p>
                <img :src="selectedEmployee.citizenship_photo" alt="Citizenship Photo" class="w-32 h-32 object-cover rounded-md" />
              </div>
              <p><strong>PAN No:</strong> {{ selectedEmployee.pan_no }}</p>
              
              <button
                class="bg-red-500 text-white px-4 py-2 rounded-md shadow hover:bg-red-600 mt-4"
                @click="closeProfile"
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
    name: 'EmployeeList',
    components: {
      Navbar,
      Sidebar,
    },
    data() {
      return {
        employees: [
          // Static data for now, you can fetch this dynamically from the API
        ],
        selectedEmployee: null,
      };
    },
    methods: {
      async viewProfile(employeeId) {
        // Make an API call to fetch the employee profile
        try {
          const response = await axios.get(`/users/${employeeId}`);
          this.selectedEmployee = response.data;
        } catch (error) {
          console.error('Error fetching employee profile:', error);
        }
      },
      formatDate(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(date).toLocaleDateString('en-US', options);
      },
      closeProfile() {
        this.selectedEmployee = null;
      }
    }
  };
  </script>
  