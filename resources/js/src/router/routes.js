export default [
    {
      path: "/",
      name: "login",
      component: () => import("../pages/Login.vue"),
      meta: {
        layout: "full",
        permissions: [],
      },
    },
  
    {
      path: "/users",
      name: "users",
      component: () => import("../pages/Users.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["users-all", "users-view"],
      },
    },
  
    {
      path: "/roles",
      name: "roles",
      component: () => import("../pages/Roles.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["roles-all", "roles-view"],
      },
    },
  
    {
      path: "/permissions",
      name: "permissions",
      component: () => import("../pages/Permissions.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["permissions-all", "permissions-view"],
      },
    },
    // Artisan routes
    {
      path: "/artisans",
      name: "artisans",
      component: () => import("../pages/artisans/Artisans.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["artisans-all", "artisans-view"],
      },
    },
    {
      path: "/artisans/create",
      name: "artisans.create",
      component: () => import("../pages/artisans/ArtisanForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["artisans-all", "artisans-create"],
      },
    },
    {
      path: "/artisans/:id",
      name: "artisans.show",
      component: () => import("../pages/artisans/ArtisanDetails.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["artisans-all", "artisans-view"],
      },
    },
    {
      path: "/artisans/:id/edit",
      name: "artisans.edit",
      component: () => import("../pages/artisans/ArtisanForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["artisans-all", "artisans-edit"],
      },
    },
    // Attendance routes
    {
      path: "/attendance",
      name: "attendance.index",
      component: () => import("../pages/attendance/AttendanceIndex.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["attendance-all", "attendance-view"],
      },
    },
    {
      path: "/attendance/check",
      name: "attendance.check",
      component: () => import("../pages/attendance/AttendanceCheck.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["attendance-all", "attendance-create"],
      },
    },
    {
      path: "/attendance/reports",
      name: "attendance.reports",
      component: () => import("../pages/attendance/AttendanceReports.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["attendance-all", "attendance-view"],
      },
    },
    // {
    //   path: "/attendance/create",
    //   name: "attendance.create",
    //   component: () => import("../pages/attendance/AttendanceForm.vue"),
    //   meta: {
    //     layout: "dashboard",
    //     permissions: ["attendance-all", "attendance-create"],
    //   },
    // },
    // {
    //   path: "/attendance/:id/edit",
    //   name: "attendance.edit",
    //   component: () => import("../pages/attendance/AttendanceForm.vue"),
    //   meta: {
    //     layout: "dashboard",
    //     permissions: ["attendance-all", "attendance-edit"],
    //   },
    // },
    // Department routes
    {
      path: "/departments",
      name: "departments",
      component: () => import("../pages/Departments.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["departments-all", "departments-view"],
      },
    },
    {
      path: "/departments/create",
      name: "departments.create",
      component: () => import("../pages/DepartmentForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["departments-all", "departments-create"],
      },
    },
    {
      path: "/departments/:id/edit",
      name: "departments.edit",
      component: () => import("../pages/DepartmentForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["departments-all", "departments-edit"],
      },
    },
    // Petty Cash routes
    {
      path: "/petty-cash",
      name: "petty-cash.dashboard",
      component: () => import("../pages/petty-cash/Dashboard.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["petty-cash-view"],
      },
    },
    {
      path: "/petty-cash/transactions",
      name: "petty-cash.transactions",
      component: () => import("../pages/petty-cash/Transactions.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["petty-cash-view"],
      },
    },
    {
      path: "/petty-cash/transactions/create",
      name: "petty-cash.transactions.create",
      component: () => import("../pages/petty-cash/TransactionForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["petty-cash-create"],
      },
    },
    {
      path: "/petty-cash/transactions/:id/edit",
      name: "petty-cash.transactions.edit",
      component: () => import("../pages/petty-cash/TransactionForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["petty-cash-edit"],
      },
    },
    {
      path: "/petty-cash/categories",
      name: "petty-cash.categories",
      component: () => import("../pages/petty-cash/Categories.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["petty-cash-manage-categories"],
      },
    },
    // Orders routes
    {
      path: "/orders",
      name: "orders",
      component: () => import("../pages/orders/Orders.vue"),
      meta: {
        requiresAuth: true,
        layout: "dashboard",
        permissions: ["orders-all", "orders-view"],
      },
    },
    {
      path: "/orders/create",
      name: "orders.create",
      component: () => import("../pages/orders/OrderForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-create"],
      },
    },
    {
      path: "/orders/:id",
      name: "orders.show",
      component: () => import("../pages/orders/OrderDetails.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-view"],
      },
    },
    {
      path: "/orders/:id/edit",
      name: "orders.edit",
      component: () => import("../pages/orders/OrderForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-edit"],
      },
    },
    {
      path: "/orders/:id/assign",
      name: "orders.assign",
      component: () => import("../pages/orders/OrderAssignmentForm.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-assign"],
      },
    },
    // New routes for assignments and dispatch
    {
      path: "/orders/assignments",
      name: "orders.assignments",
      component: () => import("../pages/orders/OrderAssignments.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-view"],
      },
    },
    {
      path: "/orders/dispatch",
      name: "orders.dispatch",
      component: () => import("../pages/orders/OrderDispatch.vue"),
      meta: {
        layout: "dashboard",
        permissions: ["orders-all", "orders-view"],
      },
    },
     
  ]
  