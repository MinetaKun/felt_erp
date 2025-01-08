<template>
    <div class="flex h-screen flex-col bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100">
      <!-- Navbar -->
      <Navbar />
      <!-- Main Content Section (Sidebar and Content) -->
      <div class="flex flex-1">
        <!-- Sidebar Component -->
        <Sidebar class="h-full bg-gray-800 text-white" />
  
        <!-- Content -->
        <div class="flex-1 p-6 overflow-auto bg-white">
          <!-- Stats Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ totalEntries }}</div>
                <p class="text-lg">Total Entries</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ totalIncome }}</div>
                <p class="text-lg">Total Cash In</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-yellow-500 to-orange-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ totalExpense }}</div>
                <p class="text-lg">Total Cash Out</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ currentBalance }}</div>
                <p class="text-lg">Current Balance</p>
              </div>
            </div>
          </div>
  
          <!-- Table Section -->
          <h1 class="text-3xl font-semibold text-gray-900 mb-6">Petty Cash Entries</h1>
          <div class="mb-6 flex items-center">
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by Description" 
              class="p-2 border border-gray-300 rounded-md mr-4"
            />
            <select v-model="sortOrder" class="p-2 border border-gray-300 rounded-md">
              <option value="desc">Sort by Date (Descending)</option>
              <option value="asc">Sort by Date (Ascending)</option>
            </select>
          </div>
          <table class="min-w-full table-auto">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Description</th>
                <th class="px-4 py-2">Cash In</th>
                <th class="px-4 py-2">Cash Out</th>
                <th class="px-4 py-2">Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in sortedEntries" :key="entry.id">
                <td class="border px-4 py-2">{{ entry.date }}</td>
                <td class="border px-4 py-2">{{ entry.description }}</td>
                <td class="border px-4 py-2">{{ entry.cashIn }}</td>
                <td class="border px-4 py-2">{{ entry.cashOut }}</td>
                <td class="border px-4 py-2">{{ entry.balance }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../Navbar.vue';
  import Sidebar from '../Sidebar.vue';
  
  export default {
    name: 'Petty_Cash',
    components: {
      Navbar,
      Sidebar
    },
    data() {
      return {
        searchQuery: '',
        sortOrder: 'desc',
        pettyCashEntries: [
          { id: 1, date: '2025-01-01', description: 'Office Supplies', cashIn: 500, cashOut: 0, balance: 500 },
          { id: 2, date: '2025-01-02', description: 'Reimbursement', cashIn: 0, cashOut: 100, balance: 400 },
          { id: 3, date: '2025-01-03', description: 'Lunch Meeting', cashIn: 0, cashOut: 50, balance: 350 },
          { id: 4, date: '2025-01-04', description: 'Petty Cash Fund', cashIn: 1000, cashOut: 0, balance: 1350 },
        ],
      };
    },
    computed: {
      totalEntries() {
        return this.pettyCashEntries.length;
      },
      totalIncome() {
        return this.pettyCashEntries.reduce((total, entry) => total + entry.cashIn, 0);
      },
      totalExpense() {
        return this.pettyCashEntries.reduce((total, entry) => total + entry.cashOut, 0);
      },
      currentBalance() {
        return this.pettyCashEntries.reduce((balance, entry) => balance + entry.balance, 0);
      },
      filteredEntries() {
        return this.pettyCashEntries.filter(entry =>
          entry.description.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      },
      sortedEntries() {
        return this.filteredEntries.sort((a, b) => {
          const dateA = new Date(a.date);
          const dateB = new Date(b.date);
          return this.sortOrder === 'asc' ? dateA - dateB : dateB - dateA;
        });
      },
    },
  };
  </script>
  
  <style scoped>
  /* Additional custom styles for petty cash management dashboard */
  </style>
  