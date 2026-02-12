import 'admin-lte/dist/js/adminlte.min.js';
import 'admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js';

import Swal from 'sweetalert2';
window.Swal = Swal;

import axios from 'axios';
window.axios = axios;

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Enable Pusher/Echo debug logging
// Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    wsPath: import.meta.env.VITE_REVERB_PATH ?? '',
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => {
        return {
            authorize: (socketId, callback) => {
                axios.post(import.meta.env.VITE_API_URL + '/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name,
                }, {
                    headers: {
                        Authorization: 'Bearer ' + (localStorage.getItem('token') || ''),
                    },
                })
                    .then(response => callback(null, response.data))
                    .catch(error => callback(error));
            },
        };
    },
});


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
        const intendedPath = to.path;
        localStorage.setItem('intendedPath', intendedPath);
        return next({ name: 'auth.signin' });
    }
    return next();
});