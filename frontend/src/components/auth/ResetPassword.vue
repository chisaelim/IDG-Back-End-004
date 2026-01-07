<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <router-link to="/" class="h1"><b>Admin</b>LTE</router-link>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Enter your email to receive a password reset link</p>
          <form @submit.prevent="requestResetLink">
            <div class="input-group mb-3">
              <input
                v-model="user.email"
                :class="{ 'is-invalid': !!userError.email }"
                type="email"
                class="form-control"
                placeholder="Email"
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
              <div class="invalid-feedback">
                {{ userError.email }}
              </div>
            </div>
            <div class="row">
              <div class="col-8"></div>
              <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Send Link</button>
              </div>
            </div>
          </form>

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
      </div>
    </div>
  </div>
</template>
<script setup>
import { useRouter } from "vue-router";
import { reactive } from "vue";
import { postRequestResetLink } from "@func/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
const router = useRouter();

const user = reactive({
  email: "",
});

const userError = reactive({
  email: "",
});

async function requestResetLink() {
  try {
    LoadingModal();
    const response = await postRequestResetLink(user);
    resetData();
    MessageModal("success", "Success", response.data.message);
  } catch (error) {
    if (!error.response) {
      return MessageModal("error", "Error", error.message);
    }
    if (error.response.status === 422) {
      Object.keys(userError).forEach((key) => {
        userError[key] = error.response.data.errors[key]
          ? error.response.data.errors[key][0]
          : "";
      });
      return CloseModal();
    }
    return MessageModal("error", "Error", error.response.data.message);
  }
}

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetData() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}
</script>
