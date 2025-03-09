<template>
    <div class="min-h-screen bg-gray-100">
      <Navbar />
      <div class="flex">
        <Sidebar class="h-screen" />
        <div class="container mx-auto p-6">
          <h1 class="text-2xl font-semibold mb-4">Add Transaction</h1>
          
          <form @submit.prevent="submitTransaction" class="bg-white p-6 rounded-lg shadow-md">
            <!-- ID -->
            <div class="mb-4">
              <label class="block text-gray-700">Transaction ID</label>
              <input v-model="transaction.id" type="text" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Date -->
            <div class="mb-4">
              <label class="block text-gray-700">Date</label>
              <input v-model="transaction.date" type="date" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- PAN Bill -->
            <div class="mb-4">
              <label class="block text-gray-700">PAN Bill</label>
              <input v-model="transaction.pan_bill" type="text" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Estimated Bill -->
            <div class="mb-4">
              <label class="block text-gray-700">Estimated Bill</label>
              <input v-model="transaction.est_bill" type="text" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Category -->
            <div class="mb-4">
              <label class="block text-gray-700">Category</label>
              <input v-model="transaction.category" type="text" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Particular -->
            <div class="mb-4">
              <label class="block text-gray-700">Particular</label>
              <input v-model="transaction.particular" type="text" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Cash In -->
            <div class="mb-4">
              <label class="block text-gray-700">Cash In</label>
              <input v-model="transaction.cash_in" type="number" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Cash Out -->
            <div class="mb-4">
              <label class="block text-gray-700">Cash Out</label>
              <input v-model="transaction.cash_out" type="number" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <!-- Balance -->
            <div class="mb-4">
              <label class="block text-gray-700">Balance</label>
              <input v-model="transaction.balance" type="number" class="w-full p-2 border border-gray-300 rounded-md" required />
            </div>
  
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
              Submit Transaction
            </button>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../../Navbar.vue';
  import Sidebar from '../../Sidebar.vue';
  
  export default {
    name: 'AddTransaction',
    components: { Navbar, Sidebar },
    data() {
      return {
        transaction: {
          id: '',
          date: '',
          pan_bill: '',
          est_bill: '',
          category: '',
          particular: '',
          cash_in: '',
          cash_out: '',
          balance: '',
        },
      };
    },
    methods: {
      async submitTransaction() {
        try {
          const response = await fetch('http://localhost:8000/api/transactions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.transaction),
          });
  
          if (response.ok) {
            alert('Transaction added successfully!');
            this.$router.push('/transactions');
          } else {
            console.error('Failed to add transaction');
          }
        } catch (error) {
          console.error('Error:', error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 600px;
  }
  </style>
  