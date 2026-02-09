import 'admin-lte/dist/js/adminlte.min.js';
import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js';

import Swal from 'sweetalert2';
window.Swal = Swal;

import axios from 'axios';
window.axios = axios;

import jquery from 'jquery';
window.$ = jquery;
window.jQuery = jquery;

window.API_URL = import.meta.env.VITE_API_URL;

import { createApp } from 'vue';
import { createStore } from 'vuex';
import App from './App.vue';
import router from './router';
import { getVerifyAccount } from '@func/api/auth';
const app = createApp(App);

const store = createStore({
    state: {
        user: null,
    },
    mutations: {
        setUser(state, user) {
            state.user = user;
        },
        setUserPhoto(state, photo) {
            if (state.user) {
                state.user.photo = photo;
            }
        },
    },
    actions: {
        async verifyAccount({ commit }) {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    localStorage.removeItem('token');
                    return commit('setUser', null);
                }
                axios.defaults.headers.common['Authorization'] = token ? `Bearer ${token}` : '';
                const response = await getVerifyAccount(token);
                return commit('setUser', response.data.user);
            } catch (error) {
                localStorage.removeItem('token');
                commit('setUser', null);
            }
        }
    }
});


app.use(router);
app.use(store);
app.mount('#app');


router.beforeEach(async (to, from, next) => {
    await store.dispatch('verifyAccount');
    const guard = Boolean(to.meta.guard);
    const isAuthenticated = Boolean(store.state.user !== null);
    if (!guard && isAuthenticated) {
        return next({ name: 'dashboard' })
    }
    if (guard && !isAuthenticated) {
        return next({ name: 'auth.signin' });
    }
    return next();
});