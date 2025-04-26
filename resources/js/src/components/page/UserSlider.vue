<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import Slider from '../ui/Slider.vue';
import FormInput from '../ui/FormInput.vue';
import FormLabelError from '../ui/FormLabelError.vue';
import VSelect from 'vue-select';
import Button from '../ui/Button.vue';
import AuthorizationFallback from '../../components/page/AuthorizationFallback.vue';
import axios from 'axios';

import useRoleStore from '../../store/useRoleStore';
import useUserStore from '../../store/useUserStore';
import useValidation from '../../composables/useValidation';
import useHttpRequest from '../../composables/useHttpRequest';
import useUtils from '../../composables/useUtils';
import useModalToast from '../../composables/useModalToast';

import * as yup from 'yup';

const props = defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    user: {
        type: [Object, null],
        default: () => null,
    },
});
const emit = defineEmits(['hide', 'user-saved']);

const userStore = useUserStore();
const roleStore = useRoleStore();
const {
    store: createUser,
    saving,
    update: updateUser,
    updating,
} = useHttpRequest('/users');
const { runYupValidation } = useValidation();
const { omitPropsFromObject } = useUtils();
const { showToast } = useModalToast();

// Computed permissions for authorization
const requiredPermissions = computed(() => {
    if (!props.user?.id) return ['users-all', 'users-create'];
    else return ['users-all', 'users-edit'];
});

// Computed title for the slider
const title = computed(() =>
    props.user ? `Update user "${props.user?.name}"` : 'Add new user',
);

// Initial form data structure
const initialFormData = () => {
    return {
        name: null,
        email: null,
        password: null,
        confirm_password: null,
        roles: [],
        phone_number: null,
        profile_photo: null,
        department_id: null,
    };
};

// Define formData and formErrors first
const formData = ref(initialFormData());
const formErrors = ref({});

// Reactive state for the existing profile photo (for display purposes)
const existingProfilePhoto = ref(null);

// Reactive state for the new file selected by the user
const newProfilePhoto = ref(null);

// Watch for changes to the show prop to initialize the form
watch(
    () => props.show,
    () => {
        if (props.show) {
            if (props.user?.id) {
                // Editing mode: populate the form with the user's data
                formData.value = Object.entries(initialFormData()).reduce(
                    (r, [key, val]) => {
                        if (props.user[key]) {
                            return { ...r, [key]: props.user[key] };
                        }
                        return { ...r, [key]: val };
                    },
                    {},
                );
                // Set the existing profile photo for preview
                existingProfilePhoto.value = props.user.profile_photo_url || null;
            } else {
                // Creating mode: reset the form
                formData.value = initialFormData();
                formErrors.value = {};
                existingProfilePhoto.value = null;
            }
            newProfilePhoto.value = null; // Reset the new file
        }
    },
);

// Computed role options for the VSelect component
const roleOptions = computed(() => {
    const formDataRoleIds = formData.value.roles.map((role) =>
        role?.id?.toString(),
    );
    return roleStore.roles.filter(
        (role) =>
            !formDataRoleIds.includes(role?.id?.toString()) &&
            role?.name !== 'super-admin',
    );
});

const selectedRole = ref(null);
const onRoleSelect = (role) => {
    formData.value = {
        ...formData.value,
        roles: [role].concat(formData.value.roles),
    };
    selectedRole.value = null;
};
const onRoleRemove = (role) => {
    const updatedRoles = formData.value.roles.filter(
        (fRole) => fRole?.id?.toString() !== role?.id?.toString(),
    );

    formData.value = {
        ...formData.value,
        roles: updatedRoles,
    };
};

