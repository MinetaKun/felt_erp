<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Attendance Report</h1>
        <p class="text-gray-600">View attendance records for users and artisans</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Date Range -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <input
                  v-model="filters.startDate"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              <div>
                <input
                  v-model="filters.endDate"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Other Filters -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Types</option>
              <option value="user">Users</option>
              <option value="artisan">Artisans</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Status</option>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
            <select
              v-model="filters.department"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <button
            @click="fetchReport"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Apply Filters
          </button>
          <button
            @click="exportReport"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
          >
            Export to CSV
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Records</h3>
          <p class="text-3xl font-bold text-blue-600">{{ statistics.total || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Present</h3>
          <p class="text-3xl font-bold text-green-600">{{ statistics.present || 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">
            {{ calculatePercentage(statistics.present, statistics.total) }}% of total
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Absent</h3>
          <p class="text-3xl font-bold text-red-600">{{ statistics.absent || 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">
            {{ calculatePercentage(statistics.absent, statistics.total) }}% of total
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900">Late</h3>
          <p class="text-3xl font-bold text-yellow-600">{{ statistics.late || 0 }}</p>
          <p class="text-sm text-gray-500 mt-1">
            {{ calculatePercentage(statistics.late, statistics.total) }}% of total
          </p>
        </div>
      </div>

      <!-- Report Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="record in reportData" :key="record.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ record.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(record.date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': record.status === 'present',
                    'bg-red-100 text-red-800': record.status === 'absent',
                    'bg-yellow-100 text-yellow-800': record.status === 'late'
                  }"
                >
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.remarks }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex space-x-2">
          <button
            @click="prevPage"
            :disabled="pagination.currentPage === 1"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'opacity-50 cursor-not-allowed': pagination.currentPage === 1 }"
          >
            Previous
          </button>
          <button
            @click="nextPage"
            :disabled="pagination.currentPage === pagination.lastPage"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'opacity-50 cursor-not-allowed': pagination.currentPage === pagination.lastPage }"
          >
            Next
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
      filters: {
        startDate: new Date().toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0],
        type: '',
        status: '',
        department: '',
      },
      departments: [],
      reportData: [],
      statistics: {
        total: 0,
        present: 0,
        absent: 0,
        late: 0,
      },
      pagination: {
        currentPage: 1,
        lastPage: 1,
        from: 0,
        to: 0,
        total: 0,
      },
    };
  },
  async mounted() {
    await this.fetchDepartments();
    await this.fetchReport();
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
    async fetchReport() {
      try {
        const params = {
          ...this.filters,
          page: this.pagination.currentPage,
        };
        const response = await axios.get('/attendance/report', { params });
        
        if (response.data) {
          this.reportData = response.data.data;
          this.pagination = {
            currentPage: response.data.current_page,
            lastPage: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
          };
          this.statistics = response.data.statistics;
        }
      } catch (error) {
        console.error('Error fetching report:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Failed to fetch attendance report',
        });
      }
    },
    prevPage() {
      if (this.pagination.currentPage > 1) {
        this.pagination.currentPage--;
        this.fetchReport();
      }
    },
    nextPage() {
      if (this.pagination.currentPage < this.pagination.lastPage) {
        this.pagination.currentPage++;
        this.fetchReport();
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    calculatePercentage(value, total) {
      if (!total) return 0;
      return Math.round((value / total) * 100);
    },
    async exportReport() {
      try {
        const params = {
          ...this.filters,
          export: true
        };
        const response = await axios.get('/attendance/report', {
          params,
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `attendance-report-${this.filters.startDate}-to-${this.filters.endDate}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error exporting report:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to export attendance report',
        });
      }
    },
  },
};
</script> 