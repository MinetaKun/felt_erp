<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Wool Suppliers</h1>
      <button @click="showAddModal = true" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
        Add Supplier
      </button>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Person</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="supplier in suppliers" :key="supplier.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.contact_person }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.phone }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ supplier.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ supplier.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="editSupplier(supplier)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
              <button @click="deleteSupplier(supplier.id)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
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
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_person">Contact Person</label>
              <input v-model="form.contact_person" type="text" id="contact_person" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
              <input v-model="form.phone" type="text" id="phone" required
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
              <input v-model="form.email" type="email" id="email"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
              <textarea v-model="form.address" id="address" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="tax_id">Tax ID</label>
              <input v-model="form.tax_id" type="text" id="tax_id"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="bank_account">Bank Account</label>
              <input v-model="form.bank_account" type="text" id="bank_account"
                     class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">Notes</label>
              <textarea v-model="form.notes" id="notes"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
              <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="form-checkbox">
                <span class="ml-2 text-gray-700">Active</span>
              </label>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModal"
                      class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                Cancel
              </button>
              <button type="submit"
                      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
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
  }
}

const createSupplier = async () => {
  try {
    await axios.post('/wool/suppliers', form.value)
    closeModal()
    fetchSuppliers()
  } catch (error) {
    console.error('Error creating supplier:', error)
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
  } catch (error) {
    console.error('Error updating supplier:', error)
  }
}

const deleteSupplier = async (id) => {
  if (confirm('Are you sure you want to delete this supplier?')) {
    try {
      await axios.delete(`/wool/suppliers/${id}`)
      fetchSuppliers()
    } catch (error) {
      console.error('Error deleting supplier:', error)
    }
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