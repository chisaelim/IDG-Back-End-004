<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Profile</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
              <li class="breadcrumb-item active">Profile</li>
            </ol>
          </div>
        </div>
      </div>
      <!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img
                    class="profile-user-img img-fluid img-circle"
                    :src="profilePic"
                    alt="User profile picture"
                  />
                </div>

                <h3 class="profile-username text-center">Nina Mcintire</h3>

                <p class="text-muted text-center">Software Engineer</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Followers</b> <a class="float-right">1,322</a>
                  </li>
                  <li class="list-group-item">
                    <b>Following</b> <a class="float-right">543</a>
                  </li>
                  <li class="list-group-item">
                    <b>Friends</b> <a class="float-right">13,287</a>
                  </li>
                </ul>

                <!-- <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a> -->
              </div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <a class="nav-link active" href="#password_settings" data-toggle="tab"
                      >Password Settings</a
                    >
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <div class="active tab-pane" id="password_settings">
                    <form @submit.prevent="changePassword" class="form-horizontal">
                      <div v-if="!userData.password_null" class="form-group row">
                        <label class="col-sm-2 col-form-label">Old Password</label>
                        <div class="col-sm-10">
                          <input
                            v-model="user.old_password"
                            type="password"
                            class="form-control"
                            placeholder="Old Password"
                            :class="!!userError.old_password ? 'is-invalid' : ''"
                          />
                          <div class="invalid-feedback">
                            {{ userError.old_password }}
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">New Password</label>
                        <div class="col-sm-10">
                          <input
                            v-model="user.new_password"
                            type="password"
                            class="form-control"
                            placeholder="New Password"
                            :class="!!userError.new_password ? 'is-invalid' : ''"
                          />
                          <div class="invalid-feedback">
                            {{ userError.new_password }}
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Confirm Password</label>
                        <div class="col-sm-10">
                          <input
                            v-model="user.new_password_confirmation"
                            type="password"
                            class="form-control"
                            placeholder="Confirm Password"
                          />
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <div class="checkbox">
                            <label>
                              <input v-model="user.terminate_sessions" type="checkbox" />
                              Terminate all sessions
                            </label>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <button type="reset" class="mx-3 btn btn-danger">Cancel</button>
                          <button type="submit" class="mx-3 btn btn-outline-primary">
                            Save
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import profilePic from "admin-lte/dist/img/user4-128x128.jpg";
import { CloseModal, LoadingModal, MessageModal } from "@func/swal";
import { useRouter } from "vue-router";
import { computed, reactive } from "vue";
import { patchChangePassword, patchCreatePassword } from "@func/api/auth";
import { useStore } from "vuex";
const store = useStore();
const userData = computed(() => store.state.user);

const router = useRouter();
const user = reactive({
  old_password: "",
  new_password: "",
  new_password_confirmation: "",
  terminate_sessions: false,
});

const userError = reactive({
  old_password: "",
  new_password: "",
});

async function changePassword() {
  try {
    LoadingModal();
    let response;
    if (userData.value.password_null) {
      response = await patchCreatePassword(user);
    } else {
      response = await patchChangePassword(user);
    }
    await MessageModal("success", "Success", response.data.message, () =>
      router.push({ name: "dashboard" })
    );
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
</script>
