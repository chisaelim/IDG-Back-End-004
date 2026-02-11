<template></template>
<script setup>
import { postSignOut } from "@func/api/auth";
import { onMounted } from "vue";
import { useRouter } from "vue-router";
const router = useRouter();

onMounted(() => {
  try {
    const token = localStorage.getItem("token");
    postSignOut(token);
    localStorage.removeItem("token");
    router.replace({ name: "auth.signin" });
  } catch (error) {
    if (!error.response) {
      return MessageModal("error", "Error", error.message);
    }
    return MessageModal("error", "Error", error.response.data.message);
  }
});
</script>
