<template>
  <router-link
    :to="{ name: 'chats', params: { chatId: chat.id } }"
    class="nav-link"
    active-class="active"
  >
    <img class="nav-icon img-circle elevation-3 my-1" :src="chat.photo" />
    <p class="chat-name">{{ chat.name }}</p>
    <p class="chat-datetime">
      {{ chat.last_message ? formatChatTime(chat.last_message.created_at) : "" }}
    </p>
    <br />
    <p class="chat-message mt-1">
      <span v-if="chat.last_message?.own_message" class="text-bold">You: </span>
      <span v-if="!chat.last_message">Start a new conversation</span>
      <span v-else-if="chat.last_message.type === 'image'">Send an image.</span>
      <span v-else-if="chat.last_message.type === 'audio'">Send an audio.</span>
      <span v-else-if="chat.last_message.type === 'video'">Send a video.</span>
      <span v-else-if="chat.last_message.type === 'file'">Send a file.</span>
      <span v-else>{{ chat.last_message.content }}</span>
    </p>
    <p class="chat-activity-icon">
      <i class="far fa-paper-plane"></i>
      <!-- <i class="far fa-comment-dots"></i>
      <i class="fas fa-microphone"></i> -->
    </p>
  </router-link>
</template>
<script setup>
import { formatChatTime } from "@func/datetime";
import { computed } from "vue";
import { useStore } from "vuex";
const store = useStore();
const userData = computed(() => store.state.user);
const props = defineProps({
  chat: {
    type: Object,
    required: true,
  },
});
</script>
