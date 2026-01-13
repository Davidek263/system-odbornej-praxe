<template>
  <teleport to="body">
    <transition name="fade">
      <div v-if="overlay" class="spinner-backdrop">
        <div class="spinner" :style="spinnerStyle"></div>
      </div>
      <div v-else class="spinner" :style="spinnerStyle"></div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'Spinner',
  props: {
    /** Priemer spinnera v px */
    size: {
      type: Number,
      default: 40
    },
    /** Základná farba */
    color: {
      type: String,
      default: '#42b883'
    },
    /** Ak true, spinner sa zobrazí ako centrálny overlay */
    overlay: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    spinnerStyle() {
      const border = `${this.size / 8}px`;
      return {
        width: `${this.size}px`,
        height: `${this.size}px`,
        borderWidth: border,
        borderColor: `${this.color} transparent ${this.color} transparent`
      };
    }
  }
};
</script>

<style scoped>
@keyframes spin {
  0%   { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.spinner {
  border-style: solid;
  border-radius: 50%;
  display: inline-block;
  animation: spin 0.8s linear infinite;
}

/* Backdrop overlay */
.spinner-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
  z-index: 100;
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner-backdrop .spinner {
  z-index: 101;
}
</style>