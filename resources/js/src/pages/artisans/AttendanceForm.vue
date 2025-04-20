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

        <!-- Check-In Input -->
        <div>
          <label for="check_in" class="block text-sm font-medium text-gray-700">Check-In</label>
          <input
            v-model="form.check_in"
            id="check_in"
            type="time"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <!-- Check-Out Input -->
        <div>
          <label for="check_out" class="block text-sm font-medium text-gray-700">Check-Out</label>
          <input
            v-model="form.check_out"
            id="check_out"
            type="time"
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
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
            <option value="Late">Late</option>
          </select>
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

export default {
  data() {
    return {
      form: {
        date: '',
        check_in: '',
        check_out: '',
        status: 'Present',
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
      }
    },
    async submitForm() {
      this.loading = true;
      const url = this.isEdit
        ? `/attendance/${this.$route.params.id}`
        : `/artisans/${this.$route.params.artisanId}/attendance`;
      const method = this.isEdit ? 'patch' : 'post';

      try {
        await axios[method](url, this.form);
        this.$router.push(`/artisans/${this.$route.params.artisanId || this.$route.params.id}`);
      } catch (error) {
        console.error('Error saving attendance:', error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Add custom styles or animations here if needed */
</style>