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
                  <!-- Editing mode (text only) -->
                  <span v-if="editingMessageId === msg.id">
                    <input
                      v-model="editingMessage"
                      @keyup.enter="saveEdit()"
                      @keyup.esc="cancelEdit"
                      class="form-control form-control-sm"
                      type="text"
                    />
                    <div class="mt-1">
                      <button @click="saveEdit()" class="btn btn-xs btn-success mr-1">
                        Save
                      </button>
                      <button @click="cancelEdit" class="btn btn-xs btn-secondary">
                        Cancel
                      </button>
                    </div>
                  </span>

                  <!-- Text message -->
                  <span v-else-if="msg.type === 'text'">
                    {{ msg.content }}
                    <span
                      v-if="msg.updated_at !== msg.created_at"
                      class="text-bold small"
                    >
                      (edited)
                    </span>
                  </span>

                  <!-- Image message -->
                  <span v-else-if="msg.type === 'image'">
                    <img
                      :src="msg.content"
                      class="img-fluid rounded"
                      style="max-width: 250px; max-height: 250px; cursor: pointer"
                      @click="openFile(msg.content)"
                    />
                  </span>

                  <!-- Video message -->
                  <span v-else-if="msg.type === 'video'">
                    <video
                      controls
                      :src="msg.content"
                      class="rounded"
                      style="max-width: 300px; max-height: 200px"
                    ></video>
                  </span>

                  <!-- Video message -->
                  <span v-else-if="msg.type === 'audio'">
                    <audio controls :src="msg.content" style="max-width: 250px"></audio>
                  </span>

                  <!-- File message (includes voice) -->
                  <span v-else-if="msg.type === 'file'">
                    <a :href="msg.content" target="_blank" class="text-white">
                      <i class="fas fa-file mr-1"></i>
                      {{ msg.originalContent }}
                    </a>
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
            <!-- File preview -->
            <div v-if="selectedFile" class="mb-2 d-flex align-items-center">
              <span class="badge badge-secondary p-2 mr-2">
                <i :class="fileTypeIcon"></i>
                {{ selectedFile.name }}
              </span>
              <button @click="clearSelectedFile" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i>
              </button>
            </div>

            <!-- Voice recording indicator -->
            <div v-if="isRecording" class="mb-2 d-flex align-items-center">
              <span class="badge p-2 mr-2" :class="isPaused ? 'badge-warning' : 'badge-danger'">
                <i class="fas fa-circle text-white" :class="{ blink: !isPaused }"></i>
                {{ isPaused ? 'Paused' : 'Recording...' }} {{ recordingDuration }}s / {{ audioDuration }}s
              </span>
              <button @click="pauseRecording" class="btn btn-sm mr-1" :class="isPaused ? 'btn-info' : 'btn-warning'">
                <i :class="isPaused ? 'fas fa-microphone' : 'fas fa-pause'"></i>
                {{ isPaused ? 'Resume' : 'Pause' }}
              </button>
              <button @click="stopRecording(false)" class="btn btn-sm btn-success mr-1">
                <i class="fas fa-paper-plane"></i> Send
              </button>
              <button @click="stopRecording(true)" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i> Cancel
              </button>
            </div>

            <div class="input-group">
              <!-- File upload -->
              <input
                ref="fileInput"
                type="file"
                class="d-none"
                @change="onFileSelected"
              />
              <span class="input-group-prepend">
                <button
                  @click="fileInput.click()"
                  type="button"
                  class="btn btn-default"
                  title="Send file"
                  :disabled="isRecording"
                >
                  <i class="fas fa-paperclip"></i>
                </button>
              </span>

              <input
                v-model="newMessage"
                @keyup.enter="sendMessage"
                type="text"
                placeholder="Type Message ..."
                class="form-control"
                :disabled="isRecording || !!selectedFile"
              />

              <span class="input-group-append">
                <!-- Voice record button -->
                <button
                  v-if="!newMessage.trim() && !selectedFile"
                  @click="startRecording"
                  type="button"
                  class="btn btn-default"
                  title="Record voice"
                  :disabled="isRecording"
                >
                  <i class="fas fa-microphone"></i>
                </button>

                <button v-else-if="isSending" type="button" class="btn btn-primary">
                  <div
                    class="spinner-border"
                    role="status"
                    style="width: 1rem; height: 1rem"
                  >
                    <span class="visually-hidden"></span>
                  </div>
                </button>
                <!-- Send button -->
                <button v-else @click="sendMessage" type="button" class="btn btn-primary">
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

// File upload
const selectedFile = ref(null);
const fileInput = ref(null);

// Voice recording
const isSending = ref(false);
const isRecording = ref(false);
const isPaused = ref(false);
const recordingDuration = ref(0);
let mediaRecorder = null;
let audioDuration = 60;
let audioChunks = [];
let recordingTimer = null;
let isCancelledRecording = false;

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

    await processMessages(messages.value);

    await nextTick();
    scrollToBottom();

    await apiMarkAllMessagesAsSeen(chatId.value);
  } catch (error) {
    return MessageModal(
      "error",
      "Error",
      error.response?.data?.message || error.message,
      onChatDeleted
    );
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

    await processMessages(olderMessages);
    messages.value = [...olderMessages, ...messages.value];

    await nextTick();
    // Maintain scroll position after prepending older messages
    container.scrollTop = container.scrollHeight - previousScrollHeight;
  } catch (error) {
    return MessageModal(
      "error",
      "Error",
      error.response?.data?.message || error.message,
      onChatDeleted
    );
  } finally {
    isLoadingMore.value = false;
  }
}

