<template>
  <div class="container mx-auto py-4 px-4">
    <!-- Date Range Filter -->
    <div class="mb-6 flex justify-end">
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">From:</label>
          <input 
            type="date" 
            v-model="startDate" 
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            @change="fetchDashboardData"
          >
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700">To:</label>
          <input 
            type="date" 
            v-model="endDate" 
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            @change="fetchDashboardData"
          >
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <!-- Total Balance Card -->
      <div class="bg-white rounded-lg shadow-md p-4 border-l-4" :class="balance >= 0 ? 'border-blue-500' : 'border-red-500'">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Balance</h3>
          <div class="p-2 rounded-full" :class="balance >= 0 ? 'bg-blue-100' : 'bg-red-100'">
            <svg class="w-6 h-6" :class="balance >= 0 ? 'text-blue-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold" :class="balance >= 0 ? 'text-blue-600' : 'text-red-600'">
          {{ formatCurrency(balance) }}
        </div>
        <div class="text-sm text-gray-500">Current petty cash balance</div>
      </div>

      <!-- Total Income Card -->
      <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Income</h3>
          <div class="p-2 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalIncome) }}</div>
        <div class="text-sm text-gray-500">Total income in period</div>
      </div>

      <!-- Total Expenses Card -->
      <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Total Expenses</h3>
          <div class="p-2 bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalExpenses) }}</div>
        <div class="text-sm text-gray-500">Total expenses in period</div>
      </div>

      <!-- Net Balance Card -->
      <div class="bg-white rounded-lg shadow-md p-4 border-l-4" :class="netBalance >= 0 ? 'border-green-500' : 'border-red-500'">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-lg font-semibold text-gray-700">Net Balance</h3>
          <div class="p-2 rounded-full" :class="netBalance >= 0 ? 'bg-green-100' : 'bg-red-100'">
            <svg class="w-6 h-6" :class="netBalance >= 0 ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold" :class="netBalance >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ formatCurrency(netBalance) }}
        </div>
        <div class="text-sm text-gray-500">Income - Expenses</div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Monthly Transactions Chart -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-700">Monthly Transactions</h3>
          <div class="flex space-x-2">
            <button 
              v-for="year in availableYears" 
              :key="year"
              @click="selectedYear = year"
              class="px-3 py-1 text-sm rounded-md"
              :class="selectedYear === year ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              {{ year }}
            </button>
          </div>
        </div>
        <div class="h-80">
          <canvas ref="monthlyChart"></canvas>
        </div>
      </div>

      <!-- Category Distribution Chart -->
      <div class="bg-white rounded-lg shadow-md p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Expense Categories</h3>
        <div class="h-80">
          <canvas ref="categoryChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Category-wise Totals -->
    <div class="bg-white rounded-lg shadow-md mb-6">
      <div class="p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-700">Category-wise Totals</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in categoryTotals" :key="category.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ category.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right" :class="category.total_in > 0 ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(category.total_in || category.total_out) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md">
      <div class="flex justify-between items-center p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-700">Recent Transactions</h3>
        <router-link 
          to="/petty-cash/transactions" 
          class="text-blue-600 hover:text-blue-800 transition duration-300 flex items-center"
        >
          View All
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </router-link>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particulars</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(transaction.transaction_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transaction.category.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ transaction.particulars }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ transaction.reference_no || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right" :class="transaction.cash_in > 0 ? 'text-green-600' : 'text-red-600'">
                {{ transaction.cash_in > 0 ? formatCurrency(transaction.cash_in) : formatCurrency(transaction.cash_out) }}
              </td>
            </tr>
            <tr v-if="!recentTransactions.length">
              <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No recent transactions found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

