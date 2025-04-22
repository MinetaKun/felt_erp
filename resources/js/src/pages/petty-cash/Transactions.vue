<template>
  <div class="p-5">
    <div class="bg-white rounded-lg shadow">
      <div class="p-5 bg-gradient-to-r from-indigo-600 to-blue-500 border-b border-indigo-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="text-white">
          <h3 class="text-xl font-bold">Petty Cash Transactions</h3>
          <p class="text-indigo-100 text-sm mt-1">Manage your petty cash transactions efficiently</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            @click="showImportModal = true"
            class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-50 transition duration-200 shadow-sm cursor-pointer"
          >
            <i class="fas fa-upload mr-2"></i> Import
          </button>
          <button
            @click="exportTransactions"
            class="inline-flex items-center px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition duration-200 shadow-sm"
          >
            <i class="fas fa-download mr-2"></i> Export
          </button>
          <router-link 
            :to="{ name: 'petty-cash.transactions.create' }"
            class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition duration-200 shadow-sm"
          >
            <i class="fas fa-plus mr-2"></i> Add Transaction
          </router-link>
        </div>
      </div>

      <!-- Import Modal -->
      <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Import Transactions</h3>
            <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-500">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="mb-4">
            <h4 class="font-medium text-gray-700 mb-2">CSV File Structure</h4>
            <div class="bg-gray-50 p-4 rounded-md">
              <p class="text-sm text-gray-600 mb-2">Your CSV file should have the following columns in this order:</p>
              <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                <li>Transaction Date (YYYY-MM-DD)</li>
                <li>BS Date (YYYY-MM-DD, optional)</li>
                <li>PAN Bill No (optional)</li>
                <li>Est Bill No (optional)</li>
                <li>Category (must match existing categories)</li>
                <li>Particulars</li>
                <li>Cash In (amount)</li>
                <li>Cash Out (amount)</li>
                <li>VAT % (optional)</li>
                <li>VAT Amount (optional)</li>
                <li>VAT Included (Yes/No, optional)</li>
                <li>Reference No (optional)</li>
                <li>Notes (optional)</li>
              </ul>
            </div>
          </div>

          <div class="mb-4">
            <h4 class="font-medium text-gray-700 mb-2">Example Data</h4>
            <div class="bg-gray-50 p-4 rounded-md overflow-x-auto">
              <pre class="text-sm text-gray-600">