// Validation schema using Yup (now that formData is defined)
const schema = yup.object().shape({
    name: yup.string().nullable().required(),
    email: yup.string().email().nullable().required(),
    password: yup
        .string()
        .nullable()
        .test('password-test', '', (value, { createError }) => {
            if (props.user?.id) return true;

            if (!value)
                return createError({ message: 'Password is a required field' });
            if (value !== formData.value.confirm_password)
                return createError({ message: "Password doesn't match" });

            return true;
        }),
    phone_number: yup
        .string()
        .nullable()
        .test('phone-test', 'Phone number must be exactly 10 digits', (value) => {
            if (!value) return true;
            const cleaned = value.replace(/\D/g, '');
            return cleaned.length === 10;
        }),
    profile_photo: yup.mixed().nullable(),
});

// Handle file selection for profile photo
const handleProfilePhotoChange = (event) => {
    const file = event.target.files[0];
    newProfilePhoto.value = file || null;
    if (file) {
        // Display a preview of the new file
        const reader = new FileReader();
        reader.onload = (e) => {
            existingProfilePhoto.value = e.target.result; // Update the preview
        };
        reader.readAsDataURL(file);
    }
};

// Clear the selected file
const clearProfilePhoto = () => {
    newProfilePhoto.value = null;
    if (props.user && props.user.profile_photo_url) {
        existingProfilePhoto.value = props.user.profile_photo_url; // Restore the original photo URL
    } else {
        existingProfilePhoto.value = null;
    }
};

