<template>
  <div class="container mx-auto px-4 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2">{{ isEdit ? 'Edit Artisan' : 'Add New Artisan' }}</h1>
      <p class="text-gray-600">{{ isEdit ? 'Update artisan information' : 'Create a new artisan record' }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <form @submit.prevent="submitForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Basic Information -->
          <div>
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Basic Information</h2>
            
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
              <input
                id="phone_number"
                v-model="form.phone_number"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="pan_number" class="block text-sm font-medium text-gray-700 mb-1">PAN Number <span class="text-red-500">*</span></label>
              <input
                id="pan_number"
                v-model="form.pan_number"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
              <select
                id="department_id"
                v-model="form.department_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
            
            <div class="mb-4">
              <label for="basic_salary" class="block text-sm font-medium text-gray-700 mb-1">Basic Salary <span class="text-red-500">*</span></label>
              <input
                id="basic_salary"
                v-model="form.basic_salary"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            
            <div class="mb-4">
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          
          <!-- Documents and Photos -->
          <div>
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Documents & Photos</h2>
            
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
              <div class="mt-1 flex items-center">
                <div v-if="existingProfilePhoto || form.profile_photo" class="relative">
                  <img
                    :src="existingProfilePhoto || (form.profile_photo ? URL.createObjectURL(form.profile_photo) : '')"
                    class="h-32 w-32 object-cover rounded-md"
                    alt="Profile Preview"
                  />
                  <button
                    type="button"
                    @click="form.profile_photo = null; existingProfilePhoto = null"
                    class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 transform translate-x-1/2 -translate-y-1/2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div v-else class="flex justify-center items-center h-32 w-32 bg-gray-100 rounded-md">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <input
                  type="file"
                  @change="onProfilePhotoChange"
                  accept="image/*"
                  class="ml-4"
                />
              </div>
            </div>
            
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Citizenship Photo</label>
              <div class="mt-1 flex items-center">
                <div v-if="existingCitizenshipPhoto || form.citizenship_photo" class="relative">
                  <img
                    :src="existingCitizenshipPhoto || (form.citizenship_photo ? URL.createObjectURL(form.citizenship_photo) : '')"
                    class="h-32 w-32 object-cover rounded-md"
                    alt="Citizenship Preview"
                  />
                  <button
                    type="button"
                    @click="form.citizenship_photo = null; existingCitizenshipPhoto = null"
                    class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 transform translate-x-1/2 -translate-y-1/2"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div v-else class="flex justify-center items-center h-32 w-32 bg-gray-100 rounded-md">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <input
                  type="file"
                  @change="onCitizenshipPhotoChange"
                  accept="image/*"
                  class="ml-4"
                />
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            @click="cancel"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : (isEdit ? 'Update Artisan' : 'Create Artisan') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios"
import Swal from "sweetalert2"

export default {
  data() {
    return {
      form: {
        name: "",
        email: "",
        phone_number: "",
        basic_salary: "",
        pan_number: "",
        department_id: "",
        profile_photo: null,
        citizenship_photo: null,
        status: "inactive",
      },
      departments: [],
      loading: false,
      isEdit: false,
      existingProfilePhoto: null,
      existingCitizenshipPhoto: null,
    }
  },
  created() {
    this.isEdit = !!this.$route.params.id
  },
  async mounted() {
    await this.fetchDepartments()
    if (this.isEdit) {
      await this.fetchArtisan()
    }
  },
  methods: {
    async fetchDepartments() {
      try {
        const response = await axios.get("/departments")
        this.departments = response.data || []
      } catch (error) {
        console.error("Error fetching departments:", error)
        alert("Failed to load departments")
      }
    },
    async fetchArtisan() {
      try {
        const response = await axios.get(`/artisans/${this.$route.params.id}`)
        const artisan = response.data?.data || {}

        // Map the response data to form fields
        this.form = {
          name: artisan.name || "",
          email: artisan.email || "",
          phone_number: artisan.phone_number || "",
          basic_salary: artisan.basic_salary || "",
          pan_number: artisan.pan_number || "",
          department_id: artisan.department?.id || "",
          profile_photo: null,
          citizenship_photo: null,
          status: artisan.status || "inactive",
        }

        // Store existing photo URLs for display
        this.existingProfilePhoto = artisan.profile_photo_url
        this.existingCitizenshipPhoto = artisan.citizenship_photo_url
      } catch (error) {
        console.error("Error fetching artisan:", error)
        alert("Failed to load artisan data")
        this.$router.push("/artisans")
      }
    },
    onProfilePhotoChange(event) {
      this.form.profile_photo = event.target.files[0]
      this.existingProfilePhoto = null // Clear existing photo preview
    },
    onCitizenshipPhotoChange(event) {
      this.form.citizenship_photo = event.target.files[0]
      this.existingCitizenshipPhoto = null // Clear existing photo preview
    },
    async submitForm() {
      this.loading = true

      const formData = new FormData()

      // Add all form fields except files
      Object.keys(this.form).forEach((key) => {
        if (key !== "profile_photo" && key !== "citizenship_photo") {
          formData.append(key, this.form[key])
        }
      })

      // Add files if they exist
      if (this.form.profile_photo instanceof File) {
        formData.append("profile_photo", this.form.profile_photo)
      }
      if (this.form.citizenship_photo instanceof File) {
        formData.append("citizenship_photo", this.form.citizenship_photo)
      }

      try {
        let response

        if (this.isEdit) {
          // For edit, we need to use PATCH method
          formData.append("_method", "PATCH") // Laravel needs this for FormData PATCH requests
          response = await axios.post(`/artisans/${this.$route.params.id}`, formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
        } else {
          // For create, use normal POST
          response = await axios.post("/artisans", formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
        }

        await Swal.fire({
          icon: "success",
          title: "Success!",
          text: this.isEdit ? "Artisan updated successfully" : "Artisan created successfully",
          timer: 2000,
          showConfirmButton: false,
        })

        this.$router.push("/artisans")
      } catch (error) {
        console.error("Error saving artisan:", error)

        let errorMessage = "Failed to save artisan"
        if (error.response) {
          if (error.response.data.errors) {
            errorMessage = Object.values(error.response.data.errors).join("\n")
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message
          }
        }

        await Swal.fire({
          icon: "error",
          title: "Error!",
          text: errorMessage,
        })
      } finally {
        this.loading = false
      }
    },
    cancel() {
      this.$router.push("/artisans")
    },
  },
}
</script>