Transaction Date,BS Date,PAN Bill No,Est Bill No,Category,Particulars,Cash In,Cash Out,VAT %,VAT Amount,VAT Included,Reference No,Notes
2024-03-01,,PAN-12345,,Office Supplies,Stationery purchase,,1500.00,13,195.00,Yes,REF-001,Monthly supplies
2024-03-02,,,EST-001,Utilities,Electricity bill,,2500.00,,,,REF-002,Monthly bill
2024-03-03,,,,Income,Client payment,5000.00,,,,,REF-003,Payment received</pre>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload CSV File</label>
            <div class="flex items-center justify-center w-full">
              <label class="flex flex-col w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                  <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                  <p class="mb-2 text-sm text-gray-500">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                  </p>
                  <p class="text-xs text-gray-500">CSV files only</p>
                </div>
                <input 
                  type="file" 
                  class="hidden" 
                  @change="handleFileUpload" 
                  accept=".csv"
                  ref="fileInput"
                />
              </label>
            </div>
            <p v-if="selectedFile" class="mt-2 text-sm text-gray-600">
              Selected file: {{ selectedFile.name }}
            </p>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              @click="showImportModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              @click="importTransactions"
              :disabled="!selectedFile"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Import
            </button>
          </div>
        </div>
      </div>

      <div class="p-5">
        <div class="flex flex-wrap gap-4 mb-4">
          <div class="w-full md:w-1/4">
            <input 
              type="text" 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              placeholder="Search..." 
              v-model="search"
              @input="fetchTransactions"
            >
          </div>
          <div class="w-full md:w-1/4">
            <select 
              class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200" 
              v-model="categoryId" 
              @change="fetchTransactions"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="w-full md:w-1/4">
            <div class="flex items-center gap-2">
              <input
                type="date"
                v-model="dateRangeStart"
                @input="updateDateRange"
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                placeholder="Start Date"
              />
              <span>to</span>
              <input
                type="date"
                v-model="dateRangeEnd"
                @input="updateDateRange"
                class="w-full p-2 border border-gray-300 rounded focus:border-blue-500 focus:ring focus:ring-blue-200"
                placeholder="End Date"
              />
            </div>
          </div>
          <div class="w-full md:w-1/4">
            <button 
              class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
              @click="resetFilters"
            >
              <i class="fas fa-sync-alt mr-1"></i> Reset
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full border-collapse">
            <!-- Table content unchanged -->
            <thead>
              <tr class="bg-gray-100">
                <th class="p-3 text-left border-b-2 border-gray-200">Date</th>
                <th class="p-3 text-left border-b-2 border-gray-200">PAN/Est Bill</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Category</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Particulars</th>
                <th class="p-3 text-right border-b-2 border-gray-200">Cash In</th>
                <th class="p-3 text-right border-b-2 border-gray-200">Cash Out</th>
                <th class="p-3 text-right border-b-2 border-gray-200">VAT</th>
                <th class="p-3 text-right border-b-2 border-gray-200">Running Balance</th>
                <th class="p-3 text-left border-b-2 border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(transaction, index) in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                <td class="p-3 border-t">{{ formatDate(transaction.transaction_date) }}</td>
                <td class="p-3 border-t">
                  <span v-if="transaction.pan_bill_no">PAN: {{ transaction.pan_bill_no }}</span>
                  <span v-if="transaction.est_bill_no">EST: {{ transaction.est_bill_no }}</span>
                </td>
                <td class="p-3 border-t">
                  <span :class="[
                    'inline-block px-2 py-1 text-xs font-semibold text-white rounded',
                    transaction.category.type === 'income' ? 'bg-green-500' : 'bg-red-500'
                  ]">
                    {{ transaction.category.name }}
                  </span>
                </td>
                <td class="p-3 border-t">{{ transaction.particulars }}</td>
                <td class="p-3 text-right text-green-600 border-t">{{ formatCurrency(transaction.cash_in) }}</td>
                <td class="p-3 text-right text-red-600 border-t">{{ formatCurrency(transaction.cash_out) }}</td>
                <td class="p-3 text-right border-t">
                  <span v-if="transaction.vat_amount > 0">
                    {{ formatCurrency(transaction.vat_amount) }} ({{ transaction.vat_percentage }}%)
                  </span>
                  <span v-else>-</span>
                </td>
                <td class="p-3 text-right border-t font-medium" :class="getRunningBalance(index) >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(getRunningBalance(index)) }}
                </td>
                <td class="p-3 border-t">
                  <router-link
                    :to="{ name: 'petty-cash.transactions.edit', params: { id: transaction.id } }"
                    class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 mr-1"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button
                    @click="deleteTransaction(transaction.id)"
                    class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="transactions.data.length === 0">
                <td colspan="10" class="p-3 text-center border-t">No transactions found</td>
              </tr>
              <!-- Totals Row -->
              <tr class="bg-gray-50 font-semibold">
                <td colspan="4" class="p-3 text-right border-t">Totals:</td>
                <td class="p-3 text-right text-green-600 border-t">{{ formatCurrency(totalIncome) }}</td>
                <td class="p-3 text-right text-red-600 border-t">{{ formatCurrency(totalExpenses) }}</td>
                <td class="p-3 text-right border-t">{{ formatCurrency(totalVAT) }}</td>
                <!-- <td class="p-3 text-right border-t" :class="netBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(netBalance) }}
                </td> -->
                <td class="p-3 text-right border-t font-medium" :class="netBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatCurrency(netBalance) }}
                </td>
                <td class="p-3 border-t"></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex justify-center">
          <button
            @click="fetchTransactions(transactions.current_page - 1)"
            :disabled="!transactions.prev_page_url"
            class="px-3 py-1 border rounded-l bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <span class="px-3 py-1 border-t border-b bg-white">
            Page {{ transactions.current_page }} of {{ transactions.last_page }}
          </span>
          <button
            @click="fetchTransactions(transactions.current_page + 1)"
            :disabled="!transactions.next_page_url"
            class="px-3 py-1 border rounded-r bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue"
