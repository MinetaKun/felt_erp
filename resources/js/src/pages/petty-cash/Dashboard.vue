<template>
  <div class="container mx-auto py-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Summary Cards -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Balance</h3>
          <div class="p-2 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold" :class="balance >= 0 ? 'text-blue-600' : 'text-red-600'">
          {{ formatCurrency(balance) }}
        </div>
        <div class="text-sm text-gray-500">Current petty cash balance</div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Income</h3>
          <div class="p-2 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalIncome) }}</div>
        <div class="text-sm text-gray-500">Total income this month</div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Expenses</h3>
          <div class="p-2 bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalExpenses) }}</div>
        <div class="text-sm text-gray-500">Total expenses this month</div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Monthly Transactions</h3>
        <div class="h-64">
          <canvas ref="monthlyChart"></canvas>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Expense Categories</h3>
        <div class="h-64">
          <canvas ref="categoryChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md">
      <div class="flex justify-between items-center p-4 bg-gray-100 rounded-t-lg">
        <h3 class="text-lg font-semibold text-gray-700">Recent Transactions</h3>
        <router-link 
          to="/petty-cash/transactions" 
          class="text-blue-600 hover:text-blue-800 transition duration-300 flex items-center"
        >
          View All
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </router-link>
      </div>
      <div class="p-4">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-gray-200 text-gray-700 uppercase text-xs font-semibold">
              <tr>
                <th class="p-3">Date</th>
                <th class="p-3">Category</th>
                <th class="p-3">Particulars</th>
                <th class="p-3">Reference</th>
                <th class="p-3 text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50 transition duration-200">
                <td class="p-3">{{ formatDate(transaction.transaction_date) }}</td>
                <td class="p-3">{{ transaction.category.name }}</td>
                <td class="p-3">{{ transaction.particulars }}</td>
                <td class="p-3">{{ transaction.reference_no || '-' }}</td>
                <td class="p-3 text-right" :class="transaction.cash_in > 0 ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.cash_in > 0 ? formatCurrency(transaction.cash_in) : formatCurrency(transaction.cash_out) }}
                </td>
              </tr>
              <tr v-if="!recentTransactions.length">
                <td colspan="5" class="text-center py-4 text-gray-500">No recent transactions found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

export default {
  setup() {
    const balance = ref(0);
    const totalIncome = ref(0);
    const totalExpenses = ref(0);
    const recentTransactions = ref([]);
    const monthlyChart = ref(null);
    const categoryChart = ref(null);
    
    const monthlyChartInstance = ref(null);
    const categoryChartInstance = ref(null);

    const fetchDashboardData = async () => {
      try {
        const response = await axios.get('/api/petty-cash/dashboard');
        
        balance.value = response.data.balance;
        totalIncome.value = response.data.total_income;
        totalExpenses.value = response.data.total_expenses;
        recentTransactions.value = response.data.recent_transactions;
        
        // Initialize charts after data is loaded
        initMonthlyChart(response.data.monthly_data);
        initCategoryChart(response.data.category_data);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    };

    const initMonthlyChart = (data) => {
      if (monthlyChartInstance.value) {
        monthlyChartInstance.value.destroy();
      }
      
      const ctx = monthlyChart.value.getContext('2d');
      monthlyChartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Income',
              data: data.income,
              backgroundColor: 'rgba(34, 197, 94, 0.5)',
              borderColor: 'rgb(34, 197, 94)',
              borderWidth: 1
            },
            {
              label: 'Expenses',
              data: data.expenses,
              backgroundColor: 'rgba(239, 68, 68, 0.5)',
              borderColor: 'rgb(239, 68, 68)',
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    };

    const initCategoryChart = (data) => {
      if (categoryChartInstance.value) {
        categoryChartInstance.value.destroy();
      }
      
      const ctx = categoryChart.value.getContext('2d');
      categoryChartInstance.value = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.values,
            backgroundColor: [
              'rgba(59, 130, 246, 0.7)',
              'rgba(16, 185, 129, 0.7)',
              'rgba(245, 158, 11, 0.7)',
              'rgba(239, 68, 68, 0.7)',
              'rgba(139, 92, 246, 0.7)',
              'rgba(236, 72, 153, 0.7)',
              'rgba(75, 85, 99, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right'
            }
          }
        }
      });
    };

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(value);
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      }).format(date);
    };

    onMounted(() => {
      fetchDashboardData();
    });

    return {
      balance,
      totalIncome,
      totalExpenses,
      recentTransactions,
      monthlyChart,
      categoryChart,
      formatCurrency,
      formatDate
    };
  }
};
</script>