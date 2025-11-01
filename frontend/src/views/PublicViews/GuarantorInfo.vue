<template>
  <div class="guarantor-page">


    <section class="hero">
      <h1>Informácie pre garantov praxe</h1>
      <p>Prehľad právomocí, možností a povinností akademického garanta v systéme.</p>
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
  name: 'guarantorinfo',
  data() {
    return {
      sections: [
        {
          title: 'Právomoci',
          items: [
            'Prístup k zoznamu všetkých praxí naprieč firmami a odbormi .',
            'Úprava atribútov praxe – firma, študent, dátumy, stav .',
            'Zmena stavu praxe (Schválená, Obhájená/Neobhájená) s automatickými notifikáciami .',
            'Export vyfiltrovaných dát praxe do CSV pre reporty a štatistiky .'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Filtrovanie praxí podľa roku, firmy, študijného odboru alebo študenta .',
            'Automatické emailové notifikácie pri každej zmene stavu praxe.',
            'Stiahnuť PDF dohody alebo výkaz študenta na kontrolu.'
          ]
        },
        {
          title: 'Povinnosti',
          items: [
            'Spracovať nové žiadosti v primeranom čase (schválenie/zamietnutie).',
            'Dbať na správnosť údajov praxe a zúčastnených strán.',
            'Zabezpečiť archiváciu a dostupnosť dokumentácie k praxiam po skončení semestra.'
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
  background: linear-gradient(135deg, #16a085 0%, #2c3e50 100%);
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