// Submit the form (now that formData is defined)
const onSubmit = async () => {
    if (saving.value || updating.value) return;

    let data = {
        ...formData.value,
        roles: formData.value.roles?.map((role) => role?.id) || [],
    };

    // Clean phone number before validation
    if (data.phone_number) {
        data.phone_number = data.phone_number.replace(/\D/g, '');
    }

    // Validate the form data
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    // Omit unnecessary fields
    const fieldsToBeOmitted = ['confirm_password'];
    if (props.user?.id) {
        // For update, only omit password if it's empty
        if (!data.password) {
            fieldsToBeOmitted.push('password');
        }
    } else {
        // For create, always omit profile_photo as it's handled separately
        fieldsToBeOmitted.push('profile_photo');
    }
    data = omitPropsFromObject(data, fieldsToBeOmitted);

    // Handle file upload if a new profile photo is selected
    const formDataToSend = new FormData();
    Object.keys(data).forEach((key) => {
        if (key === 'roles') {
            // Append each role ID individually
            data[key].forEach((roleId) => {
                formDataToSend.append('roles[]', roleId);
            });
        } else {
            // Always append the value, even if it's null or empty
            formDataToSend.append(key, data[key] || '');
        }
    });

    // Append the new profile photo if selected
    if (newProfilePhoto.value) {
        formDataToSend.append('profile_photo', newProfilePhoto.value);
    }

    // Log the data being sent
    console.log('Sending update data:', Object.fromEntries(formDataToSend));

    try {
        let response;
        if (props.user?.id) {
            // For update, use axios directly with PUT method
            response = await axios.put(`users/${props.user.id}`, formDataToSend, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            console.log('Update response:', response.data);
        } else {
            // For create, use the store function
            response = await createUser(formDataToSend);
        }

        if (response?.data?.id) {
            showToast({
                type: 'success',
                message: `User ${props.user?.id ? 'updated' : 'created'} successfully`,
                duration: 3000,
            });
            await userStore.loadUsers(); // Make sure to await the users reload
            emit('user-saved');
            emit('hide');
        }
    } catch (error) {
        console.error('Error saving user:', error);
        showToast({
            type: 'error',
            message: error.response?.data?.message || 'Failed to save user',
            duration: 3000,
        });
    }
};

const departments = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get('/departments');
    departments.value = response.data || [];
  } catch (error) {
    console.error('Error fetching departments:', error);
  }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('hide')"></div>
            
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full max-w-4xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-2xl font-semibold leading-6 text-gray-900">
                                    {{ title }}
                                </h3>
                                <button
                                    type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                                    @click="emit('hide')"
                                >
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <AuthorizationFallback :permissions="requiredPermissions">
                                <form @submit.prevent="onSubmit" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Basic Information -->
                                        <div>
                                            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Basic Information</h2>
                                            
                                            <!-- Name -->
                                            <FormInput
                                                v-model="formData.name"
                                                :focus="show"
                                                label="Name"
                                                :error="formErrors?.name"
                                                required
                                            />

                                            <!-- Email -->
                                            <FormInput
                                                v-model="formData.email"
                                                label="Email"
                                                :error="formErrors?.email"
                                                required
                                            />

                                            <!-- Phone Number -->
                                            <FormInput
                                                v-model="formData.phone_number"
                                                label="Phone Number"
                                                :error="formErrors?.phone_number"
                                            />

                                            <!-- Password (for new users only) -->
                                            <template v-if="!user?.id">
                                                <FormInput
                                                    v-model="formData.password"
                                                    label="Password"
                                                    type="password"
                                                    :error="formErrors?.password"
                                                    required
                                                />

                                                <FormInput
                                                    v-model="formData.confirm_password"
                                                    type="password"
                                                    label="Confirm password"
                                                    required
                                                />
                                            </template>
                                        </div>

                                        <!-- Documents and Roles -->
                                        <div>
                                            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Documents & Roles</h2>
                                            
                                            <!-- Profile Photo -->
                                            <div class="mb-6">
                                                <FormLabelError label="Profile Photo" :error="formErrors?.profile_photo">
                                                    <div class="mt-1 flex items-center">
                                                        <div v-if="existingProfilePhoto" class="relative">
                                                            <img
                                                                :src="existingProfilePhoto"
                                                                class="h-32 w-32 object-cover rounded-md"
                                                                alt="Profile Preview"
                                                            />
                                                            <button
                                                                type="button"
                                                                @click="clearProfilePhoto"
                                                                class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 transform translate-x-1/2 -translate-y-1/2"
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div v-else class="flex justify-center items-center h-32 w-32 bg-gray-100 rounded-md">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                        <input
                                                            type="file"
                                                            @change="handleProfilePhotoChange"
                                                            accept="image/*"
                                                            class="ml-4"
                                                        />
                                                    </div>
                                                </FormLabelError>
                                            </div>

                                            <!-- Role Selection -->
                                            <div class="mb-4">
                                                <FormLabelError label="Add role">
                                                    <VSelect
                                                        v-model="selectedRole"
                                                        :options="roleOptions"
                                                        label="name"
                                                        @update:model-value="(role) => onRoleSelect(role)"
                                                    />
                                                </FormLabelError>
                                            </div>

                                            <!-- Display Selected Roles -->
                                            <div class="w-full space-y-3">
                                                <FormLabelError v-if="formData.roles?.length" label="User roles" />

                                                <TransitionGroup
                                                    tag="ul"
                                                    name="edit-list"
                                                    class="relative space-y-3"
                                                >
                                                    <li
                                                        v-for="role in formData.roles"
                                                        :key="role.id"
                                                        class="shadow-google rounded-sm"
                                                    >
                                                        <div
                                                            class="p-4 flex-between w-full dark:bg-gray-800/60 rounded-sm border border-[#e6e6e6] dark:border-gray-700"
                                                        >
                                                            <div class="flex-1">{{ role.name }}</div>
                                                            <span
                                                                class="text-sm cursor-pointer text-red-500 dark:text-red-300"
                                                                @click="onRoleRemove(role)"
                                                            >
                                                                <svg
                                                                    viewBox="0 0 24 24"
                                                                    width="24"
                                                                    height="24"
                                                                    stroke="currentColor"
                                                                    stroke-width="2"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    class="css-i6dzq1"
                                                                >
                                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </li>
                                                </TransitionGroup>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            @click="emit('hide')"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        >
                                            Cancel
                                        </button>
                                        <Button
                                            :title="user?.id ? 'Update' : 'Save'"
                                            :loading-title="user?.id ? 'Updating...' : 'Saving...'"
                                            class="!w-auto"
                                            :loading="saving || updating"
                                            @click="onSubmit"
                                        />
                                    </div>
                                </form>
                            </AuthorizationFallback>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any custom styles if needed */
</style>