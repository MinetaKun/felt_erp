<template>
    <div class="container mx-auto p-4">
      <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
  
      <!-- Year Filter -->
      <div class="mb-4">
        <label for="year" class="block text-gray-700 font-bold mb-2">Select Year</label>
        <select v-model="year" id="year" class="p-2 border border-gray-300 rounded">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
  
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="p-4 bg-green-100 rounded shadow">
          <h2 class="text-lg font-bold">Total Income</h2>
          <p class="text-2xl">{{ summary.total_income }}</p>
        </div>
        <div class="p-4 bg-red-100 rounded shadow">
          <h2 class="text-lg font-bold">Total Expense</h2>
          <p class="text-2xl">{{ summary.total_expense }}</p>
        </div>
        <div class="p-4 bg-blue-100 rounded shadow">
          <h2 class="text-lg font-bold">Total Balance</h2>
          <p class="text-2xl">{{ summary.total_balance }}</p>
        </div>
      </div>
  
      <!-- Chart -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Monthly Income vs Expense</h2>
        <BarChart :chart-data="chartData" :options="chartOptions" />
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { Bar } from 'vue-chartjs';
  import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
  
  ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);
  
  export default {
    components: {
      BarChart: Bar,
    },
    data() {
      return {
        year: new Date().getFullYear(),
        years: Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i),
        summary: {
          monthly_data: [],
          total_income: 0,
          total_expense: 0,
          total_balance: 0,
        },
        chartData: {
          labels: [],
          datasets: [],
        },
        chartOptions: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      };
    },
    watch: {
      year() {
        this.fetchSummary();
      },
    },
    mounted() {
      this.fetchSummary();
    },
    methods: {
      async fetchSummary() {
        try {
          const response = await axios.get('/api/dashboard/petty-cash', { params: { year: this.year } });
          this.summary = response.data;
  
          // Prepare chart data
          this.chartData = {
            labels: this.summary.monthly_data.map(item => item.month),
            datasets: [
              {
                label: 'Income',
                backgroundColor: '#4CAF50',
                data: this.summary.monthly_data.map(item => item.income),
              },
              {
                label: 'Expense',
                backgroundColor: '#F44336',
                data: this.summary.monthly_data.map(item => item.expense),
              },
            ],
          };
        } catch (error) {
          console.error('Error fetching summary:', error);
        }
      },
    },
  };
  </script>