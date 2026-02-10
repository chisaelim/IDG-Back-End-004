<template>
  <a @click="createPersonalChat" class="nav-link" role="button">
    <img class="nav-icon img-circle elevation-3 my-1" :src="user.photo || emptyPhoto" />
    <p class="chat-name">{{ user.name }}</p>
    <br />
    <p class="chat-message">{{ user.email }}</p>
  </a>
</template>

<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import { apiCreateChat } from "@func/api/chat";
import { useRouter } from "vue-router";
import { MessageModal } from "@func/swal";

const router = useRouter();

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
});

async function createPersonalChat() {
  try {
    const response = await apiCreateChat({
      type: "personal",
      user_ids: [props.user.id],
    });
    window.dispatchEvent(new CustomEvent("chatUpdated", { detail: response.data.chat }));
    router.push({ name: "chats", params: { chatId: response.data.chat.id } });
  } catch (error) {
    return MessageModal("error", "Error", error.response?.data?.message || error.message);
  }
}
</script>
