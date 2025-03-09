<template>
  <div class="flex h-screen flex-col">
    <!-- Navbar -->
    <Navbar />
    <!-- Main Content Section (Sidebar and Content) -->
    <div class="flex flex-1">
      <!-- Sidebar Component -->
      <Sidebar class="h-full bg-gray-900" />
      
      <!-- Content -->
      <div class="flex-1 p-6 bg-gray-100">
        <!-- Form Section -->
        <div class="p-6">
          <div class="max-w-4xl mx-auto p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Common User Fields -->
              <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                <h2 class="text-2xl font-semibold text-gray-800">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input 
                      v-model="formData.username" 
                      type="text" 
                      class="mt-1 block w-full rounded-md shadow-sm border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input 
                      v-model="formData.password" 
                      type="password" 
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input 
                      v-model="formData.full_name" 
                      type="text" 
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input 
                      v-model="formData.email" 
                      type="email" 
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Profile Photo</label>
                    <input 
                      type="file" 
                      @change="handlePhotoUpload"
                      class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border p-2"
                      accept="image/*"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select 
                      v-model="formData.role"
                      @change="handleRoleChange"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    >
                      <option value="">Select Role</option>
                      <option value="admin">Admin</option>
                      <option value="employee">Employee</option>
                      <option value="client">Client</option>
                      <option value="supplier">Supplier</option>
                    </select>
                  </div>
                </div>
              </div>
              <!-- Employee-specific Fields -->
              <div v-if="formData.role === 'employee'" class="bg-white p-6 rounded-lg shadow-md space-y-4">
                <h2 class="text-2xl font-semibold text-gray-800">Employee Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Employee Type</label>
                    <select 
                      v-model="formData.employee.employee_type"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    >
                      <option value="artisan">Artisan</option>
                      <option value="accountant">Accountant</option>
                      <option value="quality_checker">Quality Checker</option>
                      <option value="manager">Manager</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <select 
                      v-model="formData.employee.department"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    >
                      <option value="needling">Needling</option>
                      <option value="wet_felting">Wet Felting</option>
                      <option value="shoes">Shoes</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Designation</label>
                    <input 
                      v-model="formData.designation" 
                      type="text" 
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input 
                      v-model="formData.mobile" 
                      type="number" 
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Join Date</label>
                    <input 
                      v-model="formData.employee.join_date"
                      type="date"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Citizenship Number</label>
                    <input 
                      v-model="formData.employee.citizenship_no"
                      type="text"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">PAN Number</label>
                    <input 
                      v-model="formData.employee.pan_no"
                      type="text"
                      class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Citizenship Photo</label>
                    <input 
                      type="file"
                      @change="handleCitizenshipPhotoUpload"
                      class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border p-2"
                      accept="image/*"
                    />
                  </div>
                  <div v-if="formData.employee.employee_type === 'artisan'">
                    <label class="block text-sm font-medium text-gray-700">Skills</label>
                    <select 
                      v-model="formData.employee.skills"
                      multiple
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                    >
                      <option value="felting">Felting</option>
                      <option value="needling">Needling</option>
                      <option value="weaving">Weaving</option>
                      <option value="embroidery">Embroidery</option>
                    </select>
                  </div>
                </div>
              </div>
             
              <div class="flex justify-end">
                <button 
                  type="submit"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 border p-2"
                >
                  Save User
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '../../Navbar.vue';
import Sidebar from '../../Sidebar.vue';

export default {
  name: 'Create',
  components: {
    Navbar,
    Sidebar,
  },
  data() {
    return {
      formData: {
        username: '',
        password: '',
        full_name: '',
        email: '',
        profile_photo: null,
        role: '',
        employee: {
          employee_type: '',
          department: '',
          designation: '',
          mobile: '',
          join_date: '',
          citizenship_no: '',
          pan_no: '',
          citizenship_photo: null,
          skills: [],
        },
      },
    };
  },
  methods: {
    handleSubmit() {
      // Create a FormData object to send the data including files
      const formData = new FormData();
      formData.append('username', this.formData.username);
      formData.append('password', this.formData.password);
      formData.append('full_name', this.formData.full_name);
      formData.append('email', this.formData.email);
      formData.append('role', this.formData.role);
      if (this.formData.profile_photo) {
        formData.append('profile_photo', this.formData.profile_photo);
      }
      if (this.formData.role === 'employee') {
        formData.append('employee_type', this.formData.employee.employee_type);
        formData.append('department', this.formData.employee.department);
        formData.append('designation', this.formData.employee.designation);
        formData.append('mobile', this.formData.employee.mobile);
        formData.append('join_date', this.formData.employee.join_date);
        formData.append('citizenship_no', this.formData.employee.citizenship_no);
        formData.append('pan_no', this.formData.employee.pan_no);
        if (this.formData.employee.citizenship_photo) {
          formData.append('citizenship_photo', this.formData.employee.citizenship_photo);
        }
        formData.append('skills', JSON.stringify(this.formData.employee.skills));
      }

      // Send the form data to the server
      axios.post('/api/users', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
        .then(response => {
          console.log('User created successfully:', response.data);
          // Redirect to the user list page
          this.$router.push("/users");
        })
        .catch(error => {
          console.error('Error creating user:', error);
          // Handle error (e.g., show error message)
        });
    },
    handlePhotoUpload(event) {
      this.formData.profile_photo = event.target.files[0];
    },
    handleCitizenshipPhotoUpload(event) {
      this.formData.employee.citizenship_photo = event.target.files[0];
    },
    handleRoleChange() {
      // Reset employee fields when role changes
      if (this.formData.role !== 'employee') {
        this.formData.employee = {
          employee_type: '',
          department: '',
          designation: '',
          mobile: '',
          join_date: '',
          citizenship_no: '',
          pan_no: '',
          citizenship_photo: null,
          skills: [],
        };
      }
    },
  },
};
</script>