<template>
  <div class="landing-page">
    <!-- HERO -->
    <section class="hero">
      <div class="hero-content">
        <h1>Vitajte v systéme <span>odbornej praxe</span></h1>
        <p>Spravujte evidenciu dokumentov a procesov rýchlo, bezpečne a prehľadne.</p>
      </div>

      <!-- Quick navigation pills -->
      <div class="quick-nav">
        <button @click="scrollToSection('student')" class="nav-pill">
          <i class="fas fa-user-graduate"></i>
          <span>Študent</span>
        </button>
        <button @click="scrollToSection('company')" class="nav-pill">
          <i class="fas fa-building"></i>
          <span>Firma</span>
        </button>
      </div>
    </section>

    <!-- STUDENT INFO -->
    <section id="student" class="info-section student-section">
      <div class="section-header">
        <h2>Informácie pre študentov</h2>
        <p class="subtitle">Prečítajte si svoje právomoci, možnosti a povinnosti pri evidencii odbornej praxe.</p>
      </div>
      <div class="info-grid">
        <div class="info-card" v-for="(section, idx) in studentSections" :key="idx">
          <h3>{{ section.title }}</h3>
          <ul>
            <li v-for="(item, iIdx) in section.items" :key="iIdx" v-html="item" />
          </ul>
        </div>
      </div>
    </section>

    <!-- COMPANY INFO -->
    <section id="company" class="info-section company-section">
      <div class="section-header">
        <h2>Informácie pre firmy</h2>
        <p class="subtitle">Zistite, aké máte právomoci, možnosti a povinnosti pri spolupráci na odbornej praxi.</p>
      </div>
      <div class="info-grid">
        <div class="info-card" v-for="(section, idx) in companySections" :key="idx">
          <h3>{{ section.title }}</h3>
          <ul>
            <li v-for="(item, iIdx) in section.items" :key="iIdx" v-html="item" />
          </ul>
        </div>
      </div>
    </section>


    <!-- MORE INFO -->
    <section class="info-section more-info-section">
      <div class="section-header">
        <h2>Chcete vedieť viac?</h2>
        <p class="subtitle">Viac informácií najdete tu:</p>
        <a
          class="more-info-link"
          href="https://www.fpvai.ukf.sk/sk/studium-fpv-ukf/organizacia-studia"
          target="_blank"
          rel="noopener"
        >
          https://www.fpvai.ukf.sk/sk/studium-fpv-ukf/organizacia-studia
        </a>
      </div>
    </section>
  </div>
</template>

