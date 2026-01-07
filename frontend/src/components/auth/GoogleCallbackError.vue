<template>
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-danger">
        <div class="card-body text-center">
          <div class="mb-3">
            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
          </div>
          <h4 class="login-box-msg text-danger">Google Authentication Failed</h4>
          <p class="text-muted mb-4">
            We couldn't sign you in with Google. This could be due to:
          </p>
          <ul class="text-left text-muted mb-4">
            <li>You cancelled the Google sign-in process</li>
            <li>There was a temporary issue with Google's servers</li>
            <li>Your Google account may not have the required permissions</li>
          </ul>
          <div class="row">
            <div class="col-6">
              <button @click="tryAgain" class="btn btn-danger btn-block">
                <i class="fab fa-google mr-2"></i> Try Again
              </button>
            </div>
            <div class="col-6">
              <button @click="goToSignin" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-sign-in-alt mr-2"></i> Sign In
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { LoadingModal, MessageModal } from '@func/swal';

const router = useRouter();

function tryAgain() {
  try {
    LoadingModal();
    window.location.href = `${window.API_URL}/auth/google`;
  } catch (error) {
    MessageModal("error", "Error", "Failed to redirect to Google. Please try again.");
  }
}

function goToSignin() {
  router.push({ name: 'auth.signin' });
}
</script>