<template>
    <div class="container mx-auto py-4">
      <div class="bg-white shadow-md rounded-lg">
        <div class="flex justify-between items-center p-4 bg-gray-100 rounded-t-lg">
          <h3 class="text-xl font-semibold text-gray-900">Transaction Reports</h3>
        </div>
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Report Options -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
              <div class="p-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-700">Report Options</h4>
              </div>
              <div class="p-4">
                <form @submit.prevent="generateReport">
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select 
                      v-model="reportOptions.reportType" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="detailed">Detailed Transactions</option>
                      <option value="summary">Summary by Category</option>
                      <option value="monthly">Monthly Summary</option>
                    </select>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select 
                      v-model="reportOptions.category_id" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">All Categories</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                    <select 
                      v-model="reportOptions.type" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">All Types</option>
                      <option value="income">Income</option>
                      <option value="expense">Expense</option>
                    </select>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select 
                      v-model="reportOptions.dateRange" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      @change="handleDateRangeChange"
                    >
                      <option value="custom">Custom Range</option>
                      <option value="current_month">Current Month</option>
                      <option value="previous_month">Previous Month</option>
                      <option value="current_quarter">Current Quarter</option>
                      <option value="current_year">Current Year</option>
                      <option value="previous_year">Previous Year</option>
                    </select>
                  </div>
                  
                  <div v-if="reportOptions.dateRange === 'custom'" class="mb-4 grid grid-cols-2 gap-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                      <input 
                        type="date" 
                        v-model="reportOptions.start_date" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                      <input 
                        type="date" 
                        v-model="reportOptions.end_date" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Output Format</label>
                    <div class="flex space-x-4">
                      <div class="flex items-center">
                        <input
                          type="radio"
                          id="formatPdf"
                          value="pdf"
                          v-model="reportOptions.format"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <label for="formatPdf" class="ml-2 block text-sm text-gray-900">PDF</label>
                      </div>
                      <div class="flex items-center">
                        <input
                          type="radio"
                          id="formatExcel"
                          value="excel"
                          v-model="reportOptions.format"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <label for="formatExcel" class="ml-2 block text-sm text-gray-900">Excel</label>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <div class="flex space-x-4">
                      <div class="flex items-center">
                        <input
                          type="radio"
                          id="actionDownload"
                          value="download"
                          v-model="reportOptions.action"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <label for="actionDownload" class="ml-2 block text-sm text-gray-900">Download</label>
                      </div>
                      <div class="flex items-center" v-if="reportOptions.format === 'pdf'">
                        <input
                          type="radio"
                          id="actionPreview"
                          value="preview"
                          v-model="reportOptions.action"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <label for="actionPreview" class="ml-2 block text-sm text-gray-900">Preview</label>
                      </div>
                    </div>
                  </div>
                  
                  <button 
                    type="submit" 
                    :disabled="isGenerating"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center"
                  >
                    <span v-if="isGenerating" class="inline-block animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Generate Report
                  </button>
                </form>
              </div>
            </div>
            
            <!-- Saved Reports -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
              <div class="p-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-700">Saved Reports</h4>
              </div>
              <div class="p-4">
                <div class="overflow-x-auto">
                  <table class="w-full text-left">
                    <thead class="bg-gray-200 text-gray-700 uppercase text-xs font-semibold">
                      <tr>
                        <th class="p-2">Report Name</th>
                        <th class="p-2">Type</th>
                        <th class="p-2">Date Range</th>
                        <th class="p-2">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="(report, index) in savedReports" :key="index" class="hover:bg-gray-50 transition duration-200">
                        <td class="p-2">{{ report.name }}</td>
                        <td class="p-2">{{ formatReportType(report.type) }}</td>
                        <td class="p-2">{{ report.dateRange }}</td>
                        <td class="p-2">
                          <div class="flex space-x-1">
                            <button @click="downloadSavedReport(report)" class="text-blue-600 hover:text-blue-800 transition duration-300">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                              </svg>
                            </button>
                            <button @click="deleteSavedReport(index)" class="text-red-600 hover:text-red-800 transition duration-300">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4M9 7h6m-5 4v6m4-6v6"></path>
                              </svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="!savedReports.length">
                        <td colspan="4" class="text-center py-4 text-gray-500">No saved reports found</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            
            <!-- Report Templates -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
              <div class="p-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-700">Report Templates</h4>
              </div>
              <div class="p-4">
                <div class="grid grid-cols-1 gap-4">
                  <div class="border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition duration-200">
                    <h5 class="font-medium text-gray-800 mb-1">Monthly Expense Report</h5>
                    <p class="text-sm text-gray-600 mb-2">Summary of expenses by category for the current month.</p>
                    <button 
                      @click="loadTemplate('monthly_expense')" 
                      class="w-full px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200 transition duration-300 text-sm"
                    >
                      Use Template
                    </button>
                  </div>
                  
                  <div class="border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition duration-200">
                    <h5 class="font-medium text-gray-800 mb-1">Quarterly Summary</h5>
                    <p class="text-sm text-gray-600 mb-2">Quarterly summary of income and expenses.</p>
                    <button 
                      @click="loadTemplate('quarterly_summary')" 
                      class="w-full px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200 transition duration-300 text-sm"
                    >
                      Use Template
                    </button>
                  </div>
                  
                  <div class="border border-gray-200 rounded-md p-3 hover:bg-gray-50 transition duration-200">
                    <h5 class="font-medium text-gray-800 mb-1">Annual Report</h5>
                    <p class="text-sm text-gray-600 mb-2">Comprehensive annual report with all transactions.</p>
                    <button 
                      @click="loadTemplate('annual_report')" 
                      class="w-full px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200 transition duration-300 text-sm"
                    >
                      Use Template
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Save Report Modal -->
      <div v-if="showSaveReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h5 class="text-lg font-semibold text-gray-900">Save Report</h5>
              <button @click="showSaveReportModal = false" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div class="mb-4">
              <label for="reportName" class="block text-sm font-medium text-gray-700 mb-1">Report Name</label>
              <input 
                type="text" 
                id="reportName" 
                v-model="saveReportName" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter a name for this report"
              >
            </div>
            <div class="flex justify-end space-x-3">
              <button 
                @click="showSaveReportModal = false" 
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-300"
              >
                Cancel
              </button>
              <button 
                @click="saveReport" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-300"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, reactive, onMounted } from 'vue';
  import axios from 'axios';
  
  export default {
    setup() {
      const categories = ref([]);
      const showSaveReportModal = ref(false);
      const saveReportName = ref('');
      const isGenerating = ref(false);
      
      // Sample saved reports (in a real app, these would be stored in the database)
      const savedReports = ref([
        {
          name: 'Q1 2023 Expenses',
          type: 'summary',
          dateRange: 'Jan 1, 2023 - Mar 31, 2023',
          created: '2023-04-01',
          options: {
            reportType: 'summary',
            category_id: '',
            type: 'expense',
            dateRange: 'custom',
            start_date: '2023-01-01',
            end_date: '2023-03-31',
            format: 'pdf',
            action: 'download'
          }
        },
        {
          name: 'Monthly Income Report',
          type: 'detailed',
          dateRange: 'Jun 1, 2023 - Jun 30, 2023',
          created: '2023-07-01',
          options: {
            reportType: 'detailed',
            category_id: '',
            type: 'income',
            dateRange: 'custom',
            start_date: '2023-06-01',
            end_date: '2023-06-30',
            format: 'excel',
            action: 'download'
          }
        }
      ]);
      
      const reportOptions = reactive({
        reportType: 'detailed',
        category_id: '',
        type: '',
        dateRange: 'current_month',
        start_date: '',
        end_date: '',
        format: 'pdf',
        action: 'download'
      });
      
      const fetchCategories = async () => {
        try {
          const response = await axios.get('/api/petty-cash/categories');
          categories.value = response.data.categories;
        } catch (error) {
          console.error('Error fetching categories:', error);
        }
      };
      
      const generateReport = async () => {
        isGenerating.value = true;
        
        try {
          // Set date range based on selection
          if (reportOptions.dateRange !== 'custom') {
            const dates = getDateRangeFromOption(reportOptions.dateRange);
            reportOptions.start_date = dates.start_date;
            reportOptions.end_date = dates.end_date;
          }
          
          // Prepare parameters
          const params = {
            category_id: reportOptions.category_id,
            type: reportOptions.type,
            start_date: reportOptions.start_date,
            end_date: reportOptions.end_date,
            report_type: reportOptions.reportType
          };
          
          // Determine endpoint based on format
          let endpoint;
          if (reportOptions.format === 'pdf') {
            endpoint = reportOptions.action === 'download' 
              ? '/api/petty-cash/reports/pdf/download' 
              : '/api/petty-cash/reports/pdf/preview';
          } else {
            endpoint = '/api/petty-cash/export';
          }
          
          // For PDF preview, open in a new tab
          if (reportOptions.format === 'pdf' && reportOptions.action === 'preview') {
            // Create a form and submit it to open in a new tab
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = endpoint;
            form.target = '_blank';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Add parameters
            for (const key in params) {
              if (params[key]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = params[key];
                form.appendChild(input);
              }
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
          } else {
            // For download, use axios
            const response = await axios.post(endpoint, params, {
              responseType: 'blob'
            });
            
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            
            // Set filename based on format
            const filename = reportOptions.format === 'pdf' 
              ? 'petty_cash_report.pdf' 
              : 'petty_cash_transactions.xlsx';
              
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
          }
          
          // Ask if user wants to save this report
          showSaveReportModal.value = true;
        } catch (error) {
          console.error('Error generating report:', error);
          alert('Failed to generate report. Please try again.');
        } finally {
          isGenerating.value = false;
        }
      };
      
      const getDateRangeFromOption = (option) => {
        const now = new Date();
        let startDate, endDate;
        
        switch (option) {
          case 'current_month':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            break;
          case 'previous_month':
            startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            endDate = new Date(now.getFullYear(), now.getMonth(), 0);
            break;
          case 'current_quarter':
            const quarter = Math.floor(now.getMonth() / 3);
            startDate = new Date(now.getFullYear(), quarter * 3, 1);
            endDate = new Date(now.getFullYear(), (quarter + 1) * 3, 0);
            break;
          case 'current_year':
            startDate = new Date(now.getFullYear(), 0, 1);
            endDate = new Date(now.getFullYear(), 11, 31);
            break;
          case 'previous_year':
            startDate = new Date(now.getFullYear() - 1, 0, 1);
            endDate = new Date(now.getFullYear() - 1, 11, 31);
            break;
          default:
            startDate = new Date();
            endDate = new Date();
        }
        
        return {
          start_date: startDate.toISOString().split('T')[0],
          end_date: endDate.toISOString().split('T')[0]
        };
      };
      
      const handleDateRangeChange = () => {
        if (reportOptions.dateRange !== 'custom') {
          const dates = getDateRangeFromOption(reportOptions.dateRange);
          reportOptions.start_date = dates.start_date;
          reportOptions.end_date = dates.end_date;
        }
      };
      
      const saveReport = () => {
        if (!saveReportName.value) {
          alert('Please enter a report name');
          return;
        }
        
        // Get date range description
        let dateRangeText;
        if (reportOptions.dateRange === 'custom') {
          dateRangeText = `${formatDate(reportOptions.start_date)} - ${formatDate(reportOptions.end_date)}`;
        } else {
          const dates = getDateRangeFromOption(reportOptions.dateRange);
          dateRangeText = `${formatDate(dates.start_date)} - ${formatDate(dates.end_date)}`;
        }
        
        // Create saved report object
        const savedReport = {
          name: saveReportName.value,
          type: reportOptions.reportType,
          dateRange: dateRangeText,
          created: new Date().toISOString().split('T')[0],
          options: { ...reportOptions }
        };
        
        // Add to saved reports
        savedReports.value.push(savedReport);
        
        // Close modal and reset name
        showSaveReportModal.value = false;
        saveReportName.value = '';
      };
      
      const downloadSavedReport = (report) => {
        // Copy options from saved report
        Object.assign(reportOptions, report.options);
        
        // Generate report
        generateReport();
      };
      
      const deleteSavedReport = (index) => {
        if (confirm('Are you sure you want to delete this saved report?')) {
          savedReports.value.splice(index, 1);
        }
      };
      
      const loadTemplate = (template) => {
        switch (template) {
          case 'monthly_expense':
            Object.assign(reportOptions, {
              reportType: 'summary',
              category_id: '',
              type: 'expense',
              dateRange: 'current_month',
              format: 'pdf',
              action: 'download'
            });
            break;
          case 'quarterly_summary':
            Object.assign(reportOptions, {
              reportType: 'summary',
              category_id: '',
              type: '',
              dateRange: 'current_quarter',
              format: 'pdf',
              action: 'download'
            });
            break;
          case 'annual_report':
            Object.assign(reportOptions, {
              reportType: 'detailed',
              category_id: '',
              type: '',
              dateRange: 'current_year',
              format: 'pdf',
              action: 'download'
            });
            break;
        }
        
        // Update date fields based on the template
        handleDateRangeChange();
      };
      
      const formatReportType = (type) => {
        switch (type) {
          case 'detailed':
            return 'Detailed Transactions';
          case 'summary':
            return 'Summary by Category';
          case 'monthly':
            return 'Monthly Summary';
          default:
            return type;
        }
      };
      
      const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        }).format(date);
      };
      
      onMounted(() => {
        fetchCategories();
        handleDateRangeChange();
      });
      
      return {
        categories,
        reportOptions,
        savedReports,
        showSaveReportModal,
        saveReportName,
        isGenerating,
        generateReport,
        handleDateRangeChange,
        saveReport,
        downloadSavedReport,
        deleteSavedReport,
        loadTemplate,
        formatReportType,
        formatDate
      };
    }
  };
  </script>