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

              <!-- Member Management (only for existing group chats) -->
              <div v-if="chatId && chatData.type === 'group'" class="form-group mt-3">
                <label>Members</label>

                <!-- Add Member Search (admin only) -->
                <div v-if="chatData.updatable" class="mb-2">
                  <div class="input-group mb-2">
                    <input
                      v-model="memberSearchQuery"
                      type="text"
                      class="form-control"
                      placeholder="Search users to add..."
                    />
                  </div>
                  <div
                    v-if="memberSearchQuery && availableMemberUsers.length"
                    class="list-group mb-2"
                    style="max-height: 150px; overflow-y: auto"
                  >
                    <a
                      v-for="user in availableMemberUsers"
                      :key="user.id"
                      class="list-group-item list-group-item-action d-flex align-items-center"
                      role="button"
                      @click="addChatMember(user)"
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
                    v-if="memberSearchQuery && !availableMemberUsers.length"
                    class="text-muted small"
                  >
                    No users found.
                  </div>
                </div>

                <!-- Existing Members List -->
                <div
                  class="list-group"
                  style="max-height: 200px; overflow-y: auto"
                >
                  <div v-if="isLoadingMembers" class="text-center p-2">
                    <i class="fas fa-spinner fa-spin"></i> Loading members...
                  </div>
                  <div
                    v-for="member in chatMembers"
                    :key="member.id"
                    class="list-group-item d-flex align-items-center justify-content-between"
                  >
                    <div class="d-flex align-items-center">
                      <img
                        :src="member.user?.photo || emptyPhoto"
                        class="img-circle mr-2"
                        width="30"
                        height="30"
                      />
                      <div>
                        <strong>{{ member.user?.name }}</strong>
                        <small class="d-block text-muted">{{ member.user?.email }}</small>
                      </div>
                      <span
                        v-if="member.role === 'admin'"
                        class="badge badge-info ml-2"
                      >
                        Admin
                      </span>
                    </div>
                    <button
                      v-if="chatData.updatable"
                      @click="removeChatMember(member)"
                      class="btn btn-danger btn-sm"
                      title="Remove member"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div
                    v-if="!isLoadingMembers && !chatMembers.length"
                    class="text-muted small text-center p-2"
                  >
                    No other members.
                  </div>
                </div>
                <!-- Load More Members -->
                <div
                  v-if="memberMeta && memberMeta.current_page < memberMeta.last_page"
                  class="text-center mt-2"
                >
                  <button
                    @click="loadMoreMembers"
                    class="btn btn-sm btn-outline-primary"
                    :disabled="isLoadingMembers"
                  >
                    Load More
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="modal-footer justify-content-between"
          v-if="chatId && chatData.type === 'group'"
        >
          <div>
            <button type="button" class="btn btn-default" @click="hideChatModal">
              Close
            </button>
          </div>
          <div>
            <button type="button" class="btn btn-warning mr-2" @click="leaveChat">
              <i class="fas fa-sign-out-alt"></i> Leave Chat
            </button>
            <button
              v-if="chatData.updatable"
              type="button"
              class="btn btn-danger mr-2"
              @click="deleteChat"
            >
              <i class="fas fa-trash"></i> Delete Chat
            </button>
            <button
              v-if="chatData.updatable"
              type="button"
              class="btn btn-primary"
              @click="submitChat"
            >
              Update Chat
            </button>
          </div>
        </div>
        <div
          class="modal-footer justify-content-between"
          v-else-if="!chatId || chatData.updatable"
        >
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
  apiDeleteGroupChat,
  apiLeaveGroupChat,
  apiReadChat,
  apiGetChatFile,
} from "@func/api/chat";
import { apiGetMembers, apiAddMember, apiRemoveMember } from "@func/api/chat_member";
import { apiGetUsers } from "@func/api/user";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
import { useRouter } from "vue-router";

const router = useRouter();
const emit = defineEmits(["chatCreated", "chatUpdated", "chatDeleted"]);
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

// Member management for existing group chats
const chatMembers = ref([]);
const memberMeta = ref(null);
const isLoadingMembers = ref(false);
const memberSearchQuery = ref("");
const filteredMemberUsers = ref([]);
let memberSearchTimeout = null;

