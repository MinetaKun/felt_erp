<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Mark Daily Attendance</h1>
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

      <!-- Action Buttons -->
      <div class="flex justify-between items-center mb-6">
        <button
          @click="loadExistingAttendance"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          View/Edit Existing Attendance
        </button>
        <button
          v-if="showExistingAttendance"
          @click="showExistingAttendance = false"
          class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
        >
          Mark New Attendance
        </button>
      </div>

      <!-- Existing Attendance Alert -->
      <div v-if="existingAttendance && !showExistingAttendance" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-yellow-700">
              Attendance has already been marked for {{ new Date(existingAttendanceDate).toLocaleDateString() }}.
              <button @click="loadExistingAttendance" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                Click here to view and edit
              </button>
            </p>
          </div>
        </div>
      </div>

      <!-- Existing Attendance Table -->
      <div v-if="showExistingAttendance" class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Existing Attendance Records</h3>
          <p class="mt-1 text-sm text-gray-500">Edit attendance records for {{ new Date(selectedDate).toLocaleDateString() }}</p>
        </div>
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
            <tr v-for="record in existingAttendanceRecords" :key="record.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ record.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <select
                  v-model="record.status"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="present">Present</option>
                  <option value="absent">Absent</option>
                  <option value="late">Late</option>
                </select>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  v-model="record.remarks"
                  type="text"
                  placeholder="Add remarks..."
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </td>
            </tr>
          </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <button
            @click="updateExistingAttendance"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <span v-if="loading">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </div>

      <!-- New Attendance Section -->
      <div v-if="!showExistingAttendance">
        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="markSelected('present')"
                :disabled="!hasSelected"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
              >
                Mark Selected Present
              </button>
              <button
                @click="markSelected('absent')"
                :disabled="!hasSelected"
                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
              >
                Mark Selected Absent
              </button>
              <button
                @click="markSelected('late')"
                :disabled="!hasSelected"
                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:opacity-50"
              >
                Mark Selected Late
              </button>
            </div>
            <div class="flex-1 max-w-md">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search by name..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="person in filteredPeople" :key="person.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :checked="selectedPeople.includes(person.id)"
                    @change="toggleSelect(person.id)"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ person.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.department?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <select
                    :value="attendanceData[person.id]?.status"
                    @change="updateAttendanceStatus(person.id, $event.target.value)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late</option>
                  </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    :value="attendanceData[person.id]?.remarks"
                    @input="updateAttendanceRemarks(person.id, $event.target.value)"
                    type="text"
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
      searchQuery: '',
      departments: [],
      users: [],
      artisans: [],
      attendanceData: {},
      loading: false,
      existingAttendance: false,
      existingAttendanceDate: null,
      selectedPeople: [],
      showExistingAttendance: false,
      existingAttendanceRecords: [],
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

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        people = people.filter(person => person.name.toLowerCase().includes(query));
      }
      
      return people;
    },
    hasSelected() {
      return this.selectedPeople.length > 0;
    },
    isAllSelected() {
      return this.filteredPeople.length > 0 && this.selectedPeople.length === this.filteredPeople.length;
    }
  },
  watch: {
    selectedDate: {
      immediate: true,
      async handler(newDate) {
        if (newDate) {
          await this.checkExistingAttendance(newDate);
        }
      }
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
        const users = response.data.data || response.data;
        this.users = Array.isArray(users) ? users.map(user => ({
          ...user,
          type: 'user'
        })) : [];
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
        const artisans = response.data.data || response.data;
        this.artisans = Array.isArray(artisans) ? artisans.map(artisan => ({
          ...artisan,
          type: 'artisan'
        })) : [];
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
        if (person && person.id) {
          acc[person.id] = {
            status: 'present',
            remarks: '',
            type: person.type
          };
        }
        return acc;
      }, {});
    },
    markAll(status) {
      this.filteredPeople.forEach(person => {
        this.attendanceData[person.id].status = status;
      });
    },
    async checkExistingAttendance(date) {
      try {
        const response = await axios.get(`/attendance/check-date/${date}`);
        this.existingAttendance = response.data.exists;
        if (this.existingAttendance) {
          this.existingAttendanceDate = date;
          Swal.fire({
            icon: 'warning',
            title: 'Attendance Already Marked',
            text: `Attendance has already been marked for ${new Date(date).toLocaleDateString()}. You cannot mark attendance for the same date twice.`,
            confirmButtonText: 'OK'
          });
        }
      } catch (error) {
        console.error('Error checking existing attendance:', error);
      }
    },
    async saveAttendance() {
      if (this.existingAttendance) {
        Swal.fire({
          icon: 'error',
          title: 'Cannot Save',
          text: `Attendance has already been marked for ${new Date(this.existingAttendanceDate).toLocaleDateString()}. You cannot mark attendance for the same date twice.`,
          confirmButtonText: 'OK'
        });
        return;
      }

      this.loading = true;
      try {
        const attendanceRecords = Object.entries(this.attendanceData)
          .filter(([id, data]) => data && data.status) // Only include valid records
          .map(([id, data]) => ({
            date: this.selectedDate,
            status: data.status,
            remarks: data.remarks || '',
            attendanceable_id: id,
            attendanceable_type: data.type === 'user' ? 'App\\Models\\User' : 'App\\Models\\Artisan'
          }));

        if (attendanceRecords.length === 0) {
          throw new Error('No valid attendance records to save');
        }

        await axios.post('/attendance/bulk', { records: attendanceRecords });

        this.existingAttendance = true;
        this.existingAttendanceDate = this.selectedDate;

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
          text: error.response?.data?.message || 'Failed to save attendance',
        });
      } finally {
        this.loading = false;
      }
    },
    updateAttendanceStatus(personId, status) {
      if (!this.attendanceData[personId]) {
        this.$set(this.attendanceData, personId, {
          status: 'present',
          remarks: '',
          type: this.users.find(u => u.id === personId) ? 'user' : 'artisan'
        });
      }
      this.attendanceData[personId].status = status;
    },
    updateAttendanceRemarks(personId, remarks) {
      if (!this.attendanceData[personId]) {
        this.$set(this.attendanceData, personId, {
          status: 'present',
          remarks: '',
          type: this.users.find(u => u.id === personId) ? 'user' : 'artisan'
        });
      }
      this.attendanceData[personId].remarks = remarks;
    },
    toggleSelect(personId) {
      const index = this.selectedPeople.indexOf(personId);
      if (index === -1) {
        this.selectedPeople.push(personId);
      } else {
        this.selectedPeople.splice(index, 1);
      }
    },
    toggleSelectAll() {
      if (this.isAllSelected) {
        this.selectedPeople = [];
      } else {
        this.selectedPeople = this.filteredPeople.map(person => person.id);
      }
    },
    markSelected(status) {
      this.selectedPeople.forEach(personId => {
        this.updateAttendanceStatus(personId, status);
      });
    },
    async loadExistingAttendance() {
      try {
        const response = await axios.get(`/attendance/date/${this.selectedDate}`);
        this.existingAttendanceRecords = response.data;
        this.showExistingAttendance = true;
        this.existingAttendance = true;
        this.existingAttendanceDate = this.selectedDate;
      } catch (error) {
        console.error('Error loading existing attendance:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load existing attendance records',
        });
      }
    },
    async updateExistingAttendance() {
      this.loading = true;
      try {
        await axios.put(`/attendance/date/${this.selectedDate}`, {
          records: this.existingAttendanceRecords
        });

        // Reload the existing attendance records after update
        await this.loadExistingAttendance();

        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Attendance records updated successfully',
        });
      } catch (error) {
        console.error('Error updating attendance:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to update attendance records',
        });
      } finally {
        this.loading = false;
      }
    },
  }
};
</script> 