<script setup>
import { ref } from 'vue';
import DashboardHeader from './DashboardHeader.vue';
import Sidebar from './Sidebar.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';

const asyncLoading = ref(false);
const isSidebarCollapsed = ref(true); // Start with the sidebar collapsed by default

// Handle the toggle-collapse event from Sidebar.vue
const handleToggleCollapse = (collapsed) => {
    isSidebarCollapsed.value = collapsed;
};
</script>

<template>
    <div class="w-full">
        <!-- Header -->
        <header class="w-full bg-cc-10 h-20 min-h-[80px] sticky top-0 shadow-google z-10">
            <DashboardHeader
                class="h-full container mx-auto flex-between max-w-[1460px] px-4 xl:px-0 gap-4 text-dark-color"
            />
        </header>

        <!-- Main Content -->
        <main class="h-[calc(100vh-80px)] w-full relative flex">
            <!-- Sidebar -->
            <Sidebar @toggle-collapse="handleToggleCollapse" />

            <!-- Page Content -->
            <div
                class="flex-1 container mx-auto max-w-[1172px] px-4 lg:px-0 h-full overflow-y-auto transition-all duration-300"
                :class="isSidebarCollapsed ? 'ml-20' : 'ml-64'"
            >
                <PageLoader :loading="asyncLoading" />

                <RouterView v-slot="{ Component }">
                    <template v-if="Component">
                        <Suspense
                            @pending="asyncLoading = true"
                            @resolve="asyncLoading = false"
                        >
                            <component :is="Component"></component>
                            <template #fallback>
                                <SuspenseFallback />
                            </template>
                        </Suspense>
                    </template>
                </RouterView>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Smooth transition for the main content */
.transition-all {
    transition: margin-left 0.3s ease;
}
</style>