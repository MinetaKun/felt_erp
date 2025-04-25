  <li v-if="hasPermission('inventory-all|inventory-view')">
    <router-link
      to="/inventory"
      class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"
    >
      <i class="fas fa-boxes text-gray-500 w-5 h-5"></i>
      <span class="ml-3">Inventory</span>
    </router-link>
  </li>
  <li v-if="hasPermission('inventory-all|inventory-view')">
    <router-link
      to="/inventory/raw-materials"
      class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"
    >
      <i class="fas fa-box text-gray-500 w-5 h-5"></i>
      <span class="ml-3">Raw Materials</span>
    </router-link>
  </li>
  <li v-if="hasPermission('inventory-all|inventory-view')">
    <router-link
      to="/inventory/products"
      class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"
    >
      <i class="fas fa-tshirt text-gray-500 w-5 h-5"></i>
      <span class="ml-3">Products</span>
    </router-link>
  </li>
  <li v-if="hasPermission('artisans-all|artisans-view')">
    <router-link
      to="/artisans"
      class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"
    >
      <i class="fas fa-users text-gray-500 w-5 h-5"></i>
      <span class="ml-3">Artisans</span>
    </router-link>
  </li>
  <li v-if="hasPermission('artisans-all|artisans-view')">
    <router-link
      to="/artisans/productivity"
      class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group"
    >
      <i class="fas fa-chart-line text-gray-500 w-5 h-5"></i>
      <span class="ml-3">Artisan Productivity</span>
    </router-link>
  </li> 