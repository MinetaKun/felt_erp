<template>
    <div class="petty-cash-transaction-form">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            {{ isEditMode ? 'Edit Petty Cash Transaction' : 'Create New Petty Cash Transaction' }}
          </h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Transaction Date (AD)</label>
                  <input 
                    type="date" 
                    class="form-control" 
                    v-model="form.transaction_date"
                    required
                  >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Transaction Date (BS)</label>
                  <input 
                    type="date" 
                    class="form-control" 
                    v-model="form.bs_date"
                  >
                </div>
              </div>
            </div>
  
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>PAN Bill No.</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.pan_bill_no"
                    placeholder="Enter PAN bill number"
                  >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>EST Bill No.</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    v-model="form.est_bill_no"
                    placeholder="Enter EST bill number"
                  >
                </div>
              </div>
            </div>
  
            <div class="form-group">
              <label>Category *</label>
              <select 
                class="form-control" 
                v-model="form.category_id"
                required
              >
                <option value="">Select Category</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }} ({{ category.type }})
                </option>
              </select>
            </div>
  
            <div class="form-group">
              <label>Particulars *</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.particulars"
                placeholder="Enter particulars"
                required
              >
            </div>
  
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Cash In (RS)</label>
                  <input 
                    type="number" 
                    class="form-control" 
                    v-model="form.cash_in"
                    placeholder="Enter cash in amount"
                    min="0"
                    step="0.01"
                    @change="handleAmountChange"
                  >
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Cash Out (RS)</label>
                  <input 
                    type="number" 
                    class="form-control" 
                    v-model="form.cash_out"
                    placeholder="Enter cash out amount"
                    min="0"
                    step="0.01"
                    @change="handleAmountChange"
                  >
                </div>
              </div>
            </div>
  
            <div class="row" v-if="form.cash_out > 0">
              <div class="col-md-4">
                <div class="form-group">
                  <label>VAT Percentage</label>
                  <input 
                    type="number" 
                    class="form-control" 
                    v-model="form.vat_percentage"
                    placeholder="Enter VAT percentage"
                    min="0"
                    max="100"
                    step="0.01"
                    @input="calculateVat"
                  >
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>VAT Included?</label>
                  <div class="form-check">
                    <input 
                      type="checkbox" 
                      class="form-check-input" 
                      v-model="form.is_vat_included"
                      id="vatIncludedCheck"
                      @change="calculateVat"
                    >
                    <label class="form-check-label" for="vatIncludedCheck">
                      VAT is included in the amount
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>VAT Amount</label>
                  <input 
                    type="text" 
                    class="form-control" 
                    :value="formatCurrency(form.vat_amount)"
                    readonly
                  >
                </div>
              </div>
            </div>
  
            <div class="form-group">
              <label>Reference No.</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="form.reference_no"
                placeholder="Enter reference number"
              >
            </div>
  
            <div class="form-group">
              <label>Notes</label>
              <textarea 
                class="form-control" 
                v-model="form.notes"
                placeholder="Enter any additional notes"
                rows="3"
              ></textarea>
            </div>
  
            <div class="form-group text-right">
              <button type="button" class="btn btn-default mr-2" @click="cancel">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary">
                {{ isEditMode ? 'Update' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted, computed } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  export default {
    setup() {
      const route = useRoute();
      const router = useRouter();
      const categories = ref([]);
      const form = ref({
        transaction_date: new Date().toISOString().split('T')[0],
        bs_date: '',
        pan_bill_no: '',
        est_bill_no: '',
        category_id: '',
        particulars: '',
        cash_in: 0,
        cash_out: 0,
        vat_percentage: 13,
        vat_amount: 0,
        is_vat_included: false,
        reference_no: '',
        notes: '',
      });
  
      const isEditMode = computed(() => route.name === 'petty-cash.transactions.edit');
  
      const fetchCategories = async () => {
        try {
          const response = await axios.get('/api/petty-cash/categories');
          categories.value = response.data;
        } catch (error) {
          console.error('Error fetching petty cash categories:', error);
          Swal.fire('Error!', 'Failed to load categories.', 'error');
        }
      };
  
      const fetchTransaction = async (id) => {
        try {
          const response = await axios.get(`/api/petty-cash/${id}`);
          form.value = response.data;
        } catch (error) {
          console.error('Error fetching petty cash transaction:', error);
          Swal.fire(
            'Error!',
            'Failed to load transaction data.',
            'error'
          ).then(() => {
            router.push({ name: 'petty-cash.transactions' });
          });
        }
      };
  
      const handleAmountChange = () => {
        if (form.value.cash_in > 0 && form.value.cash_out > 0) {
          Swal.fire(
            'Warning!',
            'You cannot have both cash in and cash out for a single transaction.',
            'warning'
          );
          form.value.cash_out = 0;
        }
        calculateVat();
      };
  
      const calculateVat = () => {
        if (form.value.vat_percentage > 0 && form.value.cash_out > 0) {
          if (form.value.is_vat_included) {
            // VAT is included in the amount
            const amount = form.value.cash_out;
            form.value.vat_amount = amount - (amount / (1 + (form.value.vat_percentage / 100)));
          } else {
            // VAT is added to the amount
            form.value.vat_amount = form.value.cash_out * (form.value.vat_percentage / 100);
          }
        } else {
          form.value.vat_amount = 0;
        }
      };
  
      const submitForm = async () => {
        try {
          calculateVat();
  
          if (isEditMode.value) {
            await axios.put(`/api/petty-cash/${route.params.id}`, form.value);
            Swal.fire(
              'Success!',
              'Transaction updated successfully.',
              'success'
            );
          } else {
            await axios.post('/api/petty-cash', form.value);
            Swal.fire(
              'Success!',
              'Transaction created successfully.',
              'success'
            );
          }
          router.push({ name: 'petty-cash.transactions' });
        } catch (error) {
          console.error('Error saving petty cash transaction:', error);
          Swal.fire(
            'Error!',
            'Failed to save transaction.',
            'error'
          );
        }
      };
  
      const cancel = () => {
        router.push({ name: 'petty-cash.transactions' });
      };
  
      const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-NP', {
          style: 'currency',
          currency: 'NPR',
        }).format(value);
      };
  
      onMounted(() => {
        fetchCategories();
        if (isEditMode.value) {
          fetchTransaction(route.params.id);
        }
      });
  
      return {
        form,
        categories,
        isEditMode,
        handleAmountChange,
        calculateVat,
        submitForm,
        cancel,
        formatCurrency,
      };
    },
  };
  </script>
  
  <style scoped>
  .petty-cash-transaction-form {
    padding: 20px;
  }
  
  .card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .card-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
  }
  
  .card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
  }
  
  .card-body {
    padding: 20px;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-control {
    display: block;
    width: 100%;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }
  
  .form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }
  
  .form-check {
    position: relative;
    display: block;
    padding-left: 1.25rem;
  }
  
  .form-check-input {
    position: absolute;
    margin-top: 0.3rem;
    margin-left: -1.25rem;
  }
  
  .form-check-label {
    margin-bottom: 0;
  }
  
  .btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }
  
  .btn-default {
    color: #212529;
    background-color: #f8f9fa;
    border-color: #f8f9fa;
  }
  
  .btn-default:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
  }
  
  .btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
  }
  
  .btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
  }
  
  .text-right {
    text-align: right;
  }
  
  .row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
  }
  
  .col-md-4,
  .col-md-6 {
    position: relative;
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
  }
  
  @media (min-width: 768px) {
    .col-md-4 {
      flex: 0 0 33.333333%;
      max-width: 33.333333%;
    }
    .col-md-6 {
      flex: 0 0 50%;
      max-width: 50%;
    }
  }
  </style>