import axios from "axios"
import Swal from "sweetalert2"

export default {
  setup() {
    const transactions = ref({ data: [], current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
    const categories = ref([])
    const search = ref("")
    const categoryId = ref("")
    const dateRange = ref("")
    const dateRangeStart = ref("")
    const dateRangeEnd = ref("")
    const showImportModal = ref(false)
    const selectedFile = ref(null)
    const fileInput = ref(null)

    // Computed properties for totals
    const totalIncome = computed(() => {
      return transactions.value.data.reduce((sum, transaction) => sum + parseFloat(transaction.cash_in || 0), 0)
    })

    const totalExpenses = computed(() => {
      return transactions.value.data.reduce((sum, transaction) => sum + parseFloat(transaction.cash_out || 0), 0)
    })

    const totalVAT = computed(() => {
      return transactions.value.data.reduce((sum, transaction) => sum + parseFloat(transaction.vat_amount || 0), 0)
    })

    const netBalance = computed(() => {
      return totalIncome.value - totalExpenses.value
    })

    // Function to calculate running balance
    const getRunningBalance = (index) => {
      let balance = 0
      for (let i = 0; i <= index; i++) {
        const transaction = transactions.value.data[i]
        // Add income and subtract expenses
        balance = balance + parseFloat(transaction.cash_in || 0) - parseFloat(transaction.cash_out || 0)
      }
      return balance
    }

    const fetchTransactions = async (page = 1) => {
      try {
        const params = {
          page,
          search: search.value,
          category_id: categoryId.value,
          date_range: dateRange.value,
        }

        const response = await axios.get("/petty-cash", { params })
        transactions.value = response.data.transactions
        categories.value = response.data.categories
      } catch (error) {
        console.error("Error fetching petty cash transactions:", error)
        Swal.fire("Error!", "Failed to load transactions.", "error")
      }
    }

    const exportTransactions = async () => {
      try {
        Swal.fire({
          title: "Exporting...",
          text: "Please wait while we generate your file.",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          },
        })

        const params = {
          search: search.value,
          category_id: categoryId.value,
          date_range: dateRange.value,
        }

        // Use the correct API endpoint
        const response = await axios.get("/petty-cash/export", {
          params,
          responseType: "blob",
        })

        // Check if the response is JSON (error message)
        const contentType = response.headers["content-type"]

        if (contentType.includes("application/json")) {
          // Convert blob to JSON to read the error message
          const text = await response.data.text()
          const error = JSON.parse(text)
          throw new Error(error.message || "Export failed")
        }

        // Process the CSV file
        const blob = new Blob([response.data], { type: contentType })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement("a")
        link.href = url
        link.setAttribute("download", `petty_cash_transactions_${new Date().toISOString().slice(0, 10)}.csv`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        Swal.fire({
          icon: "success",
          title: "Success!",
          text: "Transactions exported successfully.",
          timer: 2000,
          showConfirmButton: false,
        })
      } catch (error) {
        console.error("Error exporting transactions:", error)
        let errorMessage = "Failed to export transactions."

        if (error.response?.status === 403) {
          errorMessage = "You do not have permission to export transactions."
        } else if (error.response?.status === 404) {
          errorMessage = "No transactions found to export."
        } else if (error.response?.data) {
          try {
            // Try to read the response data as text
            const reader = new FileReader()
            reader.onload = () => {
              try {
                const jsonResponse = JSON.parse(reader.result)
                errorMessage = jsonResponse.message || errorMessage
              } catch (e) {
                // Not JSON, use default message
              }

              Swal.fire({
                icon: "error",
                title: "Export Failed",
                text: errorMessage,
              })
            }
            reader.readAsText(error.response.data)
            return // Early return to prevent showing the alert twice
          } catch (e) {
            // If we can't read as text, use the error message
            errorMessage = error.message || errorMessage
          }
        } else {
          errorMessage = error.message || errorMessage
        }

        Swal.fire({
          icon: "error",
          title: "Export Failed",
          text: errorMessage,
        })
      }
    }

    const handleFileUpload = (event) => {
      const file = event.target.files[0]
      if (file) {
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid File Type',
            text: 'Please upload a CSV file.',
          })
          return
        }
        selectedFile.value = file
      }
    }

    const importTransactions = async () => {
      if (!selectedFile.value) {
        Swal.fire({
          icon: 'error',
          title: 'No File Selected',
          text: 'Please select a CSV file to import.',
        })
        return
      }

      const formData = new FormData()
      formData.append('file', selectedFile.value)

      try {
        Swal.fire({
          title: 'Importing...',
          text: 'Please wait while we process your file.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          },
        })

        const response = await axios.post('/petty-cash/import', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })

        if (response.data.errors && response.data.errors.length > 0) {
          Swal.fire({
            title: 'Import Partially Successful',
            html: `
              <div class="text-left">
                <p>${response.data.message}</p>
                <div class="mt-4">
                  <h4 class="font-semibold">Errors:</h4>
                  <ul class="list-disc list-inside mt-2">
                    ${response.data.errors.map(error => `<li>${error}</li>`).join('')}
                  </ul>
                </div>
              </div>
            `,
            icon: 'warning',
          })
        } else {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.data.message,
            timer: 2000,
            showConfirmButton: false,
          })
        }

        // Reset form and close modal
        selectedFile.value = null
        if (fileInput.value) {
          fileInput.value.value = ''
        }
        showImportModal.value = false
        fetchTransactions() // Refresh the list
      } catch (error) {
        console.error('Error importing transactions:', error)
        Swal.fire({
          icon: 'error',
          title: 'Import Failed',
          text: error.response?.data?.error || 'Failed to import transactions.',
        })
      }
    }

    const deleteTransaction = async (id) => {
      try {
        const result = await Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        })

        if (result.isConfirmed) {
          await axios.delete(`/petty-cash/${id}`)
          await fetchTransactions(transactions.value.current_page)
          Swal.fire("Deleted!", "Transaction has been deleted.", "success")
        }
      } catch (error) {
        if (error.response && error.response.status === 403) {
          Swal.fire("Forbidden!", "You do not have permission to delete this transaction.", "error")
        } else {
          Swal.fire("Error!", "Failed to delete transaction.", "error")
        }
      }
    }

    const updateDateRange = () => {
      if (dateRangeStart.value && dateRangeEnd.value) {
        dateRange.value = `${dateRangeStart.value} to ${dateRangeEnd.value}`
      } else {
        dateRange.value = ""
      }
      fetchTransactions()
    }

    const resetFilters = () => {
      search.value = ""
      categoryId.value = ""
      dateRange.value = ""
      dateRangeStart.value = ""
      dateRangeEnd.value = ""
      fetchTransactions()
    }

    const formatCurrency = (value) => {
      return new Intl.NumberFormat("en-NP", {
        style: "currency",
        currency: "NPR",
      }).format(value)
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
      })
    }

    onMounted(() => {
      fetchTransactions()
    })

    return {
      transactions,
      categories,
      search,
      categoryId,
      dateRange,
      dateRangeStart,
      dateRangeEnd,
      showImportModal,
      selectedFile,
      fileInput,
      totalIncome,
      totalExpenses,
      totalVAT,
      netBalance,
      getRunningBalance,
      fetchTransactions,
      exportTransactions,
      handleFileUpload,
      importTransactions,
      deleteTransaction,
      updateDateRange,
      resetFilters,
      formatCurrency,
      formatDate,
    }
  },
}

</script>