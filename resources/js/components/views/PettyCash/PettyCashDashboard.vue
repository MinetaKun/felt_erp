<template>
    <div class="min-h-screen bg-gray-100">
      <Navbar />
      <div class="flex">
        <Sidebar class="h-screen" />
        <div class="container mx-auto p-6">
          <h1 class="text-2xl font-bold mb-4">Petty Cash Dashboard</h1>
          
          <!-- Summary Cards -->
          <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-green-500 text-white rounded">Total Balance: Rs. {{ totalBalance }}</div>
            <div class="p-4 bg-blue-500 text-white rounded">Total Income: Rs. {{ totalIncome }}</div>
            <div class="p-4 bg-red-500 text-white rounded">Total Expenses: Rs. {{ totalExpenses }}</div>
          </div>
          
          <!-- Chart Section -->
          <div class="mt-6 bg-white p-4 rounded-lg shadow-md">
            <canvas ref="chartCanvas"></canvas>
          </div>
          
          <!-- Recent Transactions -->
          <div class="mt-6 bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-3">Recent Transactions</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead>
                  <tr class="bg-gray-200">
                    <th class="border px-4 py-2">Date</th>
                    <th class="border px-4 py-2">Category</th>
                    <th class="border px-4 py-2">Particular</th>
                    <th class="border px-4 py-2">Cash In</th>
                    <th class="border px-4 py-2">Cash Out</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(transaction, index) in recentTransactions" :key="index" class="hover:bg-gray-100">
                    <td class="border px-4 py-2">{{ transaction.date }}</td>
                    <td class="border px-4 py-2">{{ transaction.category }}</td>
                    <td class="border px-4 py-2">{{ transaction.particular }}</td>
                    <td class="border px-4 py-2">{{ transaction.cashIn }}</td>
                    <td class="border px-4 py-2">{{ transaction.cashOut }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../../Navbar.vue';
  import Sidebar from '../../Sidebar.vue';
  import Chart from 'chart.js/auto';
  
  export default {
    components: { Navbar, Sidebar },
    data() {
      return {
        totalBalance: 50000,
        totalIncome: 70000,
        totalExpenses: 20000,
        recentTransactions: [
          { date: '2025-03-09', category: 'Office Exp', particular: 'Stationery', cashIn: 0, cashOut: 2000 },
          { date: '2025-03-08', category: 'Travel', particular: 'Taxi Fare', cashIn: 0, cashOut: 500 },
        ],
      };
    },
    mounted() {
      this.renderChart();
    },
    methods: {
      renderChart() {
        const ctx = this.$refs.chartCanvas.getContext('2d');
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Income', 'Expenses'],
            datasets: [{
              label: 'Amount',
              data: [this.totalIncome, this.totalExpenses],
              backgroundColor: ['#3b82f6', '#ef4444']
            }]
          }
        });
      }
    }
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 900px;
  }
  </style>
  