<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <router-link to="/" class="h1"><b>Admin</b>LTE</router-link>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Enter your new password</p>
          <form @submit.prevent="setNewPassword">
            <div class="input-group mb-3">
              <input
                v-model="user.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': !!userError.password }"
                placeholder="Password"
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <div class="invalid-feedback">
                {{ userError.password }}
              </div>
            </div>
            <div class="input-group mb-3">
              <input
                v-model="user.password_confirmation"
                type="password"
                class="form-control"
                placeholder="Confirm Password"
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-8"></div>
              <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Reset</button>
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
import { reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
const route = useRoute();
const router = useRouter();

const user = reactive({
  password: "",
  password_confirmation: "",
});

const userError = reactive({
  password: "",
});

async function setNewPassword() {
  try {
    LoadingModal();
    const response = await axios.post(new URL(route.params.api_url), user);
    resetData();
    Swal.fire({
      title: response.data.message,
      text: "You can now sign in with your new password.",
      icon: "success",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#d33",
      confirmButtonText: "Go to Sign In",
    }).then((result) => {
      if (result.isConfirmed) {
        router.push({ name: "auth.signin" });
      }
    });
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
