<template>
  <div class="alert alert-success" role="alert">
    You are logged in successfully. This is your dashboard.
    <router-link :to="{ name: 'auth.signout' }"
      ><i class="fas fa-sign-out-alt text-danger"></i
    ></router-link>
  </div>
</template>
