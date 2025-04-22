<template>
    <div class="container mx-auto px-4 py-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Order Assignments</h1>
        <p class="text-gray-600">Manage and process order assignments</p>
      </div>
  
      <!-- Filters -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <input
              type="text"
              v-model="filters.search"
              @input="debouncedFetchAssignments"
              placeholder="Search by order ID, product, or artisan"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Artisan</label>
            <select
              v-model="filters.artisan_id"
              @change="fetchAssignments"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="">All Artisans</option>
              <option v-for="artisan in artisans" :key="artisan.id" :value="artisan.id">{{ artisan.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
            <select
              v-model="filters.department_id"
              @change="fetchAssignments"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="fetchAssignments"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_production">In Production</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-md mr-2"
            >
              Reset
            </button>
            <button
              @click="fetchAssignments"
              class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>
  
      <!-- Bulk Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <div>
          <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ selectedAssignments.length }} of {{ assignments.data.length }} selected
          </span>
        </div>
        <div>
          <button
            @click="openBulkApprovalModal"
            :disabled="!hasSelectedAssignments"
            :class="[
              'py-2 px-4 rounded-md mr-2',
              !hasSelectedAssignments
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-green-500 hover:bg-green-600 text-white'
            ]"
          >
            Bulk Approve
          </button>
        </div>
      </div>
  
      <!-- Assignments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      @change="toggleSelectAll"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                  </div>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Order ID
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Product
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Artisan
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Assigned
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Approved
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Rejected
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" class="animate-pulse">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  Loading assignments...
                </td>
              </tr>
              <tr v-else-if="assignments.data.length === 0" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No assignments found matching your criteria.
                </td>
              </tr>
              <tr v-for="assignment in assignments.data" :key="assignment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    v-model="selectedAssignments"
                    :value="assignment.id"
                    :disabled="assignment.status !== 'in_production'"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ assignment.order?.order_id || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ assignment.order?.product_name || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ assignment.artisan?.name || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ assignment.assigned_quantity }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ assignment.approved_quantity }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ assignment.rejected_quantity }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusClass(assignment.status)]">
                    {{ formatStatus(assignment.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="assignment.status === 'in_production'"
                    @click="openApprovalModal(assignment)"
                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3"
                  >
                    Approve
                  </button>
                  <!-- <button
                    @click="viewDetails(assignment)"
                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                  >
                    Details
                  </button> -->
                  <button
                    @click="deleteAssignment(assignment.id)"
                    :disabled="deleting === assignment.id"
                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                  >
                    {{ deleting === assignment.id ? 'Deleting...' : 'Delete' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Pagination -->
        <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing
              <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
              to
              <span class="font-medium">
                {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
              </span>
              of
              <span class="font-medium">{{ pagination.total }}</span>
              results
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="handlePageChange(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  :class="[
                    'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium',
                    pagination.current_page === 1
                      ? 'text-gray-300 dark:text-gray-500 cursor-not-allowed'
                      : 'text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                  ]"
                >
                  <span class="sr-only">Previous</span>
                  &larr;
                </button>
                <template v-for="(page, index) in getPageNumbers()" :key="index">
                  <span
                    v-if="page === '...'"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    ...
                  </span>
                  <button
                    v-else
                    @click="handlePageChange(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium',
                      page === pagination.current_page
                        ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-500 text-blue-600 dark:text-blue-200'
                        : 'text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                    ]"
                  >
                    {{ page }}
                  </button>
                </template>
                <button
                  @click="handlePageChange(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  :class="[
                    'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium',
                    pagination.current_page === pagination.last_page
                      ? 'text-gray-300 dark:text-gray-500 cursor-not-allowed'
                      : 'text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                  ]"
                >
                  <span class="sr-only">Next</span>
                  &rarr;
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Individual Approval Modal -->
      <div v-if="showApprovalModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
          </div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                    Approve Assignment
                  </h3>
                  <div class="mt-4">
                    <div class="mb-4">
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order</label>
                      <div class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ selectedAssignment?.order?.order_id }} - {{ selectedAssignment?.order?.product_name }}
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Artisan</label>
                      <div class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ selectedAssignment?.artisan?.name }}
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Quantity</label>
                      <div class="mt-1 text-sm text-gray-900 dark:text-white">
                        {{ selectedAssignment?.assigned_quantity }}
                      </div>
                    </div>
                    <div class="mb-4">
                      <label for="approved_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Approved Quantity</label>
                      <input
                        type="number"
                        id="approved_quantity"
                        v-model="approvalForm.approved_quantity"
                        min="0"
                        :max="selectedAssignment?.assigned_quantity"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      />
                      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Rejected: {{ selectedAssignment?.assigned_quantity - approvalForm.approved_quantity }}
                      </p>
                    </div>
                    <div class="mb-4" v-if="selectedAssignment?.assigned_quantity - approvalForm.approved_quantity > 0">
                      <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Reason</label>
                      <textarea
                        id="rejection_reason"
                        v-model="approvalForm.rejection_reason"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="confirmApproval"
                :disabled="processing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                {{ processing ? 'Processing...' : 'Approve' }}
              </button>
              <button
                type="button"
                @click="showApprovalModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Bulk Approval Modal -->
      <div v-if="showBulkApprovalModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
          </div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                    Bulk Approve Assignments
                  </h3>
                  <div class="mt-4 max-h-96 overflow-y-auto">
                    <div v-for="(assignment, index) in bulkAssignments" :key="assignment.id" class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                      <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                          {{ assignment.order_id }} - {{ assignment.product_name }} ({{ assignment.artisan_name }})
                        </label>
                      </div>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Approved Quantity</label>
                          <input
                            type="number"
                            v-model="assignment.approved_quantity"
                            min="0"
                            :max="assignment.assigned_quantity"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          />
                          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Rejected: {{ assignment.assigned_quantity - assignment.approved_quantity }}
                          </p>
                        </div>
                        <div v-if="assignment.assigned_quantity - assignment.approved_quantity > 0">
                          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Reason</label>
                          <textarea
                            v-model="assignment.rejection_reason"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          ></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="confirmBulkApproval"
                :disabled="processing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                {{ processing ? 'Processing...' : 'Approve All' }}
              </button>
              <button
                type="button"
                @click="showBulkApprovalModal = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
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
      const selectedAssignment = ref(null)
      const approvalForm = ref({
        approved_quantity: 0,
        rejection_reason: "",
      })
  
      // Bulk approval modal
      const showBulkApprovalModal = ref(false)
      const bulkAssignments = ref([])
  
      const isAllSelected = computed(() => {
        if (!assignments.value.data || assignments.value.data.length === 0) return false
  
        const selectableAssignments = assignments.value.data.filter(
          (a) => a.status === 'in_production'
        )
  
        if (selectableAssignments.length === 0) return false
  
        return selectableAssignments.every((a) => selectedAssignments.value.includes(a.id))
      })
  
      const hasSelectedAssignments = computed(() => {
        return selectedAssignments.value.length > 0
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
          selectedAssignments.value = assignments.value.data
            .filter((a) => a.status === 'in_production')
            .map((a) => a.id)
        } else {
          selectedAssignments.value = []
        }
      }
  
      function getStatusClass(status) {
        const classes = {
          pending: "bg-yellow-100 text-yellow-800",
          in_production: "bg-blue-100 text-blue-800",
          approved: "bg-green-100 text-green-800",
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
  
      // Open approval modal for a single assignment
      const openApprovalModal = (assignment) => {
        selectedAssignment.value = assignment
        approvalForm.value = {
          approved_quantity: assignment.assigned_quantity,
          rejection_reason: "",
        }
        showApprovalModal.value = true
      }
  
      // Confirm approval for a single assignment
      const confirmApproval = async () => {
        if (!selectedAssignment.value) return
        
        if (approvalForm.value.approved_quantity > selectedAssignment.value.assigned_quantity) {
          Swal.fire("Error!", "Approved quantity cannot exceed assigned quantity.", "error")
          return
        }
  
        processing.value = true
        try {
          await axios.patch(`/order-assignments/${selectedAssignment.value.id}/approve-reject`, {
            action: "approve",
            approved_quantity: approvalForm.value.approved_quantity,
            rejected_quantity: selectedAssignment.value.assigned_quantity - approvalForm.value.approved_quantity,
            rejection_reason: approvalForm.value.rejection_reason,
          })
          showApprovalModal.value = false
          Swal.fire("Success!", "Assignment approved successfully", "success")
          fetchAssignments(pagination.value.current_page)
        } catch (error) {
          console.error("Error approving assignment:", error)
          Swal.fire("Error!", "Failed to approve assignment.", "error")
        } finally {
          processing.value = false
        }
      }
  
      // Open bulk approval modal
      const openBulkApprovalModal = () => {
        if (selectedAssignments.value.length === 0) return
        
        bulkAssignments.value = assignments.value.data
          .filter(a => selectedAssignments.value.includes(a.id))
          .map(a => ({
            id: a.id,
            artisan_name: a.artisan?.name || 'Unknown',
            order_id: a.order?.order_id || 'Unknown',
            product_name: a.order?.product_name || 'Unknown',
            assigned_quantity: a.assigned_quantity,
            approved_quantity: a.assigned_quantity, // Default to full approval
            rejection_reason: ''
          }))
        
        showBulkApprovalModal.value = true
      }
  
      // Confirm bulk approval
      const confirmBulkApproval = async () => {
        processing.value = true
        try {
          const approvals = bulkAssignments.value.map(assignment => ({
            id: assignment.id,
            approved_quantity: assignment.approved_quantity,
            rejection_reason: assignment.rejection_reason,
          }))
          
          await axios.post("/order-assignments/bulk-approve", {
            assignment_ids: selectedAssignments.value,
            approvals: approvals
          })
          
          showBulkApprovalModal.value = false
          selectedAssignments.value = []
          selectAll.value = false
          Swal.fire("Success!", "Assignments approved successfully", "success")
          fetchAssignments(pagination.value.current_page)
        } catch (error) {
          console.error("Error bulk approving assignments:", error)
          Swal.fire("Error!", "Failed to approve assignments.", "error")
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
            confirmButtonColor: "#EF4444",
            cancelButtonColor: "#6B7280",
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
        fetchAssignments(page)
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
        selectedAssignment,
        approvalForm,
        showBulkApprovalModal,
        bulkAssignments,
        isAllSelected,
        hasSelectedAssignments,
        fetchAssignments,
        debouncedFetchAssignments,
        resetFilters,
        toggleSelectAll,
        getStatusClass,
        formatStatus,
        openApprovalModal,
        confirmApproval,
        openBulkApprovalModal,
        confirmBulkApproval,
        deleteAssignment,
        viewDetails,
        handlePageChange,
        getPageNumbers,
      }
    }
  }
  </script>
  
  