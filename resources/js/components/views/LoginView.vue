<template>
  <div class="login-view">
    <Login @login="loginUser" />
  </div>
</template>

<script>
import Login from '@/components/auth/Login.vue';

export default {
  components: {
    Login,
  },
  methods: {
    loginUser() {
      console.log('Logging in...');
      // You can choose to handle additional logic in this parent component if needed.
    },
  },
};
</script>
