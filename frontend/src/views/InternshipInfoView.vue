<template>
  <div class="internship-page">

    <section class="hero">
      <h1>Informácie o praxiach</h1>
      <p>Prehľad funkcií a povinností spojených so správou odbornej praxe v systéme.</p>
    </section>

    <section class="info-grid">
      <div class="info-card" v-for="(section, idx) in sections" :key="idx">
        <h2>{{ section.title }}</h2>
        <ul>
          <li v-for="(item, idy) in section.items" :key="idy" v-html="item" />
        </ul>
      </div>
    </section>
  </div>
</template>

<script>

export default {
  name: 'interneshipinfo',
  data() {
    return {
      sections: [
        {
          title: 'Právomoci',
          items: [
            'Prehľad praxí s detailným zobrazením stavu, dátumov a zmluvných strán.',
            'Filtrovanie praxí podľa roku, stavu, firmy alebo študenta.',
            'Stiahnutie PDF dohody a výkazu praxe .',
            'Export vyfiltrovaného zoznamu do CSV pre reporty a štatistiky.'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Založiť novú prax (študent) vyplnením firmy a termínov .',
            'Potvrdiť alebo zamietnuť prax (firma) v stave Vytvorená.',
            'Schváliť, označiť za obhájenú alebo neobhájenú (garant).',
            'Vyžiadať alebo nahrať ďalšie dokumenty praxe podľa potreby.'
          ]
        },
        {
          title: 'Povinnosti',
          items: [
            'Nahrať povinné dokumenty (zmluva pri stave Schválená).',
            'Dodržiavať termíny potvrdenia, schválenia a obhajoby praxe.',
            'Udržiavať aktuálne informácie o praxi a jej účastníkoch.'
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
  background: linear-gradient(135deg, #2c3e50 0%, #16a085 100%);
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
  transition: transform 0.2s, box-shadow 0.2s;
}

.info-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
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