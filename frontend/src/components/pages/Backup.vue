<template>
  <div class="content-wrapper" style="min-height: 1416px">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Backups</h1>
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
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">List of Backups</h3>

            <div class="card-tools">
              <div class="input-group input-group-sm" style="width: 150px">
                <input
                  type="text"
                  name="table_search"
                  class="form-control float-right"
                  placeholder="Search"
                />

                <div class="input-group-append">
                  <button type="submit" class="btn btn-default">
                    <i class="fas fa-search"></i>
                  </button>
                  <button type="button" class="btn btn-success" @click="createBackup">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 300px">
            <table class="table table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Size</th>
                  <th>Date</th>
                  <th>Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(backup, index) in backups" :key="backup.name">
                  <td>{{ index + 1 }}</td>
                  <td>{{ backup.name }}</td>
                  <td>{{ backup.size_human }}</td>
                  <td>{{ backup.date_human }}</td>
                  <td>
                    <button
                      class="btn btn-primary btn-sm"
                      @click="downloadBackup(backup.name)"
                    >
                      <i class="fas fa-download"></i> Download
                    </button>
                    <button
                      class="btn btn-danger btn-sm"
                      @click="deleteBackup(backup.name)"
                    >
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { MessageModal, LoadingModal, CloseModal } from "@func/swal";
import {
  apiGetBackups,
  apiCreateBackup,
  apiDownloadBackup,
  apiDeleteBackup,
} from "@func/api/backup";
import { downloadBlobResponse } from "@func/download";

const backups = ref([]);

onMounted(async () => {
  try {
    const response = await apiGetBackups();
    backups.value = response.data.backups;
  } catch (error) {
    return MessageModal("error", "Error", error.response.data?.message || error.message);
  }
});

async function createBackup() {
  Swal.fire({
    title: "Create Backup",
    text: "Are you sure you want to create a new backup?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    confirmButtonText: "Create Backup",
    input: "select",
    inputOptions: {
      db: "Database Only",
      files: "Files Only",
      full: "All Data",
    },
    inputPlaceholder: "Select an item",
  }).then(async (result) => {
    if (!result.isConfirmed) {
      return;
    }
    if (result.value) {
      try {
        LoadingModal();
        const response = await apiCreateBackup({
          flag: result.value,
        });
        return MessageModal("success", "Success", response.data.message);
      } catch (error) {
        return MessageModal(
          "error",
          "Error",
          error.response.data?.message || error.message
        );
      }
    }
    return createBackup();
  });
}
async function downloadBackup(filename) {
  try {
    LoadingModal();
    const response = await apiDownloadBackup(filename);
    downloadBlobResponse(response, filename);
    CloseModal();
  } catch (error) {
    return MessageModal("error", "Error", error.response.data?.message || error.message);
  }
}
async function deleteBackup(filename) {
  try {
    LoadingModal();
    const response = await apiDeleteBackup(filename);
    backups.value = backups.value.filter((backup) => backup.name !== filename);
    return MessageModal("success", "Success", response.data.message);
  } catch (error) {
    return MessageModal("error", "Error", error.response.data?.message || error.message);
  }
}
</script>
