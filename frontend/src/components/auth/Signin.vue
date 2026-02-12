<template>
  <div class="login-page">
    <div class="login-box">
      <!-- /.login-logo -->
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <router-link to="/" class="h1"><b>Admin</b>LTE</router-link>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Sign in to start your session</p>
          <form @submit.prevent="signIn">
            <div class="input-group mb-3">
              <input
                v-model="user.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': !!userError.email }"
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
            <div class="row">
              <div class="col-8"></div>
              <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
              </div>
            </div>
          </form>

          <div class="social-auth-links text-center mt-2 mb-3">
            <button @click="googleSignIn" class="btn btn-block btn-danger">
              <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
            </button>
          </div>

          <p class="mb-1">
            <router-link :to="{ name: 'auth.reset.password' }" class="text-center"
              >I forgot my password</router-link
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
import { useRouter } from "vue-router";
import { reactive } from "vue";
import { postSignIn } from "@func/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
import { useStore } from "vuex";

const store = useStore();
const router = useRouter();

const user = reactive({
  email: "",
  password: "",
});

const userError = reactive({
  email: "",
  password: "",
});

async function signIn() {
  try {
    LoadingModal();
    const response = await postSignIn(user);
    localStorage.setItem("token", response.data.token);
    await store.dispatch("verifyAccount");
    resetData();

    const intendedPath = localStorage.getItem("intendedPath");
    if (intendedPath) {
      localStorage.removeItem("intendedPath");
      router.replace(intendedPath);
      return;
    }

    router.replace({ name: "dashboard" });
    CloseModal();
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

function googleSignIn() {
  try {
    LoadingModal();
    window.location.href = `${window.API_URL}/auth/google`;
  } catch (error) {
    MessageModal("error", "Error", "Google sign-in failed");
  }
}

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetData() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}
</script>
