<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
      <!-- Form Title -->
      <h1 class="text-3xl font-bold text-center text-gray-900">
        {{ isEdit ? 'Edit Attendance' : 'Add Attendance' }}
      </h1>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="mt-8 space-y-6">
        <!-- Date Input -->
        <div>
          <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
          <input
            v-model="form.date"
            id="date"
            type="date"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Status Dropdown -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
          <select
            v-model="form.status"
            id="status"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
          </select>
        </div>

        <!-- Remarks Input -->
        <div>
          <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
          <textarea
            v-model="form.remarks"
            id="remarks"
            rows="3"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Optional remarks..."
          ></textarea>
        </div>

        <!-- Submit Button -->
        <div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300"
            :class="{ 'opacity-50 cursor-not-allowed': loading }"
          >
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      form: {
        date: '',
        status: 'present',
        remarks: '',
      },
      loading: false,
      isEdit: !!this.$route.params.id,
    };
  },
  mounted() {
    if (this.isEdit) {
      this.fetchAttendance();
    }
  },
  methods: {
    async fetchAttendance() {
      try {
        const response = await axios.get(`/attendance/${this.$route.params.id}`);
        this.form = response.data;
      } catch (error) {
        console.error('Error fetching attendance:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to fetch attendance data',
        });
      }
    },
    async submitForm() {
      this.loading = true;
      const url = this.isEdit
        ? `/attendance/${this.$route.params.id}`
        : '/attendance';
      const method = this.isEdit ? 'patch' : 'post';

      try {
        await axios[method](url, this.form);
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: this.isEdit ? 'Attendance updated successfully' : 'Attendance recorded successfully',
          timer: 2000,
          showConfirmButton: false,
        });
        this.$router.push('/attendance');
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
  },
};
</script> 