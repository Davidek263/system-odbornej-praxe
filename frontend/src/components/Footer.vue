<template>
  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-left">
        <p class="footer-title">Evidencia odbornej praxe</p>
        <p class="footer-sub">
          © {{ new Date().getFullYear() }} — všetky práva vyhradené.
        </p>
      </div>

      <div class="footer-links">
        <RouterLink to="/about" class="footer-link">O projekte</RouterLink>
        <RouterLink to="/contact" class="footer-link">Kontakt</RouterLink>
        <a
          href="mailto:info@praxe.sk"
          class="footer-link special-link"
        >
          info@praxe.sk
        </a>
      </div>
    </div>
  </footer>
</template>

<script setup>
// čisto prezentačný komponent
</script>

<style scoped>
/* ============================= */
/*  HLAVNÉ NASTAVENIA */
/* ============================= */

.app-footer {
  background-color: #fafafa;
  border-top: 10px solid #dcdcdc;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.03);
  font-family: 'Inter', sans-serif;
  padding-bottom: 20px;
  color: #555;
}

/* ============================= */
/*  VNÚTORNÁ ŠTRUKTÚRA */
/* ============================= */

.footer-container {
  max-width: 1500px;
  margin: 0 auto;
  padding: 0 5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}

/* ============================= */
/*  ĽAVÁ ČASŤ */
/* ============================= */

.footer-left {
  display: outside;
  flex-direction: column;
}

.footer-title {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.footer-sub {
  font-size: 0.9rem;
  color: #666;
}

/* ============================= */
/*  ODKAZY */
/* ============================= */

.footer-links {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.footer-link {
  font-size: 0.9rem;
  text-decoration: none;
  color: #555;
  transition: color 0.2s ease;
}

.footer-link:hover {
  color: #1976d2;
}

.special-link {
  color: #1976d2;
  font-weight: 500;
}

/* ============================= */
/*  MOBILE RESPONSIVE */
/* ============================= */

@media (max-width: 768px) {
  .footer-container {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
  }

  .footer-links {
    justify-content: center;
    gap: 16px;
  }

  .footer-link {
    padding: 4px 0;
  }
}
</style>
