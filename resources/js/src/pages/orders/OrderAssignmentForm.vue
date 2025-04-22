<template>
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
      <h2 class="text-2xl font-bold mb-6">Assign Order: {{ order.product_name }}</h2>
      
      <div class="mb-6">
        <div class="flex justify-between mb-4">
          <div>
            <p><span class="font-semibold">Order ID:</span> {{ order.order_id }}</p>
            <p><span class="font-semibold">Total Quantity:</span> {{ order.total_quantity }}</p>
          </div>
          <div>
            <p><span class="font-semibold">Assigned:</span> {{ order.total_quantity - remainingQuantity }}</p>
            <p><span class="font-semibold">Remaining:</span> {{ remainingQuantity }}</p>
          </div>
        </div>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4" v-if="remainingQuantity < 0">
          <p class="text-yellow-700">
            <span class="font-bold">Warning:</span> You have assigned more than the available quantity.
          </p>
        </div>
      </div>
      
      <div class="mb-6">
        <div class="flex items-center mb-4">
          <label class="mr-4 font-medium">Filter by Department:</label>
          <select 
            v-model="selectedDepartment" 
            @change="loadArtisans"
            class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
          </select>
        </div>
      </div>
      
      <div v-if="loading" class="flex justify-center my-8">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <div v-if="assignments.length === 0" class="mb-4">
          <button 
            @click="addAssignment" 
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md"
          >
            Add Assignment
          </button>
        </div>
        
        <div v-for="(assignment, index) in assignments" :key="index" class="mb-6 p-4 border border-gray-200 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Artisan</label>
              <select 
                v-model="assignment.artisan_id" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Artisan</option>
                <option v-for="artisan in availableArtisans" :key="artisan.id" :value="artisan.id">
                  {{ artisan.name }} ({{ artisan.department ? artisan.department.name : 'No Department' }})
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Quantity</label>
              <input 
                type="number" 
                v-model.number="assignment.quantity" 
                min="1" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Status</label>
              <select 
                v-model="assignment.status" 
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="pending">Pending</option>
                <option value="in_production">In Production</option>
                <option value="completed">Completed</option>
                <option value="approved">Approved</option>
                <option value="dispatched">Dispatched</option>
              </select>
            </div>
          </div>
          
          <div class="mt-2">
            <label class="block text-sm font-medium mb-1">Notes (Optional)</label>
            <textarea 
              v-model="assignment.notes" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="2"
            ></textarea>
          </div>
          
          <div class="mt-3 flex justify-end">
            <button 
              @click="removeAssignment(index)" 
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm"
            >
              Remove
            </button>
          </div>
        </div>
        
        <div class="flex justify-between mt-6">
          <button 
            @click="addAssignment" 
            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md"
            :disabled="loading || saving"
          >
            Add Another Assignment
          </button>
          
          <div>
            <button 
              @click="$emit('cancelled')" 
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md mr-2"
            >
              Cancel
            </button>
            
            <button 
              @click="saveAssignments" 
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md"
              :disabled="assignments.length === 0 || saving"
            >
              <span v-if="saving">Saving...</span>
              <span v-else>Save Assignments</span>
            </button>
          </div>
        </div>
        
        <div v-if="Object.keys(errors).length > 0" class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
          <p class="text-red-700 font-bold">Please correct the following errors:</p>
          <ul class="mt-2 list-disc pl-5">
            <li v-for="(error, field) in errors" :key="field" class="text-red-700">
              {{ error[0] }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, computed, onMounted } from "vue"
import axios from "axios"

export default {
  props: {
    order: {
      type: Object,
      required: true,
    },
  },
  emits: ["saved", "cancelled"],
  setup(props, { emit }) {
    const loading = ref(false)
    const saving = ref(false)
    const errors = ref({})
    const availableArtisans = ref([])
    const departments = ref([])
    const selectedDepartment = ref("")
    const assignments = ref([])

    // Computed properties
    const assignedQuantity = computed(() => {
      return assignments.value.reduce((total, assignment) => {
        return total + (assignment.quantity || 0)
      }, 0)
    })

    const remainingQuantity = computed(() => {
      const existingAssignments = props.order.assignments || []
      const existingAssignedQuantity = existingAssignments.reduce((total, assignment) => {
        return total + assignment.assigned_quantity
      }, 0)

      return props.order.total_quantity - existingAssignedQuantity - assignedQuantity.value
    })

    // Load artisans
    const loadArtisans = async () => {
      loading.value = true
      try {
        // First, load departments
        const deptResponse = await axios.get("/departments")
        departments.value = deptResponse.data

        // Then load artisans, filtered by department if selected
        const url = selectedDepartment.value
          ? `/orders/${props.order.id}/available-artisans?department_id=${selectedDepartment.value}`
          : `/orders/${props.order.id}/available-artisans`

        const response = await axios.get(url)
        availableArtisans.value = response.data.data
      } catch (error) {
        console.error("Error loading artisans:", error)
        alert("Failed to load artisans. Please try again.")
      } finally {
        loading.value = false
      }
    }

    // Add a new assignment
    const addAssignment = () => {
      assignments.value.push({
        artisan_id: "",
        quantity: 1,
        status: "pending", // Default status
        notes: "",
      })
    }

    // Remove an assignment
    const removeAssignment = (index) => {
      assignments.value.splice(index, 1)
    }

    // Save assignments
    async function saveAssignments() {
      if (assignments.value.length === 0) return
      if (remainingQuantity.value < 0) {
        alert("You have assigned more than the available quantity")
        return
      }

      saving.value = true
      errors.value = {}

      try {
        const assignmentsData = assignments.value.map((assignment) => ({
          artisan_id: assignment.artisan_id,
          assigned_quantity: assignment.quantity,
          status: assignment.status,
          notes: assignment.notes,
        }))

        const response = await axios.post(`/orders/${props.order.id}/assignments`, {
          assignments: assignmentsData,
        })

        alert("Assignments saved successfully")
        emit("saved", response.data)
      } catch (error) {
        console.error("Error saving assignments:", error)

        if (error.response && error.response.data && error.response.data.errors) {
          errors.value = error.response.data.errors
        } else {
          alert("Failed to save assignments. Please try again.")
        }
      } finally {
        saving.value = false
      }
    }

    // Initialize
    onMounted(() => {
      loadArtisans()
      addAssignment() // Add one assignment row by default
    })

    return {
      loading,
      saving,
      errors,
      availableArtisans,
      departments,
      selectedDepartment,
      assignments,
      assignedQuantity,
      remainingQuantity,
      loadArtisans,
      addAssignment,
      removeAssignment,
      saveAssignments,
    }
  },
}
</script>