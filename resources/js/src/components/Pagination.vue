<template>
    <div class="pagination flex justify-center mt-4">
      <button
        @click="changePage(currentPage - 1)"
        :disabled="currentPage === 1"
        class="px-3 py-1 border rounded-l bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
      >
        Previous
      </button>
      <span class="px-3 py-1 border-t border-b bg-white">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button
        @click="changePage(currentPage + 1)"
        :disabled="currentPage === totalPages"
        class="px-3 py-1 border rounded-r bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
      >
        Next
      </button>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Pagination',
    props: {
      currentPage: {
        type: Number,
        required: true,
      },
      totalItems: {
        type: Number,
        required: true,
      },
      itemsPerPage: {
        type: Number,
        required: true,
      },
    },
    computed: {
      totalPages() {
        return Math.ceil(this.totalItems / this.itemsPerPage);
      },
    },
    methods: {
      changePage(page) {
        if (page >= 1 && page <= this.totalPages) {
          this.$emit('update:currentPage', page);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .pagination button:disabled {
    cursor: not-allowed;
  }
  </style>