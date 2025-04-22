<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daily Attendance</h1>
        <p class="text-gray-600">Mark attendance for all users and artisans</p>
      </div>

      <!-- Date Selection -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center space-x-4">
          <div class="flex-1">
            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
            <input
              type="date"
              id="date"
              v-model="selectedDate"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          <div class="flex-1">
            <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
            <select
              id="department"
              v-model="selectedDepartment"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
          <div class="flex-1">
            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
            <select
              id="type"
              v-model="selectedType"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Types</option>
              <option value="user">Users</option>
              <option value="artisan">Artisans</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="person in filteredPeople" :key="person.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ person.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.department?.name || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <select
                  v-model="attendanceData[person.id].status"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="present">Present</option>
                  <option value="absent">Absent</option>
                  <option value="late">Late</option>
                </select>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  type="text"
                  v-model="attendanceData[person.id].remarks"
                  placeholder="Add remarks..."
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Save Button -->
      <div class="mt-6 flex justify-end">
        <button
          @click="saveAttendance"
          :disabled="loading"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <span v-if="loading">Saving...</span>
          <span v-else>Save Attendance</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      selectedDate: new Date().toISOString().split('T')[0],
      selectedDepartment: '',
      selectedType: '',
      departments: [],
      users: [],
      artisans: [],
      attendanceData: {},
      loading: false,
    };
  },
  computed: {
    filteredPeople() {
      let people = [
        ...this.users.map(user => ({ ...user, type: 'user' })),
        ...this.artisans.map(artisan => ({ ...artisan, type: 'artisan' }))
      ];
      
      if (this.selectedDepartment) {
        people = people.filter(person => person.department_id === parseInt(this.selectedDepartment));
      }
      
      if (this.selectedType) {
        people = people.filter(person => person.type === this.selectedType);
      }
      
      return people;
    }
  },
  async mounted() {
    await this.fetchDepartments();
    await this.fetchUsers();
    await this.fetchArtisans();
    this.initializeAttendanceData();
  },
  methods: {
    async fetchDepartments() {
      try {
        const response = await axios.get('/departments');
        this.departments = response.data;
      } catch (error) {
        console.error('Error fetching departments:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch departments',
        });
      }
    },
    async fetchUsers() {
      try {
        const response = await axios.get('/users');
        this.users = response.data.map(user => ({
          ...user,
          type: 'user'
        }));
      } catch (error) {
        console.error('Error fetching users:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch users',
        });
      }
    },
    async fetchArtisans() {
      try {
        const response = await axios.get('/artisans');
        this.artisans = response.data.map(artisan => ({
          ...artisan,
          type: 'artisan'
        }));
      } catch (error) {
        console.error('Error fetching artisans:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch artisans',
        });
      }
    },
    initializeAttendanceData() {
      const allPeople = [...this.users, ...this.artisans];
      this.attendanceData = allPeople.reduce((acc, person) => {
        acc[person.id] = {
          status: 'present',
          remarks: '',
          type: person.type
        };
        return acc;
      }, {});
    },
    async saveAttendance() {
      this.loading = true;
      try {
        const attendanceRecords = Object.entries(this.attendanceData).map(([id, data]) => ({
          date: this.selectedDate,
          status: data.status,
          remarks: data.remarks,
          attendanceable_id: id,
          attendanceable_type: data.type === 'user' ? 'App\\Models\\User' : 'App\\Models\\Artisan'
        }));

        await axios.post('/attendance/bulk', { records: attendanceRecords });

        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Attendance saved successfully',
        });
      } catch (error) {
        console.error('Error saving attendance:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save attendance',
        });
      } finally {
        this.loading = false;
      }
    }
  }
};
</script> 