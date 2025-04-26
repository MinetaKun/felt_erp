<template>
  <div class="fixed inset-0 z-50 overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
      <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="pointer-events-auto w-screen max-w-md">
          <div class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl">
            <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll py-6">
              <div class="px-4 sm:px-6">
                <div class="flex items-start justify-between">
                  <h2 class="text-base font-semibold leading-6 text-gray-900" id="slide-over-title">
                    {{ isEditing ? 'Edit User' : 'Add New User' }}
                  </h2>
                  <div class="ml-3 flex h-7 items-center">
                    <button
                      type="button"
                      class="relative rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      @click="$emit('close')"
                    >
                      <span class="absolute -inset-2.5"></span>
                      <span class="sr-only">Close panel</span>
                      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <div class="relative mt-6 flex-1 px-4 sm:px-6">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <div class="mt-1">
                      <input
                        type="text"
                        name="name"
                        id="name"
                        v-model="form.name"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                      <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                    </div>
                  </div>

                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="mt-1">
                      <input
                        type="email"
                        name="email"
                        id="email"
                        v-model="form.email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                      <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
                    </div>
                  </div>

                  <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1">
                      <input
                        type="password"
                        name="password"
                        id="password"
                        v-model="form.password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                      <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
                    </div>
                  </div>

                  <div>
                    <label for="roles" class="block text-sm font-medium text-gray-700">Roles</label>
                    <div class="mt-1">
                      <VSelect
                        v-model="form.roles"
                        :options="roles"
                        :reduce="role => role.id"
                        label="name"
                        multiple
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                    </div>
                    <p v-if="errors.roles" class="mt-2 text-sm text-red-600">{{ errors.roles }}</p>
                  </div>

                  <div class="flex justify-end">
                    <button
                      type="button"
                      class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                      @click="$emit('close')"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="ml-4 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                      {{ isEditing ? 'Update' : 'Create' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import axios from 'axios'
import VSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const isEditing = !!props.user
const roles = ref([])

const schema = yup.object({
  name: yup.string().required('Name is required'),
  email: yup.string().email('Invalid email').required('Email is required'),
  password: isEditing ? yup.string() : yup.string().required('Password is required'),
  roles: yup.array().required('At least one role is required')
})

const { handleSubmit, errors, values: form } = useForm({
  validationSchema: schema,
  initialValues: {
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    roles: props.user?.roles?.map(role => role.id) || []
  }
})

onMounted(async () => {
  try {
    const rolesResponse = await axios.get('/api/roles')
    roles.value = rolesResponse.data
  } catch (error) {
    console.error('Error fetching data:', error)
  }
})

const handleSubmit = handleSubmit(async (values) => {
  try {
    const url = isEditing ? `/api/users/${props.user.id}` : '/api/users'
    const method = isEditing ? 'put' : 'post'
    
    await axios[method](url, values)
    emit('saved')
    emit('close')
  } catch (error) {
    console.error('Error saving user:', error)
  }
})
</script> 