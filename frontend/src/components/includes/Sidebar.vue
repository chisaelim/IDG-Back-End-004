<template>
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="height: auto">
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
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input
            class="form-control form-control-sidebar"
            type="search"
            placeholder="Search"
            aria-label="Search"
          />
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

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
    </div>
  </aside>
</template>
<script setup>
import emptyPhoto from "@assets/images/emptyPhoto.png";
import logoImg from "admin-lte/dist/img/AdminLTELogo.png";
import { useStore } from "vuex";
import { computed } from "vue";
const store = useStore();
const userData = computed(() => store.state.user);
const isAdmin = computed(() => userData.value && userData.value.level === "admin");
</script>
