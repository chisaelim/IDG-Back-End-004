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
    <p class="chat-message">
      <span v-if="chat.last_message?.user?.id === userData.id" class="text-bold"
        >You:
      </span>
      {{ chat.last_message ? chat.last_message.content : "Start a new conversation" }}
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
