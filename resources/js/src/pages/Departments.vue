<template>
  <div class="container mx-auto p-6">
   <!-- Header Section -->
<div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
  <div class="text-white">
    <h3 class="text-xl font-bold">Departments</h3>
    <p class="text-indigo-100 text-sm mt-1">Manage your organization's departments</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <div class="relative">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search departments..."
        class="w-full sm:w-64 px-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white shadow-sm transition duration-200"
      />
      <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
        <span class="sr-only">Clear</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
          <path d="M18 6 6 18"></path>
          <path d="m6 6 12 12"></path>
        </svg>
      </button>
    </div>
    <button
      @click="$router.push('/departments/create')"
      class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition duration-200 shadow-sm"
    >
      <i class="fas fa-plus mr-2"></i> Add Department
    </button>
  </div>
</div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow-md p-8">
      <div class="flex flex-col items-center justify-center h-64">
        <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600">Loading departments...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white rounded-lg shadow-md p-8">
      <div class="flex flex-col items-center justify-center h-64 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center text-red-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <path d="M12 9v4"></path>
            <path d="M12 17h.01"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">Unable to load departments</h3>
        <p class="mt-1 text-gray-500">{{ error }}</p>
        <button @click="fetchDepartments" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
          Try Again
        </button>
      </div>
    </div>

    <!-- Content: Departments Table or Empty State -->
    <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
      <!-- Empty State -->
      <div v-if="filteredDepartments.length === 0" class="flex flex-col items-center justify-center h-64 p-6 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building">
            <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
            <path d="M9 22v-4h6v4"></path>
            <path d="M8 6h.01"></path>
            <path d="M16 6h.01"></path>
            <path d="M12 6h.01"></path>
            <path d="M12 10h.01"></path>
            <path d="M12 14h.01"></path>
            <path d="M16 10h.01"></path>
            <path d="M16 14h.01"></path>
            <path d="M8 10h.01"></path>
            <path d="M8 14h.01"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900">
          {{ departments.length === 0 ? 'No departments found' : 'No matching departments' }}
        </h3>
        <p class="mt-1 text-gray-500">
          {{ departments.length === 0 
            ? 'Get started by creating your first department' 
            : 'Try adjusting your search query' }}
        </p>
        <div v-if="departments.length === 0" class="mt-4">
          <button 
            @click="$router.push('/departments/create')" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
          >
            Create Department
          </button>
        </div>
        <div v-else-if="searchQuery" class="mt-4">
          <button @click="searchQuery = ''" class="text-blue-600 hover:text-blue-800">
            Clear search
          </button>
        </div>
      </div>

      <!-- Departments Table -->
      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th @click="sort('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center">
                    ID
                    <span v-if="sortField === 'id'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center">
                    Name
                    <span v-if="sortField === 'name'" class="ml-1">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="department in filteredDepartments"
                :key="department.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ department.id || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ department.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex space-x-2">
                    <router-link
                      :to="`/departments/${department.id}/edit`"
                      class="inline-flex items-center px-2 py-1 bg-emerald-500 text-white text-sm rounded hover:bg-emerald-600 mr-1"
                    >
                      <i class="fas fa-edit"></i>
                    </router-link>
                    <button
                      @click="deleteDepartmentPrompt(department.id)"
                      :disabled="deleting === department.id"
                      class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                    >
                      <i v-if="deleting !== department.id" class="fas fa-trash"></i>
                      <i v-else class="fas fa-spinner fa-spin"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ filteredDepartments.length }}</span> of <span class="font-medium">{{ departments.length }}</span> departments
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Deletion</h3>
        <p class="text-gray-500 mb-6">
          Are you sure you want to delete this department? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="cancelDelete"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
            :disabled="deleting"
          >
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
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
      departments: [],
      loading: false,
      deleting: null,
      error: null,
      searchQuery: "",
      sortField: "id",
      sortDirection: "asc",
      showDeleteModal: false,
      departmentToDelete: null,
    }
  },
  computed: {
    filteredDepartments() {
      if (!this.searchQuery) {
        return this.sortedDepartments
      }

      const query = this.searchQuery.toLowerCase()
      return this.sortedDepartments.filter(
        (dept) =>
          (dept.name && dept.name.toLowerCase().includes(query)) || (dept.id && dept.id.toString().includes(query)),
      )
    },
    sortedDepartments() {
      return [...this.departments].sort((a, b) => {
        let aValue = a[this.sortField]
        let bValue = b[this.sortField]

        // Handle undefined or null values
        if (aValue === undefined || aValue === null) aValue = ""
        if (bValue === undefined || bValue === null) bValue = ""

        // String comparison
        if (typeof aValue === "string") {
          aValue = aValue.toLowerCase()
          bValue = bValue.toString().toLowerCase()
        }

        if (this.sortDirection === "asc") {
          return aValue > bValue ? 1 : -1
        } else {
          return aValue < bValue ? 1 : -1
        }
      })
    },
  },
  mounted() {
    this.fetchDepartments()
  },
  methods: {
    async fetchDepartments() {
      if (this.loading) return
      this.loading = true
      this.error = null

      try {
        const response = await axios.get("/departments")
        this.departments = Array.isArray(response.data) ? response.data : []
      } catch (error) {
        console.error("Error fetching departments:", error)
        this.error = "Failed to load departments. Please try again later."
        this.departments = []
      } finally {
        this.loading = false
      }
    },

    editDepartment(department) {
      this.$router.push(`/departments/${department.id}/edit`)
    },

    deleteDepartmentPrompt(id) {
      this.departmentToDelete = id
      this.showDeleteModal = true
    },

    cancelDelete() {
      this.showDeleteModal = false
      this.departmentToDelete = null
    },

    async confirmDelete() {
      if (!this.departmentToDelete) return
      await this.deleteDepartment(this.departmentToDelete)
      this.showDeleteModal = false
      this.departmentToDelete = null
    },

    async deleteDepartment(id) {
      if (this.deleting) return
      if (!id) {
        console.error("Cannot delete department: ID is undefined")
        this.showToast("Error: Department ID is missing.", "error")
        return
      }

      this.deleting = id
      try {
        await axios.delete(`/departments/${id}`)
        this.departments = this.departments.filter((dept) => dept.id !== id)
        this.showToast("Department successfully deleted", "success")
      } catch (error) {
        console.error("Error deleting department:", error)

        if (error.response && error.response.status === 400) {
          this.showToast("Cannot delete department with assigned artisans.", "error")
        } else {
          this.showToast("Failed to delete department. Please try again.", "error")
        }

        await this.fetchDepartments()
      } finally {
        this.deleting = null
      }
    },

    sort(field) {
      if (this.sortField === field) {
        // Toggle direction if already sorting by this field
        this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc"
      } else {
        // Default to ascending for new sort field
        this.sortField = field
        this.sortDirection = "asc"
      }
    },
  },
}
</script>