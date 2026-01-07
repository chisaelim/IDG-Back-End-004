import Signin from '@com/auth/Signin.vue';
import Signout from '@com/auth/Signout.vue';
import Signup from '@com/auth/Signup.vue';
import VerifyEmail from '@com/auth/VerifyEmail.vue';
import ResetPassword from '@com/auth/ResetPassword.vue';
import SetNewPassword from '@com/auth/SetNewPassword.vue';
import GoogleCallback from '@com/auth/GoogleCallback.vue';
import GoogleCallbackError from '@com/auth/GoogleCallbackError.vue';
import Dashboard from '@com/pages/Dashboard.vue';

import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'auth.signin',
      component: Signin,
    },
    {
      path: '/signout',
      name: 'auth.signout',
      component: Signout,
    },
    {
      path: '/signup',
      name: 'auth.signup',
      component: Signup,
    },
    {
      path: '/email/verify/:api_url',
      name: 'auth.verify.email',
      component: VerifyEmail,

    },
    {
      path: '/password/reset',
      name: 'auth.reset.password',
      component: ResetPassword,
    },
    {
      path: '/password/reset/:api_url',
      name: 'auth.set.password',
      component: SetNewPassword,
    },
    {
      path: '/auth/google/callback',
      name: 'auth.google.callback',
      component: GoogleCallback,
    },
    {
      path: '/auth/google/callback/error',
      name: 'auth.google.callback.error',
      component: GoogleCallbackError,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: Dashboard,

    }
  ],
})

export default router
