<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Artisans</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your artisans efficiently</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <router-link 
            to="/artisans/productivity"
            class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white text-sm font-medium rounded-lg hover:bg-indigo-600 transition duration-200 shadow-sm"
          >
            <i class="fas fa-chart-line mr-2"></i> Productivity
          </router-link>
          <label class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-50 transition duration-200 shadow-sm cursor-pointer">
            <i class="fas fa-upload mr-2"></i> Import
            <input type="file" class="sr-only" @change="importArtisans" accept=".csv" />
          </label>
          <button
            @click="exportArtisans"
            class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm"
          >
            <i class="fas fa-download mr-2"></i> Export
          </button>
          <router-link 
            to="/artisans/create"
            class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
          >
            <i class="fas fa-plus mr-2"></i> Add Artisan
          </router-link>
        </div>
      </div>

      <div class="p-5">
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <input 
              type="text" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              placeholder="Search by name, email, or phone..." 
              v-model="searchQuery"
              @input="debouncedFetchArtisans"
            >
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="selectedDepartment" 
              @change="fetchArtisans"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="sortBy" 
              @change="fetchArtisans"
            >
              <option value="created_at:desc">Newest First</option>
              <option value="created_at:asc">Oldest First</option>
              <option value="name:asc">Name (A-Z)</option>
              <option value="name:desc">Name (Z-A)</option>
              <option value="basic_salary:desc">Salary (High to Low)</option>
              <option value="basic_salary:asc">Salary (Low to High)</option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <button 
              class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              @click="resetFilters"
            >
              <i class="fas fa-sync-alt mr-1"></i> Reset
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
            <tr class="bg-gray-100">
              <th class="p-3 text-left border-b-2 border-gray-200">Photo</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Name</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Department</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Phone</th>
              <th class="p-3 text-left border-b-2 border-gray-200">PAN Number</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Skills</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Status</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Join Date</th>
              <th class="p-3 text-left border-b-2 border-gray-200">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="artisan in artisans.data" :key="artisan.id" class="hover:bg-gray-50">
              <td class="p-3 border-t">
                <div class="flex-shrink-0 h-10 w-10">
                  <img v-if="artisan.profile_photo" 
                      :src="'/storage/' + artisan.profile_photo" 
                      class="h-10 w-10 rounded-full object-cover"
                      :alt="artisan.name">
                  <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-user text-gray-400"></i>
                  </div>
                </div>
              </td>
              <td class="p-3 border-t">{{ artisan.name || 'N/A' }}</td>
              <td class="p-3 border-t">
                <span :class="[
                  'inline-block px-2 py-1 text-xs font-semibold rounded',
                  getDepartmentColor(artisan.department ? artisan.department.name : '')
                ]">
                  {{ artisan.department ? artisan.department.name : 'N/A' }}
                </span>
              </td>
              <td class="p-3 border-t">{{ artisan.phone_number || 'N/A' }}</td>
              <td class="p-3 border-t">{{ artisan.pan_number || 'N/A' }}</td>
              <td class="p-3 border-t">
                <div class="flex flex-wrap gap-1">
                  <span v-for="skill in artisan.skills" :key="skill" 
                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                    {{ skill }}
                  </span>
                  <span v-if="!artisan.skills || artisan.skills.length === 0" class="text-gray-500">No skills</span>
                </div>
              </td>
              <td class="p-3 border-t">
                <span :class="[
                  'inline-block px-2 py-1 text-xs font-semibold rounded',
                  artisan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                ]">
                  {{ artisan.status || 'inactive' }}
                </span>
              </td>
              <td class="p-3 border-t">{{ formatDate(artisan.created_at) }}</td>
                <td class="p-3 border-t">
                  <router-link
                    :to="`/artisans/${artisan.id}`"
                    class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1"
                  >
                    <i class="fas fa-eye"></i>
                  </router-link>
                  <router-link
                    :to="`/artisans/${artisan.id}/edit`"
                    class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 mr-1"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button
                    @click="deleteArtisan(artisan.id)"
                    :disabled="deleting === artisan.id"
                    class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                  >
                    <i v-if="deleting !== artisan.id" class="fas fa-trash"></i>
                    <i v-else class="fas fa-spinner fa-spin"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="artisans.data.length === 0">
                <td colspan="8" class="p-3 text-center border-t">No artisans found</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex justify-center">
          <button
            @click="fetchArtisans(artisans.current_page - 1)"
            :disabled="!artisans.prev_page_url"
            class="px-3 py-1 border rounded-l bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span class="px-3 py-1 border-t border-b bg-white">
            Page {{ artisans.current_page }} of {{ artisans.last_page }}
          </span>
          <button
            @click="fetchArtisans(artisans.current_page + 1)"
            :disabled="!artisans.next_page_url"
            class="px-3 py-1 border rounded-r bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue"
import axios from "axios"
import { debounce } from "lodash"
import Swal from "sweetalert2"

