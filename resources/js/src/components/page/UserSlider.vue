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
                existingProfilePhoto.value = props.user.profile_photo || null;
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
        .matches(/^[0-9]{10}$/, 'Phone number is not valid'),
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
    if (props.user && props.user.profile_photo) {
        existingProfilePhoto.value = props.user.profile_photo; // Restore the original photo preview
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

    // Validate the form data
    const { validated, errors } = await runYupValidation(schema, data);
    if (!validated) {
        formErrors.value = errors;
        return;
    }
    formErrors.value = {};

    // Omit unnecessary fields
    const fieldsToBeOmitted = ['confirm_password', 'profile_photo'];
    if (props.user?.id) fieldsToBeOmitted.push('password');
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
            formDataToSend.append(key, data[key] || '');
        }
    });

    // Append the new profile photo if selected
    if (newProfilePhoto.value) {
        formDataToSend.append('profile_photo', newProfilePhoto.value);
    }

    // Send the request
    const response = props.user?.id
        ? await updateUser(props.user?.id, formDataToSend)
        : await createUser(formDataToSend);

    if (response?.id) {
        showToast({
            type: 'success',
            message: `User ${props.user?.id ? 'updated' : 'created'} successfully`,
            duration: 3000,
        });
        userStore.loadUsers();
        emit('user-saved');
        emit('hide');
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
    <Slider :show="show" :title="title" @hide="emit('hide')">
        <AuthorizationFallback :permissions="requiredPermissions">
            <div class="mt-4 space-y-4">
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

                <!-- Profile Photo -->
                <div>
                    <FormLabelError label="Profile Photo" :error="formErrors?.profile_photo">
                        <!-- Display existing or new photo preview -->
                        <div v-if="existingProfilePhoto" class="mt-2 flex items-center space-x-2">
                            <img
                                :src="existingProfilePhoto"
                                alt="Profile Preview"
                                class="w-16 h-16 rounded-full object-cover border-2 border-blue-200"
                            />
                            <button
                                type="button"
                                @click="clearProfilePhoto"
                                class="text-red-500 hover:text-red-700"
                            >
                                Remove
                            </button>
                        </div>
                        <!-- File input for uploading a new photo -->
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleProfilePhotoChange"
                            class="mt-1 block w-full border border-gray-300 rounded-lg p-2"
                        />
                    </FormLabelError>
                </div>

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

                <!-- Role Selection -->
                <FormLabelError label="Add role">
                    <VSelect
                        v-model="selectedRole"
                        :options="roleOptions"
                        label="name"
                        @update:model-value="(role) => onRoleSelect(role)"
                    />
                </FormLabelError>

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

                        <!-- Department Selection -->
                        <select
                            v-model="formData.department_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Select Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>

                        <!-- Submit Button -->
                        <Button
                            :title="user?.id ? 'Update' : 'Save'"
                            key="submit-btn"
                            :loading-title="user?.id ? 'Updating...' : 'Saving...'"
                            class="!w-full"
                            :loading="saving || updating"
                            @click="onSubmit"
                        />
                    </TransitionGroup>
                </div>
            </div>
        </AuthorizationFallback>
    </Slider>
</template>

<style scoped>
/* Add any custom styles if needed */
</style>