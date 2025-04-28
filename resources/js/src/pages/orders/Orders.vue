<template>
    <div class="container mx-auto px-4 py-8">
      <div class="bg-white rounded-lg shadow">
        <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div class="text-white">
            <h3 class="text-xl font-bold">Orders Management</h3>
            <p class="text-indigo-100 text-sm mt-1">Manage your orders efficiently</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              @click="openOrderForm"
              class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
            >
              <i class="fas fa-plus mr-2"></i> New Order
            </button>
            <button
              v-if="selectedOrders.length > 0"
              @click="bulkApprove"
              :disabled="processing"
              class="inline-flex items-center px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition duration-200 shadow-sm"
            >
              <span v-if="processing">Processing...</span>
              <span v-else>Approve Selected ({{ selectedOrders.length }})</span>
            </button>
          </div>
        </div>
      </div>
  
      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Search</label>
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search orders..."
              class="w-full p-2 border rounded-md"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="loadOrders"
              class="w-full p-2 border rounded-md"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_production">In Production</option>
              <option value="approved">Approved</option>
              <option value="dispatched">Dispatched</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <input
              type="date"
              v-model="filters.start_date"
              @change="loadOrders"
              class="w-full p-2 border rounded-md"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">End Date</label>
            <input
              type="date"
              v-model="filters.end_date"
              @change="loadOrders"
              class="w-full p-2 border rounded-md"
            />
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md"
          >
            Reset Filters
          </button>
        </div>
      </div>
  
      <!-- Orders Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      :checked="selectAll"
                      @change="toggleSelectAll"
                      class="mr-2"
                    />
                    <span>Order ID</span>
                  </div>
                </th>
                <th class="px-4 py-3 text-left">Product</th>
                <th class="px-4 py-3 text-left">Quantity</th>
                <th class="px-4 py-3 text-left">Due Date</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Progress</th>
                <th class="px-4 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="loading" class="text-center">
                <td colspan="7" class="px-4 py-4">Loading orders...</td>
              </tr>
              <tr v-else-if="orders.length === 0" class="text-center">
                <td colspan="7" class="px-4 py-4">No orders found</td>
              </tr>
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="selectedOrders"
                      :value="order.id"
                      :disabled="order.status === 'dispatched'"
                      class="mr-2"
                    />
                    <span>{{ order.order_id }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">{{ order.product_name }}</td>
                <td class="px-4 py-3">{{ order.total_quantity }}</td>
                <td class="px-4 py-3">
                  <div>{{ formatDate(order.due_date) }}</div>
                  <div class="text-xs text-gray-500">{{ getDaysRemaining(order.due_date) }}</div>
                </td>
                <td class="px-4 py-3">
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      getStatusClass(order.status),
                    ]"
                  >
                    {{ formatStatus(order.status) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div
                      class="bg-teal-500 h-2.5 rounded-full"
                      :style="{ width: getProgressPercentage(order) + '%' }"
                    ></div>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ getProgressPercentage(order) }}% Complete
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex space-x-2">
                    <button
                      @click="viewOrderDetails(order)"
                      class="text-blue-500 hover:text-blue-700"
                      title="View Details"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      @click="editOrder(order)"
                      class="text-yellow-500 hover:text-yellow-700"
                      title="Edit Order"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="assignOrder(order)"
                      class="text-green-500 hover:text-green-700"
                      title="Assign Order"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteOrder(order)"
                      class="text-red-500 hover:text-red-700"
                      title="Delete Order"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Pagination -->
        <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="handlePageChange(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              @click="handlePageChange(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                </span>
                of
                <span class="font-medium">{{ pagination.total }}</span>
                results
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
                <template v-for="(page, index) in getPageNumbers()" :key="index">
                  <button
                    v-if="page !== '...'"
                    @click="handlePageChange(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      page === pagination.current_page
                        ? 'z-10 bg-teal-50 border-teal-500 text-teal-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    ]"
                  >
                    {{ page }}
                  </button>
                  <span
                    v-else
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    ...
                  </span>
                </template>
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
  
      <!-- Order Form Modal -->
      <div v-if="showOrderForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold">{{ isEditMode ? 'Edit Order' : 'Create New Order' }}</h2>
              <button @click="showOrderForm = false" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <OrderForm
              :order="selectedOrder"
              :is-edit-mode="isEditMode"
              @saved="handleOrderSaved"
              @cancelled="showOrderForm = false"
            />
          </div>
        </div>
      </div>
  
      <!-- Order Details Modal -->
      <div v-if="showOrderDetails" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold">Order Details</h2>
              <button @click="showOrderDetails = false" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <OrderDetails
              :order="selectedOrder"
              @close="showOrderDetails = false"
              @edit="editOrder(selectedOrder)"
              @assign="assignOrder(selectedOrder)"
            />
          </div>
        </div>
      </div>
  
      <!-- Assignment Form Modal -->
      <div v-if="showAssignmentForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold">Assign Order</h2>
              <button @click="showAssignmentForm = false" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <OrderAssignmentForm
              :order="selectedOrder"
              @saved="handleAssignmentSaved"
              @cancelled="showAssignmentForm = false"
            />
          </div>
        </div>
      </div>
  
      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
          <div class="p-6">
            <h2 class="text-xl font-bold mb-4">Confirm Delete</h2>
            <p class="mb-6">Are you sure you want to delete this order? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
              <button
                @click="showDeleteConfirm = false"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                Cancel
              </button>
              <button
                @click="deleteOrder"
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md"
              >
                Delete
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
  import OrderForm from "./OrderForm.vue"
  import OrderDetails from "./OrderDetails.vue"
  import OrderAssignmentForm from "./OrderAssignmentForm.vue"
  
  export default {
    components: {
      OrderForm,
      OrderDetails,
      OrderAssignmentForm,
    },
    setup() {
      // State
      const orders = ref([])
      const loading = ref(false)
      const processing = ref(false)
      const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
      })
      const filters = reactive({
        status: "",
        search: "",
        start_date: "",
        end_date: "",
        page: 1,
        per_page: 15,
      })
  
      // Modal/Slider state
      const showOrderForm = ref(false)
      const showOrderDetails = ref(false)
      const showAssignmentForm = ref(false)
      const showDeleteConfirm = ref(false)
      const selectedOrder = ref(null)
      const isEditMode = ref(false)
      const selectedOrders = ref([])
      const selectAll = ref(false)
  
      // Load orders with filters
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
  
          const response = await axios.get(`/orders?${queryParams.toString()}`)
          orders.value = response.data.data
          pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
          }
        } catch (error) {
          console.error("Error loading orders:", error)
          alert("Failed to load orders. Please try again.")
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
          case "approved":
            return "Approved"
          case "dispatched":
            return "Dispatched"
          default:
            return status
        }
      }
  
      // Get status class for styling
      const getStatusClass = (status) => {
        switch (status) {
          case "pending":
            return "bg-yellow-100 text-yellow-800"
          case "in_production":
            return "bg-blue-100 text-blue-800"
          case "approved":
            return "bg-green-100 text-green-800"
          case "dispatched":
            return "bg-purple-100 text-purple-800"
          default:
            return "bg-gray-100 text-gray-800"
        }
      }
  
      // Format date
      const formatDate = (dateString) => {
        if (!dateString) return ""
        const date = new Date(dateString)
        return date.toLocaleDateString()
      }
  
      // Get days remaining until due date
      const getDaysRemaining = (dateString) => {
        if (!dateString) return ""
        const dueDate = new Date(dateString)
        const today = new Date()
        const diffTime = dueDate - today
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
        if (diffDays < 0) {
          return `Overdue by ${Math.abs(diffDays)} days`
        } else if (diffDays === 0) {
          return "Due today"
        } else {
          return `${diffDays} days remaining`
        }
      }
  
      // Calculate progress percentage
      const getProgressPercentage = (order) => {
        if (!order.total_quantity) return 0
  
        // If order is dispatched, return 100%
        if (order.status === "dispatched") return 100
  
        // Calculate based on approved quantity
        const approved = order.total_approved_quantity || 0
        return Math.round((approved / order.total_quantity) * 100)
      }
  
      // Toggle select all
      const toggleSelectAll = () => {
        selectAll.value = !selectAll.value
        if (selectAll.value) {
          selectedOrders.value = orders.value.filter((order) => order.status !== "dispatched").map((order) => order.id)
        } else {
          selectedOrders.value = []
        }
      }
  
      // Bulk approve orders
      const bulkApprove = async () => {
        if (selectedOrders.value.length === 0) return
  
        if (!confirm(`Are you sure you want to approve ${selectedOrders.value.length} orders?`)) {
          return
        }
  
        processing.value = true
        try {
          await axios.post("/orders/bulk-approve", {
            order_ids: selectedOrders.value,
          })
          alert("Orders approved successfully")
          selectedOrders.value = []
          selectAll.value = false
          loadOrders()
        } catch (error) {
          console.error("Error bulk approving orders:", error)
          alert("Failed to approve orders. Please try again.")
        } finally {
          processing.value = false
        }
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
        if (!pagination.value) return []
  
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
  
      // Open order form for creation
      const openOrderForm = () => {
        selectedOrder.value = null
        isEditMode.value = false
        showOrderForm.value = true
      }
  
      // Open order form for editing
      const editOrder = (order) => {
        selectedOrder.value = { ...order }
        isEditMode.value = true
        showOrderForm.value = true
      }
  
      // View order details
      const viewOrderDetails = (order) => {
        selectedOrder.value = { ...order }
        showOrderDetails.value = true
      }
  
      // Open assignment form
      const assignOrder = (order) => {
        selectedOrder.value = { ...order }
        showOrderDetails.value = false
        showAssignmentForm.value = true
      }
  
      // Confirm delete order
      const confirmDeleteOrder = (order) => {
        selectedOrder.value = { ...order }
        showDeleteConfirm.value = true
      }
  
      // Delete order
      const deleteOrder = async () => {
        try {
          await axios.delete(`/orders/${selectedOrder.value.id}`)
          alert("Order deleted successfully")
          loadOrders()
        } catch (error) {
          console.error("Error deleting order:", error)
          alert("Failed to delete order. Please try again.")
        } finally {
          showDeleteConfirm.value = false
        }
      }
  
      // Handle order saved (created or updated)
      const handleOrderSaved = () => {
        showOrderForm.value = false
        loadOrders()
      }
  
      // Handle assignment saved
      const handleAssignmentSaved = () => {
        showAssignmentForm.value = false
        loadOrders()
      }
  
      // Load orders on component mount
      onMounted(() => {
        loadOrders()
      })
  
      return {
        orders,
        loading,
        processing,
        pagination,
        filters,
        showOrderForm,
        showOrderDetails,
        showAssignmentForm,
        showDeleteConfirm,
        selectedOrder,
        isEditMode,
        selectedOrders,
        selectAll,
        loadOrders,
        formatDate,
        formatStatus,
        getStatusClass,
        getDaysRemaining,
        getProgressPercentage,
        toggleSelectAll,
        bulkApprove,
        debounceSearch,
        handlePageChange,
        getPageNumbers,
        resetFilters,
        openOrderForm,
        editOrder,
        viewOrderDetails,
        assignOrder,
        confirmDeleteOrder,
        deleteOrder,
        handleOrderSaved,
        handleAssignmentSaved,
      }
    },
  }
  </script>
  