<template>
  <nav class="bg-gray-800 text-white px-4 py-2 flex justify-between items-center">
    <!-- Logo or App Name -->
    <div class="text-white font-bold text-xl">Admin Panel</div>

    <!-- Hamburger Menu for Mobile -->
    <button
      class="text-white md:hidden"
      @click="toggleMobileMenu"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M4 6h16M4 12h16m-7 6h7"
        />
      </svg>
    </button>

    <!-- Profile Section -->
    <div class="relative hidden md:flex">
      <!-- Profile Button -->
      <button
        class="flex items-center space-x-2 text-white focus:outline-none"
        @click="toggleDropdown"
      >
        <img
          src="https://via.placeholder.com/40"
          alt="Profile"
          class="w-8 h-8 rounded-full"
        />
        <span>Kishor</span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div
        v-show="isDropdownOpen"
        class="absolute right-0 mt-2 bg-white shadow-lg rounded-md py-2 w-40"
      >
        <a
          href="#"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900"
        >
          Profile
        </a>
        <a
          href="#"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900"
        >
          Settings
        </a>
        <a
          href="#"
          @click="logout"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-gray-900"
        >
          Logout
        </a>
      </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div
      v-show="isMobileMenuOpen"
      class="absolute top-12 left-0 bg-gray-800 w-full flex flex-col md:hidden"
    >
      <a
        href="#"
        class="block px-4 py-2 text-white hover:bg-gray-700"
      >
        Profile
      </a>
      <a
        href="#"
        class="block px-4 py-2 text-white hover:bg-gray-700"
      >
        Settings
      </a>
      <!-- Mobile Logout Button -->
      <a
        href="#"
        @click="logout"
        class="block px-4 py-2 text-white hover:bg-gray-700"
      >
        Logout
      </a>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
  methods: {
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen;
    },
    logout() {
      // Clear authentication data (e.g., tokens)
      localStorage.removeItem('authToken');  // Remove token from localStorage or sessionStorage
      // Redirect to login page
      this.$router.push('/login');  // Use Vue Router to navigate to login page
    }
  },
  data() {
    return {
      isDropdownOpen: false,
      isMobileMenuOpen: false,
    };
  },
};
</script>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";

// Manage dropdown and mobile menu state
const isDropdownOpen = ref(false);
const isMobileMenuOpen = ref(false);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Handle click outside to close dropdown
const closeDropdown = (event) => {
  const dropdown = document.querySelector('.relative');
  if (!dropdown.contains(event.target)) {
    isDropdownOpen.value = false;
  }
};

// Add event listener for clicks
onMounted(() => {
  document.addEventListener("click", closeDropdown);
});

// Cleanup on unmount
onUnmounted(() => {
  document.removeEventListener("click", closeDropdown);
});
</script>

<style scoped>
[v-cloak] {
  display: none;
}
</style>
