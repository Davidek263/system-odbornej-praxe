<template>
  <div class="company-page">


    <section class="hero">
      <h1>Informácie pre firmy</h1>
      <p>Zistite, aké máte právomoci, možnosti a povinnosti pri spolupráci na odbornej praxi.</p>
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
  name: 'companyinfo',
  data() {
    return {
      sections: [
        {
          title: 'Právomoci',
          items: [
            'Prezrieť zoznam praxí v stave Vytvorená a detail jednotlivých žiadostí.',
            'Potvrdiť alebo zamietnuť prax (zmena stavu na Potvrdená / Zamietnutá), pričom systém automaticky odošle notifikácie študentovi a garantovi.',
            'Nahrať výkaz pracovných hodín a potvrdiť alebo zamietnuť výkaz od študenta .',
            'Aktualizovať profil firmy – kontaktná osoba, adresa, popis.',
            'Zobraziť históriu a archív dokončených praxí.'
          ]
        },
        {
          title: 'Možnosti',
          items: [
            'Registrácia prostredníctvom formulára: názov, adresa, kontaktná osoba.',
            'Aktivácia účtu cez potvrdzovací email (stav Neaktívny → Aktívny).',
            'Obnova alebo zmena hesla cez funkciu „Zabudli ste heslo?“.',
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
  }
}
</script>

<style scoped>
.hero {
  text-align: center;
  padding: 60px 20px 40px;
  background: linear-gradient(135deg, #2c3e50 0%, #42b883 100%);
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