import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../views/Dashboard.vue';
import LoginView from '../views/LoginView.vue';
import About from '../views/About.vue'; // Import About page
import Artisan_Management from '../views/Artisan_Management.vue'; // Import Artisan Management page
import UserRegistration from '../views/UserRegistration.vue'; // Import User Registration page
import Order_Management from '../views/Order_Management.vue';
import Payroll_Wages from '../views/Payroll_Wages.vue';
import Petty_Cash from '../views/Petty_Cash.vue';
import Users_List from '../users_components/UserLists.vue';

const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },  // Protect this route
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/about', // Add the About route
    name: 'about',
    component: About
  },
  {
    path: '/artisan-management', // Add the Artisan Management route
    name: 'artisan',
    component: Artisan_Management
  },
  {
    path: '/user-registration',  // Add the User Registration route
    name: 'user-registration',
    component: UserRegistration
  },
  {
    path: '/order-management',  // Add the Order Management Route
    name: 'order-management',
    component: Order_Management
  },
  {
    path: '/payroll-wages',  // Add the Payroll Wages route
    name: 'payroll-wages',
    component: Payroll_Wages
  },
  {
    path: '/petty-cash',  // Add the Petty Cash route
    name: 'petty-cash',
    component: Petty_Cash
  },
  {
    path: '/users-list',  // Added the User List Route
    name: 'users-list',
    component: Users_List
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
