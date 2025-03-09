import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import LoginView from '../views/LoginView.vue';
import Create from '../views/Users/Create.vue'; 
import View from '../views/Users/View.vue';
import OrderManagement from '../views/Orders/Index.vue';
import CreateOrder from '../views/Orders/Create.vue'; 
import Profile from '../views/Users/Profile.vue';
import QualityControl from '../views/Quality/Index.vue';
import Suppliers from '../views/Suppliers/Index.vue';

// 🔹 Import Petty Cash Views
import PettyCashDashboard from '../views/PettyCash/PettyCashDashboard.vue';
import PettyCashTransactions from '../views/PettyCash/PettyCashTransactions.vue';
import AddTransaction from '../views/PettyCash/AddTransaction.vue';
import ReportDownload from '../views/PettyCash/ReportDownload.vue';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/users/create',
    name: 'create',
    component: Create,
  },
  {
    path: '/users',
    name: 'view',
    component: View,
  },
  {
    path: '/order-management',
    name: 'orders',
    component: OrderManagement,
  },
  {
    path: '/orders/create',
    name: 'create',
    component: CreateOrder,
  },
  {
    path: '/quality-control',
    name: 'quality',
    component: QualityControl,
  },
  {
    path: "/users/:id",
    name: 'Profile',
    component: Profile,
    props: true,
    meta: { requiresAuth: true },
  },
  {
    path: '/suppliers',
    name: 'Suppliers',
    component: Suppliers,
  },

  // 🔹 Petty Cash Routes
  {
    path: '/petty-cash',
    name: 'PettyCashDashboard',
    component: PettyCashDashboard,
  },
  {
    path: '/petty-cash/transactions',
    name: 'PettyCashTransactions',
    component: PettyCashTransactions,
  },
  {
    path: '/petty-cash/add',
    name: 'AddTransaction',
    component: AddTransaction,
  },
  {
    path: '/petty-cash/reports',
    name: 'ReportDownload',
    component: ReportDownload,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
