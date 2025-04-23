<template>
  <div class="finished-products">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Finished Products</h1>
      <button 
        class="btn btn-primary"
        @click="openCreateModal"
        v-if="hasPermission('inventory-all') || hasPermission('inventory-create')"
      >
        <i class="fas fa-plus"></i> Add Product
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Search</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="filters.search"
                placeholder="Search by name or SKU..."
              >
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Status</label>
              <select class="form-control" v-model="filters.status">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="discontinued">Discontinued</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Category</label>
              <select class="form-control" v-model="filters.category">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>&nbsp;</label>
              <button class="btn btn-secondary w-100" @click="resetFilters">
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td>{{ product.sku }}</td>
                <td>{{ product.name }}</td>
                <td>{{ product.category }}</td>
                <td>
                  <span :class="{'text-danger': product.quantity < product.min_quantity}">
                    {{ product.quantity }}
                  </span>
                </td>
                <td>
                  <span :class="getStatusClass(product.status)">
                    {{ product.status }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button 
                      class="btn btn-sm btn-info"
                      @click="viewProduct(product)"
                      v-if="hasPermission('inventory-all') || hasPermission('inventory-view')"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-primary"
                      @click="editProduct(product)"
                      v-if="hasPermission('inventory-all') || hasPermission('inventory-edit')"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-danger"
                      @click="deleteProduct(product)"
                      v-if="hasPermission('inventory-all') || hasPermission('inventory-delete')"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-muted">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
          </div>
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                  Previous
                </a>
              </li>
              <li class="page-item" v-for="page in pagination.last_page" :key="page"
                  :class="{ active: page === pagination.current_page }">
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                  Next
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Create' }} Product</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveProduct">
              <div class="form-group">
                <label>SKU</label>
                <input type="text" class="form-control" v-model="form.sku" required>
              </div>
              <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" v-model="form.name" required>
              </div>
              <div class="form-group">
                <label>Category</label>
                <select class="form-control" v-model="form.category" required>
                  <option v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" v-model="form.description" rows="3"></textarea>
              </div>
              <div class="form-group">
                <label>Quantity</label>
                <input type="number" class="form-control" v-model="form.quantity" required>
              </div>
              <div class="form-group">
                <label>Minimum Quantity</label>
                <input type="number" class="form-control" v-model="form.min_quantity" required>
              </div>
              <div class="form-group">
                <label>Status</label>
                <select class="form-control" v-model="form.status" required>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="discontinued">Discontinued</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveProduct">
              {{ isEditing ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FinishedProducts',
  data() {
    return {
      products: [],
      categories: [],
      filters: {
        search: '',
        status: '',
        category: ''
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 1,
        to: 1,
        total: 0,
        prev_page_url: null,
        next_page_url: null
      },
      form: {
        id: null,
        sku: '',
        name: '',
        category: '',
        description: '',
        quantity: 0,
        min_quantity: 0,
        status: 'active'
      },
      isEditing: false
    };
  },
  created() {
    this.fetchProducts();
    this.fetchCategories();
  },
  methods: {
    async fetchProducts() {
      try {
        const response = await axios.get('/api/inventory/finished-products', {
          params: {
            page: this.pagination.current_page,
            ...this.filters
          }
        });
        this.products = response.data.data;
        this.pagination = response.data.meta;
      } catch (error) {
        console.error('Error fetching products:', error);
        this.$toast.error('Failed to fetch products');
      }
    },
    async fetchCategories() {
      try {
        const response = await axios.get('/api/inventory/categories');
        this.categories = response.data;
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    },
    openCreateModal() {
      this.isEditing = false;
      this.resetForm();
      $('#productModal').modal('show');
    },
    editProduct(product) {
      this.isEditing = true;
      this.form = { ...product };
      $('#productModal').modal('show');
    },
    async saveProduct() {
      try {
        const url = this.isEditing
          ? `/api/inventory/finished-products/${this.form.id}`
          : '/api/inventory/finished-products';
        
        const method = this.isEditing ? 'put' : 'post';
        
        await axios[method](url, this.form);
        
        this.$toast.success(`Product ${this.isEditing ? 'updated' : 'created'} successfully`);
        $('#productModal').modal('hide');
        this.fetchProducts();
      } catch (error) {
        console.error('Error saving product:', error);
        this.$toast.error('Failed to save product');
      }
    },
    async deleteProduct(product) {
      if (!confirm('Are you sure you want to delete this product?')) {
        return;
      }

      try {
        await axios.delete(`/api/inventory/finished-products/${product.id}`);
        this.$toast.success('Product deleted successfully');
        this.fetchProducts();
      } catch (error) {
        console.error('Error deleting product:', error);
        this.$toast.error('Failed to delete product');
      }
    },
    viewProduct(product) {
      this.$router.push(`/inventory/finished-products/${product.id}`);
    },
    resetForm() {
      this.form = {
        id: null,
        sku: '',
        name: '',
        category: '',
        description: '',
        quantity: 0,
        min_quantity: 0,
        status: 'active'
      };
    },
    resetFilters() {
      this.filters = {
        search: '',
        status: '',
        category: ''
      };
      this.fetchProducts();
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page;
        this.fetchProducts();
      }
    },
    getStatusClass(status) {
      const classes = {
        active: 'badge badge-success',
        inactive: 'badge badge-warning',
        discontinued: 'badge badge-danger'
      };
      return classes[status] || 'badge badge-secondary';
    },
    hasPermission(permission) {
      return this.$store.getters.hasPermission(permission);
    }
  },
  watch: {
    filters: {
      handler() {
        this.pagination.current_page = 1;
        this.fetchProducts();
      },
      deep: true
    }
  }
};
</script>

<style scoped>
.finished-products {
  padding: 20px;
}

.table th {
  background-color: #f8f9fa;
}

.badge {
  padding: 5px 10px;
  border-radius: 4px;
}

.btn-group .btn {
  margin-right: 5px;
}

.btn-group .btn:last-child {
  margin-right: 0;
}
</style> 