export default {
  setup() {
    const balance = ref(0);
    const totalIncome = ref(0);
    const totalExpenses = ref(0);
    const recentTransactions = ref([]);
    const categoryTotals = ref([]);
    const monthlyChart = ref(null);
    const categoryChart = ref(null);
    
    // Set default dates to current month
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    const startDate = ref(firstDayOfMonth.toISOString().split('T')[0]);
    const endDate = ref(lastDayOfMonth.toISOString().split('T')[0]);
    const selectedYear = ref(today.getFullYear());
    
    const monthlyChartInstance = ref(null);
    const categoryChartInstance = ref(null);

    const netBalance = computed(() => totalIncome.value - totalExpenses.value);
    const availableYears = computed(() => {
      const currentYear = new Date().getFullYear();
      return Array.from({ length: 5 }, (_, i) => currentYear - i);
    });

    const fetchDashboardData = async () => {
      try {
        console.log('Fetching dashboard data with params:', {
          start_date: startDate.value,
          end_date: endDate.value,
          year: selectedYear.value
        });

        const response = await axios.get('/petty-cash/dashboard', {
          params: {
            start_date: startDate.value,
            end_date: endDate.value,
            year: selectedYear.value
          }
        });
        
        console.log('Raw API Response:', response.data);
        
        // Calculate totals from transactions if not provided
        if (response.data.recent_transactions && response.data.recent_transactions.length > 0) {
          const calculatedIncome = response.data.recent_transactions.reduce((sum, t) => sum + (parseFloat(t.cash_in) || 0), 0);
          const calculatedExpenses = response.data.recent_transactions.reduce((sum, t) => sum + (parseFloat(t.cash_out) || 0), 0);
          
          console.log('Calculated from transactions:', {
            income: calculatedIncome,
            expenses: calculatedExpenses
          });
        }

        // Set values with proper parsing and fallbacks
        balance.value = parseFloat(response.data.balance) || 0;
        totalIncome.value = parseFloat(response.data.total_income) || 
                          (response.data.recent_transactions?.reduce((sum, t) => sum + (parseFloat(t.cash_in) || 0), 0) || 0);
        totalExpenses.value = parseFloat(response.data.total_expenses) || 
                            (response.data.recent_transactions?.reduce((sum, t) => sum + (parseFloat(t.cash_out) || 0), 0) || 0);
        
        console.log('Final Values:', {
          balance: balance.value,
          totalIncome: totalIncome.value,
          totalExpenses: totalExpenses.value,
          netBalance: netBalance.value
        });

        recentTransactions.value = response.data.recent_transactions || [];
        categoryTotals.value = response.data.category_totals || [];
        
        // Initialize charts with proper data handling
        if (response.data.monthly_data) {
          initMonthlyChart(response.data.monthly_data);
        }
        
        if (response.data.category_totals) {
          initCategoryChart(response.data.category_totals);
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
        }
      }
    };

    const initMonthlyChart = (data) => {
      if (!data || !Array.isArray(data)) {
        console.warn('Invalid monthly data received');
        return;
      }

      if (monthlyChartInstance.value) {
        monthlyChartInstance.value.destroy();
      }
      
      const ctx = monthlyChart.value.getContext('2d');
      monthlyChartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.map(item => item.month),
          datasets: [
            {
              label: 'Income',
              data: data.map(item => parseFloat(item.income) || 0),
              backgroundColor: 'rgba(34, 197, 94, 0.5)',
              borderColor: 'rgb(34, 197, 94)',
              borderWidth: 1
            },
            {
              label: 'Expenses',
              data: data.map(item => parseFloat(item.expense) || 0),
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
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return formatCurrency(value);
                }
              }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    label += formatCurrency(context.parsed.y);
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    };

    const initCategoryChart = (data) => {
      if (!data || !Array.isArray(data)) {
        console.warn('Invalid category data received');
        return;
      }

      if (categoryChartInstance.value) {
        categoryChartInstance.value.destroy();
      }
      
      const ctx = categoryChart.value.getContext('2d');
      categoryChartInstance.value = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: data.map(item => item.name),
          datasets: [{
            data: data.map(item => parseFloat(item.total_out) || 0),
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
              position: 'right',
              labels: {
                boxWidth: 12,
                padding: 15
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.raw || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                  return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    };

    const formatCurrency = (value) => {
      if (typeof value !== 'number') {
        value = parseFloat(value) || 0;
      }
      return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        currencyDisplay: 'symbol',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value).replace('₹', 'Rs.');
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
      netBalance,
      recentTransactions,
      categoryTotals,
      monthlyChart,
      categoryChart,
      startDate,
      endDate,
      selectedYear,
      availableYears,
      formatCurrency,
      formatDate,
      fetchDashboardData
    };
  }
};
</script>