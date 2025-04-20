<template>
    <div class="container mx-auto p-4">
      <form @submit.prevent="submitForm" class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">{{ isEdit ? 'Edit Department' : 'Add Department' }}</h1>
  
        <!-- Name -->
        <div class="mb-4">
          <label for="name" class="block text-gray-700 font-bold mb-2">Name</label>
          <input
            v-model="form.name"
            type="text"
            id="name"
            placeholder="Department Name"
            required
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
  
        <!-- Buttons Container -->
        <div class="flex justify-end space-x-2">
          <button
            type="button"
            @click="cancel"
            class="border border-gray-500 text-gray-500 px-4 py-2 rounded hover:bg-gray-100 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading || !form.name.trim()"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition disabled:bg-blue-300 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        form: {
          name: '',
        },
        loading: false,
        isEdit: !!this.$route.params.id,
      };
    },
    mounted() {
      if (this.isEdit) {
        this.fetchDepartment();
      }
    },
    methods: {
      async fetchDepartment() {
        try {
          const response = await axios.get(`/departments/${this.$route.params.id}`);
          this.form = response.data;
        } catch (error) {
          console.error('Error fetching department:', error);
        }
      },
      async submitForm() {
        if (!this.form.name.trim()) {
          alert('Department name cannot be empty.');
          return;
        }
  
        this.loading = true;
        const url = this.isEdit ? `/departments/${this.$route.params.id}` : '/departments';
        const method = this.isEdit ? 'patch' : 'post';
  
        try {
          await axios({
            method,
            url,
            data: this.form,
          });
          this.$router.push('/departments');
        } catch (error) {
          console.error('Error saving department:', error);
          if (error.response) {
            alert(error.response.data.message || 'Error saving department.');
          }
        } finally {
          this.loading = false;
        }
      },
      cancel() {
        this.$router.push('/departments');
      },
    },
  };
  </script>