// views/PettyCash/Dashboard.vue
<template>
  <div>
    <h1 class="text-2xl font-bold">Petty Cash Dashboard</h1>
    <div class="grid grid-cols-3 gap-4 mt-4">
      <div class="p-4 bg-green-500 text-white rounded">Total Balance: Rs. {{ totalBalance }}</div>
      <div class="p-4 bg-blue-500 text-white rounded">Total Income: Rs. {{ totalIncome }}</div>
      <div class="p-4 bg-red-500 text-white rounded">Total Expenses: Rs. {{ totalExpenses }}</div>
    </div>
    <div class="mt-6">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      totalBalance: 50000,
      totalIncome: 70000,
      totalExpenses: 20000,
    };
  },
};
</script>