const availableMemberUsers = computed(() => {
  const existingUserIds = chatMembers.value.map((m) => m.user_id);
  return filteredMemberUsers.value.filter((u) => !existingUserIds.includes(u.id));
});

watch(memberSearchQuery, (newQuery) => {
  if (memberSearchTimeout) clearTimeout(memberSearchTimeout);
  if (!newQuery.trim()) {
    filteredMemberUsers.value = [];
    return;
  }
  memberSearchTimeout = setTimeout(async () => {
    try {
      const response = await apiGetUsers({ search: newQuery });
      filteredMemberUsers.value = response.data.users;
    } catch (error) {
      MessageModal("error", "Error", error.response?.data?.message || error.message);
    }
  }, 500);
});

async function loadMembers() {
  if (!props.chatId) return;
  isLoadingMembers.value = true;
  try {
    const response = await apiGetMembers(props.chatId);
    chatMembers.value = response.data.chat_members;
    memberMeta.value = response.data.meta;
  } catch (error) {
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  } finally {
    isLoadingMembers.value = false;
  }
}

async function loadMoreMembers() {
  if (!memberMeta.value || memberMeta.value.current_page >= memberMeta.value.last_page) return;
  isLoadingMembers.value = true;
  try {
    const nextPage = memberMeta.value.current_page + 1;
    const response = await apiGetMembers(props.chatId, { page: nextPage });
    chatMembers.value = [...chatMembers.value, ...response.data.chat_members];
    memberMeta.value = response.data.meta;
  } catch (error) {
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  } finally {
    isLoadingMembers.value = false;
  }
}

async function addChatMember(user) {
  try {
    LoadingModal();
    const response = await apiAddMember(props.chatId, { user_id: user.id });
    chatMembers.value.push(response.data.chat_member);
    memberSearchQuery.value = "";
    filteredMemberUsers.value = [];
    CloseModal();
  } catch (error) {
    MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
}

async function removeChatMember(member) {
  Swal.fire({
    icon: "warning",
    title: "Remove Member",
    text: `Are you sure you want to remove ${member.user?.name} from this chat?`,
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, remove!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        await apiRemoveMember(props.chatId, member.user_id);
        chatMembers.value = chatMembers.value.filter((m) => m.id !== member.id);
        CloseModal();
      } catch (error) {
        MessageModal("error", "Error", error.response?.data?.message || error.message);
      }
    }
  });
}

onMounted(() => {
  $(chatModal.value).on("show.bs.modal", async function () {
    if (props.chatId) {
      try {
        LoadingModal();
        await readChat(props.chatId);
        CloseModal();
      } catch (error) {
        MessageModal("error", "Error", error.response?.data?.message || error.message);
      }
    }
  });
  $(chatModal.value).on("hide.bs.modal", async function () {
    resetData();
  });
});

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

async function readChat(chatId) {
  const response = await apiReadChat(chatId);
  await onChatUpdated(response.data.chat);
  tempChatPhoto.value = chatData.photo;
  if (chatData.type === 'group') {
    await loadMembers();
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
  // await onChatUpdated(response.data.chat);
  hideChatModal();
}

async function deleteChat() {
  Swal.fire({
    icon: "warning",
    title: "Delete Chat",
    text: "Are you sure you want to delete this chat? This action cannot be undone.",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        await apiDeleteGroupChat(props.chatId);
        hideChatModal();
        CloseModal();
        emit("chatDeleted", props.chatId);
        window.dispatchEvent(new CustomEvent("chatDeleted", { detail: props.chatId }));
      } catch (error) {
        MessageModal("error", "Error", error.response?.data?.message || error.message);
      }
    }
  });
}

async function leaveChat() {
  Swal.fire({
    icon: "warning",
    title: "Leave Chat",
    text: "Are you sure you want to leave this chat?",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Yes, leave!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        LoadingModal();
        await apiLeaveGroupChat(props.chatId);
        hideChatModal();
        CloseModal();
        emit("chatDeleted", props.chatId);
        window.dispatchEvent(new CustomEvent("chatDeleted", { detail: props.chatId }));
      } catch (error) {
        MessageModal("error", "Error", error.response?.data?.message || error.message);
      }
    }
  });
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
  chatMembers.value = [];
  memberMeta.value = null;
  memberSearchQuery.value = "";
  filteredMemberUsers.value = [];
}
defineExpose({
  openChatModal,
  hideChatModal,
});
</script>
