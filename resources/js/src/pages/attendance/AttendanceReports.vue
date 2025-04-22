<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Attendance Reports</h1>
        <div class="flex items-center space-x-4">
          <button
            @click="exportReport"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300"
          >
            Export Report
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <div class="flex space-x-2">
              <input
                v-model="filters.startDate"
                type="date"
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <input
                v-model="filters.endDate"
                type="date"
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="filters.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All</option>
              <option value="user">Users</option>
              <option value="artisan">Artisans</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All</option>
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select
              v-model="filters.department"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="fetchReport"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300"
          >
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Present</h3>
          <p class="text-3xl font-bold text-green-600">{{ statistics.present }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Absent</h3>
          <p class="text-3xl font-bold text-red-600">{{ statistics.absent }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900">Total Late</h3>
          <p class="text-3xl font-bold text-yellow-600">{{ statistics.late }}</p>
        </div>
      </div>

      <!-- Report Table -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
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
        const response = await axios.get('/attendance/reports', { params });
        this.reportData = response.data.data;
        this.pagination = {
          currentPage: response.data.current_page,
          lastPage: response.data.last_page,
          from: response.data.from,
          to: response.data.to,
          total: response.data.total,
        };
        this.statistics = response.data.statistics;
      } catch (error) {
        console.error('Error fetching report:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch attendance report',
        });
      }
    },
    async exportReport() {
      try {
        const params = {
          ...this.filters,
          export: true,
        };
        const response = await axios.get('/attendance/reports/export', {
          params,
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `attendance-report-${new Date().toISOString().split('T')[0]}.xlsx`);
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
  },
};
</script> 