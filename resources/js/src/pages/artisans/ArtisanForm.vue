<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-6">
    <form @submit.prevent="submitForm" class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-2xl">
      <h1 class="text-3xl font-semibold text-gray-900 mb-6 text-center">{{ isEdit ? 'Edit Artisan' : 'Add Artisan' }}</h1>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label for="name" class="block text-gray-700 font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" id="name" placeholder="Enter name" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" id="email" placeholder="Enter email" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label for="phone_number" class="block text-gray-700 font-medium mb-1">Phone Number</label>
          <input v-model="form.phone_number" type="text" id="phone_number" placeholder="Enter phone number" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label for="basic_salary" class="block text-gray-700 font-medium mb-1">Basic Salary</label>
          <input v-model="form.basic_salary" type="number" id="basic_salary" placeholder="Enter salary" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label for="pan_number" class="block text-gray-700 font-medium mb-1">PAN Number</label>
          <input v-model="form.pan_number" type="text" id="pan_number" placeholder="Enter PAN number" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label for="department_id" class="block text-gray-700 font-medium mb-1">Department</label>
          <select v-model="form.department_id" id="department_id" required
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            <option value="" disabled>Select Department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
          </select>
        </div>

        <div>
          <label for="profile_photo" class="block text-gray-700 font-medium mb-1">Profile Photo</label>
          <input type="file" id="profile_photo" @change="onProfilePhotoChange"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
          <div v-if="existingProfilePhoto" class="mt-2">
            <p class="text-sm text-gray-500">Current Photo:</p>
            <img :src="existingProfilePhoto" class="h-20 w-20 rounded-full object-cover border">
          </div>
        </div>

        <div>
          <label for="citizenship_photo" class="block text-gray-700 font-medium mb-1">Citizenship Photo</label>
          <input type="file" id="citizenship_photo" @change="onCitizenshipPhotoChange"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
          <div v-if="existingCitizenshipPhoto" class="mt-2">
            <p class="text-sm text-gray-500">Current Citizenship:</p>
            <img :src="existingCitizenshipPhoto" class="h-20 w-20 rounded object-cover border">
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" @click="cancel"
          class="border border-gray-500 text-gray-500 px-4 py-2 rounded-lg hover:bg-gray-100 transition">Cancel</button>
        <button type="submit" :disabled="loading"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
          {{ loading ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from "axios";
import Swal from 'sweetalert2';

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
      },
      departments: [],
      loading: false,
      isEdit: false,
      existingProfilePhoto: null,
      existingCitizenshipPhoto: null
    };
  },
  created() {
    this.isEdit = !!this.$route.params.id;
  },
  async mounted() {
    await this.fetchDepartments();
    if (this.isEdit) {
      await this.fetchArtisan();
    }
  },
  methods: {
    async fetchDepartments() {
      try {
        const response = await axios.get("/departments");
        this.departments = response.data || [];
      } catch (error) {
        console.error("Error fetching departments:", error);
        alert("Failed to load departments");
      }
    },
    async fetchArtisan() {
      try {
        const response = await axios.get(`/artisans/${this.$route.params.id}`);
        const artisan = response.data?.data || {};

        // Map the response data to form fields
        this.form = {
          name: artisan.name || "",
          email: artisan.email || "",
          phone_number: artisan.phone_number || "",
          basic_salary: artisan.basic_salary || "",
          pan_number: artisan.pan_number || "",
          department_id: artisan.department?.id || "",
          profile_photo: null,
          citizenship_photo: null
        };

        // Store existing photo URLs for display
        this.existingProfilePhoto = artisan.profile_photo_url;
        this.existingCitizenshipPhoto = artisan.citizenship_photo_url;

      } catch (error) {
        console.error("Error fetching artisan:", error);
        alert("Failed to load artisan data");
        this.$router.push("/artisans");
      }
    },
    onProfilePhotoChange(event) {
      this.form.profile_photo = event.target.files[0];
      this.existingProfilePhoto = null; // Clear existing photo preview
    },
    onCitizenshipPhotoChange(event) {
      this.form.citizenship_photo = event.target.files[0];
      this.existingCitizenshipPhoto = null; // Clear existing photo preview
    },
    async submitForm() {
      this.loading = true;

      const formData = new FormData();
      
      // Add all form fields except files
      Object.keys(this.form).forEach(key => {
        if (key !== 'profile_photo' && key !== 'citizenship_photo') {
          formData.append(key, this.form[key]);
        }
      });

      // Add files if they exist
      if (this.form.profile_photo instanceof File) {
        formData.append('profile_photo', this.form.profile_photo);
      }
      if (this.form.citizenship_photo instanceof File) {
        formData.append('citizenship_photo', this.form.citizenship_photo);
      }

      try {
        let response;
        
        if (this.isEdit) {
          // For edit, we need to use PATCH method
          formData.append('_method', 'PATCH'); // Laravel needs this for FormData PATCH requests
          response = await axios.post(`/artisans/${this.$route.params.id}`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        } else {
          // For create, use normal POST
          response = await axios.post('/artisans', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        }

        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: this.isEdit ? 'Artisan updated successfully' : 'Artisan created successfully',
          timer: 2000,
          showConfirmButton: false
        });

        this.$router.push("/artisans");
      } catch (error) {
        console.error("Error saving artisan:", error);
        
        let errorMessage = "Failed to save artisan";
        if (error.response) {
          if (error.response.data.errors) {
            errorMessage = Object.values(error.response.data.errors).join('\n');
          } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
        }

        await Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: errorMessage,
        });
      } finally {
        this.loading = false;
      }
    },
    cancel() {
      this.$router.push("/artisans");
    },
  },
};
</script>