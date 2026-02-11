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
          <li v-if="isAdmin" class="nav-item">
            <router-link :to="{ name: 'users' }" class="nav-link" active-class="active">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
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
          <li v-if="isLoadingMoreSearch" class="nav-item text-center p-2">
            <i class="fas fa-spinner fa-spin"></i> Loading...
          </li>
        </ul>
        <ul
          v-else
          class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false"
        >
          <li class="nav-header d-flex justify-content-between align-items-center">
            <span>Recent Chats</span>
            <button @click="chatModal.openChatModal" class="btn btn-sm btn-success">
              New Chat
            </button>
          </li>
          <li
            @click="clearSearchQuery"
            class="nav-item"
            v-for="chat in sortedRecentChats"
            :key="chat.id"
          >
            <ChatOption :chat="chat" />
          </li>
          <li v-if="isLoadingMore" class="nav-item text-center p-2">
            <i class="fas fa-spinner fa-spin"></i> Loading...
          </li>
        </ul>
      </nav>
    </div>
  </aside>
  <ChatModal ref="chatModal" />
</template>
<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import logoImg from "admin-lte/dist/img/AdminLTELogo.png";
import { useStore } from "vuex";
import { computed, onMounted, onBeforeUnmount, ref, watch } from "vue";
import { MessageModal } from "@func/swal";
import { apiGetChats, apiGetChatFile } from "@func/api/chat";
import { apiGetUsers } from "@func/api/user";

import ChatOption from "@com/includes/controls/ChatOption.vue";
import UserOption from "@com/includes/controls/UserOption.vue";
import ChatModal from "@com/includes/controls/ChatModal.vue";
import { useRoute, useRouter } from "vue-router";

const router = useRouter();
const route = useRoute();
const store = useStore();
const userData = computed(() => store.state.user);
const isAdmin = computed(() => userData.value && userData.value.level === "admin");

let searchTimeout = null;
const searchQuery = ref("");
const filteredChats = ref([]);
const filteredUsers = ref([]);
const recentChats = ref([]);
const chatModal = ref(null);
const chatMeta = ref(null);
const isLoadingMore = ref(false);

const filteredChatMeta = ref(null);
const filteredUserMeta = ref(null);
const isLoadingMoreSearch = ref(false);

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
  window.addEventListener("chatCreated", onChatCreated);
  window.addEventListener("chatUpdated", onChatUpdated);
  window.addEventListener("chatDeleted", onChatDeleted);

  // Subscribe to the user's private ChatEvent channel for real-time chat events
  if (userData.value?.id) {
    subscribeToChatChannel(userData.value.id);
  }

  try {
    const response = await apiGetChats();
    recentChats.value = response.data.chats;
    chatMeta.value = response.data.meta;
    await processChatImages(recentChats.value);
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message);
  }

  // jQuery infinite scroll on sidebar
  $(".sidebar").on("scroll", function () {
    const $this = $(this);
    const scrollTop = $this.scrollTop();
    const innerHeight = $this.innerHeight();
    const scrollHeight = $this[0].scrollHeight;

    if (scrollTop + innerHeight >= scrollHeight - 50) {
      if (searchQuery.value.trim()) {
        loadMoreSearchResults();
      } else {
        loadMoreChats();
      }
    }
  });
});

onBeforeUnmount(() => {
  $(".sidebar").off("scroll");
  window.removeEventListener("chatCreated", onChatCreated);
  window.removeEventListener("chatUpdated", onChatUpdated);
  window.removeEventListener("chatDeleted", onChatDeleted);

  // Leave the Echo ChatEvent channel
  if (userData.value?.id) {
    window.Echo.leave(`ChatEvent.${userData.value.id}`);
  }
});

// If user data loads after mount, subscribe then
watch(
  () => userData.value?.id,
  (newId, oldId) => {
    if (newId && newId !== oldId) {
      if (oldId) {
        window.Echo.leave(`ChatEvent.${oldId}`);
      }
      subscribeToChatChannel(newId);
    }
  }
);

