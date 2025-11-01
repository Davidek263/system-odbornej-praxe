<template>
  <div class="student-page">
    <!-- posun na začiatok stránky -->

    <section class="hero">
      <h1>Informácie pre študentov</h1>
      <p>Prečítajte si svoje právomoci, možnosti a povinnosti pri evidencii odbornej praxe.</p>
    </section>

    <!-- GRID generovaný cez v‑for -->
    <section class="info-grid">
      <div class="info-card" v-for="(section, sIdx) in sections" :key="sIdx">
        <h2>{{ section.title }}</h2>
        <ul>
          <li v-for="(item, iIdx) in section.items" :key="iIdx" v-html="item" />
        </ul>
      </div>
    </section>
  </div>
</template>

<script>


export default {
  name: 'studentinfo',
  data() {
    return {
      sections: [
        {
          title: 'Právomoci',
          items: [
            'Prezerať zoznam vlastných odborných praxí.',
            'Zakladať novú prax výberom firmy, dátumov, roku a semestra.',
            'Stiahnuť vygenerované PDF „Dohoda o odbornej praxi“.',
            'Uploadovať podpísané dokumenty (zmluva, výkaz).',
            'Prijímať emailové notifikácie o zmenách stavu praxe.'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Full‑text vyhľadávanie firiem pri zakladaní praxe.',
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
      ]
    }
  }
}
</script>

<style scoped>
.hero {
  text-align: center;
  padding: 60px 20px 40px;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  color: #fff;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 24px;
  padding: 40px 20px 60px;
}

.info-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  padding: 32px 24px;
}

.info-card h2 {
  font-size: 1.25rem;
  margin-bottom: 16px;
  color: #2c3e50;
}

.info-card ul {
  list-style: disc inside;
  color: #374151;
}

.info-card ul li {
  margin-bottom: 8px;
  line-height: 1.4;
  font-size: 0.95rem;
}

@media (max-width: 640px) {
  .hero {
    padding: 40px 16px 32px;
  }
  .info-grid {
    padding: 32px 16px 48px;
  }
}
</style>
