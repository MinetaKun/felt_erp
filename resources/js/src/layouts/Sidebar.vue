<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const isCollapsed = ref(true);
const emit = defineEmits(['toggle-collapse']);
const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    emit('toggle-collapse', isCollapsed.value);
};
</script>

<template>
    <div>
        <aside
            class="bg-gray-100 shadow-lg h-[calc(100vh-80px)] fixed left-0 top-20 transition-all duration-300 z-10"
            :class="{ 'w-20': isCollapsed, 'w-64': !isCollapsed }"
        >
            <nav class="p-4">
                <div class="flex justify-end mb-4">
                    <button
                        @click="toggleCollapse"
                        class="p-2 bg-gray-200 rounded-full shadow-lg hover:bg-gray-300 transition-all"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                v-if="isCollapsed"
                                fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Users Management Section -->
                <h2
                    class="text-lg font-bold text-gray-800 mb-4"
                    :class="{ 'text-center': isCollapsed }"
                >
                    {{ isCollapsed ? "M" : "Users Management" }}
                </h2>
                <ul class="space-y-2">
                    <li>
                        <RouterLink
                            :to="{ name: 'users' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"
                                />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Users</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'roles' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Roles</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'permissions' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Permissions</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'artisans' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"
                                />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Artisans</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'departments' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                                />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Departments</span>
                        </RouterLink>
                    </li>
                </ul>

                <!-- Order Management Section -->
                <h2
                        class="text-lg font-bold text-gray-800 mb-4 mt-6"
                        :class="{ 'text-center': isCollapsed }"
                    >
                        {{ isCollapsed ? "O" : "Order Management" }}
                    </h2>
                   

                <!-- Petty Cash Section -->
                <h2
                    class="text-lg font-bold text-gray-800 mb-4 mt-6"
                    :class="{ 'text-center': isCollapsed }"
                >
                    {{ isCollapsed ? "P" : "Petty Cash" }}
                </h2>
                <ul class="space-y-2">
                    <li>
                        <RouterLink
                            :to="{ name: 'petty-cash.dashboard' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Petty Cash Dashboard</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'petty-cash.transactions' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6zm2 2h4v2H8V6zm0 4h4v2H8v-2zm0 4h4v2H8v-2z" clip-rule="evenodd" />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Transactions</span>
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="{ name: 'petty-cash.categories' }"
                            class="flex items-center px-4 py-2 text-gray-800 hover:bg-gray-200 rounded-lg"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            <span v-if="!isCollapsed" class="ml-2">Categories</span>
                        </RouterLink>
                    </li>
                </ul>
            </nav>
        </aside>
    </div>
</template>

<style scoped>
aside {
    transition: width 0.3s ease;
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.main-content {
    margin-left: 16rem;
    transition: margin-left 0.3s ease;
}

.main-content.collapsed {
    margin-left: 5rem;
}
</style>