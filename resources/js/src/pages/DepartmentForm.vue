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
  import axios from "axios"
  import useModalToast from "../composables/useModalToast"

  export default {
    setup() {
      const { showToast } = useModalToast()
      return { showToast }
    },
    data() {
      return {
        form: {
          name: "",
        },
        errors: {},
        loading: false,
        isEdit: !!this.$route.params.id,
      }
    },
    mounted() {
      if (this.isEdit) {
        this.fetchDepartment()
      }
    },
    methods: {
      async fetchDepartment() {
        try {
          this.loading = true
          const response = await axios.get(`/departments/${this.$route.params.id}`)
          this.form = response.data
        } catch (error) {
          console.error("Error fetching department:", error)
          this.showToast("Failed to load department details.", "error")
          if (error.response && error.response.status === 404) {
            this.$router.push("/departments")
          }
        } finally {
          this.loading = false
        }
      },

      async submitForm() {
        if (!this.form.name.trim()) {
          this.errors = { name: ["Department name cannot be empty."] }
          return
        }

        this.loading = true
        this.errors = {}

        const url = this.isEdit ? `/departments/${this.$route.params.id}` : "/departments"
        const method = this.isEdit ? "patch" : "post"

        try {
          await axios({
            method,
            url,
            data: this.form,
          })

          this.showToast(`Department successfully ${this.isEdit ? "updated" : "created"}.`, "success")
          this.$router.push("/departments")
        } catch (error) {
          console.error(`Error ${this.isEdit ? "updating" : "creating"} department:`, error)

          if (error.response && error.response.data && error.response.data.errors) {
            this.errors = error.response.data.errors
          } else if (error.response && error.response.data && error.response.data.message) {
            this.showToast(error.response.data.message, "error")
          } else {
            this.showToast(`Failed to ${this.isEdit ? "update" : "create"} department.`, "error")
          }
        } finally {
          this.loading = false
        }
      },

      cancel() {
        this.$router.push("/departments")
      },
    },
    directives: {
      focus: {
        mounted(el) {
          el.focus()
        },
      },
    },
  }
  </script>