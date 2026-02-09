<template>
  <div class="modal fade" :id="id" ref="chatModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Chat</h4>
          <button type="button" class="close" @click="hideChatModal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="card card-primary card-outline">
            <div class="card-body box-profile">
              <div class="text-center">
                <img
                  class="profile-user-img img-fluid img-circle"
                  :src="chatData.photo ?? emptyPhoto"
                  alt="Chat profile picture"
                />
                <input
                  @change="onChangePhoto($event)"
                  type="file"
                  class="d-none"
                  :accept="allowedExtensions.map((ext) => '.' + ext).join(', ')"
                  :class="{ 'is-invalid': chatDataErr.photo }"
                  :id="'-FILE-INPUT-' + id"
                />
                <span class="invalid-feedback">{{ chatDataErr.photo }}</span>
                <div class="mt-1" v-if="chatData.updatable">
                  <label :for="'-FILE-INPUT-' + id">
                    <a type="button" class="m-1 btn btn-primary btn-sm">
                      <i class="fas fa-upload"></i>
                    </a>
                  </label>
                  <a
                    type="button"
                    @click="onDeletePhoto()"
                    class="m-1 btn btn-danger btn-sm"
                  >
                    <i class="fas fa-trash"></i>
                  </a>
                  <a
                    type="button"
                    @click="onResetPhoto()"
                    class="m-1 btn btn-secondary btn-sm"
                  >
                    <i class="fas fa-undo-alt"></i>
                  </a>
                </div>
              </div>

              <!-- Chat Name -->
              <div class="form-group mt-3">
                <label>Name</label>
                <input
                  v-model="chatData.name"
                  :disabled="!chatData.updatable"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': chatDataErr.name }"
                  placeholder="Enter group name"
                />
                <span class="invalid-feedback">{{ chatDataErr.name }}</span>
              </div>

              <!-- Member Selection (only for create) -->
              <div v-if="!chatId" class="form-group mt-3">
                <label>Add Members</label>
                <div class="input-group mb-2">
                  <input
                    v-model="searchQuery"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': chatDataErr.user_ids }"
                    placeholder="Search users by name or email..."
                  />
                  <span class="invalid-feedback">{{ chatDataErr.user_ids }}</span>
                </div>

                <!-- Search Results -->
                <div
                  v-if="searchQuery && availableUsers.length"
                  class="list-group mb-2"
                  style="max-height: 150px; overflow-y: auto"
                >
                  <a
                    v-for="user in availableUsers"
                    :key="user.id"
                    class="list-group-item list-group-item-action d-flex align-items-center"
                    role="button"
                    @click="addMember(user)"
                  >
                    <img
                      :src="user.photo || emptyPhoto"
                      class="img-circle mr-2"
                      width="30"
                      height="30"
                    />
                    <div>
                      <strong>{{ user.name }}</strong>
                      <small class="d-block text-muted">{{ user.email }}</small>
                    </div>
                  </a>
                </div>
                <div
                  v-if="searchQuery && !availableUsers.length"
                  class="text-muted small"
                >
                  No users found.
                </div>

                <!-- Selected Members -->
                <div v-if="selectedUsers.length" class="mt-2">
                  <span class="text-muted small">Selected members:</span>
                  <div class="d-flex flex-wrap mt-1">
                    <span
                      v-for="member in selectedUsers"
                      :key="member.id"
                      class="badge badge-primary mr-1 mb-1 p-2 d-flex align-items-center"
                    >
                      <img
                        :src="member.photo || emptyPhoto"
                        class="img-circle mr-1"
                        width="20"
                        height="20"
                      />
                      {{ member.name }}
                      <a
                        @click="removeMember(member.id)"
                        class="ml-1 text-white"
                        role="button"
                      >
                        <i class="fas fa-times"></i>
                      </a>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between" v-if="chatData.updatable">
          <button type="button" class="btn btn-default" @click="hideChatModal">
            Close
          </button>
          <button type="button" class="btn btn-primary" @click="submitChat">
            {{ chatId ? "Update Chat" : "Create Chat" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import { onMounted, reactive, ref, computed, watch } from "vue";
import {
  apiCreateChat,
  apiUpdateGroupChat,
  apiReadChat,
  apiGetChatFile,
} from "@func/api/chat";
import { apiGetUsers } from "@func/api/user";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
import { useRouter } from "vue-router";

const router = useRouter();
const emit = defineEmits(["chatCreated", "chatUpdated"]);
const props = defineProps({
  id: {
    type: String,
    default: () => "chatModal" + Math.random().toString(36).substr(2, 9),
  },
  chatId: {
    type: Number,
    default: null,
  },
});

const chatModal = ref(null);
const tempChatPhoto = ref(null);
const chatData = reactive({
  name: "",
  type: "group",
  photo: null,
  user_ids: [],
  updatable: true,
});
const chatDataErr = reactive({
  name: "",
  photo: "",
  user_ids: "",
});
const defaultChatData = JSON.parse(JSON.stringify(chatData));
const defaultChatDataErr = JSON.parse(JSON.stringify(chatDataErr));
async function onChatUpdated(chat) {
  Object.assign(chatData, chat);
  if (chat.type === "group" && chat.photo) {
    try {
      const photoResponse = await apiGetChatFile(chat.photo);
      chatData.photo = URL.createObjectURL(photoResponse.data);
    } catch {
      chatData.photo = null;
    }
  } else {
    chatData.photo = chat.photo;
  }
}
// User search for member selection
const searchQuery = ref("");
const filteredUsers = ref([]);
const selectedUsers = ref([]);
let searchTimeout = null;

const availableUsers = computed(() => {
  const selectedIds = selectedUsers.value.map((m) => m.id);
  return filteredUsers.value.filter((u) => !selectedIds.includes(u.id));
});

watch(searchQuery, (newQuery) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!newQuery.trim()) {
    filteredUsers.value = [];
    return;
  }
  searchTimeout = setTimeout(async () => {
    try {
      const response = await apiGetUsers({ search: newQuery });
      filteredUsers.value = response.data.users;
    } catch (error) {
      MessageModal("error", "Error", error.response?.data?.message || error.message);
    }
  }, 500);
});

