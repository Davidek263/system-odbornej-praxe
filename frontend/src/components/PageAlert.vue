<template>
  <teleport to="body">
    <transition name="slide-fade">
      <div v-if="visible" :class="['page-alert-floating', type]">
        <slot>{{ message }}</slot>
        <button v-if="dismissible" class="close" @click="visible = false">&times;</button>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'PageAlert',
  props: {
    /** Text hlásenia (ak nepoužiješ slot) */
    message: {
      type: String,
      default: ''
    },
    /** Typ vzhľadu: error | success | warning | info | validation | network | timeout | auth | server | notfound | forbidden | ratelimit */
    type: {
      type: String,
      default: 'error',
      validator: (value) => [
        'error', 'success', 'warning', 'info', 
        'validation', 'network', 'timeout', 'auth', 
        'server', 'notfound', 'forbidden', 'ratelimit'
      ].includes(value)
    },
    /** Zobrazí X tlačidlo */
    dismissible: {
      type: Boolean,
      default: true
    },
    /** Východzí stav viditeľnosti */
    show: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      visible: this.show
    };
  },
  watch: {
    show(newVal) {
      this.visible = newVal;
    }
  }
};
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.25s ease-in;
}

.slide-fade-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

.page-alert-floating {
  position: fixed;
  top: 150px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  padding: 16px 24px;
  border-radius: 8px;
  max-width: 600px;
  min-width: 300px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Variants - Standard Types */
.page-alert-floating.error   { background: #fee2e2; color: #b91c1c; }
.page-alert-floating.success { background: #dcfce7; color: #166534; }
.page-alert-floating.warning { background: #fef9c3; color: #854d0e; }
.page-alert-floating.info    { background: #e0f2fe; color: #0c4a6e; }

/* Variants - Specific Error Types */
.page-alert-floating.validation { background: #fef3c7; color: #92400e; }
.page-alert-floating.network    { background: #fee2e2; color: #991b1b; }
.page-alert-floating.timeout    { background: #fed7aa; color: #9a3412; }
.page-alert-floating.auth       { background: #fce7f3; color: #831843; }
.page-alert-floating.server     { background: #fee2e2; color: #7f1d1d; }
.page-alert-floating.notfound   { background: #e0e7ff; color: #3730a3; }
.page-alert-floating.forbidden  { background: #ffe4e6; color: #9f1239; }
.page-alert-floating.ratelimit  { background: #fed7aa; color: #7c2d12; }

.close {
  margin-left: auto;
  background: transparent;
  border: none;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.close:hover {
  opacity: 1;
}

/* Mobile responsive */
@media (max-width: 640px) {
  .page-alert-floating {
    left: 10px;
    right: 10px;
    transform: none;
    max-width: none;
    min-width: auto;
  }
}
</style>