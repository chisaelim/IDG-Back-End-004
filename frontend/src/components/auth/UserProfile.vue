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
                    :src="tempPhoto"
                    alt="User profile picture"
                  />
                  <input
                    @change="onUpdatePhoto($event)"
                    type="file"
                    class="d-none"
                    :accept="allowedExtensions.map((ext) => '.' + ext).join(', ')"
                    id="file-input"
                  />
                  <div class="mt-1">
                    <label :for="'file-input'">
                      <a type="button" class="m-1 btn btn-primary btn-sm"
                        ><i class="fas fa-upload"></i
                      ></a>
                    </label>
                    <a
                      type="button"
                      @click="onDeletePhoto()"
                      class="m-1 btn btn-danger btn-sm"
                      ><i class="fas fa-trash"></i
                    ></a>
                    <a
                      type="button"
                      @click="onResetPhoto()"
                      class="m-1 btn btn-secondary btn-sm"
                      ><i class="fas fa-undo-alt"></i
                    ></a>
                    <a
                      v-if="changedPhoto"
                      type="button"
                      @click="updatePhoto()"
                      class="m-1 btn btn-success btn-sm"
                      ><i class="fas fa-check"></i
                    ></a>
                  </div>
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
import emptyPhoto from "@assets/images/emptyPhoto.png";
import { CloseModal, LoadingModal, MessageModal } from "@func/swal";
import { useRouter } from "vue-router";
import { computed, reactive, ref, watch } from "vue";
import {
  patchChangePassword,
  patchCreatePassword,
  patchUpdateUserPhoto,
} from "@func/api/auth";
import { useStore } from "vuex";
const store = useStore();
const userData = computed(() => store.state.user);
const userPhoto = computed(() => store.state.user.photo);

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

const tempPhoto = ref(null);
watch(
  () => userPhoto.value,
  (nv) => {
    if (nv === null) {
      return (tempPhoto.value = emptyPhoto);
    }
    return (tempPhoto.value = nv);
  },
  { immediate: true }
);
const changedPhoto = computed(
  () =>
    (tempPhoto.value !== emptyPhoto && tempPhoto.value !== userPhoto.value) ||
    (tempPhoto.value === emptyPhoto && userPhoto.value !== null)
);
const allowedExtensions = ["jpg", "jpeg", "png"];
function onUpdatePhoto(event) {
  const files = event.target.files;
  if (files && files.length > 0) {
    const fileName = files[0].name;
    const idxDot = fileName.lastIndexOf(".") + 1;
    const extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
    if (!allowedExtensions.includes(extFile)) {
      return (modelError.value = "Only jpg/jpeg and png files are allowed!");
    }
    const reader = new FileReader();
    reader.onloadend = function () {
      const img = new Image();
      img.onload = function () {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        // Set canvas size to 454x454
        canvas.width = 454;
        canvas.height = 454;

        // Calculate crop dimensions (center crop)
        const size = Math.min(img.width, img.height);
        const x = (img.width - size) / 2;
        const y = (img.height - size) / 2;

        // Draw image cropped and resized to 454x454
        ctx.drawImage(img, x, y, size, size, 0, 0, 454, 454);

        // Convert canvas to base64
        tempPhoto.value = canvas.toDataURL("image/png");
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(files[0]);
    event.target.value = null;
  }
}
function onDeletePhoto() {
  tempPhoto.value = emptyPhoto;
}
function onResetPhoto() {
  tempPhoto.value = userPhoto.value ? userPhoto.value : emptyPhoto;
}
async function updatePhoto() {
  try {
    LoadingModal();
    if (tempPhoto.value === emptyPhoto) {
      tempPhoto.value = null;
    }
    const response = await patchUpdateUserPhoto({ photo: tempPhoto.value });
    store.commit("setUserPhoto", response.data.photo);
    await MessageModal("success", "Success", response.data.message);
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
}
</script>
