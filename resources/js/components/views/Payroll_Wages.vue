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
                <div class="text-4xl font-semibold">{{ employees.length }}</div>
                <p class="text-lg">Total Employees</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ totalDepartments }}</div>
                <p class="text-lg">Departments</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-yellow-500 to-orange-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ totalPayrollAmount }}</div>
                <p class="text-lg">Total Payroll</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6 rounded-lg shadow-lg text-white">
              <div class="mr-4">
                <div class="text-4xl font-semibold">{{ topPaidEmployees }}</div>
                <p class="text-lg">Top Paid Employees</p>
              </div>
            </div>
          </div>
  
          <!-- Charts Section -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
              <h2 class="text-2xl font-bold mb-4 text-gray-800">Payroll by Department</h2>
              <BarChart :data="departmentPayroll" />
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
              <h2 class="text-2xl font-bold mb-4 text-gray-800">Salary Distribution</h2>
              <PieChart :data="salaryDistribution" />
            </div>
          </div>
  
          <!-- Employee Cards -->
          <h1 class="text-3xl font-semibold text-gray-900 mb-6">Employee List</h1>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="employee in employees"
              :key="employee.id"
              class="bg-white p-6 rounded-lg shadow-xl transition-transform transform hover:scale-105 hover:shadow-2xl"
            >
              <h2 class="text-xl font-semibold text-gray-800">{{ employee.name }}</h2>
              <p class="text-gray-600">Department: {{ employee.department }}</p>
              <p class="text-gray-600">Position: {{ employee.position }}</p>
              <p class="text-gray-600">Salary: ${{ employee.salary }}</p>
              <div class="mt-4 flex space-x-2">
                <button
                  class="px-6 py-3 bg-blue-500 text-white rounded-lg transition duration-200 hover:bg-blue-600 hover:scale-105"
                >
                  View Details
                </button>
                <button
                  class="px-6 py-3 bg-yellow-500 text-white rounded-lg transition duration-200 hover:bg-yellow-600 hover:scale-105"
                >
                  Edit
                </button>
                <button
                  class="px-6 py-3 bg-red-500 text-white rounded-lg transition duration-200 hover:bg-red-600 hover:scale-105"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Navbar from '../Navbar.vue';
  import Sidebar from '../Sidebar.vue';
  // import BarChart from './components/BarChart.vue';
  // import PieChart from './components/PieChart.vue';
  
  export default {
    name: 'Payroll_Wages',
    components: {
      Sidebar,
      Navbar, 
      // BarChart, 
      // PieChart
    },
    data() {
      return {
        employees: [
          { id: 1, name: 'John Doe', department: 'Accounting', position: 'Manager', salary: 5000 },
          { id: 2, name: 'Jane Smith', department: 'Marketing', position: 'Executive', salary: 3500 },
        ], 
        totalDepartments: 4,
        totalPayrollAmount: 15000,
        topPaidEmployees: 1,
        departmentPayroll: {
          labels: ['Accounting', 'Marketing', 'HR', 'Sales'],
          datasets: [
            {
              label: 'Payroll by Department',
              data: [7000, 3500, 2000, 1500],
              backgroundColor: ['#4CAF50', '#FF9800', '#F44336', '#2196F3'],
            },
          ],
        },
        salaryDistribution: {
          labels: ['Low', 'Mid', 'High'],
          datasets: [
            {
              data: [50, 30, 20],
              backgroundColor: ['#2196F3', '#FFC107', '#4CAF50'],
            },
          ],
        },
      };
    },
  };
  </script>
  
  <style scoped>
  /* Additional custom styles for the payroll and wages dashboard */
  </style>
  