export default {
  setup() {
    const artisans = ref({ data: [], current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
    const departments = ref([])
    const loading = ref(false)
    const deleting = ref(null)
    const searchQuery = ref("")
    const selectedDepartment = ref("")
    const sortBy = ref("created_at:desc")

    const debouncedFetchArtisans = debounce(fetchArtisans, 500)

    async function fetchArtisans(page = 1) {
      loading.value = true
      try {
        const params = {
          page,
          search: searchQuery.value,
          department: selectedDepartment.value,
          sort: sortBy.value,
        }

        const response = await axios.get("/artisans", { params })
        artisans.value = response.data
        if (!departments.value.length) {
          fetchDepartments()
        }
      } catch (error) {
        console.error("Error fetching artisans:", error)
        Swal.fire("Error!", "Failed to load artisans.", "error")
      } finally {
        loading.value = false
      }
    }

    async function fetchDepartments() {
      try {
        const response = await axios.get("/departments")
        departments.value = response.data || []
      } catch (error) {
        console.error("Error fetching departments:", error)
      }
    }

    async function deleteArtisan(id) {
      try {
        const result = await Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        })

        if (result.isConfirmed) {
          deleting.value = id
          await axios.delete(`/artisans/${id}`)
          await fetchArtisans(artisans.value.current_page)
          Swal.fire("Deleted!", "Artisan has been deleted.", "success")
        }
      } catch (error) {
        if (error.response && error.response.status === 403) {
          Swal.fire("Forbidden!", "You do not have permission to delete this artisan.", "error")
        } else {
          Swal.fire("Error!", "Failed to delete artisan.", "error")
        }
      } finally {
        deleting.value = null
      }
    }

    async function exportArtisans() {
      try {
        Swal.fire({
          title: 'Exporting...',
          text: 'Please wait while we generate your file.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          }
        })

        const params = {
          search: searchQuery.value,
          department: selectedDepartment.value,
          sort: sortBy.value,
        }

        const response = await axios.get("/artisans/export", {
          params,
          responseType: "blob",
        })

        // Check if the response is JSON (error message)
        const contentType = response.headers['content-type']
        
        if (contentType.includes('application/json')) {
          // Convert blob to JSON to read the error message
          const text = await response.data.text()
          const error = JSON.parse(text)
          throw new Error(error.message || 'Export failed')
        }
        
        // Process the CSV file
        const blob = new Blob([response.data], { type: contentType })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement("a")
        link.href = url
        link.setAttribute("download", `artisans_export_${new Date().toISOString().slice(0, 10)}.csv`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        Swal.fire({
          icon: "success",
          title: "Success!",
          text: "Artisans exported successfully.",
          timer: 2000,
          showConfirmButton: false,
        })
      } catch (error) {
        console.error("Error exporting artisans:", error)
        let errorMessage = "Failed to export artisans."
        
        if (error.response?.status === 403) {
          errorMessage = "You do not have permission to export artisans."
        } else if (error.response?.data) {
          try {
            // Try to read the response data as text
            const reader = new FileReader()
            reader.onload = () => {
              try {
                const jsonResponse = JSON.parse(reader.result)
                errorMessage = jsonResponse.message || errorMessage
              } catch (e) {
                // Not JSON, use default message
              }
              
              Swal.fire({
                icon: "error",
                title: "Export Failed",
                text: errorMessage,
              })
            }
            reader.readAsText(error.response.data)
            return // Early return to prevent showing the alert twice
          } catch (e) {
            // If we can't read as text, use the error message
            errorMessage = error.message || errorMessage
          }
        } else {
          errorMessage = error.message || errorMessage
        }
        
        Swal.fire({
          icon: "error",
          title: "Export Failed",
          text: errorMessage,
        })
      }
    }

    async function importArtisans(event) {
      const file = event.target.files[0]
      if (!file) return

      // Check file type
      const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
      if (!allowedTypes.includes(file.type)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid File Type',
          text: 'Please upload a CSV or Excel file.',
        })
        event.target.value = '' // Clear the file input
        return
      }

      const formData = new FormData()
      formData.append("file", file)

      try {
        Swal.fire({
          title: 'Importing...',
          text: 'Please wait while we process your file.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          }
        })

        const response = await axios.post("/artisans/import", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })

        if (response.data.errors && response.data.errors.length > 0) {
          Swal.fire({
            title: "Import Partially Successful",
            html: `${response.data.message}<br><br><strong>Errors:</strong><br>${response.data.errors.join("<br>")}`,
            icon: "warning",
          })
        } else {
          Swal.fire("Success!", response.data.message, "success")
        }
        fetchArtisans()
      } catch (error) {
        console.error("Error importing artisans:", error)
        let errorMessage = "Failed to import artisans."
        
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors
          errorMessage = Object.values(errors).flat().join('<br>')
        } else if (error.response?.data?.error) {
          errorMessage = error.response.data.error
        }
        
        Swal.fire({
          icon: "error",
          title: "Import Failed",
          html: errorMessage,
        })
      } finally {
        event.target.value = '' // Clear the file input
      }
    }

    function getDepartmentColor(department) {
      const colors = {
        Pottery: "bg-amber-100 text-amber-800",
        Weaving: "bg-emerald-100 text-emerald-800",
        Woodwork: "bg-orange-100 text-orange-800",
        Textiles: "bg-blue-100 text-blue-800",
        Handicrafts: "bg-purple-100 text-purple-800",
        Jewelry: "bg-rose-100 text-rose-800",
      }

      return colors[department] || "bg-gray-100 text-gray-800"
    }

    function formatCurrency(value) {
      return value ? new Intl.NumberFormat("ne-NP", { style: "currency", currency: "NPR" }).format(value) : "N/A"
    }

    function formatDate(dateString) {
      if (!dateString) return "N/A"
      const date = new Date(dateString)
      return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" })
    }

    function resetFilters() {
      searchQuery.value = ""
      selectedDepartment.value = ""
      sortBy.value = "created_at:desc"
      fetchArtisans()
    }

    onMounted(() => {
      fetchArtisans()
    })

    return {
      artisans,
      departments,
      loading,
      deleting,
      searchQuery,
      selectedDepartment,
      sortBy,
      fetchArtisans,
      debouncedFetchArtisans,
      deleteArtisan,
      getDepartmentColor,
      formatCurrency,
      formatDate,
      importArtisans,
      exportArtisans,
      resetFilters,
    }
  },
}

</script>