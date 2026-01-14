<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-body text-center">
          <p class="login-box-msg">Processing Google authentication...</p>
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { MessageModal } from "@func/swal";
import { patchRefreshToken } from "@func/api/auth";
import { useStore } from "vuex";

const store = useStore();
const route = useRoute();
const router = useRouter();

onMounted(async () => {
  try {
    const token = route.query.token;

    if (token) {
      const response = await patchRefreshToken(token);
      localStorage.setItem("token", response.data.token);
      await store.dispatch("verifyAccount");
      router.replace({ name: "dashboard" });
    } else {
      router.replace({ name: "auth.signin" });
    }
  } catch (error) {
    MessageModal("error", "Error", "An error occurred during authentication.");
    router.replace({ name: "auth.signin" });
  }
});
</script>
