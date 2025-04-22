<template>
    <div class="container mx-auto px-4 py-8">
      <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Order Assignments</h1>
        <div class="flex space-x-2">
          <button
            v-if="hasCompletedAssignments"
            @click="bulkApprove"
            :disabled="processing"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
          >
            Bulk Approve
          </button>
          <button
            v-if="hasApprovedAssignments"
            @click="bulkDispatch"
            :disabled="processing"
            class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:opacity-50"
          >
            Bulk Dispatch
          </button>
          <button
            v-if="selectedAssignments.length > 0"
            @click="openBulkReviewModal"
            :disabled="processing"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
          >
            Review Selected
          </button>
        </div>
      </div>
  
      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Search</label>
            <input
              type="text"
              v-model="filters.search"
              @input="debouncedFetchAssignments"
              placeholder="Search..."
              class="w-full p-2 border rounded"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Artisan</label>
            <select
              v-model="filters.artisan_id"
              @change="fetchAssignments"
              class="w-full p-2 border rounded"
            >
              <option value="">All Artisans</option>
              <option v-for="artisan in artisans" :key="artisan.id" :value="artisan.id">
                {{ artisan.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Department</label>
            <select
              v-model="filters.department_id"
              @change="fetchAssignments"
              class="w-full p-2 border rounded"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="fetchAssignments"
              class="w-full p-2 border rounded"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_production">In Production</option>
              <option value="completed">Completed</option>
              <option value="approved">Approved</option>
              <option value="dispatched">Dispatched</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          >
            Reset Filters
          </button>
        </div>
      </div>
  
      <!-- Assignments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Order ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Product
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Artisan
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Assigned
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Completed
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Approved
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="9" class="px-6 py-4">
                  <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                  </div>
                </td>
              </tr>
              <tr v-else-if="assignments.data && assignments.data.length === 0" class="text-center">
                <td colspan="9" class="px-6 py-4 text-gray-500 dark:text-gray-400">
                  No assignments found
                </td>
              </tr>
              <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="assignment.id"
                    v-model="selectedAssignments"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ assignment.order?.order_id || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ assignment.order?.product_name || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ assignment.artisan?.name || 'N/A' }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ assignment.artisan?.department?.name || 'No Department' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ assignment.assigned_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ assignment.completed_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ assignment.approved_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                      getStatusClass(assignment.status)
                    ]"
                  >
                    {{ formatStatus(assignment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      v-if="assignment.status === 'completed'"
                      @click="approveAssignment(assignment)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      Approve
                    </button>
                    <button
                      v-if="assignment.status === 'approved'"
                      @click="dispatchAssignment(assignment)"
                      class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                    >
                      Dispatch
                    </button>
                    <button
                      @click="viewDetails(assignment)"
                      class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                    >
                      View
                    </button>
                    <button
                      @click="deleteAssignment(assignment.id)"
                      :disabled="deleting === assignment.id"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 disabled:opacity-50"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing
              <span class="font-medium">{{ assignments.data ? assignments.data.length : 0 }}</span>
              of
              <span class="font-medium">{{ pagination.total }}</span>
              results
            </div>
            <div class="flex space-x-2">
              <button
                @click="handlePageChange(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-1 border rounded text-sm disabled:opacity-50"
              >
                Previous
              </button>
              <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="handlePageChange(page)"
                :disabled="page === '...'"
                :class="[
                  'px-3 py-1 border rounded text-sm',
                  page === pagination.current_page
                    ? 'bg-blue-500 text-white'
                    : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                ]"
              >
                {{ page }}
              </button>
              <button
                @click="handlePageChange(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-1 border rounded text-sm disabled:opacity-50"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Approval Modal -->
      <div v-if="showApprovalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
          <h2 class="text-xl font-bold mb-4">Approve Assignment</h2>
          <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
              Order: {{ selectedAssignment?.order?.product_name }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
              Artisan: {{ selectedAssignment?.artisan?.name }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              Completed Quantity: {{ selectedAssignment?.completed_quantity }}
            </p>
            <div class="mb-4">
              <label class="block text-sm font-medium mb-1">Approved Quantity</label>
              <input
                type="number"
                v-model.number="approvalForm.approved_quantity"
                min="0"
                :max="selectedAssignment?.completed_quantity"
                class="w-full p-2 border rounded"
              />
            </div>
            <div v-if="approvalForm.approved_quantity < selectedAssignment?.completed_quantity" class="mb-4">
              <label class="block text-sm font-medium mb-1">Rejection Reason</label>
              <textarea
                v-model="approvalForm.rejection_reason"
                rows="3"
                class="w-full p-2 border rounded"
                placeholder="Reason for rejecting some items..."
              ></textarea>
            </div>
          </div>
          <div class="flex justify-end space-x-2">
            <button
              @click="showApprovalModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              @click="confirmApproval"
              :disabled="!isApprovalFormValid || processing"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
            >
              <span v-if="processing">Processing...</span>
              <span v-else>Confirm</span>
            </button>
          </div>
        </div>
      </div>
  
      <!-- Bulk Review Modal -->
      <div v-if="showBulkReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
          <h2 class="text-xl font-bold mb-4">Review Selected Assignments</h2>
          <div class="mb-4">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Order
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Artisan
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Completed
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Action
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Reason (if rejecting)
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                <tr v-for="assignment in selectedAssignmentsData" :key="assignment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-4 py-2 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ assignment.order?.order_id || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ assignment.order?.product_name || 'N/A' }}
                    </div>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                      {{ assignment.artisan?.name || 'N/A' }}
                    </div>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    {{ assignment.completed_quantity }}
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <select
                      v-model="bulkActions[assignment.id]"
                      class="p-1 border rounded text-sm"
                      :disabled="assignment.status !== 'completed'"
                    >
                      <option value="approve">Approve</option>
                      <option value="reject">Reject</option>
                    </select>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <input
                      v-if="bulkActions[assignment.id] === 'reject'"
                      v-model="bulkReasons[assignment.id]"
                      type="text"
                      class="p-1 border rounded text-sm w-full"
                      placeholder="Reason for rejection"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end space-x-2">
            <button
              @click="closeBulkReviewModal"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              @click="processBulkActions"
              :disabled="processing"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
            >
              <span v-if="processing">Processing...</span>
              <span v-else>Process All</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed, onMounted } from "vue"
import axios from "axios"
import { debounce } from "lodash"
import Swal from "sweetalert2"

export default {
  setup() {
    // State
    const assignments = ref({ data: [] })
    const artisans = ref([])
    const departments = ref([])
    const loading = ref(false)
    const processing = ref(false)
    const deleting = ref(null)
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    })
    const selectedAssignments = ref([])
    const selectAll = ref(false)
    const filters = ref({
      search: "",
      artisan_id: "",
      department_id: "",
      status: "",
      sort: "created_at:desc",
      page: 1,
      per_page: 15,
    })

    // Approval modal
    const showApprovalModal = ref(false)
    const showBulkReviewModal = ref(false)
    const selectedAssignment = ref(null)
    const approvalForm = ref({
      approved_quantity: 0,
      rejection_reason: "",
    })
    const bulkActions = ref({})
    const bulkReasons = ref({})

    const isAllSelected = computed(() => {
      return (
        assignments.value.data &&
        assignments.value.data.length > 0 &&
        selectedAssignments.value.length === assignments.value.data.length
      )
    })

    const isApprovalFormValid = computed(() => {
      if (approvalForm.value.approved_quantity < 0) return false
      if (!selectedAssignment.value) return false
      if (approvalForm.value.approved_quantity > selectedAssignment.value.completed_quantity) return false

      // If not approving all items, require a rejection reason
      if (
        approvalForm.value.approved_quantity < selectedAssignment.value.completed_quantity &&
        !approvalForm.value.rejection_reason
      ) {
        return false
      }

      return true
    })

    const hasCompletedAssignments = computed(() => {
      return assignments.value.data.some((a) => a.status === "completed" && selectedAssignments.value.includes(a.id))
    })

    const hasApprovedAssignments = computed(() => {
      return assignments.value.data.some((a) => a.status === "approved" && selectedAssignments.value.includes(a.id))
    })

    const selectedAssignmentsData = computed(() => {
      return assignments.value.data.filter((a) => selectedAssignments.value.includes(a.id))
    })

    const debouncedFetchAssignments = debounce(() => {
      fetchAssignments()
    }, 500)

    async function fetchAssignments(page = 1) {
      loading.value = true
      try {
        const params = {
          page,
          ...filters.value,
        }

        const response = await axios.get("/order-assignments", { params })
        assignments.value = response.data
        pagination.value = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
        }
      } catch (error) {
        console.error("Error fetching assignments:", error)
        Swal.fire("Error!", "Failed to load assignments.", "error")
      } finally {
        loading.value = false
      }
    }

    async function fetchArtisans() {
      try {
        const response = await axios.get("/artisans")
        artisans.value = response.data.data || []
      } catch (error) {
        console.error("Error fetching artisans:", error)
      }
    }

    async function fetchDepartments() {
      try {
        const response = await axios.get("/departments")
        departments.value = response.data
      } catch (error) {
        console.error("Error fetching departments:", error)
      }
    }

    function changePage(page) {
      if (page < 1 || page > pagination.value.last_page) return
      fetchAssignments(page)
    }

    function resetFilters() {
      filters.value = {
        search: "",
        artisan_id: "",
        department_id: "",
        status: "",
        sort: "created_at:desc",
        page: 1,
        per_page: 15,
      }
      fetchAssignments()
    }

    function toggleSelectAll() {
      selectAll.value = !selectAll.value
      if (selectAll.value) {
        selectedAssignments.value = assignments.value.data.map((a) => a.id)
      } else {
        selectedAssignments.value = []
      }
    }

    function getStatusClass(status) {
      const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        in_production: "bg-blue-100 text-blue-800",
        completed: "bg-orange-100 text-orange-800",
        approved: "bg-green-100 text-green-800",
        dispatched: "bg-purple-100 text-purple-800",
        rejected: "bg-red-100 text-red-800",
      }
      return classes[status] || "bg-gray-100 text-gray-800"
    }

    function formatStatus(status) {
      if (!status) return "N/A"
      return status
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ")
    }

    function formatDate(dateString) {
      if (!dateString) return "N/A"
      const date = new Date(dateString)
      return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" })
    }

    // Approve a single assignment
    const approveAssignment = (assignment) => {
      selectedAssignment.value = assignment
      approvalForm.value.approved_quantity = assignment.completed_quantity
      approvalForm.value.rejection_reason = ""
      showApprovalModal.value = true
    }

    // Dispatch a single assignment
    const dispatchAssignment = async (assignment) => {
      if (!confirm(`Are you sure you want to dispatch this assignment?`)) {
        return
      }

      processing.value = true
      try {
        await axios.patch(`/order-assignments/${assignment.id}/dispatch`)
        Swal.fire("Success!", "Assignment dispatched successfully", "success")
        fetchAssignments()
      } catch (error) {
        console.error("Error dispatching assignment:", error)
        Swal.fire("Error!", "Failed to dispatch assignment.", "error")
      } finally {
        processing.value = false
      }
    }

    // Confirm approval for a single assignment
    const confirmApproval = async () => {
      if (!isApprovalFormValid.value) return

      processing.value = true
      try {
        await axios.patch(`/order-assignments/${selectedAssignment.value.id}/approve-reject`, {
          action: "approve",
          approved_quantity: approvalForm.value.approved_quantity,
          rejected_quantity: selectedAssignment.value.completed_quantity - approvalForm.value.approved_quantity,
          rejection_reason: approvalForm.value.rejection_reason,
        })
        showApprovalModal.value = false
        Swal.fire("Success!", "Assignment approved successfully", "success")
        fetchAssignments()
      } catch (error) {
        console.error("Error approving assignment:", error)
        Swal.fire("Error!", "Failed to approve assignment.");
        console.error("Error approving assignment:", error)
        Swal.fire("Error!", "Failed to approve assignment.", "error")
      } finally {
        processing.value = false
      }
    }

    // Bulk approve selected assignments
    const bulkApprove = async () => {
      if (selectedAssignments.value.length === 0) return

      if (!confirm(`Are you sure you want to approve ${selectedAssignments.value.length} assignments?`)) {
        return
      }

      processing.value = true
      try {
        await axios.post("/order-assignments/bulk-approve", {
          assignment_ids: selectedAssignments.value,
        })
        Swal.fire("Success!", "Assignments approved successfully", "success")
        selectedAssignments.value = []
        selectAll.value = false
        fetchAssignments()
      } catch (error) {
        console.error("Error bulk approving assignments:", error)
        Swal.fire("Error!", "Failed to approve assignments.", "error")
      } finally {
        processing.value = false
      }
    }

    // Bulk dispatch selected assignments
    const bulkDispatch = async () => {
      if (selectedAssignments.value.length === 0) return

      if (!confirm(`Are you sure you want to dispatch ${selectedAssignments.value.length} assignments?`)) {
        return
      }

      processing.value = true
      try {
        await axios.post("/order-assignments/bulk-dispatch", {
          assignment_ids: selectedAssignments.value,
        })
        Swal.fire("Success!", "Assignments dispatched successfully", "success")
        selectedAssignments.value = []
        selectAll.value = false
        fetchAssignments()
      } catch (error) {
        console.error("Error bulk dispatching assignments:", error)
        Swal.fire("Error!", "Failed to dispatch assignments.", "error")
      } finally {
        processing.value = false
      }
    }

    // Open bulk review modal
    const openBulkReviewModal = () => {
      if (selectedAssignments.value.length === 0) return

      // Initialize bulk actions
      selectedAssignments.value.forEach((id) => {
        const assignment = assignments.value.data.find((a) => a.id === id)
        if (assignment) {
          bulkActions.value[id] = "approve"
          bulkReasons.value[id] = ""
        }
      })

      showBulkReviewModal.value = true
    }

    // Close bulk review modal
    const closeBulkReviewModal = () => {
      showBulkReviewModal.value = false
      // Clear bulk actions
      Object.keys(bulkActions.value).forEach((key) => delete bulkActions.value[key])
      Object.keys(bulkReasons.value).forEach((key) => delete bulkReasons.value[key])
    }

    // Process bulk actions
    const processBulkActions = async () => {
      processing.value = true
      try {
        const approveIds = []
        const rejectActions = []

        // Separate approve and reject actions
        Object.keys(bulkActions.value).forEach((id) => {
          if (bulkActions.value[id] === "approve") {
            approveIds.push(parseInt(id))
          } else if (bulkActions.value[id] === "reject") {
            const assignment = assignments.value.data.find((a) => a.id === parseInt(id))
            if (assignment) {
              rejectActions.push({
                id: parseInt(id),
                reason: bulkReasons.value[id] || "Rejected in bulk action",
              })
            }
          }
        })

        // Process approvals
        if (approveIds.length > 0) {
          await axios.post("/order-assignments/bulk-approve", {
            assignment_ids: approveIds,
          })
        }

        // Process rejections
        for (const reject of rejectActions) {
          await axios.patch(`/order-assignments/${reject.id}/approve-reject`, {
            action: "reject",
            rejected_quantity: assignments.value.data.find((a) => a.id === reject.id)?.completed_quantity || 0,
            rejection_reason: reject.reason,
          })
        }

        Swal.fire("Success!", "Bulk actions processed successfully", "success")
        closeBulkReviewModal()
        selectedAssignments.value = []
        selectAll.value = false
        fetchAssignments()
      } catch (error) {
        console.error("Error processing bulk actions:", error)
        Swal.fire("Error!", "Failed to process bulk actions.", "error")
      } finally {
        processing.value = false
      }
    }

    // Delete assignment
    async function deleteAssignment(id) {
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
          await axios.delete(`/order-assignments/${id}`)

          // Remove from selected if it was selected
          selectedAssignments.value = selectedAssignments.value.filter((assignmentId) => assignmentId !== id)

          await fetchAssignments(pagination.value.current_page)
          Swal.fire("Deleted!", "Assignment has been deleted.", "success")
        }
      } catch (error) {
        console.error("Error deleting assignment:", error)
        Swal.fire("Error!", "Failed to delete assignment.", "error")
      } finally {
        deleting.value = null
      }
    }

    // View assignment details
    const viewDetails = (assignment) => {
      // Navigate to order details page
      window.location.href = `/orders/${assignment.order_id}`
    }

    // Handle pagination
    const handlePageChange = (page) => {
      if (page < 1 || page > pagination.value.last_page) return
      filters.value.page = page
      fetchAssignments()
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

    // Load assignments on component mount
    onMounted(() => {
      fetchAssignments()
      fetchArtisans()
      fetchDepartments()
    })

    return {
      assignments,
      artisans,
      departments,
      loading,
      processing,
      deleting,
      pagination,
      filters,
      selectedAssignments,
      selectAll,
      showApprovalModal,
      showBulkReviewModal,
      selectedAssignment,
      approvalForm,
      bulkActions,
      bulkReasons,
      isAllSelected,
      isApprovalFormValid,
      hasCompletedAssignments,
      hasApprovedAssignments,
      selectedAssignmentsData,
      fetchAssignments,
      debouncedFetchAssignments,
      changePage,
      resetFilters,
      toggleSelectAll,
      getStatusClass,
      formatStatus,
      formatDate,
      approveAssignment,
      dispatchAssignment,
      confirmApproval,
      bulkApprove,
      bulkDispatch,
      openBulkReviewModal,
      closeBulkReviewModal,
      processBulkActions,
      deleteAssignment,
      viewDetails,
      handlePageChange,
      getPageNumbers,
    }
  },
}
</script>