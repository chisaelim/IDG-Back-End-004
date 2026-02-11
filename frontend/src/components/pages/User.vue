<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Users</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">Home</router-link>
              </li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <CustomTable :title="'Users'" :data="users" :columns="columns" :pageSize="25" />
        <!-- <CustomTablePaginated
          v-model:link="link"
          v-model:meta="meta"
          v-model="users"
          :title="'Users'"
          :columns="columns"
          :pageSize="25"
        /> -->
      </div>
    </section>
  </div>
  <div class="modal fade" ref="userModal" aria-modal="true" role="dialog">
    <form @submit.prevent="saveUser">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">User</h4>
            <button type="button" class="close" @click="hideUserModal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="userName">Name</label>
              <input
                type="text"
                class="form-control"
                v-model="userObject.name"
                :class="{ 'is-invalid': userObjectErr.name }"
              />
              <div class="invalid-feedback">{{ userObjectErr.name }}</div>
            </div>
            <div class="form-group">
              <label for="userEmail">Email</label>
              <input
                type="email"
                class="form-control"
                v-model="userObject.email"
                :class="{ 'is-invalid': userObjectErr.email }"
              />
              <div class="invalid-feedback">{{ userObjectErr.email }}</div>
            </div>
            <div class="form-group">
              <label for="userPassword">Password</label>
              <input
                type="password"
                class="form-control"
                v-model="userObject.password"
                :class="{ 'is-invalid': userObjectErr.password }"
              />
              <div class="invalid-feedback">{{ userObjectErr.password }}</div>
            </div>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" @click="hideUserModal">
              Close
            </button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { apiGetDetailUsers, apiReadDetailUser, apiDeleteUser } from "@func/api/user";
import { CloseModal, LoadingModal, MessageModal } from "@func/swal";
import { onMounted, ref, h, reactive } from "vue";
import CustomTable from "../includes/tables/CustomTable.vue";
import CustomTablePaginated from "../includes/tables/CustomTablePaginated.vue";
import { apiCreateUser, apiUpdateUser } from "@/functions/api/user";

const userModal = ref(null);
const link = ref(window.API_URL + "/manage/users");
const meta = ref({});
const users = ref([]);
const userObject = reactive({
  id: null,
  name: "",
  email: "",
  password: "",
});
const userObjectErr = reactive({
  name: "",
  email: "",
  password: "",
});
const defaultUserObject = JSON.parse(JSON.stringify(userObject));
const defaultUserObjectErr = JSON.parse(JSON.stringify(userObjectErr));

const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Name",

    accessorKey: "name",
  },
  {
    header: "Email",
    accessorKey: "email",
  },
  {
    accessorKey: "action",
    header: () => [
      "Actions",
      h(
        "button",
        {
          onClick: () => showUserModal(),
          class: "btn btn-sm btn-success ml-3",
        },
        "Create"
      ),
    ],
    cell: ({
      row: {
        original: { id },
      },
    }) => [
      // delete btn
      h(
        "button",
        {
          onClick: () => removeUser(id),
          class: "btn btn-sm btn-outline-danger mx-1",
        },
        h("i", { class: "fa fa-trash" })
      ),
      // view btn
      h(
        "button",
        {
          onClick: () => viewUser(id),
          class: "btn btn-sm btn-outline-secondary mx-1",
        },
        h("i", { class: "fa fa-pen" })
      ),
    ],
    enableSorting: false,
  },
];
onMounted(async () => {
  $(userModal.value).on("show.bs.modal", function () {
    // Implement logic to populate modal with user data if editing an existing user
  });
  $(userModal.value).on("hide.bs.modal", function () {
    resetData();
  });
  try {
    LoadingModal();
    const response = await apiGetDetailUsers();
    const { data, meta } = response.data;
    users.value = data;
    CloseModal();
  } catch (error) {
    return MessageModal(
      "error",
      "Error",
      error.response.data.message || "An error occurred while fetching users."
    );
  }
});

async function saveUser() {
  try {
    LoadingModal();
    let response = null;
    if (userObject.id) {
      response = await apiUpdateUser(userObject.id, userObject);
      onUserUpdate(response.data.user);
    } else {
      response = await apiCreateUser(userObject);
      onUserCreate(response.data.user);
    }

    // Implement save user logic here
    hideUserModal();
    MessageModal("success", "Success", response.data.message);
  } catch (error) {
    if (!error.response) {
      return MessageModal("error", "Error", error.message);
    }
    if (error.response.status === 422) {
      Object.keys(userObjectErr).forEach((key) => {
        userObjectErr[key] = error.response.data.errors[key]
          ? error.response.data.errors[key][0]
          : "";
      });
      return CloseModal();
    }
    return MessageModal("error", "Error", error.response.data.message);
  }
}

async function viewUser(id) {
  try {
    LoadingModal();
    const response = await apiReadDetailUser(id);
    Object.assign(userObject, response.data.user);
    showUserModal();
    CloseModal();
  } catch (error) {
    return MessageModal("error", "Error", error.response.data.message || error.message);
  }
}
async function removeUser(id) {
  Swal.fire({
    icon: "warning",
    title: "Delete User",
    text: "Are you sure you want to delete this user? This action cannot be undone.",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        const response = await apiDeleteUser(id);
        onUserDelete(id);
        MessageModal("success", "Success", response.data.message);
      } catch (error) {
        return MessageModal(
          "error",
          "Error",
          error.response.data.message || error.message
        );
      }
    }
  });
}

function showUserModal() {
  $(userModal.value).modal("show");
}

function hideUserModal() {
  $(userModal.value).modal("hide");
}

function resetData() {
  Object.assign(userObject, defaultUserObject);
  Object.assign(userObjectErr, defaultUserObjectErr);
}

function onUserCreate(user) {
  users.value.unshift(user);
}
function onUserUpdate(user) {
  users.value = users.value.map((u) => (u.id === user.id ? user : u));
}
function onUserDelete(id) {
  users.value = users.value.filter((u) => u.id !== id);
}
</script>
