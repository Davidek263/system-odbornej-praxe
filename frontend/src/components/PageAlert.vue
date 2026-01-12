<template>
  <teleport to="body">
    <transition name="slide-fade">
      <div v-if="visible" :class="['page-alert-floating', type]">
        <div class="alert-content">
          <span class="alert-icon">{{ icon }}</span>
          <span class="alert-message">{{ displayMessage }}</span>
        </div>
        <button v-if="dismissible" class="close" @click="handleClose">&times;</button>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'PageAlert',
  props: {
    message: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'error',
      validator: (value) => [
        'error', 'success', 'warning', 'info', 
        'validation', 'network', 'timeout', 'auth', 
        'server', 'notfound', 'forbidden', 'ratelimit'
      ].includes(value)
    },
    dismissible: {
      type: Boolean,
      default: true
    },
    show: {
      type: Boolean,
      default: true
    },
    duration: {
      type: Number,
      default: 0 // 0 = manual dismiss, otherwise auto-dismiss after X ms
    }
  },
  emits: ['close'],
  data() {
    return {
      visible: this.show,
      autoCloseTimer: null,
      messages: {
        // Validačné chyby
        'validation.form': 'Prosím opravte chyby vo formulári.',
        'validation.required': 'Toto pole je povinné.',
        'validation.email.required': 'Email je povinný.',
        'validation.email.invalid': 'Zadajte platný email.',
        'validation.email.format': 'Email musí byť vo formáte: meno.priezvisko@student.ukf.sk',
        'validation.password.required': 'Heslo je povinné.',
        'validation.password.min': 'Heslo musí mať aspoň 8 znakov.',
        'validation.password.mismatch': 'Heslá sa nezhodujú.',
        'validation.first_name.required': 'Meno je povinné.',
        'validation.last_name.required': 'Priezvisko je povinné.',
        'validation.company_name.required': 'Názov firmy je povinný.',
        'validation.address.required': 'Adresa je povinná.',
        'validation.phone.required': 'Telefónne číslo je povinné.',
        'validation.study_field.required': 'Študijný odbor je povinný.',
        
        // Autentifikačné chyby a správy
        'auth.invalid': 'Nesprávny email alebo heslo.',
        'auth.forbidden': 'Nemáte oprávnenie na prístup.',
        'auth.unauthorized': 'Musíte sa prihlásiť.',
        'auth.inactive': 'Váš účet nie je aktivovaný. Skontrolujte si email.',
        'auth.expired': 'Relácia vypršala. Prihláste sa znova.',
        'auth.token.invalid': 'Neplatný alebo expirovaný prístupový token.',
        'auth.logout': 'Boli ste úspešne odhlásení.',
        
        // Úspešné akcie - prihlásenie a registrácia
        'success.login': 'Prihlásenie úspešné.',
        'success.logout': 'Odhlásenie úspešné.',
        'success.register.student': 'Registrácia úspešná! Skontrolujte si email pre aktiváciu účtu.',
        'success.register.company': 'Firma bola úspešne zaregistrovaná! Skontrolujte si email pre aktiváciu.',
        'success.activation': 'Účet bol úspešne aktivovaný. Môžete sa prihlásiť.',
        'success.password.changed': 'Heslo bolo úspešne zmenené.',
        'success.password.reset.sent': 'Odkaz na obnovenie hesla bol odoslaný na váš email.',
        'success.email.changed': 'Email bol úspešne zmenený.',
        'success.email.change.requested': 'Verifikačný email bol odoslaný. Skontrolujte svoj email.',
        'info.email.verification.pending': 'Verifikačný email bol odoslaný na váš aktuálny email.',
        'success.email.verified': 'Email bol úspešne zmenený.',

        // Úspešné akcie - praxe
        'success.internship.confirmed': 'Prax bola úspešne potvrdená.',
        'success.internship.rejected': 'Prax bola zamietnutá.',
        'success.internship.created': 'Prax bola úspešne vytvorená.',
        'success.internship.updated': 'Prax bola úspešne aktualizovaná.',
        'success.internship.deleted': 'Prax bola úspešne zmazaná.',
        
        // Úspešné akcie - dokumenty
        'success.document.uploaded': 'Dokument bol úspešne nahraný.',
        'success.document.deleted': 'Dokument bol úspešne zmazaný.',
        'success.document.verified': 'Dokument bol overený.',
        
        // Chyby servera
        'server.error': 'Chyba servera. Skúste to neskôr.',
        'server.maintenance': 'Server je momentálne nedostupný kvôli údržbe.',
        'server.unavailable': 'Server je nedostupný. Skúste to neskôr.',
        
        // Email chyby
        'error.email.duplicate': 'Tento email už je použitý.',
        'error.email.format': 'Email musí byť vo formáte: meno.priezvisko@student.ukf.sk',
        'error.email.token.expired': 'Odkaz na zmenu emailu vypršal.',
        'error.email.token.invalid': 'Neplatný odkaz na zmenu emailu.',

        // Sieťové chyby
        'network.error': 'Chyba spojenia. Skontrolujte internetové pripojenie.',
        'network.offline': 'Ste offline. Skontrolujte pripojenie.',
        'network.timeout': 'Časový limit požiadavky vypršal.',
        
        // Timeout
        'timeout.error': 'Požiadavka trvala príliš dlho. Skúste to znova.',
        
        // Rate limiting
        'ratelimit.error': 'Príliš veľa pokusov. Počkajte chvíľu a skúste znova.',
        'ratelimit.login': 'Príliš veľa neúspešných pokusov o prihlásenie. Počkajte 15 minút.',
        
        // Duplicitné záznamy
        'duplicate.email': 'Tento email je už zaregistrovaný.',
        'duplicate.user': 'Používateľ s týmto emailom už existuje.',
        'duplicate.internship': 'Táto prax už existuje.',
        
        // Not found
        'notfound.user': 'Používateľ nebol nájdený.',
        'notfound.company': 'Firma nebola nájdená.',
        'notfound.internship': 'Prax nebola nájdená.',
        'notfound.document': 'Dokument nebol nájdený.',
        'notfound.resource': 'Požadovaný zdroj nebol nájdený.',
        'notfound.page': 'Stránka nebola nájdená.',
        
        // Forbidden
        'forbidden.access': 'Nemáte oprávnenie na prístup k tomuto zdroju.',
        'forbidden.action': 'Nemáte oprávnenie vykonať túto akciu.',
        'forbidden.company': 'Nemôžete pristupovať k dátam inej firmy.',
        
        // Chyby praxí
        'error.internship.load': 'Nepodarilo sa načítať odborné praxe.',
        'error.internship.confirm': 'Nepodarilo sa potvrdiť prax.',
        'error.internship.reject': 'Nepodarilo sa zamietnuť prax.',
        'error.internship.state': 'Prax nemôže byť potvrdená v tomto stave.',
        'error.internship.missing.company': 'Váš účet nie je spojený s firmou.',
        
        // Generické chyby
        'error.generic': 'Niečo sa pokazilo. Skúste to znova.',
        'error.unknown': 'Nastala neznáma chyba.',
        'error.login': 'Prihlásenie zlyhalo. Skúste to znova.',
        'error.register': 'Registrácia zlyhala. Skúste to znova.',
        'error.password.reset': 'Obnovenie hesla zlyhalo.',
        'error.password.reset.invalid': 'Neplatný alebo expirovaný odkaz na obnovenie hesla.',
        'error.load': 'Nepodarilo sa načítať dáta.',
        'error.save': 'Nepodarilo sa uložiť zmeny.',
        'error.delete': 'Nepodarilo sa zmazať záznam.',
        
        // Info správy
        'info.loading': 'Načítavam...',
        'info.saving': 'Ukladám...',
        'info.processing': 'Spracovávam...',
        'info.empty': 'Žiadne záznamy.',
        'info.no.results': 'Žiadne výsledky pre zadané filtre.',
        
        // Warnings
        'warning.unsaved': 'Máte neuložené zmeny.',
        'warning.delete.confirm': 'Naozaj chcete zmazať tento záznam?',
        'warning.irreversible': 'Táto akcia je nenávratná.',
      }
    };
  },
  computed: {
    displayMessage() {
      // Check if message key exists in dictionary
      if (this.messages[this.message]) {
        return this.messages[this.message];
      }
      // Otherwise use the message as-is
      return this.message || 'Nastala chyba.';
    },
    icon() {
      const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ',
        validation: '⚠',
        network: '⚠',
        timeout: '⚠',
        auth: '🔒',
        server: '✕',
        notfound: 'ℹ',
        forbidden: '🔒',
        ratelimit: '⚠'
      };
      return icons[this.type] || 'ℹ';
    }
  },
  watch: {
    show(newVal) {
      this.visible = newVal;
      if (newVal && this.duration > 0) {
        this.startAutoClose();
      }
    }
  },
  mounted() {
    if (this.visible && this.duration > 0) {
      this.startAutoClose();
    }
  },
  beforeUnmount() {
    this.clearAutoClose();
  },
  methods: {
    handleClose() {
      this.visible = false;
      this.clearAutoClose();
      this.$emit('close');
    },
    startAutoClose() {
      this.clearAutoClose();
      this.autoCloseTimer = setTimeout(() => {
        this.handleClose();
      }, this.duration);
    },
    clearAutoClose() {
      if (this.autoCloseTimer) {
        clearTimeout(this.autoCloseTimer);
        this.autoCloseTimer = null;
      }
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
  top: 130px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10000;
  padding: 16px 24px;
  border-radius: 10px;
  max-width: 600px;
  min-width: 320px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  font-weight: 500;
  font-size: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(10px);
}

.alert-content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.alert-icon {
  font-size: 20px;
  font-weight: bold;
  flex-shrink: 0;
}

.alert-message {
  line-height: 1.5;
  word-wrap: break-word;
}

/* Standard Types */
.page-alert-floating.error {
  background: #fee2e2;
  color: #991b1b;
  border-left: 4px solid #dc2626;
}

.page-alert-floating.success {
  background: #dcfce7;
  color: #166534;
  border-left: 4px solid #16a34a;
}

.page-alert-floating.warning {
  background: #fef3c7;
  color: #92400e;
  border-left: 4px solid #f59e0b;
}

.page-alert-floating.info {
  background: #dbeafe;
  color: #1e40af;
  border-left: 4px solid #3b82f6;
}

/* Specific Error Types */
.page-alert-floating.validation {
  background: #fef3c7;
  color: #92400e;
  border-left: 4px solid #f59e0b;
}

.page-alert-floating.network {
  background: #fee2e2;
  color: #991b1b;
  border-left: 4px solid #dc2626;
}

.page-alert-floating.timeout {
  background: #fed7aa;
  color: #9a3412;
  border-left: 4px solid #ea580c;
}

.page-alert-floating.auth {
  background: #fce7f3;
  color: #831843;
  border-left: 4px solid #db2777;
}

.page-alert-floating.server {
  background: #fee2e2;
  color: #7f1d1d;
  border-left: 4px solid #b91c1c;
}

.page-alert-floating.notfound {
  background: #e0e7ff;
  color: #3730a3;
  border-left: 4px solid #6366f1;
}

.page-alert-floating.forbidden {
  background: #ffe4e6;
  color: #9f1239;
  border-left: 4px solid #e11d48;
}

.page-alert-floating.ratelimit {
  background: #fed7aa;
  color: #7c2d12;
  border-left: 4px solid #c2410c;
}

.close {
  background: rgba(0, 0, 0, 0.1);
  border: none;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  opacity: 0.7;
  transition: all 0.2s;
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.close:hover {
  opacity: 1;
  background: rgba(0, 0, 0, 0.2);
  transform: scale(1.1);
}

/* Mobile responsive */
@media (max-width: 640px) {
  .page-alert-floating {
    left: 10px;
    right: 10px;
    transform: none;
    max-width: none;
    min-width: auto;
    font-size: 14px;
  }
  
  .alert-icon {
    font-size: 18px;
  }
}
</style>