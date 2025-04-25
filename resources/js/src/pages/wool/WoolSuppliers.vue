<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Wool Suppliers</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your wool suppliers efficiently</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add Supplier
          </button>
        </div>
      </div>

      <div class="p-5">
        <!-- Suppliers Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100">
                <th class="p-3 text-left border-b-2 border-gray-200">Name</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Contact Person</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Phone</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Email</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Status</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-gray-50" :class="{ 'opacity-50': supplier.deleted_at }">
                <td class="p-3 border-t">
                  <div class="flex items-center">
                    {{ supplier.name }}
                    <span v-if="supplier.deleted_at" class="ml-2 px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                      Deleted
                    </span>
                  </div>
                </td>
                <td class="p-3 border-t">{{ supplier.contact_person }}</td>
                <td class="p-3 border-t">{{ supplier.phone }}</td>
                <td class="p-3 border-t">{{ supplier.email }}</td>
                <td class="p-3 border-t">
                  <span :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ supplier.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="p-3 border-t">
                  <button v-if="!supplier.deleted_at" @click="editSupplier(supplier)" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button v-if="!supplier.deleted_at" @click="deleteSupplier(supplier.id)" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                  </button>
                  <button v-if="supplier.deleted_at" @click="restoreSupplier(supplier.id)" class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                    <i class="fas fa-undo"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="suppliers.length === 0">
                <td colspan="6" class="p-3 text-center border-t">No suppliers found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ showEditModal ? 'Edit Supplier' : 'Add New Supplier' }}
          </h3>
          <form @submit.prevent="showEditModal ? updateSupplier() : createSupplier()" class="mt-4">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
              <input v-model="form.name" type="text" id="name" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_person">Contact Person</label>
              <input v-model="form.contact_person" type="text" id="contact_person" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
              <input v-model="form.phone" type="text" id="phone" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
              <input v-model="form.email" type="email" id="email"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
              <textarea v-model="form.address" id="address" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="tax_id">Tax ID</label>
              <input v-model="form.tax_id" type="text" id="tax_id"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="bank_account">Bank Account</label>
              <input v-model="form.bank_account" type="text" id="bank_account"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="form.notes" id="notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>
            <div class="mb-4">
              <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                <span class="ml-2 text-gray-700">Active</span>
              </label>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModal"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                {{ showEditModal ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const suppliers = ref([])
const showAddModal = ref(false)
const showEditModal = ref(false)
const form = ref({
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  tax_id: '',
  bank_account: '',
  notes: '',
  is_active: true
})
const editingId = ref(null)

const fetchSuppliers = async () => {
  try {
    const response = await axios.get('/wool/suppliers')
    suppliers.value = response.data
  } catch (error) {
    console.error('Error fetching suppliers:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load suppliers. Please try again.',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const createSupplier = async () => {
  try {
    await axios.post('/wool/suppliers', form.value)
    closeModal()
    fetchSuppliers()
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Supplier created successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error creating supplier:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to create supplier',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const editSupplier = (supplier) => {
  editingId.value = supplier.id
  form.value = { ...supplier }
  showEditModal.value = true
}

const updateSupplier = async () => {
  try {
    await axios.put(`/wool/suppliers/${editingId.value}`, form.value)
    closeModal()
    fetchSuppliers()
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Supplier updated successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error updating supplier:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to update supplier',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const deleteSupplier = async (id) => {
  try {
    // First check if the supplier exists in our current list
    const supplierToDelete = suppliers.value.find(s => s.id === id)
    if (!supplierToDelete) {
      await fetchSuppliers() // Refresh the list
      Swal.fire({
        icon: 'warning',
        title: 'Supplier Not Found',
        text: 'The supplier you are trying to delete is no longer in the list.',
        timer: 3000,
        showConfirmButton: false
      })
      return
    }

    const result = await Swal.fire({
      title: 'Are you sure?',
      text: `You are about to delete supplier: ${supplierToDelete.name}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    })

    if (result.isConfirmed) {
      try {
        await axios.delete(`/wool/suppliers/${id}`)
        // Remove the supplier from the local list immediately
        suppliers.value = suppliers.value.filter(s => s.id !== id)
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: `Supplier "${supplierToDelete.name}" has been deleted.`,
          timer: 2000,
          showConfirmButton: false
        })
      } catch (error) {
        if (error.response?.status === 410) {
          // Supplier was already soft-deleted
          const deletedAt = error.response?.data?.deleted_at
          const deletedDate = deletedAt ? new Date(deletedAt).toLocaleDateString() : 'previously'
          
          // Remove the supplier from the local list
          suppliers.value = suppliers.value.filter(s => s.id !== id)
          
          Swal.fire({
            icon: 'info',
            title: 'Already Deleted',
            text: `This supplier was deleted ${deletedDate}. It has been removed from the list.`,
            timer: 3000,
            showConfirmButton: false
          })
        } else if (error.response?.status === 404) {
          // Supplier not found
          await fetchSuppliers() // Refresh the list
          Swal.fire({
            icon: 'error',
            title: 'Not Found',
            text: 'This supplier does not exist in the database.',
            timer: 3000,
            showConfirmButton: false
          })
        } else {
          throw error // Re-throw other errors to be caught by outer catch
        }
      }
    }
  } catch (error) {
    console.error('Error deleting supplier:', error)
    let errorMessage = 'Failed to delete supplier. Please try again.'
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.message) {
      errorMessage = error.message
    }

    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: errorMessage,
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const restoreSupplier = async (id) => {
  try {
    await axios.post(`/wool/suppliers/${id}/restore`)
    await fetchSuppliers()
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Supplier restored successfully',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    console.error('Error restoring supplier:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to restore supplier',
      timer: 3000,
      showConfirmButton: false
    })
  }
}

const closeModal = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = {
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    tax_id: '',
    bank_account: '',
    notes: '',
    is_active: true
  }
}

onMounted(() => {
  fetchSuppliers()
})
</script> 