onMounted(() => {
  $(chatModal.value).on("show.bs.modal", function () {
    if (props.chatId) {
      readChat(props.chatId);
    }
  });
  $(chatModal.value).on("hide.bs.modal", function () {
    resetData();
  });
});

async function readChat(chatId) {
  try {
    LoadingModal();
    const response = await apiReadChat(chatId);
    const chat = response.data.chat;
    await onChatUpdated(chat);
    tempChatPhoto.value = chatData.photo;
    CloseModal();
  } catch (error) {
    CloseModal();
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
}

function openChatModal() {
  $(chatModal.value).modal("show");
}
function hideChatModal() {
  $(chatModal.value).modal("hide");
}

function addMember(user) {
  if (!selectedUsers.value.find((m) => m.id === user.id)) {
    selectedUsers.value.push(user);
    chatData.user_ids.push(user.id);
  }
  searchQuery.value = "";
  filteredUsers.value = [];
}

function removeMember(userId) {
  selectedUsers.value = selectedUsers.value.filter((m) => m.id !== userId);
  chatData.user_ids = chatData.user_ids.filter((id) => id !== userId);
}

async function submitChat() {
  try {
    LoadingModal();
    if (props.chatId) {
      await updateChat();
    } else {
      await createChat();
    }
    CloseModal();
  } catch (error) {
    if (error.response?.status === 422) {
      Object.keys(chatDataErr).forEach((key) => {
        chatDataErr[key] = error.response.data.errors[key]
          ? error.response.data.errors[key][0]
          : "";
      });
      return CloseModal();
    }
    return MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
}

async function createChat() {
  const response = await apiCreateChat(chatData);
  emit("chatCreated", response.data.chat);
  window.dispatchEvent(new CustomEvent("chatCreated", { detail: response.data.chat }));
  hideChatModal();
  router.push({ name: "chats", params: { chatId: response.data.chat.id } });
}

async function updateChat() {
  if (chatData.photo === tempChatPhoto.value) {
    delete chatData.photo;
  }
  const response = await apiUpdateGroupChat(props.chatId, chatData);
  emit("chatUpdated", response.data.chat);
  window.dispatchEvent(new CustomEvent("chatUpdated", { detail: response.data.chat }));
  hideChatModal();
}

const allowedExtensions = ["jpg", "jpeg", "png"];
function onChangePhoto(event) {
  const files = event.target.files;
  if (files && files.length > 0) {
    const fileName = files[0].name;
    const idxDot = fileName.lastIndexOf(".") + 1;
    const extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
    if (!allowedExtensions.includes(extFile)) {
      chatDataErr.photo = "Only jpg/jpeg and png files are allowed!";
      return;
    }
    const reader = new FileReader();
    reader.onloadend = function () {
      const img = new Image();
      img.onload = function () {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        canvas.width = 454;
        canvas.height = 454;

        const size = Math.min(img.width, img.height);
        const x = (img.width - size) / 2;
        const y = (img.height - size) / 2;

        ctx.drawImage(img, x, y, size, size, 0, 0, 454, 454);

        chatData.photo = canvas.toDataURL("image/png");
        chatDataErr.photo = "";
      };
      img.src = reader.result;
    };
    reader.readAsDataURL(files[0]);
    event.target.value = null;
  }
}
function onDeletePhoto() {
  chatData.photo = null;
}
function onResetPhoto() {
  chatData.photo = tempChatPhoto.value ? tempChatPhoto.value : null;
}
function resetData() {
  Object.assign(chatData, defaultChatData);
  Object.assign(chatDataErr, defaultChatDataErr);
  tempChatPhoto.value = null;
  selectedUsers.value = [];
  filteredUsers.value = [];
  searchQuery.value = "";
}
defineExpose({
  openChatModal,
  hideChatModal,
});
</script>
