<template>
  <div class="login-page">
    <div class="login-box">
      <!-- /.login-logo -->
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <a href="../../index2.html" class="h1"><b>Admin</b>LTE</a>
        </div>
        <div class="card-body">
          <div v-if="status === 'success'" class="alert alert-success" role="alert">
            {{ message }}
          </div>
          <div v-if="status === 'error'" class="alert alert-danger" role="alert">
            {{ message }}
          </div>
          <p class="mb-1">
            <router-link :to="{ name: 'auth.signin' }" class="text-center"
              >Go back to login</router-link
            >
          </p>
          <p class="mb-0">
            <router-link :to="{ name: 'auth.signup' }" class="text-center"
              >Register a new membership</router-link
            >
          </p>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
const route = useRoute();

const status = ref(null);
const message = ref("");

onMounted(async () => {
  try {
    LoadingModal();
    const response = await axios.get(new URL(route.params.api_url));
    status.value = "success";
    message.value = response.data.message;
    MessageModal("success", "Success", response.data.message);
  } catch (error) {
    if (!error.response) {
      return MessageModal("error", "Error", error.message);
    }
    status.value = "error";
    message.value = error.response?.data?.message ?? error.message;
    return CloseModal();
  }
});
</script>
