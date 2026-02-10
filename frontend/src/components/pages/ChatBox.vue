<template>
  <div class="content-wrapper">
    <section class="content pt-3">
      <div class="container-fluid">
        <div class="card card-primary card-outline direct-chat direct-chat-primary">
          <div class="card-header d-flex align-items-center">
            <h3 class="card-title">
              <img :src="chatData.photo ?? emptyPhoto" class="direct-chat-img" />
            </h3>
            <h3 class="card-title mx-3">{{ chatData.name }}</h3>
            <div class="card-tools ml-auto">
              <button
                @click="chatModal.openChatModal"
                type="button"
                class="btn btn-tool"
                title="Edit Chat"
              >
                <i class="fas fa-edit"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div
              ref="messagesContainer"
              class="direct-chat-messages table-responsive"
              style="min-height: calc(100vh - 280px)"
            >
              <div v-if="isLoadingMore" class="text-center p-2">
                <i class="fas fa-spinner fa-spin"></i> Loading older messages...
              </div>
              <div
                v-for="msg in messages"
                :key="msg.id"
                class="direct-chat-msg"
                :class="{ right: msg.own_message }"
              >
                <div class="direct-chat-infos clearfix">
                  <span
                    class="direct-chat-name"
                    :class="msg.own_message ? 'float-right' : 'float-left'"
                  >
                    {{ msg.user?.name }}
                  </span>
                  <span
                    class="direct-chat-timestamp"
                    :class="msg.own_message ? 'float-left' : 'float-right'"
                  >
                    {{ formatFullDateTime(msg.created_at) }}
                  </span>
                </div>
                <img class="direct-chat-img" :src="msg.user?.photo || emptyPhoto" />
                <div class="direct-chat-text">
                  <span v-if="editingMessageId === msg.id">
                    <input
                      v-model="editingMessage"
                      @keyup.enter="saveEditMessage(msg.id)"
                      @keyup.esc="cancelEdit"
                      class="form-control form-control-sm"
                      type="text"
                    />
                    <div class="mt-1">
                      <button
                        @click="saveEditMessage(msg.id)"
                        class="btn btn-xs btn-success mr-1"
                      >
                        Save
                      </button>
                      <button @click="cancelEdit" class="btn btn-xs btn-secondary">
                        Cancel
                      </button>
                    </div>
                  </span>
                  <span v-else>
                    {{ msg.content }}
                    <span
                      v-if="msg.updated_at !== msg.created_at"
                      class="text-bold small"
                    >
                      (edited)
                    </span>
                  </span>
                </div>
                <!-- Actions for own messages -->
                <div
                  v-if="msg.own_message && editingMessageId !== msg.id"
                  class="mt-1 text-right"
                >
                  <a
                    v-if="msg.type === 'text'"
                    @click="startEdit(msg)"
                    class="text-primary mr-2 small"
                    role="button"
                  >
                    <i class="fas fa-edit"></i>
                  </a>
                  <a
                    @click="deleteMessage(msg.id)"
                    class="text-danger small"
                    role="button"
                  >
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </div>
              <div v-if="!messages.length" class="text-center text-muted p-3">
                No messages yet. Start a conversation!
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="input-group">
              <input
                v-model="newMessage"
                @keyup.enter="sendMessage"
                type="text"
                placeholder="Type Message ..."
                class="form-control"
              />
              <span class="input-group-append">
                <button @click="sendMessage" type="button" class="btn btn-primary">
                  Send
                </button>
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <ChatModal
    ref="chatModal"
    :chatId="chatId"
    @chatUpdated="onChatUpdated"
    @chatDeleted="onChatDeleted"
  />
</template>

<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import { useRoute, useRouter } from "vue-router";
import {
  computed,
  onMounted,
  onBeforeUnmount,
  ref,
  watch,
  nextTick,
  reactive,
} from "vue";
import { LoadingModal, MessageModal, CloseModal } from "@func/swal";
import { apiReadChat, apiGetChatFile } from "@func/api/chat";
import {
  apiGetMessages,
  apiCreateMessage,
  apiUpdateMessage,
  apiDeleteMessage,
  apiMarkAllMessagesAsSeen,
} from "@func/api/chat_message";
import { formatFullDateTime } from "@func/datetime";
import ChatModal from "@com/includes/controls/ChatModal.vue";

