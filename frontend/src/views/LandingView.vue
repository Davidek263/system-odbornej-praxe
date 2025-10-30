<template>
  <div class="landing-page">
    <!-- HERO -->
    <section class="hero">
      <div class="hero-content">
        <h1>Vitajte v systéme <span>odbornej praxe</span></h1>
        <p>Spravujte evidenciu dokumentov a procesov rýchlo, bezpečne a prehľadne.</p>
        <div class="cta">
          <router-link class="btn primary" to="/register">Začať</router-link>
          <a class="btn secondary" href="#info">Zistiť viac</a>
        </div>
      </div>
    </section>

    <!-- FEATURES (benefits) -->
    <section id="features" class="features">
      <div class="feature" v-for="(feature, index) in features" :key="index">
        <div class="icon">
          <i :class="feature.icon"></i>
        </div>
        <h3>{{ feature.title }}</h3>
        <p>{{ feature.desc }}</p>
      </div>
    </section>

    <!-- INFO CARDS -->
    <section id="info" class="info-cards">
      <div class="card" v-for="card in cards" :key="card.title">
        <router-link :to="card.to" class="card-link">
          <div class="icon"><i :class="card.icon"></i></div>
          <h3>{{ card.title }}</h3>
          <p>{{ card.desc }}</p>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'LandingPage',
  data() {
    return {
      features: [
        {
          icon: 'fas fa-eye',
          title: 'Prehľadnosť',
          desc: 'Všetky praxe a dokumenty na jednom mieste.'
        },
        {
          icon: 'fas fa-magic',
          title: 'Automatizácia',
          desc: 'Automatické generovanie PDF dokumentov a notifikácií.'
        },
        {
          icon: 'fas fa-lock',
          title: 'Bezpečnosť',
          desc: 'Moderné štandardy ochrany dát a prístupových práv.'
        }
      ],
      cards: [
        {
          icon: 'fas fa-user-graduate',
          title: 'Študent',
          desc: 'Nájdite ideálnu prax a sledujte svoj pokrok.',
          to: '/student-info'
        },
        {
          icon: 'fas fa-building',
          title: 'Firma',
          desc: 'Zverejnite ponuku a spravujte študentov na praxi.',
          to: '/company-info'
        },
        {
          icon: 'fas fa-briefcase',
          title: 'Praxe',
          desc: 'Prehľad všetkých dostupných praxí a dokumentov.',
          to: '/internship-info'
        },
        {
          icon: 'fas fa-chalkboard-teacher',
          title: 'Garant',
          desc: 'Schvaľujte a kontrolujte priebeh praxe.',
          to: '/guarantor-info'
        }
      ]
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');

.landing-page {
  font-family: 'Inter', sans-serif;
  color: #1f2937;
}

/* HERO */
.hero {
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  color: #fff;
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 100px 20px 60px;
}

.hero-content h1 {
  font-size: clamp(2rem, 4.5vw + 1rem, 3rem);
  margin-bottom: 1rem;
  line-height: 1.2;
}

.hero-content p {
  font-size: 1.125rem;
  margin-bottom: 1.75rem;
  opacity: 0.9;
}

.cta {
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}

.btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.25s, transform 0.2s;
}

.btn.primary {
  background: #fff;
  color: #2c3e50;
}

.btn.secondary {
  background: transparent;
  border: 2px solid #fff;
  color: #fff;
}

.btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

/* FEATURES */
.features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 32px;
  padding: 60px 24px;
  background: #f9fafb;
}

.feature {
  text-align: center;
  padding: 16px;
}

.feature .icon {
  font-size: 2.2rem;
  margin-bottom: 14px;
  color: #42b883;
}

.feature h3 {
  font-size: 1.125rem;
  margin-bottom: 10px;
}

.feature p {
  color: #6b7280;
  font-size: 0.95rem;
}

/* INFO CARDS */
.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 32px;
  padding: 60px 24px 80px;
}

.card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s, box-shadow 0.2s;
  text-align: center;
}

.card-link {
  display: block;
  padding: 32px 24px;
  text-decoration: none;
  color: inherit;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.card .icon {
  font-size: 2rem;
  margin-bottom: 14px;
  color: #42b883;
}

.card h3 {
  font-size: 1.125rem;
  margin-bottom: 10px;
  font-weight: 600;
}

.card p {
  color: #6b7280;
  font-size: 0.95rem;
}

/* MOBILE ADJUSTMENTS */
@media (max-width: 640px) {
  .hero {
    padding: 80px 16px 40px;
  }
  .info-cards {
    padding: 40px 16px 60px;
  }
}
</style>
