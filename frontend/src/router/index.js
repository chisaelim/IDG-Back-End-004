import Signin from '@com/auth/Signin.vue';
import Signout from '@com/auth/Signout.vue';
import Signup from '@com/auth/Signup.vue';
import VerifyEmail from '@com/auth/VerifyEmail.vue';
import ResetPassword from '@com/auth/ResetPassword.vue';
import SetNewPassword from '@com/auth/SetNewPassword.vue';
import GoogleCallback from '@com/auth/GoogleCallback.vue';
import GoogleCallbackError from '@com/auth/GoogleCallbackError.vue';
import UserProfile from '@com/auth/UserProfile.vue';
import Dashboard from '@com/pages/Dashboard.vue';

import { createRouter, createWebHistory } from 'vue-router'
import Navbar from '@com/includes/Navbar.vue';
import Footer from '@com/includes/Footer.vue';
import Sidebar from '@/components/includes/Sidebar.vue';

const includes = {
  navbar: Navbar,
  sidebar: Sidebar,
  footer: Footer,
}
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'auth.signin',
      component: Signin,
      meta: { guard: false },
    },
    {
      path: '/signout',
      name: 'auth.signout',
      component: Signout,
      meta: { guard: true },
    },
    {
      path: '/signup',
      name: 'auth.signup',
      component: Signup,
      meta: { guard: false },
    },
    {
      path: '/email/verify/:api_url',
      name: 'auth.verify.email',
      component: VerifyEmail,
      meta: { guard: false },
    },
    {
      path: '/password/reset',
      name: 'auth.reset.password',
      component: ResetPassword,
      meta: { guard: false },
    },
    {
      path: '/password/reset/:api_url',
      name: 'auth.set.password',
      component: SetNewPassword,
      meta: { guard: false },
    },
    {
      path: '/auth/google/callback',
      name: 'auth.google.callback',
      component: GoogleCallback,
      meta: { guard: false },
    },
    {
      path: '/auth/google/callback/error',
      name: 'auth.google.callback.error',
      component: GoogleCallbackError,
      meta: { guard: false },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      components: {
        default: Dashboard,
        ...includes,
      },
      meta: { guard: true },
    },
    {
      path: '/profile',
      name: 'profile',
      components: {
        default: UserProfile,
        ...includes,
      },
      meta: { guard: true },
    }
  ],
})

export default router
