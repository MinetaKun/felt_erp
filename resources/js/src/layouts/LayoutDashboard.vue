<script setup>
import { ref, onMounted } from 'vue';
import DashboardHeader from './DashboardHeader.vue';
import Sidebar from './Sidebar.vue';
import PageLoader from './PageLoader.vue';
import SuspenseFallback from './SuspenseFallback.vue';

const asyncLoading = ref(false);
const isSidebarCollapsed = ref(true);
const isReady = ref(false);

const handleToggleCollapse = (collapsed) => {
    isSidebarCollapsed.value = collapsed;
    localStorage.setItem('sidebarCollapsed', collapsed);
};

onMounted(() => {
    // Get the stored value or default to true (collapsed)
    const storedValue = localStorage.getItem('sidebarCollapsed');
    isSidebarCollapsed.value = storedValue !== null ? storedValue === 'true' : true;
    isReady.value = true;
});
</script>

<template>
    <div v-if="isReady" class="w-full min-h-screen flex flex-col">
        <!-- Header -->
        <header class="w-full bg-cc-10 h-20 min-h-[80px] fixed top-0 left-0 z-20 shadow-google">
            <DashboardHeader
                class="h-full container mx-auto flex-between max-w-[1460px] px-4 xl:px-0 gap-4 text-dark-color"
            />
        </header>

        <!-- Main Layout (Sidebar + Content) -->
        <div class="flex w-full pt-20">
            <!-- Sidebar -->
            <Sidebar 
            :collapsed="isSidebarCollapsed" 
            @toggle-collapse="handleToggleCollapse" 
            class="fixed top-20 left-0 z-10" 
            />


            <!-- Page Content -->
            <main
        class="flex-1 container mx-auto max-w-[1172px] px-4 lg:px-0 h-[calc(100vh-80px)] overflow-y-auto transition-all duration-300"
        :class="isSidebarCollapsed ? 'ml-16' : 'ml-64'"
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
            </main>
        </div>
    </div>
    <div v-else class="w-full h-screen flex items-center justify-center">
        <p>Loading...</p>
    </div>
</template>

<style scoped>
.main-content {
    transition: margin-left 0.3s ease;
    margin-left: 4rem; /* matches ml-16 */
}

.main-content.expanded {
    margin-left: 16rem; /* matches ml-64 */
}
</style>