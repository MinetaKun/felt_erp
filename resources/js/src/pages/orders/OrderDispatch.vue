<template>
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Order Dispatch</h1>
        <div class="flex space-x-2">
          <button
            v-if="selectedAssignments.length > 0"
            @click="bulkDispatch"
            :disabled="processing"
            class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md flex items-center"
          >
            <span v-if="processing" class="mr-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Bulk Dispatch ({{ selectedAssignments.length }})
          </button>
        </div>
      </div>
  
      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search orders or artisans..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <!-- <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select
              v-model="filters.department_id"
              @change="loadAssignments"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div> -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="loadAssignments"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              <option value="approved">Approved</option>
              <option value="dispatched">Dispatched</option>
              <option value="">All Statuses</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
          >
            Reset Filters
          </button>
        </div>
      </div>
  
      <!-- Assignments Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="selectAll"
                      @change="toggleSelectAll"
                      class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                    />
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Product
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Artisan
                </th>
                <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Department
                </th> -->
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Quantity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Approved Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="9" class="px-6 py-4">
                  <div class="flex justify-center">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </div>
                </td>
              </tr>
              <tr v-else-if="assignments.length === 0" class="text-center">
                <td colspan="9" class="px-6 py-4 text-gray-500">
                  No assignments found
                </td>
              </tr>
              <tr v-for="assignment in assignments" :key="assignment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    v-model="selectedAssignments"
                    :value="assignment.id"
                    :disabled="assignment.status === 'dispatched'"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ assignment.order?.order_id || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ assignment.order?.product_name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ assignment.artisan?.name || 'N/A' }}
                </td>
                <!-- <td class="px-6 py-4 whitespace-nowrap">
                  {{ assignment.artisan?.department?.name || 'N/A' }}
                </td> -->
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ assignment.approved_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs rounded-full', getStatusClass(assignment.status)]">
                    {{ formatStatus(assignment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(assignment.approved_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-2">
                    <button
                      v-if="assignment.status === 'approved'"
                      @click="dispatchAssignment(assignment)"
                      class="text-purple-600 hover:text-purple-900"
                      title="Dispatch"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-5h2.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-1-1h-8a1 1 0 00-.8.4L8.4 8H5V5a1 1 0 00-1-1H3z" />
                      </svg>
                    </button>
                    <button
                      v-if="assignment.status === 'dispatched'"
                      @click="downloadChallan(assignment)"
                      class="text-green-600 hover:text-green-900"
                      title="Download Challan"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    <button
                      @click="viewDetails(assignment)"
                      class="text-blue-600 hover:text-blue-900"
                      title="View Details"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                      </svg>
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
                Showing
                <span class="font-medium">{{ pagination.current_page }}</span>
                of
                <span class="font-medium">{{ pagination.last_page }}</span>
                pages
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="handlePageChange(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <span class="sr-only">Previous</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
                <button
                  v-for="page in getPageNumbers()"
                  :key="page"
                  @click="typeof page === 'number' ? handlePageChange(page) : null"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium',
                    page === pagination.current_page
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'text-gray-500 hover:bg-gray-50',
                    typeof page !== 'number' ? 'cursor-default' : 'cursor-pointer'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="handlePageChange(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <span class="sr-only">Next</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Dispatch Modal -->
      <div v-if="showDispatchModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <h2 class="text-xl font-bold mb-4">Dispatch Assignment</h2>
          <div v-if="selectedAssignment" class="mb-4">
            <p class="mb-2">
              <span class="font-semibold">Order:</span> {{ selectedAssignment.order?.order_id }}
            </p>
            <p class="mb-2">
              <span class="font-semibold">Product:</span> {{ selectedAssignment.order?.product_name }}
            </p>
            <p class="mb-2">
              <span class="font-semibold">Artisan:</span> {{ selectedAssignment.artisan?.name }}
            </p>
            <p class="mb-2">
              <span class="font-semibold">Approved Quantity:</span> {{ selectedAssignment.approved_quantity }}
            </p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Date</label>
            <input
              type="date"
              v-model="dispatchForm.dispatch_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Method</label>
            <select
              v-model="dispatchForm.dispatch_method"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              <option value="vehicle">Vehicle</option>
              <option value="runner">Runner</option>
              <option value="courier">Courier</option>
              <option value="pickup">Pickup</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="dispatchForm.dispatch_notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md"
              placeholder="Additional notes about the dispatch"
            ></textarea>
          </div>
          <div class="flex justify-end space-x-2">
            <button
              @click="showDispatchModal = false"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md"
            >
              Cancel
            </button>
            <button
              @click="confirmDispatch"
              :disabled="processing"
              class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md flex items-center"
            >
              <span v-if="processing" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Dispatch
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted } from "vue"
  import axios from "axios"
  import Swal from "sweetalert2"
  import Modal from '../../components/Modal.vue'
  import { useToast } from 'vue-toastification'
  
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
    status: "", // Default to show only approved assignments
    page: 1,
    per_page: 15,
  })
  const selectedAssignments = ref([])
  const selectAll = ref(false)
  const showDispatchModal = ref(false)
  const selectedAssignment = ref(null)
  const dispatchForm = ref({
    dispatch_date: new Date().toISOString().split('T')[0],
    dispatch_method: "vehicle",
    dispatch_notes: ""
  })
  const toast = useToast()
  
  // Load assignments with filters
  const loadAssignments = async () => {
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
      Swal.fire("Error!", "Failed to load assignments.", "error")
    } finally {
      loading.value = false
    }
  }
  
  // Format status for display
  const formatStatus = (status) => {
    if (!status) return "N/A"
    return status
      .split("_")
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(" ")
  }
  
  // Format date
  const formatDate = (dateString) => {
    if (!dateString) return "N/A"
    const date = new Date(dateString)
    return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" })
  }
  
  // Get status class for styling
  const getStatusClass = (status) => {
    const classes = {
      pending: "bg-yellow-100 text-yellow-800",
      in_production: "bg-blue-100 text-blue-800",
      completed: "bg-orange-100 text-orange-800",
      approved: "bg-green-100 text-green-800",
      dispatched: "bg-purple-100 text-purple-800",
    }
    return classes[status] || "bg-gray-100 text-gray-800"
  }
  
  // Toggle select all
  const toggleSelectAll = () => {
    selectAll.value = !selectAll.value
    if (selectAll.value) {
      selectedAssignments.value = assignments.value
        .filter((a) => a.status === "approved")
        .map((a) => a.id)
    } else {
      selectedAssignments.value = []
    }
  }
  
  // Open dispatch modal for a single assignment
  const openDispatchModal = (assignment) => {
    selectedAssignment.value = assignment
    dispatchForm.value = {
      dispatch_date: new Date().toISOString().split('T')[0],
      dispatch_method: "vehicle",
      dispatch_notes: ""
    }
    showDispatchModal.value = true
  }
  
  // Confirm dispatch for a single assignment
  const confirmDispatch = async () => {
    if (loading.value) return
  
    loading.value = true
    try {
      const response = await axios.post(`/api/order-assignments/${selectedAssignment.value.id}/dispatch`, dispatchForm.value)
      
      if (response.data.success) {
        toast.success('Assignment dispatched successfully')
        showDispatchModal.value = false
        loadAssignments()
      } else {
        toast.error(response.data.message || 'Failed to dispatch assignment')
      }
    } catch (error) {
      console.error('Dispatch error:', error)
      toast.error(error.response?.data?.message || 'Failed to dispatch assignment')
    } finally {
      loading.value = false
    }
  }
  
  // Bulk dispatch selected assignments
  const bulkDispatch = () => {
    if (selectedAssignments.value.length === 0) return
  
    Swal.fire({
      title: 'Dispatch Multiple Assignments',
      html: `
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Dispatch Date</label>
          <input id="swal-dispatch-date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="${new Date().toISOString().split('T')[0]}">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Dispatch Method</label>
          <select id="swal-dispatch-method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="vehicle">Vehicle</option>
            <option value="runner">Runner</option>
            <option value="courier">Courier</option>
            <option value="pickup">Pickup</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Notes</label>
          <textarea id="swal-dispatch-notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="3"></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Dispatch',
      preConfirm: () => {
        return {
          dispatch_date: document.getElementById('swal-dispatch-date').value,
          dispatch_method: document.getElementById('swal-dispatch-method').value,
          dispatch_notes: document.getElementById('swal-dispatch-notes').value
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        processing.value = true
        axios.post("/api/order-assignments/bulk-dispatch", {
          assignment_ids: selectedAssignments.value,
          ...result.value
        })
        .then(() => {
          Swal.fire("Success!", "Assignments dispatched successfully", "success")
          selectedAssignments.value = []
          selectAll.value = false
          loadAssignments()
        })
        .catch((error) => {
          console.error("Error bulk dispatching assignments:", error)
          Swal.fire("Error!", "Failed to dispatch assignments.", "error")
        })
        .finally(() => {
          processing.value = false
        })
      }
    })
  }
  
  // Download challan for a dispatched assignment
  const downloadChallan = async (assignment) => {
    try {
      const response = await axios.get(`/order-assignments/${assignment.id}/challan`, {
        responseType: "blob",
      })
  
      // Create a blob URL and trigger download
      const blob = new Blob([response.data], { type: "application/pdf" })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement("a")
      link.href = url
      link.setAttribute("download", `challan-${assignment.order.order_id}-${assignment.id}.pdf`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } catch (error) {
      console.error("Error downloading challan:", error)
      Swal.fire("Error!", "Failed to download challan.", "error")
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
      loadAssignments()
    }, 500)
  }
  
  // Handle pagination
  const handlePageChange = (page) => {
    if (page < 1 || page > pagination.value.last_page) return
    filters.page = page
    loadAssignments()
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
      if (key !== "page" && key !== "per_page" && key !== "status") {
        filters[key] = ""
      }
    })
    filters.page = 1
    filters.status = "approved" // Keep the status filter
    loadAssignments()
  }
  
  // Load assignments on component mount
  onMounted(() => {
    loadAssignments()
  })
  
  // Dispatch assignment
  const dispatchAssignment = async (assignment) => {
    try {
      const result = await Swal.fire({
        title: 'Dispatch Assignment',
        html: `
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Dispatch Date</label>
              <input type="date" id="dispatch_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Dispatch Method</label>
              <select id="dispatch_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                <option value="courier">Courier</option>
                <option value="hand_delivery">Hand Delivery</option>
                <option value="pickup">Pickup</option>
                <option value="vehicle_runner">Vehicle Runner</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Notes</label>
              <textarea id="dispatch_notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" rows="3"></textarea>
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Dispatch',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          return {
            dispatch_date: document.getElementById('dispatch_date').value,
            dispatch_method: document.getElementById('dispatch_method').value,
            dispatch_notes: document.getElementById('dispatch_notes').value
          }
        }
      })

      if (result.isConfirmed) {
        processing.value = true
        await axios.patch(`/order-assignments/${assignment.id}/dispatch`, result.value)
        await loadAssignments()
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Assignment dispatched successfully',
          showConfirmButton: false,
          timer: 1500
        })
      }
    } catch (error) {
      console.error('Error dispatching assignment:', error)
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Failed to dispatch assignment'
      })
    } finally {
      processing.value = false
    }
  }
  </script>
  