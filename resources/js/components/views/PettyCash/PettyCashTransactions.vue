<template>
    <div class="min-h-screen bg-gray-100">
      <Navbar />
      <div class="flex">
        <Sidebar class="h-screen" />
        <div class="container mx-auto p-6">
          <h1 class="text-2xl font-bold mb-4">Petty Cash Transactions</h1>
          
          <!-- Action Buttons -->
          <div class="flex justify-between mb-4">
            <button @click="$router.push('/petty-cash/add')" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
              + Add Transaction
            </button>
            <div class="flex space-x-2">
              <input v-model="searchQuery" type="text" placeholder="Search..." class="border p-2 rounded-md" />
              <button @click="exportData" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                Export
              </button>
            </div>
          </div>
          
          <!-- Transactions Table -->
          <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-md">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gray-200">
                  <th class="border px-4 py-2">ID</th>
                  <th class="border px-4 py-2">Date</th>
                  <th class="border px-4 py-2">PAN Bill</th>
                  <th class="border px-4 py-2">Est. Bill</th>
                  <th class="border px-4 py-2">Category</th>
                  <th class="border px-4 py-2">Particular</th>
                  <th class="border px-4 py-2">Cash In</th>
                  <th class="border px-4 py-2">Cash Out</th>
                  <th class="border px-4 py-2">Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(transaction, index) in filteredTransactions" :key="index" class="hover:bg-gray-100">
                  <td class="border px-4 py-2">{{ transaction.id }}</td>
                  <td class="border px-4 py-2">{{ transaction.date }}</td>
                  <td class="border px-4 py-2">{{ transaction.pan_bill }}</td>
                  <td class="border px-4 py-2">{{ transaction.est_bill }}</td>
                  <td class="border px-4 py-2">{{ transaction.category }}</td>
                  <td class="border px-4 py-2">{{ transaction.particular }}</td>
                  <td class="border px-4 py-2">{{ transaction.cashIn }}</td>
                  <td class="border px-4 py-2">{{ transaction.cashOut }}</td>
                  <td class="border px-4 py-2">{{ transaction.balance }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../../Navbar.vue';
  import Sidebar from '../../Sidebar.vue';
  
  export default {
    components: { Navbar, Sidebar },
    data() {
      return {
        searchQuery: '',
        transactions: [
          { id: 1, date: '2025-03-09', pan_bill: '12345', est_bill: '5000', category: 'Office Exp', particular: 'Stationery', cashIn: 0, cashOut: 2000, balance: 48000 },
        ],
      };
    },
    computed: {
      filteredTransactions() {
        return this.transactions.filter(transaction => 
          Object.values(transaction).some(value => 
            String(value).toLowerCase().includes(this.searchQuery.toLowerCase())
          )
        );
      }
    },
    methods: {
      exportData() {
        const csvContent = [
          ['ID', 'Date', 'PAN Bill', 'Est. Bill', 'Category', 'Particular', 'Cash In', 'Cash Out', 'Balance'],
          ...this.transactions.map(t => [t.id, t.date, t.pan_bill, t.est_bill, t.category, t.particular, t.cashIn, t.cashOut, t.balance])
        ].map(e => e.join(",")).join("\n");
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'petty_cash_transactions.csv';
        link.click();
      }
    }
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 900px;
  }
  </style>