function subscribeToChatChannel(userId) {
  window.Echo.private(`ChatEvent.${userId}`)
    .listen(".ChatCreated", async (e) => {
      const chat = e.chat;
      await processChatImages([chat]);
      recentChats.value.unshift(chat);
    })
    .listen(".ChatUpdated", async (e) => {
      const chat = e.chat;
      await processChatImages([chat]);
      const exists = recentChats.value.some((c) => c.id === chat.id);
      if (exists) {
        recentChats.value = recentChats.value.map((c) => (c.id === chat.id ? chat : c));
      } else {
        recentChats.value.unshift(chat);
      }
    })
    .listen(".ChatDeleted", (e) => {
      const id = e.chatId;
      recentChats.value = recentChats.value.filter((c) => c.id !== id);
      if(route.name === 'chats' && route.params.chatId == id) {
        // If currently viewing the deleted chat, redirect to dashboard
        router.push({ name: 'dashboard' });
      }
    });
}

async function loadMoreChats() {
  if (isLoadingMore.value) return;
  if (!chatMeta.value) return;
  if (chatMeta.value.current_page >= chatMeta.value.last_page) return;

  isLoadingMore.value = true;
  try {
    const nextPage = chatMeta.value.current_page + 1;
    const response = await apiGetChats({ page: nextPage });
    const newChats = response.data.chats;
    chatMeta.value = response.data.meta;
    await processChatImages(newChats);
    recentChats.value = [...recentChats.value, ...newChats];
  } catch (error) {
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  } finally {
    isLoadingMore.value = false;
  }
}
watch(searchQuery, async (newQuery) => {
  // Clear the previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  if (newQuery.trim() === "") {
    filteredChats.value = [];
    filteredUsers.value = [];
    filteredChatMeta.value = null;
    filteredUserMeta.value = null;
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
      filteredChatMeta.value = response[0].data.meta;
      filteredUserMeta.value = response[1].data.meta;

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

async function loadMoreSearchResults() {
  if (isLoadingMoreSearch.value) return;

  const canLoadMoreChats =
    filteredChatMeta.value &&
    filteredChatMeta.value.current_page < filteredChatMeta.value.last_page;
  const canLoadMoreUsers =
    filteredUserMeta.value &&
    filteredUserMeta.value.current_page < filteredUserMeta.value.last_page;

  if (!canLoadMoreChats && !canLoadMoreUsers) return;

  isLoadingMoreSearch.value = true;
  try {
    const promises = [];

    if (canLoadMoreChats) {
      promises.push(
        apiGetChats({
          search: searchQuery.value,
          page: filteredChatMeta.value.current_page + 1,
        })
      );
    } else {
      promises.push(null);
    }

    if (canLoadMoreUsers) {
      promises.push(
        apiGetUsers({
          search: searchQuery.value,
          page: filteredUserMeta.value.current_page + 1,
        })
      );
    } else {
      promises.push(null);
    }

    const responses = await Promise.all(promises);

    if (responses[0]) {
      const newChats = responses[0].data.chats;
      filteredChatMeta.value = responses[0].data.meta;
      await processChatImages(newChats);
      filteredChats.value = [...filteredChats.value, ...newChats];
    }

    if (responses[1]) {
      filteredUsers.value = [...filteredUsers.value, ...responses[1].data.users];
      filteredUserMeta.value = responses[1].data.meta;
    }
  } catch (error) {
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  } finally {
    isLoadingMoreSearch.value = false;
  }
}
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

async function onChatCreated(event) {
  const chat = event.detail;
  await processChatImages([chat]);
  recentChats.value.unshift(chat);
}

async function onChatUpdated(event) {
  const chat = event.detail;
  await processChatImages([chat]);
  const exists = recentChats.value.some((c) => c.id === chat.id);
  if (exists) {
    recentChats.value = recentChats.value.map((c) => (c.id === chat.id ? chat : c));
  } else {
    recentChats.value.unshift(chat);
  }
}

function onChatDeleted(event) {
  const id = event.detail;
  recentChats.value = recentChats.value.filter((c) => c.id !== id);
}
</script>
