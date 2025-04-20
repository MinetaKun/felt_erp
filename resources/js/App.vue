<script setup>
import LayoutRenderer from './src/layouts/LayoutRenderer.vue';
import ConfirmModal from './src/components/page/ConfirmModal.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import useTheme from './src/composables/useTheme';

useTheme();
</script>

<template>
    <LayoutRenderer />

    <!-- confirm modal -->
    <ConfirmModal />
</template>
