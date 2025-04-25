<template>
    <div class="date-range-picker flex gap-2">
      <input
        type="date"
        v-model="localRange.startDate"
        @input="updateRange"
        class="p-2 border border-gray-300 rounded"
      />
      <span>to</span>
      <input
        type="date"
        v-model="localRange.endDate"
        @input="updateRange"
        class="p-2 border border-gray-300 rounded"
      />
    </div>
  </template>
  
  <script>
  export default {
    name: 'DateRangePicker',
    props: {
      modelValue: {
        type: Object,
        default: () => ({ startDate: null, endDate: null }),
      },
    },
    data() {
      return {
        localRange: {
          startDate: this.modelValue?.startDate || null,
          endDate: this.modelValue?.endDate || null,
        },
      };
    },
    watch: {
      modelValue: {
        handler(newValue) {
          this.localRange = { ...newValue };
        },
        deep: true,
      },
    },
    methods: {
      updateRange() {
        this.$emit('update:modelValue', { ...this.localRange });
      },
    },
  };
  </script>
  
  <style scoped>
  .date-range-picker {
    display: flex;
    align-items: center;
  }
  </style>