async function sendMessage() {
  try {
    isSending.value = true;
    if (selectedFile.value) {
      // Send file message
      const type = getFileType(selectedFile.value);
      const response = await apiCreateMessage(chatId.value, {
        content: selectedFile.value,
        type: type,
      });
      const fileMsg = response.data.chat_message;
      await processMessages([fileMsg]);
      messages.value.push(fileMsg);
      clearSelectedFile();
    } else {
      // Send text message
      if (!newMessage.value.trim()) return;

      const response = await apiCreateMessage(chatId.value, {
        content: newMessage.value,
        type: "text",
      });
      messages.value.push(response.data.chat_message);
      newMessage.value = "";
    }

    await nextTick();
    scrollToBottom();
  } catch (error) {
    return MessageModal(
      "error",
      "Error",
      error.response?.data?.message || error.message,
      onChatDeleted
    );
  } finally {
    isSending.value = false;
  }
}

// File helpers
function getFileType(file) {
  const mime = file.type;
  if (mime.startsWith("image/")) return "image";
  if (mime.startsWith("video/")) return "video";
  if (mime.startsWith("audio/")) return "audio";
  return "file";
}

function onFileSelected(event) {
  const file = event.target.files[0];
  if (!file) return;
  selectedFile.value = file;
  event.target.value = null;
}

function clearSelectedFile() {
  selectedFile.value = null;
}

const fileTypeIcon = computed(() => {
  if (!selectedFile.value) return "fas fa-file";
  const type = getFileType(selectedFile.value);
  if (type === "image") return "fas fa-image";
  if (type === "video") return "fas fa-video";
  if (type === "audio") return "fas fa-microphone";
  return "fas fa-file";
});

function isVoiceFile(filename) {
  if (!filename) return false;
  return (
    filename.toLowerCase().endsWith(".webm") || filename.toLowerCase().endsWith(".ogg")
  );
}

async function loadFile(uri) {
  try {
    const response = await apiGetChatFile(uri);
    return URL.createObjectURL(response.data);
  } catch {
    return emptyPhoto;
  }
}

function openFile(url) {
  window.open(url, "_blank");
}

async function processMessages(msgs) {
  await Promise.all(
    msgs.map(async (msg) => {
      if (msg.type !== "text") {
        msg.content = await loadFile(msg.content);
      }
    })
  );
}

// Voice recording
async function startRecording() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(stream);
    audioChunks = [];
    isCancelledRecording = false;

    mediaRecorder.ondataavailable = (e) => {
      if (e.data.size > 0) audioChunks.push(e.data);
    };

    mediaRecorder.onstop = async () => {
      stream.getTracks().forEach((track) => track.stop());

      if (isCancelledRecording || audioChunks.length === 0) return;

      const blob = new Blob(audioChunks, { type: "audio/webm" });
      const file = new File([blob], `VOICE-${Date.now()}.webm`, { type: "audio/webm" });

      try {
        const response = await apiCreateMessage(chatId.value, {
          content: file,
          type: "audio",
        });
        const voiceMsg = response.data.chat_message;
        await processMessages([voiceMsg]);
        messages.value.push(voiceMsg);

        await nextTick();
        scrollToBottom();
      } catch (error) {
        MessageModal(
          "error",
          "Error",
          error.response?.data?.message || error.message,
          onChatDeleted
        );
      }
    };

    mediaRecorder.start();
    isRecording.value = true;
    isPaused.value = false;
    recordingDuration.value = 0;
    recordingTimer = setInterval(() => {
      if (!isPaused.value) {
        recordingDuration.value++;
        if (recordingDuration.value >= audioDuration) {
          stopRecording(false);
        }
      }
    }, 1000);
  } catch (error) {
    MessageModal(
      "error",
      "Error",
      "Microphone access denied. Please allow microphone permissions."
    );
  }
}

function pauseRecording() {
  if (!mediaRecorder || mediaRecorder.state === "inactive") return;
  if (isPaused.value) {
    mediaRecorder.resume();
    isPaused.value = false;
  } else {
    mediaRecorder.pause();
    isPaused.value = true;
  }
}

function stopRecording(value) {
  isCancelledRecording = value;
  if (mediaRecorder && mediaRecorder.state !== "inactive") {
    mediaRecorder.stop();
  }
  isRecording.value = false;
  isPaused.value = false;
  clearInterval(recordingTimer);
}

function startEdit(msg) {
  editingMessageId.value = msg.id;
  editingMessage.value = msg.content;
}

function cancelEdit() {
  editingMessageId.value = null;
  editingMessage.value = "";
}

async function saveEdit() {
  if (!editingMessage.value.trim()) return;

  try {
    const response = await apiUpdateMessage(chatId.value, editingMessageId.value, {
      content: editingMessage.value,
    });
    messages.value = messages.value.map((m) =>
      m.id === editingMessageId.value ? response.data.chat_message : m
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
    return MessageModal(
      "error",
      "Error",
      error.response?.data?.message || error.message,
      onChatDeleted
    );
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
          error.response?.data?.message || error.message,
          onChatDeleted
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
  selectedFile.value = null;
  stopRecording(true);
}
</script>
