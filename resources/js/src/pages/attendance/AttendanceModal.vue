<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <!-- Header -->
      <div class="p-4 border-b">
        <h3 class="text-lg font-medium text-gray-900">
          {{ record ? 'Edit Attendance Record' : 'Add Attendance Record' }}
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-4">
        <!-- Type Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select
            v-model="form.attendanceable_type"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
            required
          >
            <option value="">Select Type</option>
            <option value="App\\Models\\User">User</option>
            <option value="App\\Models\\Artisan">Artisan</option>
          </select>
        </div>

        <!-- User/Artisan Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <select
            v-model="form.attendanceable_id"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
            required
          >
            <option value="">Select {{ form.attendanceable_type === 'App\\Models\\User' ? 'User' : 'Artisan' }}</option>
            <option v-for="item in items" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
          </select>
        </div>

        <!-- Date -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
          <input
            type="date"
            v-model="form.date"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
            required
          />
        </div>

        <!-- Check In -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
          <input
            type="time"
            v-model="form.check_in"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
        </div>

        <!-- Check Out -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
          <input
            type="time"
            v-model="form.check_out"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
        </div>

        <!-- Status -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="form.status"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
            required
          >
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
          </select>
        </div>

        <!-- Remarks -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
          <textarea
            v-model="form.remarks"
            class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
            rows="3"
          ></textarea>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-3 mt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from "vue"
import axios from "axios"

export default {
  props: {
    record: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'save'],
  setup(props, { emit }) {
    const form = ref({
      attendanceable_type: '',
      attendanceable_id: '',
      date: new Date().toISOString().split('T')[0],
      check_in: '',
      check_out: '',
      status: 'present',
      remarks: ''
    })
    const items = ref([])
    const loading = ref(false)

    // Load form data if editing
    if (props.record) {
      form.value = {
        ...props.record,
        date: props.record.date.split('T')[0]
      }
    }

    // Fetch users or artisans based on selected type
    watch(() => form.value.attendanceable_type, async (newType) => {
      if (newType) {
        const endpoint = newType === 'App\\Models\\User' ? '/users' : '/artisans'
        try {
          const response = await axios.get(endpoint)
          items.value = response.data.data
        } catch (error) {
          console.error('Error fetching items:', error)
        }
      } else {
        items.value = []
      }
    })

    const submitForm = async () => {
      loading.value = true
      try {
        await emit('save', form.value)
      } catch (error) {
        console.error('Error saving attendance:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      items,
      loading,
      submitForm
    }
  }
}
</script> 