# Praximoron - Frontend

Moderný, responzívny Vue 3 frontend pre systém správy odborných praxí Praximoron.

## Obsah

- [Prehľad](#prehľad)
- [Technologický stack](#technologický-stack)
- [Systémové požiadavky](#systémové-požiadavky)
- [Inštalácia](#inštalácia)
- [Konfigurácia](#konfigurácia)
- [Spustenie aplikácie](#spustenie-aplikácie)
- [Štruktúra projektu](#štruktúra-projektu)
- [Funkcie](#funkcie)
- [Vývoj](#vývoj)
- [Build pre produkciu](#build-pre-produkciu)
- [Riešenie problémov](#riešenie-problémov)

---

## Prehľad

Frontend Praximoronu je moderná single-page aplikácia (SPA) postavená na Vue 3 a Vite. Poskytuje intuitívne rozhranie pre študentov, firmy a garantov na správu praxí, dokumentov a súvisiacich pracovných postupov.

**Kľúčové schopnosti:**
- Dashboardy podľa rolí (Študent, Firma, Garant)
- Vytváranie a správa praxí
- Nahrávanie a schvaľovanie dokumentov
- Pracovný postup schvaľovania firiem
- Štatistiky a reportovanie
- Responzívny dizajn pre desktop, tablet a mobil

---

## Technologický stack

- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite 7
- **Router:** Vue Router 4
- **State Management:** Pinia 3
- **HTTP klient:** Axios 1.12
- **Grafy:** Chart.js 4.5
- **Vývojové nástroje:**
  - ESLint (lintovanie kódu)
  - Prettier (formátovanie kódu)
  - Vue DevTools (debugovanie)

---

## Systémové požiadavky

- **Node.js:** >= 20.19.0 alebo >= 22.12.0
- **npm:** >= 9.0 alebo **yarn:** >= 1.22
- **Moderný prehliadač:**
  - Chrome/Edge >= 90
  - Firefox >= 88
  - Safari >= 14

---

## Inštalácia

### 1. Klonovanie repozitára

```bash
git clone <url-repozitára>
cd system-odbornej-praxe/frontend
```

### 2. Inštalácia závislostí

```bash
npm install
```

Alebo s Yarnom:

```bash
yarn install
```

---

## Konfigurácia

### Premenné prostredia

Vytvorte súbor `.env` v koreňovom adresári frontendu:

```env
# API základná URL (backend)
VITE_API_BASE_URL=http://localhost:8000/api

# Názov aplikácie
VITE_APP_NAME=Praximoron
```

**Dôležité:** Súbor `.env` nie je sledovaný v Gite. Skopírujte z `.env.example` ak je dostupný.

### API konfigurácia

API klient je nakonfigurovaný v [src/api.js](src/api.js):

```javascript
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})
```

**Funkcie:**
- Automatické vkladanie tokenu z localStorage
- 401 presmerovanie na prihlásenie pri neautorizovaných požiadavkách
- Spracovanie chýb pre bežné HTTP stavové kódy

---

## Spustenie aplikácie

### Vývojový server

Spustite vývojový server s hot module replacement:

```bash
npm run dev
```

Aplikácia bude dostupná na: `http://localhost:5173`

### Vývoj s backendom

Najprv sa uistite, že backend API beží:

```bash
# V backend adresári
php artisan serve
php artisan queue:work
```

Potom spustite frontend:

```bash
# Vo frontend adresári
npm run dev
```

---

## Štruktúra projektu

```
frontend/
├── public/                     # Statické assety
├── src/
│   ├── assets/                 # Obrázky, štýly, fonty
│   ├── components/             # Znovupoužiteľné Vue komponenty
│   │   ├── HeaderNav.vue       # Navigačná hlavička
│   │   └── ...
│   ├── router/                 # Vue Router konfigurácia
│   │   └── index.js            # Routy a navigačné guardy
│   ├── stores/                 # Pinia state management (ak sa používa)
│   ├── views/                  # Stránkové komponenty
│   │   ├── PublicViews/        # Verejné stránky
│   │   │   ├── LandingPage.vue
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── StudentViews/       # Študentské stránky
│   │   │   └── StudentDashboard.vue
│   │   ├── CompanyViews/       # Firemné stránky
│   │   │   └── CompanyDashboard.vue
│   │   ├── GuarantorViews/     # Garantské stránky
│   │   │   ├── GuarantorDashboard.vue
│   │   │   ├── GuarantorStatistics.vue
│   │   │   ├── GuarantorTokens.vue
│   │   │   └── PendingCompanies.vue
│   │   ├── ProfileView.vue     # Používateľský profil
│   │   └── ...
│   ├── api.js                  # Axios API klient
│   ├── main.js                 # Vstupný bod aplikácie
│   └── App.vue                 # Koreňový komponent
├── .env                        # Premenné prostredia (vytvorte tento súbor)
├── .gitignore                  # Git ignore súbor
├── index.html                  # HTML vstupný bod
├── package.json                # Závislosti a skripty
├── vite.config.js              # Vite konfigurácia
├── eslint.config.js            # ESLint konfigurácia
└── README.md                   # Tento súbor
```

---

## Funkcie

### Verejné stránky

#### Landing Page (Úvodná stránka)
- Prístupná bez autentifikácie
- Informácie o programe odborných praxí
- Odkazy na prihlásenie a registráciu

#### Prihlásenie
- Autentifikácia pomocou e-mailu a hesla
- Odkaz "Zabudli ste heslo?"
- Presmerovanie na dashboard podľa role po prihlásení

#### Registrácia
- Samostatné formuláre pre študentov a firmy
- Polia pre registráciu študenta:
  - Meno, priezvisko, adresa
  - Študentský e-mail (`@student.ukf.sk`)
  - Alternatívny e-mail, telefón
  - Výber študijného odboru
- Polia pre registráciu firmy:
  - Názov firmy, adresa
  - Kontaktná osoba (meno, e-mail, telefón)

#### Reset hesla
- Žiadosť o reset hesla cez e-mail
- Nastavenie nového hesla pomocou reset tokenu

---

### Dashboardy podľa rolí

#### Študentský Dashboard
- Zobrazenie zoznamu vlastných praxí so stavom
- Vytvorenie novej praxe:
  - Výber firmy z dropdown zoznamu
  - Zadanie dátumov, akademického roka, semestra
- Úprava detailov praxe (len v stave "Vytvorená")
- Nahrávanie dokumentov (výkazy, dohody)
- Stiahnutie vygenerovaných dohôd o praxi (PDF)
- Sledovanie zmien stavu praxe

#### Firemný Dashboard
- Zobrazenie praxí v stave "Vytvorená"
- Potvrdenie alebo zamietnutie žiadostí o prax
- Zobrazenie potvrdených praxí
- Schválenie alebo zamietnutie študentom nahraných výkazov
- Zobrazenie informácií o študentovi

#### Garantský Dashboard
- Zobrazenie všetkých praxí v systéme
- Filtrovanie praxí podľa:
  - Akademického roka
  - Semestra
  - Stavu
  - Firmy
  - Študenta
  - Študijného odboru
- Úprava detailov ľubovoľnej praxe
- Manuálna zmena stavu praxe
- Export údajov o praxiach do CSV

#### Garantská štatistika
- Vizuálne grafy a grafy
- Počty praxí podľa stavu
- Počty praxí podľa firmy
- Semestrálne štatistiky
- Porovnania akademických rokov

#### Správa garantských tokenov
- Vytvorenie API tokenov pre externé systémy
- Zobrazenie existujúcich tokenov
- Vymazanie tokenov
- Správa oprávnení tokenov

#### Čakajúce firmy
- Zobrazenie firiem čakajúcich na schválenie
- Schválenie firiem:
  - Automatické generovanie prihlasovacích údajov
  - Odoslanie aktivačného e-mailu
- Zamietnutie firiem s dôvodom

---

### Spoločné funkcie

#### Správa profilu
- Zobrazenie a úprava používateľského profilu
- Zmena e-mailovej adresy (s verifikáciou)
- Aktualizácia alternatívneho e-mailu
- Aktualizácia adresy
- Zmena hesla

#### Navigácia
- Navigačné menu prispôsobené roli
- Používateľský dropdown s profilom a odhlásením
- Breadcrumb navigácia (kde je to možné)

#### Responzívny dizajn
- Mobilné priateľské rozhranie
- Ovládacie prvky optimalizované pre dotykové obrazovky
- Adaptívne rozloženia pre všetky veľkosti obrazoviek

#### Spracovanie chýb
- Používateľsky prívetivé chybové hlásenia
- Automatické odhlásenie pri expirácii tokenu
- Spätná väzba validácie formulárov

---

## Vývoj

### Lintovanie kódu

Lintovanie a automatická oprava problémov so štýlom kódu:

```bash
npm run lint
```

### Formátovanie kódu

Formátovanie kódu pomocou Prettier:

```bash
npm run format
```

### Nastavenie IDE

**Odporúčané IDE:** VS Code

**Rozšírenia:**
- [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) - Podpora Vue jazyka
- [ESLint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint) - Lintovanie kódu
- [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode) - Formátovanie kódu

**Rozšírenia prehliadača:**
- **Chrome/Edge:** [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
- **Firefox:** [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)

### Debugovanie

**Vue DevTools:**
Povolte vlastné formátovače objektov v DevTools prehliadača:
- **Chrome:** Nastavenia → Console → Enable custom formatters
- **Firefox:** [Custom Object Formatters](https://fxdx.dev/firefox-devtools-custom-object-formatters/)

**Debugovanie siete:**
Použite záložku Network v DevTools prehliadača na kontrolu API požiadaviek a odpovedí.

### Vývoj komponentov

**Vytvorenie nového komponentu:**

```vue
<!-- src/components/MojKomponent.vue -->
<template>
  <div class="moj-komponent">
    <h1>{{ nazov }}</h1>
  </div>
</template>

<script>
export default {
  name: 'MojKomponent',
  props: {
    nazov: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.moj-komponent {
  padding: 20px;
}
</style>
```

**Použitie komponentu:**

```vue
<template>
  <MojKomponent nazov="Ahoj svet" />
</template>

<script>
import MojKomponent from '@/components/MojKomponent.vue'

export default {
  components: {
    MojKomponent
  }
}
</script>
```

---

## Build pre produkciu

### Build optimalizovaného balíka

```bash
npm run build
```

Toto vytvorí optimalizovaný produkčný build v adresári `dist/` s:
- Minifikovaným JavaScriptom a CSS
- Code splitting
- Optimalizáciou assetov
- Source maps (voliteľné)

### Náhľad produkčného buildu

Otestujte produkčný build lokálne:

```bash
npm run preview
```

Toto spustí lokálny server obsluhujúci adresár `dist/`.

### Nasadenie

Adresár `dist/` obsahuje statické súbory, ktoré možno nasadiť na:

#### Platformy statického hostingu
- **Netlify:** Presuňte a pustite priečinok `dist/` alebo pripojte Git
- **Vercel:** Pripojte Git repozitár
- **GitHub Pages:** Nasaďte priečinok `dist/`
- **AWS S3 + CloudFront:** Nahrajte do S3 bucketu
- **Firebase Hosting:** `firebase deploy`

#### Web server (Apache/Nginx)

**Apache (.htaccess):**

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

**Nginx:**

```nginx
location / {
  try_files $uri $uri/ /index.html;
}
```

Toto zabezpečí správne fungovanie Vue Router v history móde.

---

## Riešenie problémov

### Port je už použitý

Ak je port 5173 obsadený:

```bash
# Spustite na inom porte
npm run dev -- --port 3000
```

Alebo aktualizujte `vite.config.js`:

```javascript
export default defineConfig({
  server: {
    port: 3000
  }
})
```

### Problémy s API pripojením

**Problém:** API požiadavky zlyhávajú s CORS chybami

**Riešenie:**
1. Overte backend CORS konfiguráciu v `backend/config/cors.php`
2. Uistite sa, že `FRONTEND_URL` v backend `.env` zodpovedá frontend URL
3. Skontrolujte, že `SANCTUM_STATEFUL_DOMAINS` obsahuje frontend doménu

**Problém:** 401 Unauthorized chyby

**Riešenie:**
1. Skontrolujte, či token existuje v localStorage
2. Overte, že token je platný (nie je expirovaný)
3. Uistite sa, že backend beží a je dostupný
4. Vymažte cache a prihláste sa znova

### Problémy s buildom

**Problém:** Build zlyhá s out of memory chybou

**Riešenie:**
```bash
# Zvýšte limit pamäte Node.js
export NODE_OPTIONS=--max_old_space_size=4096
npm run build
```

**Problém:** Module not found chyby

**Riešenie:**
```bash
# Vymažte node_modules a preinštalujte
rm -rf node_modules package-lock.json
npm install
```

### Problémy s vývojovým serverom

**Problém:** Hot reload nefunguje

**Riešenie:**
1. Reštartujte vývojový server
2. Vymažte cache prehliadača
3. Skontrolujte konzolu prehliadača pre chyby

**Problém:** Zmeny sa neprejavujú

**Riešenie:**
```bash
# Tvrdé obnovenie prehliadača (Ctrl+Shift+R alebo Cmd+Shift+R)
# Alebo reštartujte dev server
npm run dev
```

---

## Konfigurácia špecifická pre prostredie

### Vývoj

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### Staging

```env
VITE_API_BASE_URL=https://staging-api.praximoron.ukf.sk/api
```

### Produkcia

```env
VITE_API_BASE_URL=https://api.praximoron.ukf.sk/api
```

---

## Dostupné skripty

```bash
# Inštalácia závislostí
npm install

# Spustenie vývojového servera (predvolený port 5173)
npm run dev

# Build pre produkciu
npm run build

# Náhľad produkčného buildu
npm run preview

# Lintovanie kódu
npm run lint

# Formátovanie kódu pomocou Prettier
npm run format
```

---

## Kompatibilita prehliadačov

Aplikácia je kompatibilná s:

- **Chrome:** >= 90
- **Firefox:** >= 88
- **Safari:** >= 14
- **Edge:** >= 90

**Poznámka:** Internet Explorer nie je podporovaný.

---

## Optimalizácia výkonu

### Lazy loading route

Route sú lazy-loaded pre optimálny výkon:

```javascript
const StudentDashboard = () => import('@/views/StudentViews/StudentDashboard.vue')
```

### Code splitting

Vite automaticky rozdeľuje kód na časti pre rýchlejšie počiatočné načítanie.

### Optimalizácia assetov

- Obrázky sú automaticky optimalizované počas buildu
- CSS je minifikované a extrahované
- JavaScript je minifikovaný s tree-shaking

---

## Bezpečnostné aspekty

- **Uloženie tokenov:** Tokeny sú uložené v localStorage (zvážte bezpečnejšie možnosti pre produkciu)
- **Ochrana pred XSS:** Vue automaticky escapuje všetok renderovaný obsah
- **CSRF ochrana:** Backend používa Laravel Sanctum CSRF ochranu
- **Bezpečná komunikácia:** Vždy používajte HTTPS v produkcii

---

## Prispievanie

### Štýl kódu

- Dodržujte Vue 3 Composition API vzory
- Používajte ESLint a Prettier konfigurácie
- Píšte samo-dokumentujúci kód s jasnými názvami premenných
- Pridávajte komentáre pre zložitú logiku

### Git workflow

```bash
# Vytvorte feature branch
git checkout -b feature/nazov-vasej-funkcie

# Urobte zmeny a commitnite
git add .
git commit -m "feat: pridať vašu funkciu"

# Pushňte do remote
git push origin feature/nazov-vasej-funkcie

# Vytvorte pull request
```

---

## Podpora a dokumentácia

- **Vue 3:** https://vuejs.org/
- **Vite:** https://vite.dev/
- **Vue Router:** https://router.vuejs.org/
- **Pinia:** https://pinia.vuejs.org/
- **Axios:** https://axios-http.com/
- **Chart.js:** https://www.chartjs.org/

---

## Licencia

Tento projekt je proprietárny softvér vyvinutý pre internú potrebu.

---

## Prispievatelia

Vyvinuté pre predmet Softvérové inžinierstvo na UKF v Nitre.

---

## Záznam zmien

### Verzia 1.0.0 (2025-01-13)
- Prvé vydanie
- Kompletné rozhranie podľa rolí
- Študentský, firemný a garantský dashboard
- Správa praxí
- Nahrávanie a schvaľovanie dokumentov
- Štatistiky a reportovanie
- Responzívny dizajn