<script>
// ============================================================
// IMPORTS & SETUP
// ============================================================
export default {
  name: 'LandingPage',

  // ============================================================
  // STATE MANAGEMENT
  // ============================================================
  data() {
    return {
      studentSections: [
        {
          title: 'Právomoci',
          items: [
            'Prezerať zoznam vlastných odborných praxí.',
            'Zakladať novú prax výberom firmy, dátumov, roku a semestra.',
            'Stiahnuť vygenerované PDF „Dohoda o odbornej praxi".',
            'Uploadovať podpísané dokumenty (zmluva, výkaz).',
            'Prijímať emailové notifikácie o zmenách stavu praxe.'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Full-text vyhľadávanie firiem pri zakladaní praxe.',
            'Úprava osobných údajov po prihlásení.',
            'Obnovenie zabudnutého hesla cez email.',
            'Voliteľné nahranie výkazu s priebehom praxe.'
          ]
        },
        {
          title: 'Povinnosti',
          items: [
            'Po prvom prihlásení si zmeniť dočasné heslo.',
            'Nahrať podpísanú zmluvu pri stave praxe <em>Schválená</em>.',
            'Vyplniť pravdivé údaje pri zakladaní novej praxe.',
            'Dodržať termíny odovzdania povinných dokumentov.'
          ]
        }
      ],
      companySections: [
        {
          title: 'Právomoci',
          items: [
            'Prezrieť zoznam praxí v stave Vytvorená a detail jednotlivých žiadostí.',
            'Potvrdiť alebo zamietnuť prax (zmena stavu na Potvrdená / Zamietnutá), pričom systém automaticky odošle notifikácie študentovi a garantovi.',
            'Nahrať výkaz pracovných hodín a potvrdiť alebo zamietnuť výkaz od študenta.',
            'Aktualizovať profil firmy – kontaktná osoba, adresa, popis.',
            'Zobraziť históriu a archív dokončených praxí.'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Registrácia prostredníctvom formulára: názov, adresa, kontaktná osoba.',
            'Aktivácia účtu cez potvrdzovací email (stav Neaktívny → Aktívny).',
            'Obnova alebo zmena hesla cez funkciu „Zabudli ste heslo?".',
            'Prijímanie emailových notifikácií pri zmenách stavu praxe a dokumentov.',
            'Možnosť filtrovať vlastné praxe podľa roku alebo stavu.'
          ]
        },
        {
          title: 'Povinnosti',
          items: [
            'Po prvom prihlásení zmeniť dočasné heslo.',
            'Reagovať na nové žiadosti o prax – potvrdiť alebo zamietnuť do stanoveného termínu.',
            'Poskytnúť študentovi podpísaný výkaz alebo potvrdiť jeho elektronickú verziu.',
            'Zabezpečiť, aby kontaktné údaje boli aktuálne a pravdivé.',
            'Dodržiavať podmienky dohody o odbornej praxi, vrátane bezpečnosti práce.'
          ]
        }
      ]
    }
  },

  // ============================================================
  // FUNCTIONS
  // ============================================================
  methods: {
    scrollToSection(sectionId) {
      const element = document.getElementById(sectionId)
      if (element) {
        const headerOffset = 80
        const elementPosition = element.getBoundingClientRect().top
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        })
      }
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');

.landing-page {
  font-family: 'Inter', sans-serif;
  color: #1f2937;
  overflow-x: hidden;
  width: 100%;
  max-width: 100vw;
}

/* ============================= */
/* HERO SECTION */
/* ============================= */
.hero {
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  color: #fff;
  min-height: 70vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 100px 20px 40px;
  position: relative;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
}

.hero-content {
  max-width: 800px;
  margin-bottom: 40px;
}

.hero-content h1 {
  font-size: clamp(2rem, 4.5vw + 1rem, 3.5rem);
  margin-bottom: 1.25rem;
  line-height: 1.2;
  font-weight: 800;
}

.hero-content h1 span {
  display: inline-block;
  background: linear-gradient(90deg, #fff 0%, rgba(255,255,255,0.85) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-content p {
  font-size: 1.2rem;
  margin-bottom: 2rem;
  opacity: 0.95;
  line-height: 1.6;
}

.cta {
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

.btn {
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn.primary {
  background: #fff;
  color: #2c3e50;
}

.btn.primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
}

/* Quick Navigation Pills */
.quick-nav {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 900px;
  margin-top: 20px;
}

.nav-pill {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
  padding: 12px 24px;
  border-radius: 50px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Inter', sans-serif;
}

.nav-pill:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-pill i {
  font-size: 1.1rem;
}

/* ============================= */
/* INFO SECTIONS */
/* ============================= */
.info-section {
  padding: 80px 40px;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
  box-sizing: border-box;
}

.info-section:nth-child(odd) {
  background: #ffffff;
}

.info-section:nth-child(even) {
  background: #f8f9fa;
}

.section-header {
  text-align: center;
  margin-bottom: 50px;
}

.section-header h2 {
  font-size: 2.25rem;
  margin-bottom: 12px;
  color: #2c3e50;
  font-weight: 800;
}

.section-header .subtitle {
  font-size: 1.1rem;
  color: #6b7280;
  max-width: 700px;
  margin: 0 auto;
  line-height: 1.6;
}

.more-info-link {
  display: inline-block;
  margin-top: 10px;
  color: #2c3e50;
  text-decoration: underline;
  font-weight: 600;
  word-break: break-word;
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 30px;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
}

.info-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  padding: 32px 28px;
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.info-section:nth-child(even) .info-card {
  background: #ffffff;
}

.info-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
}

.info-card h3 {
  font-size: 1.35rem;
  margin-bottom: 18px;
  color: #2c3e50;
  font-weight: 700;
}

.info-card ul {
  list-style: none;
  padding: 0;
  color: #374151;
}

.info-card ul li {
  margin-bottom: 12px;
  line-height: 1.6;
  font-size: 0.98rem;
  padding-left: 24px;
  position: relative;
}

.info-card ul li::before {
  content: '✓';
  position: absolute;
  left: 0;
  color: #42b883;
  font-weight: bold;
  font-size: 1.1rem;
}

/* ============================= */
/* MOBILE RESPONSIVENESS */
/* ============================= */
@media (max-width: 768px) {
  .hero {
    padding: 80px 20px 40px;
    min-height: 60vh;
  }

  .hero-content {
    margin-bottom: 30px;
  }

  .hero-content h1 {
    font-size: 2rem;
  }

  .hero-content p {
    font-size: 1rem;
  }

  .quick-nav {
    gap: 12px;
  }

  .nav-pill {
    padding: 10px 18px;
    font-size: 0.9rem;
  }

  .info-section {
    padding: 60px 24px;
  }

  .section-header {
    margin-bottom: 40px;
  }

  .section-header h2 {
    font-size: 1.75rem;
  }

  .section-header .subtitle {
    font-size: 1rem;
  }

  .info-grid {
    gap: 24px;
  }

  .info-card {
    padding: 24px 20px;
  }
}

@media (max-width: 480px) {
  .hero {
    padding: 60px 16px 30px;
  }

  .hero-content {
    width: 100%;
    max-width: 100%;
  }

  .hero-content h1 {
    font-size: 1.75rem;
  }

  .quick-nav {
    gap: 8px;
    flex-wrap: wrap;
    width: 100%;
  }

  .nav-pill {
    padding: 10px 16px;
    font-size: 0.8rem;
    min-width: 45%;
    justify-content: center;
  }

  .nav-pill i {
    font-size: 1rem;
  }

  .info-section {
    padding: 40px 12px;
  }

  .info-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .section-header h2 {
    font-size: 1.5rem;
  }

  
  
  .info-card {
    width: 100%;
    box-sizing: border-box;
  }

  .info-card h3 {
    font-size: 1.2rem;
  }

  .info-card ul li {
    font-size: 0.92rem;
  }
}
</style>