const router = useRouter();
const route = useRoute();
const chatId = computed(() => Number(route.params.chatId));
const chatModal = ref(null);

const chatData = reactive({
  name: "",
  photo: null,
  type: "",
  updatable: false,
});
const defaultChatData = JSON.parse(JSON.stringify(chatData));
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
  window.dispatchEvent(new CustomEvent("chatUpdated", { detail: chat }));
}

function onChatDeleted() {
  router.push({ name: "dashboard" });
}

const messages = ref([]);
const messageMeta = ref(null);
const isLoadingMore = ref(false);
const messagesContainer = ref(null);

const newMessage = ref("");
const editingMessageId = ref(null);
const editingMessage = ref("");

// Load chat info and messages
onMounted(async () => {
  await readChat();

  // jQuery scroll up to load older messages
  $(messagesContainer.value).on("scroll", function () {
    const scrollTop = $(this).scrollTop();
    if (scrollTop <= 50) {
      loadMoreMessages();
    }
  });
});

onBeforeUnmount(() => {
  if (messagesContainer.value) {
    $(messagesContainer.value).off("scroll");
  }
});

// Watch for route param changes (switching between chats)
watch(
  () => route.params.chatId,
  async (newChatId) => {
    if (newChatId) {
      resetData();
      await readChat();
    }
  }
);

async function readChat() {
  try {
    const response = await Promise.all([
      apiReadChat(chatId.value),
      apiGetMessages(chatId.value),
    ]);

    const chat = response[0].data.chat;
    await onChatUpdated(chat);

    messages.value = response[1].data.chat_messages.reverse();
    messageMeta.value = response[1].data.meta;

    await nextTick();
    scrollToBottom();

    await apiMarkAllMessagesAsSeen(chatId.value);
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message, onChatDeleted);
  }
}

async function loadMoreMessages() {
  if (isLoadingMore.value) return;
  if (!messageMeta.value) return;
  if (messageMeta.value.current_page >= messageMeta.value.last_page) return;

  isLoadingMore.value = true;
  const container = messagesContainer.value;
  const previousScrollHeight = container.scrollHeight;

  try {
    const nextPage = messageMeta.value.current_page + 1;
    const response = await apiGetMessages(chatId.value, { page: nextPage });
    const olderMessages = response.data.chat_messages.reverse();
    messageMeta.value = response.data.meta;
    messages.value = [...olderMessages, ...messages.value];

    await nextTick();
    // Maintain scroll position after prepending older messages
    container.scrollTop = container.scrollHeight - previousScrollHeight;
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message, onChatDeleted);
  } finally {
    isLoadingMore.value = false;
  }
}

async function sendMessage() {
  if (!newMessage.value.trim()) return;

  try {
    const response = await apiCreateMessage(chatId.value, {
      content: newMessage.value,
      type: "text",
    });
    messages.value.push(response.data.chat_message);
    newMessage.value = "";

    await nextTick();
    scrollToBottom();
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message, onChatDeleted);
  }
}

function startEdit(msg) {
  editingMessageId.value = msg.id;
  editingMessage.value = msg.content;
}

function cancelEdit() {
  editingMessageId.value = null;
  editingMessage.value = "";
}

async function saveEditMessage(messageId) {
  if (!editingMessage.value.trim()) return;

  try {
    const response = await apiUpdateMessage(chatId.value, messageId, {
      content: editingMessage.value,
    });
    messages.value = messages.value.map((m) =>
      m.id === messageId ? response.data.chat_message : m
    );
    cancelEdit();
  } catch (error) {
    if (error.response?.status === 422) {
      return MessageModal(
        "error",
        "Validation Error",
        error.response.data.errors?.content?.[0] || "Invalid input"
      );
    }
    return MessageModal("error", "Error", error.response?.data?.message || error.message, onChatDeleted);
  }
}

async function deleteMessage(messageId) {
  Swal.fire({
    title: "Are you sure you want to delete this message?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await apiDeleteMessage(chatId.value, messageId);
        messages.value = messages.value.filter((m) => m.id !== messageId);
      } catch (error) {
        return MessageModal(
          "error",
          "Error",
          error.response?.data?.message || error.message, onChatDeleted
        );
      }
    }
  });
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function resetData() {
  Object.assign(chatData, defaultChatData);
  messages.value = [];
  messageMeta.value = null;
  newMessage.value = "";
  editingMessageId.value = null;
  editingMessage.value = "";
}
</script>
