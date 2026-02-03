<template>
  <aside class="main-sidebar sidebar-light-primary elevation-4" style="height: auto">
    <router-link :to="{ name: 'dashboard' }" class="brand-link">
      <img
        :src="logoImg"
        alt="AdminLTE Logo"
        class="brand-image img-circle elevation-3"
        style="opacity: 0.8"
      />
      <span class="brand-text font-weight-light">AdminLTE 3</span>
    </router-link>

    <div class="sidebar">
      <router-link :to="{ name: 'profile' }">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img
              :src="userData.photo || emptyPhoto"
              class="img-circle elevation-2"
              alt="User Image"
            />
          </div>
          <div class="info">
            <a href="#" class="d-block">{{ userData.name }}</a>
          </div>
        </div>
      </router-link>
      <nav class="mt-2">
        <ul
          class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false"
        >
          <li class="nav-item">
            <router-link
              :to="{ name: 'dashboard' }"
              class="nav-link"
              active-class="active"
            >
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </router-link>
          </li>
          <li v-if="isAdmin" class="nav-header">Systems</li>
          <li v-if="isAdmin" class="nav-item">
            <router-link :to="{ name: 'backups' }" class="nav-link" active-class="active">
              <i class="nav-icon fas fa-database"></i>
              <p>Backups</p>
            </router-link>
          </li>
        </ul>
      </nav>
      <hr />

      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input
            v-model="searchQuery"
            class="form-control form-control-sidebar"
            type="search"
            placeholder="Search"
            aria-label="Search"
          />
          <div class="input-group-append">
            <button @click="clearSearchQuery" type="button" class="btn btn-sidebar">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </div>

      <nav class="mt-2">
        <ul
          v-if="searchQuery"
          class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false"
        >
          <li class="nav-header">Search Results</li>
          <li
            @click="clearSearchQuery"
            class="nav-item"
            v-for="user in filteredUsers"
            :key="user.id"
          >
            <UserOption :user="user" />
          </li>
          <li
            @click="clearSearchQuery"
            class="nav-item"
            v-for="chat in sortedFilteredChats"
            :key="chat.id"
          >
            <ChatOption :chat="chat" />
          </li>
        </ul>
        <ul
          v-else
          class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false"
        >
          <li class="nav-header">Recent Chats</li>
          <li
            @click="clearSearchQuery"
            class="nav-item"
            v-for="chat in sortedRecentChats"
            :key="chat.id"
          >
            <ChatOption :chat="chat" />
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>
<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import logoImg from "admin-lte/dist/img/AdminLTELogo.png";
import { useStore } from "vuex";
import { useRoute } from "vue-router";
import { computed, onMounted, ref, watch } from "vue";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
import { apiGetChats, apiGetChatFile } from "@func/api/chat";
import { apiGetUsers } from "@func/api/user";

import ChatOption from "@com/includes/controls/ChatOption.vue";
import UserOption from "@com/includes/controls/UserOption.vue";
const store = useStore();
const userData = computed(() => store.state.user);
const isAdmin = computed(() => userData.value && userData.value.level === "admin");

const route = useRoute();
let searchTimeout = null;
const searchQuery = ref("");
const filteredChats = ref([]);
const filteredUsers = ref([]);
const recentChats = ref([]);

const sortedRecentChats = computed(() => {
  return [...recentChats.value].sort((a, b) => {
    const timeA = a.last_message?.created_at;
    const timeB = b.last_message?.created_at;
    return new Date(timeB) - new Date(timeA); // Descending order (newest first)
  });
});

const sortedFilteredChats = computed(() => {
  return [...filteredChats.value].sort((a, b) => {
    const timeA = a.last_message?.created_at;
    const timeB = b.last_message?.created_at;
    return new Date(timeB) - new Date(timeA);
  });
});

onMounted(async () => {
  try {
    LoadingModal();
    const response = await apiGetChats();
    recentChats.value = response.data.chats;
    await processChatImages(recentChats.value);
    CloseModal();
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
});
watch(searchQuery, async (newQuery) => {
  // Clear the previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  if (newQuery.trim() === "") {
    filteredChats.value = [];
    filteredUsers.value = [];
    return;
  }

  // Set a new timeout for 1 second
  searchTimeout = setTimeout(async () => {
    try {
      const response = await Promise.all([
        apiGetChats({ search: newQuery }),
        apiGetUsers({ search: newQuery }),
      ]);
      filteredChats.value = response[0].data.chats;
      filteredUsers.value = response[1].data.users;

      await processChatImages(filteredChats.value);
    } catch (error) {
      return MessageModal(
        "error",
        "Error",
        error.response?.data?.message || error.message
      );
    }
  }, 1000);
});

async function processChatImages(chats) {
  await Promise.all(
    chats.map(async (chat) => {
      if (!chat.photo) {
        chat.photo = emptyPhoto;
        return;
      }
      if (chat.type === "group") {
        chat.photo = await loadChatImage(chat.photo);
        return;
      }
    })
  );
}
async function loadChatImage(uri) {
  try {
    const response = await apiGetChatFile(uri);
    return URL.createObjectURL(response.data);
  } catch (error) {
    return emptyPhoto;
  }
}
function clearSearchQuery() {
  searchQuery.value = "";
}
</script>
