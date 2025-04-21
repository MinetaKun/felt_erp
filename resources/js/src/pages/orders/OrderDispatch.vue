<template>
    <div class="container mx-auto px-4 py-6">
      <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Order Assignments for Dispatch</h1>
        <button
          v-if="selectedOrders.length > 0"
          @click="bulkDispatch"
          :disabled="processing"
          class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? "Processing..." : `Dispatch Selected (${selectedOrders.length})` }}
        </button>
      </div>
  
      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 p-4 rounded-md shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Search</label>
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search by order ID or artisan"
              class="w-full p-2 border rounded-md"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Department</label>
            <select v-model="filters.department_id" @change="loadOrders" class="w-full p-2 border rounded-md">
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select v-model="filters.status" @change="loadOrders" class="w-full p-2 border rounded-md">
              <option value="">All Statuses</option>
              <option value="approved">Approved</option>
              <option value="dispatched">Dispatched</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
            >
              Reset Filters
            </button>
          </div>
        </div>
      </div>
  
      <!-- Assignments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-md shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="selectAll"
                      @change="toggleSelectAll"
                      class="mr-2 h-4 w-4"
                    />
                    <span>Order ID</span>
                  </div>
                </th>
                <th class="px-4 py-3 text-left">Product</th>
                <th class="px-4 py-3 text-left">Artisan</th>
                <th class="px-4 py-3 text-left">Department</th>
                <th class="px-4 py-3 text-left">Quantity</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Approved Date</th>
                <th class="px-4 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="8" class="px-4 py-4">Loading assignments...</td>
              </tr>
              <tr v-else-if="assignments.length === 0" class="text-center">
                <td colspan="8" class="px-4 py-4">No assignments found.</td>
              </tr>
              <tr v-for="assignment in assignments" :key="assignment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      v-if="assignment.status === 'approved'"
                      v-model="selectedOrders"
                      :value="assignment.id"
                      class="mr-2 h-4 w-4"
                    />
                    <span>{{ assignment.order?.order_id || 'N/A' }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">{{ assignment.order?.product_name || 'N/A' }}</td>
                <td class="px-4 py-3">{{ assignment.artisan?.name || 'N/A' }}</td>
                <td class="px-4 py-3">{{ assignment.artisan?.department?.name || 'N/A' }}</td>
                <td class="px-4 py-3">
                  <div class="flex flex-col">
                    <span>Assigned: {{ assignment.assigned_quantity }}</span>
                    <span>Completed: {{ assignment.completed_quantity }}</span>
                    <span>Approved: {{ assignment.approved_quantity }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span :class="getStatusClass(assignment.status)" class="px-2 py-1 rounded-full text-xs">
                    {{ formatStatus(assignment.status) }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ formatDate(assignment.approved_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex space-x-2">
                    <button
                      v-if="assignment.status === 'approved'"
                      @click="dispatchAssignment(assignment)"
                      class="px-3 py-1 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm"
                    >
                      Dispatch
                    </button>
                    <button
                      v-if="assignment.status === 'dispatched'"
                      @click="downloadInvoice(assignment)"
                      class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                    >
                      Invoice
                    </button>
                    <button
                      @click="viewDetails(assignment)"
                      class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                    >
                      View
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Pagination -->
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ assignments.length }}</span>
                results of
                <span class="font-medium">{{ pagination.total }}</span>
                total
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="handlePageChange(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  Previous
                </button>
                <button
                  v-for="(page, index) in getPageNumbers()"
                  :key="index"
                  @click="typeof page === 'number' ? handlePageChange(page) : null"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium',
                    page === pagination.current_page
                      ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                      : 'text-gray-500 hover:bg-gray-50',
                    page === '...' ? 'cursor-default' : 'cursor-pointer',
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="handlePageChange(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  Next
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Dispatch Modal -->
      <div v-if="showDispatchModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
          </div>
  
          <!-- Modal panel -->
          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Dispatch Assignment</h3>
                  
                  <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">
                      Order: <span class="font-semibold">{{ selectedAssignment?.order?.order_id || 'N/A' }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mb-2">
                      Product: <span class="font-semibold">{{ selectedAssignment?.order?.product_name || 'N/A' }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mb-2">
                      Artisan: <span class="font-semibold">{{ selectedAssignment?.artisan?.name || 'N/A' }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mb-2">
                      Approved Quantity: <span class="font-semibold">{{ selectedAssignment?.approved_quantity || 0 }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mb-2">
                      Wages per Unit: <span class="font-semibold">{{ formatCurrency(selectedAssignment?.order?.wages_per_unit || 0) }}</span>
                    </p>
                    <p class="text-sm text-gray-500 mb-2">
                      Total Wages: <span class="font-semibold">{{ formatCurrency((selectedAssignment?.approved_quantity || 0) * (selectedAssignment?.order?.wages_per_unit || 0)) }}</span>
                    </p>
                  </div>
  
                  <div class="mb-4">
                    <p class="text-sm text-gray-700">
                      Are you sure you want to dispatch this assignment? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="confirmDispatch"
                :disabled="processing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ processing ? "Processing..." : "Dispatch" }}
              </button>
              <button
                type="button"
                @click="showDispatchModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, onMounted, computed } from "vue"
  import axios from "axios"
  
  export default {
    setup() {
      // State
      const assignments = ref([])
      const departments = ref([])
      const loading = ref(false)
      const processing = ref(false)
      const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
      })
      const filters = reactive({
        search: "",
        department_id: "",
        status: "",
        page: 1,
        per_page: 15,
      })
      const selectedOrders = ref([])
      const selectAll = ref(false)
      const showDispatchModal = ref(false)
      const selectedAssignment = ref(null)
  
      // Load assignments with filters
      const loadOrders = async () => {
        loading.value = true
        try {
          const queryParams = new URLSearchParams()
  
          // Add filters to query params
          Object.keys(filters).forEach((key) => {
            if (filters[key]) {
              queryParams.append(key, filters[key])
            }
          })
  
          // Load departments if not already loaded
          if (departments.value.length === 0) {
            const deptResponse = await axios.get("/departments")
            departments.value = deptResponse.data
          }
  
          // Load assignments
          const response = await axios.get(`/order-assignments?${queryParams.toString()}`)
          assignments.value = response.data.data
          pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
          }
        } catch (error) {
          console.error("Error loading assignments:", error)
          alert("Failed to load assignments. Please try again.")
        } finally {
          loading.value = false
        }
      }
  
      // Format status for display
      const formatStatus = (status) => {
        switch (status) {
          case "pending":
            return "Pending"
          case "in_production":
            return "In Production"
          case "completed":
            return "Completed"
          case "approved":
            return "Approved"
          case "dispatched":
            return "Dispatched"
          default:
            return status
        }
      }
  
      // Format date
      const formatDate = (dateString) => {
        if (!dateString) return "N/A"
        const date = new Date(dateString)
        return date.toLocaleDateString()
      }
  
      // Format currency
      const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD',
        }).format(amount)
      }
  
      // Get status class for styling
      const getStatusClass = (status) => {
        switch (status) {
          case "pending":
            return "bg-yellow-100 text-yellow-800"
          case "in_production":
            return "bg-blue-100 text-blue-800"
          case "completed":
            return "bg-orange-100 text-orange-800"
          case "approved":
            return "bg-green-100 text-green-800"
          case "dispatched":
            return "bg-purple-100 text-purple-800"
          default:
            return "bg-gray-100 text-gray-800"
        }
      }
  
      // Toggle select all
      const toggleSelectAll = () => {
        selectAll.value = !selectAll.value
        if (selectAll.value) {
          selectedOrders.value = assignments.value.filter((a) => a.status === "approved").map((a) => a.id)
        } else {
          selectedOrders.value = []
        }
      }
  
      // Dispatch a single assignment
      const dispatchAssignment = (assignment) => {
        selectedAssignment.value = assignment
        showDispatchModal.value = true
      }
  
      // Confirm dispatch for a single assignment
      const confirmDispatch = async () => {
        processing.value = true
        try {
          await axios.patch(`/order-assignments/${selectedAssignment.value.id}/dispatch`)
          showDispatchModal.value = false
          alert("Assignment dispatched successfully")
          loadOrders()
        } catch (error) {
          console.error("Error dispatching assignment:", error)
          alert("Failed to dispatch assignment. Please try again.")
        } finally {
          processing.value = false
        }
      }
  
      // Download invoice for a dispatched assignment
      const downloadInvoice = async (assignment) => {
        try {
          const response = await axios.get(`/orders/${assignment.order_id}/invoice`, {
            responseType: 'blob'
          })
          
          // Create a blob URL and trigger download
          const blob = new Blob([response.data], { type: 'application/pdf' })
          const url = window.URL.createObjectURL(blob)
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', `invoice-${assignment.order.order_id}.pdf`)
          document.body.appendChild(link)
          link.click()
          document.body.removeChild(link)
          window.URL.revokeObjectURL(url)
        } catch (error) {
          console.error("Error downloading invoice:", error)
          alert("Failed to download invoice. Please try again.")
        }
      }
  
      // Bulk dispatch selected assignments
      const bulkDispatch = async () => {
        if (selectedOrders.value.length === 0) return
  
        if (!confirm(`Are you sure you want to dispatch ${selectedOrders.value.length} assignments?`)) {
          return
        }
  
        processing.value = true
        try {
          await axios.post("/order-assignments/bulk-dispatch", {
            assignment_ids: selectedOrders.value,
          })
          alert("Assignments dispatched successfully")
          selectedOrders.value = []
          selectAll.value = false
          loadOrders()
        } catch (error) {
          console.error("Error bulk dispatching assignments:", error)
          alert("Failed to dispatch assignments. Please try again.")
        } finally {
          processing.value = false
        }
      }
  
      // View assignment details
      const viewDetails = (assignment) => {
        // Navigate to order details page
        window.location.href = `/orders/${assignment.order_id}`
      }
  
      // Debounce search input
      let searchTimeout
      const debounceSearch = () => {
        clearTimeout(searchTimeout)
        searchTimeout = setTimeout(() => {
          loadOrders()
        }, 500)
      }
  
      // Handle pagination
      const handlePageChange = (page) => {
        if (page < 1 || page > pagination.value.last_page) return
        filters.page = page
        loadOrders()
      }
  
      // Get page numbers for pagination
      const getPageNumbers = () => {
        const current = pagination.value.current_page
        const last = pagination.value.last_page
  
        if (last <= 7) {
          return Array.from({ length: last }, (_, i) => i + 1)
        }
  
        if (current <= 3) {
          return [1, 2, 3, 4, "...", last - 1, last]
        }
  
        if (current >= last - 2) {
          return [1, 2, "...", last - 3, last - 2, last - 1, last]
        }
  
        return [1, "...", current - 1, current, current + 1, "...", last]
      }
  
      // Reset filters
      const resetFilters = () => {
        Object.keys(filters).forEach((key) => {
          if (key !== "page" && key !== "per_page") {
            filters[key] = ""
          }
        })
        filters.page = 1
        loadOrders()
      }
  
      // Load orders on component mount
      onMounted(() => {
        loadOrders()
      })
  
      return {
        assignments,
        departments,
        loading,
        processing,
        pagination,
        filters,
        selectedOrders,
        selectAll,
        showDispatchModal,
        selectedAssignment,
        formatStatus,
        formatDate,
        formatCurrency,
        getStatusClass,
        toggleSelectAll,
        dispatchAssignment,
        confirmDispatch,
        downloadInvoice,
        bulkDispatch,
        viewDetails,
        debounceSearch,
        handlePageChange,
        getPageNumbers,
        resetFilters,
        loadOrders,
      }
    },
}
</script>
  