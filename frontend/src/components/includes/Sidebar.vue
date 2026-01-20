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
            <img :src="profilePic" class="img-circle elevation-2" alt="User Image" />
          </div>
          <div class="info">
            <a href="#" class="d-block">Nina Mcintire</a>
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
        <!-- <div class="sidebar-search-results">
          <div class="list-group">
            <a href="#" class="list-group-item"
              ><div class="search-title">
                <strong class="text-light"></strong>N<strong class="text-light"></strong
                >o<strong class="text-light"></strong>
                <strong class="text-light"></strong>e<strong class="text-light"></strong
                >l<strong class="text-light"></strong>e<strong class="text-light"></strong
                >m<strong class="text-light"></strong>e<strong class="text-light"></strong
                >n<strong class="text-light"></strong>t<strong
                  class="text-light"
                ></strong>
                <strong class="text-light"></strong>f<strong class="text-light"></strong
                >o<strong class="text-light"></strong>u<strong class="text-light"></strong
                >n<strong class="text-light"></strong>d<strong class="text-light"></strong
                >!<strong class="text-light"></strong>
              </div>
              <div class="search-path"></div
            ></a>
          </div>
        </div> -->
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
import logoImg from "admin-lte/dist/img/AdminLTELogo.png";
import profilePic from "admin-lte/dist/img/user4-128x128.jpg";
import { useStore } from "vuex";
import { computed } from "vue";
const store = useStore();
const user = computed(() => store.state.user);
const isAdmin = computed(() => user.value && user.value.level === "admin");
</script>
