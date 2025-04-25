<template>
  <div class="min-h-screen bg-gray-100">
    <DashboardHeader />
    <div class="flex">
      <DashboardSidebar />
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
    <Notifications />
  </div>
</template>

<script>
import DashboardHeader from './DashboardHeader.vue';
import DashboardSidebar from './DashboardSidebar.vue';
import Notifications from '../components/Notifications.vue';

export default {
  components: {
    DashboardHeader,
    DashboardSidebar,
    Notifications
  }
};
</script> 