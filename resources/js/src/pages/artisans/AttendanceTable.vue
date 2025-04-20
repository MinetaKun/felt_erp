<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Add Attendance Button -->
      <div class="flex justify-end mb-6">
        <button
          @click="$router.push(`/artisans/${artisanId}/attendance/create`)"
          class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-all duration-300 shadow-md hover:shadow-lg"
        >
          Add Attendance
        </button>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <span class="text-gray-500">Loading...</span>
      </div>

      <!-- Attendance Table -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-4 text-left text-sm font-semibold text-gray-700 uppercase">Date</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-700 uppercase">Check-In</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-700 uppercase">Check-Out</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-700 uppercase">Status</th>
              <th class="p-4 text-left text-sm font-semibold text-gray-700 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="record in attendance"
              :key="record.id"
              class="hover:bg-gray-50 transition-all duration-200"
            >
              <td class="p-4 text-sm text-gray-700">{{ record.date || 'N/A' }}</td>
              <td class="p-4 text-sm text-gray-700">{{ record.check_in || 'N/A' }}</td>
              <td class="p-4 text-sm text-gray-700">{{ record.check_out || 'N/A' }}</td>
              <td class="p-4 text-sm text-gray-700">
                <span
                  :class="{
                    'bg-green-100 text-green-800': record.status === 'Present',
                    'bg-red-100 text-red-800': record.status === 'Absent',
                    'bg-yellow-100 text-yellow-800': record.status === 'Late',
                  }"
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                >
                  {{ record.status || 'N/A' }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex space-x-3">
                  <button
                    @click="$router.push(`/attendance/${record.id}/edit`)"
                    class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition-all duration-300 shadow-sm hover:shadow-md"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteAttendance(record.id)"
                    class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition-all duration-300 shadow-sm hover:shadow-md"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- No Attendance Records Message -->
      <div v-if="!loading && attendance.length === 0" class="flex justify-center items-center h-64">
        <span class="text-gray-500">No attendance records found.</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: {
    artisanId: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      attendance: [],
      loading: false,
    };
  },
  mounted() {
    this.fetchAttendance();
  },
  methods: {
    async fetchAttendance() {
      this.loading = true;
      try {
        const response = await axios.get(`/artisans/${this.artisanId}/attendance`);
        this.attendance = response.data;
      } catch (error) {
        console.error('Error fetching attendance:', error);
      } finally {
        this.loading = false;
      }
    },
    async deleteAttendance(id) {
      if (confirm('Are you sure you want to delete this attendance record?')) {
        try {
          await axios.delete(`/attendance/${id}`);
          this.fetchAttendance(); // Refresh the list
        } catch (error) {
          console.error('Error deleting attendance:', error);
        }
      }
    },
  },
};
</script>

<style scoped>
/* Add custom styles or animations here if needed */
</style>