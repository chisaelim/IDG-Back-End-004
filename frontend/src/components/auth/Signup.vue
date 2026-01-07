<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <router-link to="/" class="h1"><b>Admin</b>LTE</router-link>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Sign up for a new membership</p>
          <form @submit.prevent="signUp">
            <div class="input-group mb-3">
              <input
                v-model="user.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': !!userError.name }"
                placeholder="Name"
              />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user"></span>
                </div>
              </div>

              <div class="invalid-feedback">
                {{ userError.name }}
              </div>
            </div>
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
                <button type="submit" class="btn btn-primary btn-block">Sign up</button>
              </div>
            </div>
          </form>
          <p class="mb-1">
            <router-link :to="{ name: 'auth.signin' }" class="text-center"
              >I already have a membership</router-link
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
import { postSignUp } from "@func/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
const router = useRouter();

const user = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const userError = reactive({
  name: "",
  email: "",
  password: "",
});

async function signUp() {
  try {
    LoadingModal();
    const response = await postSignUp(user);
    resetData();

    Swal.fire({
      title: response.data.message,
      text: "You can now sign in with your new account.",
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
