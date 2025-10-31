<template>
  <teleport to="body">
    <transition name="slide-fade">
      <div v-if="visible" :class="['page-alert-floating', type]">
        <slot>{{ displayMessage }}</slot>
        <button v-if="dismissible" class="close" @click="handleClose">&times;</button>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'PageAlert',
  props: {
    /** Kľúč hlásenia alebo vlastný text */
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
  emits: ['close'],
  data() {
    return {
      visible: this.show,
      // Predefinované správy
      messages: {
        // Validačné chyby
        'validation.form': 'Oprav chyby vo formulári.',
        'validation.email.required': 'Email je povinný.',
        'validation.email.invalid': 'Zadaj platný email.',
        'validation.password.required': 'Heslo je povinné.',
        'validation.password.min': 'Heslo musí mať aspoň 6 znakov.',
        'validation.first_name.required': 'Meno je povinné.',
        'validation.last_name.required': 'Priezvisko je povinné.',
        'validation.company_name.required': 'Názov firmy je povinný.',
        'validation.address.required': 'Adresa je povinná.',
        'validation.phone.required': 'Telefónne číslo je povinné.',
        
        // Autentifikačné chyby
        'auth.invalid': 'Nesprávny email alebo heslo.',
        'auth.forbidden': 'Nemáš oprávnenie na prihlásenie.',
        'auth.unauthorized': 'Musíš sa prihlásiť.',
        
        // Úspešné akcie
        'success.login': 'Prihlásenie úspešné!',
        'success.register.student': 'Registrácia úspešná! Skontroluj email pre nastavenie hesla.',
        'success.register.company': 'Firma bola úspešne zaregistrovaná!',
        'success.password.reset': 'Heslo bolo úspešne zmenené.',
        'success.password.reset.sent': 'Odkaz na obnovenie hesla bol odoslaný na tvoj email.',
        
        // Chyby servera
        'server.error': 'Problém so serverom. Skús to neskôr.',
        'server.maintenance': 'Server je momentálne nedostupný. Skús to neskôr.',
        
        // Sieťové chyby
        'network.error': 'Nemožno sa pripojiť k serveru. Skontroluj pripojenie.',
        'network.offline': 'Si offline. Skontroluj internetové pripojenie.',
        
        // Timeout
        'timeout.error': 'Požiadavka vypršala. Skús to znova.',
        
        // Rate limiting
        'ratelimit.error': 'Príliš veľa pokusov. Skús to neskôr.',
        
        // Duplicitné záznamy
        'duplicate.email': 'Tento email je už zaregistrovaný.',
        'duplicate.user': 'Používateľ s týmto emailom už existuje.',
        
        // Not found
        'notfound.user': 'Používateľ nebol nájdený.',
        'notfound.resource': 'Požadovaný zdroj nebol nájdený.',
        
        // Generické chyby
        'error.generic': 'Niečo sa pokazilo. Skús to znova.',
        'error.login': 'Prihlásenie zlyhalo. Skús to znova.',
        'error.register': 'Registrácia zlyhala. Skús to znova.',
        'error.password.reset': 'Obnovenie hesla zlyhalo. Skús to znova.',
        'error.password.reset.invalid': 'Neplatný alebo expirovaný odkaz na obnovenie hesla.'
      }
    };
  },
  computed: {
    displayMessage() {
      // Ak je správa v slovníku, použije sa tá
      if (this.messages[this.message]) {
        return this.messages[this.message];
      }
      // Inak sa použije vlastný text
      return this.message;
    }
  },
  watch: {
    show(newVal) {
      this.visible = newVal;
    }
  },
  methods: {
    handleClose() {
      this.visible = false;
      this.$emit('close');
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
  z-index: 10000;
  padding: 16px 24px;
  border-radius: 8px;
  max-width: 600px;
  min-width: 300px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-height: 80vh;
  overflow-y: auto;
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
  flex-shrink: 0;
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