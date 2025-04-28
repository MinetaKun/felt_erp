<script setup>
import LayoutRenderer from './layouts/LayoutRenderer.vue';
import ConfirmModal from './components/page/ConfirmModal.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { useTheme } from './composables/useTheme';

useTheme();
</script>

<template>
    <LayoutRenderer />

    <!-- confirm modal -->
    <ConfirmModal />
</template>