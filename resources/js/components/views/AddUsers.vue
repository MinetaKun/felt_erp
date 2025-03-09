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
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input 
              v-model="formData.password" 
              type="password" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input 
              v-model="formData.full_name" 
              type="text" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input 
              v-model="formData.email" 
              type="email" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Mobile</label>
            <input 
              v-model="formData.mobile" 
              type="tel" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Age</label>
            <input 
              v-model="formData.age" 
              type="number" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea 
              v-model="formData.address" 
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              rows="3"
              required
            ></textarea>
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
              required
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
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2 "
              required
            >
              <option value="artisan">Artisan</option>
              <option value="accountant">Accountant</option>
              <option value="quality_checker">Quality Checker</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Department</label>
            <input 
              v-model="formData.employee.department"
              type="text"
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Join Date</label>
            <input 
              v-model="formData.employee.join_date"
              type="date"
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Citizenship Number</label>
            <input 
              v-model="formData.employee.citizenship_no"
              type="text"
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">PAN Number</label>
            <input 
              v-model="formData.employee.pan_no"
              type="text"
              class="mt-1 block w-full rounded-md border-black-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Citizenship Photo</label>
            <input 
              type="file"
              @change="handleCitizenshipPhotoUpload"
              class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border p-2"
              accept="image/*"
              required
            />
          </div>

          <div v-if="formData.employee.employee_type === 'artisan'">
            <label class="block text-sm font-medium text-gray-700">Skills</label>
            <select 
              v-model="formData.employee.skills"
              multiple
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
            >
              <option value="Needlework">Needlework</option>
              <option value="Coloring">Coloring</option>
              <option value="Weaving">Weaving</option>
              <option value="Embroidery">Embroidery</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Client-specific Fields -->
      <div v-if="formData.role === 'client'" class="bg-white p-6 rounded-lg shadow-md space-y-4">
        <h2 class="text-2xl font-semibold text-gray-800">Client Details</h2>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Company Name</label>
          <input 
            v-model="formData.client.company_name"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
            required
          />
        </div>
      </div>

      <!-- Supplier-specific Fields -->
      <div v-if="formData.role === 'supplier'" class="bg-white p-6 rounded-lg shadow-md space-y-4">
        <h2 class="text-2xl font-semibold text-gray-800">Supplier Details</h2>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Company Name</label>
          <input 
            v-model="formData.supplier.company_name"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
            required
          />
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
import axios from 'axios';
import Navbar from '../Navbar.vue';
import Sidebar from '../Sidebar.vue';

export default {
  name: 'AddUser',
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
        photo: null,
        age: '',
        email: '',
        mobile: '',
        address: '',
        role: '',
        is_active: true,
        permissions: [],
        employee: {
          employee_type: '',
          department: '',
          join_date: '',
          skills: [],
          citizenship_no: '',
          citizenship_photo: null,
          pan_no: ''
        },
        client: {
          company_name: ''
        },
        supplier: {
          company_name: ''
        }
      }
    }
  },
  methods: {
    handlePhotoUpload(event) {
      this.formData.photo = event.target.files[0]
    },
    handleCitizenshipPhotoUpload(event) {
      this.formData.employee.citizenship_photo = event.target.files[0]
    },
    handleRoleChange() {
      // Reset role-specific data when role changes
      this.formData.employee = {
        employee_type: '',
        department: '',
        join_date: '',
        skills: [],
        citizenship_no: '',
        citizenship_photo: null,
        pan_no: ''
      }
      this.formData.client = {
        company_name: ''
      }
      this.formData.supplier = {
        company_name: ''
      }
    },
    async handleSubmit() {
      try {
        // Create FormData object for file uploads
        const formData = new FormData()
        
        // Append common user data
        Object.keys(this.formData).forEach(key => {
          if (key !== 'employee' && key !== 'client' && key !== 'supplier') {
            formData.append(key, this.formData[key])
          }
        })

        // Append role-specific data
        if (this.formData.role === 'employee') {
          Object.keys(this.formData.employee).forEach(key => {
            if (key === 'skills') {
              formData.append(key, JSON.stringify(this.formData.employee[key]))
            } else {
              formData.append(key, this.formData.employee[key])
            }
          })
        } else if (this.formData.role === 'client') {
          formData.append('company_name', this.formData.client.company_name)
        } else if (this.formData.role === 'supplier') {
          formData.append('company_name', this.formData.supplier.company_name)
        }

        // Send API request
        // const response = await axios.post('/api/users', formData)
        console.log('Form submitted:', formData)
        
        // Reset form after successful submission
        this.resetForm()
      } catch (error) {
        console.error('Error submitting form:', error)
      }
    },
    resetForm() {
      this.formData = {
        username: '',
        password: '',
        full_name: '',
        photo: null,
        age: '',
        email: '',
        mobile: '',
        address: '',
        role: '',
        is_active: true,
        permissions: [],
        employee: {
          employee_type: '',
          department: '',
          join_date: '',
          skills: [],
          citizenship_no: '',
          citizenship_photo: null,
          pan_no: ''
        },
        client: {
          company_name: ''
        },
        supplier: {
          company_name: ''
        }
      }
    }
  }
};

</script>

