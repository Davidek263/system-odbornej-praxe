# Praximoron - Systém správy odborných praxí

Komplexný CRM-like systém pre správu študentských odborných praxí na UKF v Nitre. Postavený na Laravel 12 a Vue 3.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)
![Vue](https://img.shields.io/badge/Vue-3.5-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)
![License](https://img.shields.io/badge/license-Proprietary-lightgrey.svg)

---

## Obsah

- [Prehľad](#prehľad)
- [Funkcie](#funkcie)
- [Technologický stack](#technologický-stack)
- [Systémové požiadavky](#systémové-požiadavky)
- [Rýchly štart](#rýchly-štart)
- [Štruktúra projektu](#štruktúra-projektu)
- [Dokumentácia](#dokumentácia)
- [Vývoj](#vývoj)
- [Nasadenie](#nasadenie)
- [Prispievanie](#prispievanie)
- [Licencia](#licencia)

---

## Prehľad

**Praximoron** je moderný webový systém navrhnutý na zefektívnenie správy študentských odborných praxí na Univerzite Konštantína Filozofa v Nitre (UKF). Systém integruje a automatizuje existujúce procesy, zlepšuje vedenie záznamov a poskytuje jednotnú databázu firiem a študentov.

### Účel

- **Integrovať** existujúce procesy správy odborných praxí
- **Automatizovať** rutinné administratívne úlohy
- **Zlepšiť** viditeľnosť a sledovanie aktívnych a archivovaných praxí
- **Centralizovať** databázu firiem a študentov
- **Uľahčiť** komunikáciu medzi študentmi, firmami a garantmi

### Používateľské role

Systém podporuje **5 odlišných používateľských rolí**:

1. **Anonymný používateľ** - Prístup k verejnej úvodnej stránke
2. **Študent** - Vytváranie a správa žiadostí o prax
3. **Firma** - Posúdenie a schválenie žiadostí o prax
4. **Garant** - Administratívny dohľad nad všetkými praxami
5. **Externý systém** - API prístup pre integráciu s inými systémami

---

## Funkcie

### Základná funkcionalita

- **Viacúrovňová autentifikácia** - Tokenová autentifikácia s oprávneniami špecifickými pre role
- **Správa životného cyklu praxe** - Kompletný pracovný postup od vytvorenia po dokončenie
- **Správa dokumentov** - Nahrávanie, schvaľovanie a sledovanie dokumentov praxe
- **E-mailové notifikácie** - Automatizované notifikácie pre kľúčové udalosti
- **Pracovný postup schvaľovania firiem** - Schvaľovanie registrácie firiem garantom
- **Štatistiky a reportovanie** - Vizuálne dashboardy a CSV exporty
- **Integrácia s externým API** - OAuth2-kompatibilné tokeny pre externé systémy
- **Generovanie PDF** - Automatické generovanie dohôd o praxi

### Funkcie pre študentov

- Zobrazenie histórie osobných praxí
- Vytvorenie nových žiadostí o prax
- Výber firiem z prehľadávateľnej databázy
- Nahrávanie potrebných dokumentov a výkazov
- Stiahnutie vygenerovaných dohôd o praxi
- Sledovanie stavu praxe v reálnom čase

### Funkcie pre firmy

- Zobrazenie žiadostí o prax
- Potvrdenie alebo zamietnutie žiadostí študentov
- Schválenie alebo zamietnutie nahraných výkazov
- Prístup ku kontaktným informáciám študenta
- Sledovanie potvrdených praxí

### Funkcie pre garantov

- Úplný administratívny prístup ku všetkým praxiam
- Schválenie alebo zamietnutie registrácií firiem
- Manuálna zmena stavov praxe
- Filtrovanie a vyhľadávanie praxí podľa viacerých kritérií
- Generovanie správ a štatistík
- Export údajov do CSV
- Správa API tokenov externých systémov

---

## Technologický stack

### Backend
- **Framework:** Laravel 12.0
- **Jazyk:** PHP 8.2+
- **Databáza:** MySQL 8.0+ (podporuje PostgreSQL, SQLite)
- **Autentifikácia:** Laravel Sanctum
- **Generovanie PDF:** DomPDF
- **E-mail:** Laravel Mail s SMTP

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite 7
- **Router:** Vue Router 4
- **State Management:** Pinia 3
- **HTTP klient:** Axios 1.12
- **Grafy:** Chart.js 4.5

### Vývojové nástroje
- **Testovanie:** PHPUnit
- **Kvalita kódu:** ESLint, Prettier
- **Debugovanie:** Laravel Pail, Vue DevTools

---

## Systémové požiadavky

### Softvérové požiadavky

- **PHP:** >= 8.2
- **Composer:** >= 2.0
- **Node.js:** >= 20.19.0
- **npm:** >= 9.0 alebo **yarn:** >= 1.22
- **Databáza:** MySQL >= 8.0, PostgreSQL >= 13, alebo SQLite >= 3.35
- **Web server:** Apache 2.4+ alebo Nginx 1.18+

### PHP rozšírenia

- BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Podpora prehliadačov

- Chrome/Edge >= 90
- Firefox >= 88
- Safari >= 14

---

## Rýchly štart

### 1. Klonovanie repozitára

```bash
git clone <url-repozitára>
cd system-odbornej-praxe
```

### 2. Nastavenie backendu

```bash
# Prejdite do backend adresára
cd backend

# Inštalujte PHP závislosti
composer install

# Skopírujte súbor prostredia
cp .env.example .env
# Na Windows: copy .env.example .env

# Vygenerujte aplikačný klúč
php artisan key:generate

# Nakonfigurujte databázu v súbore .env
# DB_DATABASE=praximoron
# DB_USERNAME=root
# DB_PASSWORD=vase_heslo

# Spustite migrácie a seedery
php artisan migrate --seed

# Spustite vývojový server
php artisan serve

# V samostatnom termináli spustite queue worker
php artisan queue:work
```

Backend API bude dostupné na: `http://localhost:8000`

### 3. Nastavenie frontendu

```bash
# Otvorte nový terminál a prejdite do frontend adresára
cd frontend

# Inštalujte Node závislosti
npm install

# Vytvorte súbor prostredia
echo "VITE_API_BASE_URL=http://localhost:8000/api" > .env

# Spustite vývojový server
npm run dev
```

Frontendová aplikácia bude dostupná na: `http://localhost:5173`

### 4. Prístup k aplikácii

Otvorte prehliadač a prejdite na `http://localhost:5173`

**Predvolení testovacie používatelia (po seedovaní):**

| Rola | E-mail | Heslo |
|------|--------|-------|
| Študent | student@student.ukf.sk | password |
| Firma | company@example.com | password |
| Garant | guarantor@ukf.sk | password |

---

## Štruktúra projektu

```
system-odbornej-praxe/
├── backend/                    # Laravel 12 REST API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/    # API Controllery
│   │   ├── Models/             # Eloquent modely
│   │   ├── Mail/               # E-mailové triedy
│   │   └── Services/           # Biznis logika
│   ├── database/
│   │   ├── migrations/         # Databázové migrácie
│   │   └── seeders/            # Databázové seedery
│   ├── routes/
│   │   └── api.php             # API routes
│   ├── storage/                # Logy, nahrávky
│   ├── .env                    # Konfigurácia prostredia
│   └── README.sk.md            # Backend dokumentácia
│
├── frontend/                   # Vue 3 SPA
│   ├── src/
│   │   ├── components/         # Znovupoužiteľné komponenty
│   │   ├── router/             # Vue Router konfigurácia
│   │   ├── views/              # Stránkové komponenty
│   │   │   ├── PublicViews/
│   │   │   ├── StudentViews/
│   │   │   ├── CompanyViews/
│   │   │   └── GuarantorViews/
│   │   ├── api.js              # Axios API klient
│   │   └── main.js             # Vstupný bod aplikácie
│   ├── .env                    # Konfigurácia prostredia
│   └── README.sk.md            # Frontend dokumentácia
│
├── additional_files/           # Dokumentácia
│   └── Specifikacia.pdf        # Špecifikácia systému
│
└── README.sk.md                # Tento súbor
```

---

## Dokumentácia

Podrobná dokumentácia je dostupná pre každú komponentu:

- **[Backend dokumentácia](backend/README.sk.md)** - API referencia, databázová schéma, nasadenie
- **[Frontend dokumentácia](frontend/README.sk.md)** - Sprievodca komponentmi, routing, building
- **[Špecifikácia systému](additional_files/Specifikacia.pdf)** - Funkčné a nefunkčné požiadavky (slovensky)

---

## Vývoj

### Backend vývoj

```bash
cd backend

# Spustenie vývojového servera
php artisan serve

# Spustenie queue workera
php artisan queue:work

# Zobrazenie logov v reálnom čase
php artisan pail

# Spustenie testov
php artisan test

# Vytvorenie novej migrácie
php artisan make:migration CreateTableName

# Vytvorenie nového controllera
php artisan make:controller ControllerName

# Vymazanie cache
php artisan cache:clear
php artisan config:clear
```

### Frontend vývoj

```bash
cd frontend

# Spustenie vývojového servera
npm run dev

# Lintovanie kódu
npm run lint

# Formátovanie kódu
npm run format

# Build pre produkciu
npm run build

# Náhľad produkčného buildu
npm run preview
```

### Správa databázy

```bash
cd backend

# Spustenie migrácií
php artisan migrate

# Vrátenie poslednej migrácie
php artisan migrate:rollback

# Reset databázy a opätovné naplnenie
php artisan migrate:fresh --seed

# Vytvorenie nového seedera
php artisan make:seeder SeederName

# Zobrazenie štruktúry databázy
php artisan db:show
php artisan db:table users
```

---

## Nasadenie

### Produkčný checklist

#### Nasadenie backendu

1. **Požiadavky na server**
   - PHP 8.2+, Composer 2.0+
   - MySQL 8.0+ alebo PostgreSQL 13+
   - Web server (Apache/Nginx)
   - SSL certifikát

2. **Konfigurácia prostredia**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://vasa-domena.com
   ```

3. **Optimalizácia aplikácie**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Spustenie migrácií**
   ```bash
   php artisan migrate --force
   ```

5. **Nastavenie oprávnení**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Konfigurácia queue workera** (Supervisor/Systemd)
   ```bash
   php artisan queue:work --sleep=3 --tries=3
   ```

#### Nasadenie frontendu

1. **Build produkčného balíka**
   ```bash
   npm run build
   ```

2. **Nasadenie adresára `dist/`**
   - Statický hosting (Netlify, Vercel, GitHub Pages)
   - Web server (Apache, Nginx)
   - CDN (CloudFront, Cloudflare)

3. **Konfigurácia web servera**

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

### Platformy pre nasadenie

- **Backend:** Laravel Forge, Laravel Vapor, AWS EC2, DigitalOcean, Heroku
- **Frontend:** Netlify, Vercel, AWS S3+CloudFront, Firebase Hosting
- **Databáza:** AWS RDS, DigitalOcean Managed Databases, PlanetScale

---

## API dokumentácia

### Základná URL

```
http://localhost:8000/api (vývoj)
https://api.vasa-domena.com/api (produkcia)
```

### Autentifikácia

Väčšina endpointov vyžaduje Bearer token autentifikáciu:

```bash
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Kľúčové endpointy

#### Autentifikácia
- `POST /register-student` - Registrácia študenta
- `POST /register-company` - Registrácia firmy
- `POST /login` - Prihlásenie používateľa
- `POST /logout` - Odhlásenie používateľa
- `POST /forgot-password` - Žiadosť o reset hesla
- `POST /reset-password` - Reset hesla

#### Praxe
- `GET /student-internships/{id}` - Získanie praxí študenta
- `POST /internships` - Vytvorenie praxe
- `PUT /internships/{id}` - Aktualizácia praxe
- `GET /company-internships/{id}` - Získanie praxí firmy
- `POST /internships/{id}/confirm` - Potvrdenie praxe
- `POST /internships/{id}/reject` - Zamietnutie praxe
- `GET /guarantor/internships` - Získanie všetkých praxí (s filtrami)
- `POST /guarantor/internships/{id}/change-status` - Zmena stavu

#### Dokumenty
- `POST /student/internships/{id}/documents` - Nahranie dokumentu
- `GET /internships/{id}/generate-dohoda` - Generovanie PDF dohody
- `POST /documents/{id}/approve-timesheet` - Schválenie výkazu
- `POST /documents/{id}/reject-timesheet` - Zamietnutie výkazu

#### Garant
- `GET /guarantor/pending-companies` - Získanie čakajúcich firiem
- `POST /guarantor/companies/{id}/approve` - Schválenie firmy
- `POST /guarantor/companies/{id}/reject` - Zamietnutie firmy
- `POST /guarantor/internships/export` - Export do CSV
- `GET /guarantor/external-system-tokens` - Správa API tokenov

Pre kompletnú API dokumentáciu pozrite [backend/README.sk.md](backend/README.sk.md#api-dokumentácia).

---

## Riešenie problémov

### Bežné problémy

#### Chyba pripojenia k databáze

```bash
# Skontrolujte konfiguráciu databázy v .env
# Overte, že databáza existuje
mysql -u root -p
CREATE DATABASE praximoron;

# Vymažte config cache
php artisan config:clear
```

#### CORS chyby

```bash
# Backend .env
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173

# Vymažte cache
php artisan config:clear
```

#### Port je už použitý

```bash
# Backend (zmeňte port)
php artisan serve --port=8001

# Frontend (zmeňte port)
npm run dev -- --port 3000
```

#### Queue sa nespracováva

```bash
# Spustite queue worker
php artisan queue:work

# Skontrolujte neúspešné joby
php artisan queue:failed
php artisan queue:retry all
```

#### E-mail sa neposiela

```bash
# Vývoj: Použite log driver
MAIL_MAILER=log

# Skontrolujte logy
tail -f storage/logs/laravel.log

# Produkcia: Overte SMTP nastavenia
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=vas-email@gmail.com
MAIL_PASSWORD=heslo-aplikacie
```

---

## Testovanie

### Backend testy

```bash
cd backend

# Spustenie všetkých testov
php artisan test

# Spustenie s pokrytím
php artisan test --coverage

# Spustenie konkrétneho testovacieho súboru
php artisan test tests/Feature/AuthenticationTest.php
```

### Frontend testy

```bash
cd frontend

# Lintovanie kódu
npm run lint

# Formátovanie kódu
npm run format
```

---

## Bezpečnosť

### Bezpečnostné funkcie

- **Hashovanie hesiel:** bcrypt s automatickým rehashovaním
- **Tokenová autentifikácia:** Laravel Sanctum s expirujúcimi tokenmi
- **CSRF ochrana:** Vstavaná Laravel CSRF ochrana
- **Prevencia SQL Injection:** Eloquent ORM s prepared statements
- **XSS ochrana:** Vue automatické escapovanie
- **Rate limiting:** Nakonfigurované API rate limiting
- **Soft deletes:** GDPR-kompatibilné uchovávanie údajov

### Najlepšie postupy

- Vždy používajte HTTPS v produkcii
- Udržiavajte závislosti aktuálne
- Používajte premenné prostredia pre citlivé údaje
- Povoľte 2FA pre garantské účty (ak je implementované)
- Pravidelné bezpečnostné audity
- Pravidelné zálohovanie databázy

---

## Optimalizácia výkonu

### Backend

- Route caching: `php artisan route:cache`
- Config caching: `php artisan config:cache`
- View caching: `php artisan view:cache`
- Opcode caching: Povoľte OPcache v PHP
- Databázové indexovanie: Správne indexy na cudzích kľúčoch
- Optimalizácia dotazov: Eager loading, stránkovanie

### Frontend

- Lazy loading routes
- Code splitting s Vite
- Optimalizácia assetov (minifikácia, kompresia)
- CDN pre statické assety
- Browser caching headers

---

## Prispievanie

### Vývojový workflow

1. **Vytvorenie feature branchu**
   ```bash
   git checkout -b feature/nazov-vasej-funkcie
   ```

2. **Vykonanie zmien**
   - Dodržujte štandardy kódovania (PSR-12 pre PHP, Vue Style Guide)
   - Píšte testy pre nové funkcie
   - Aktualizujte dokumentáciu

3. **Commit zmien**
   ```bash
   git add .
   git commit -m "feat: pridať popis vašej funkcie"
   ```

4. **Push a vytvorenie PR**
   ```bash
   git push origin feature/nazov-vasej-funkcie
   ```

### Štýl kódu

- **PHP:** Dodržujte PSR-12, používajte Laravel konvencie
- **JavaScript:** Používajte ESLint a Prettier konfigurácie
- **Vue:** Dodržujte Vue 3 Composition API vzory
- **Commity:** Používajte konvenčné commit správy

---

## Podpora a zdroje

### Dokumentácia

- **Laravel:** https://laravel.com/docs/12.x
- **Vue 3:** https://vuejs.org/
- **Vite:** https://vite.dev/
- **Laravel Sanctum:** https://laravel.com/docs/12.x/sanctum
- **Vue Router:** https://router.vuejs.org/
- **Chart.js:** https://www.chartjs.org/

### Komunita

- Nahláste problémy na GitHube
- Kontaktujte vývojový tím pre podporu

---

## Licencia

Tento projekt je proprietárny softvér vyvinutý pre internú potrebu Univerzity Konštantína Filozofa v Nitre.

**Copyright © 2025 UKF Nitra. Všetky práva vyhradené.**

---

## Prispievatelia

Vyvinuté pre predmet **Softvérové inžinierstvo** na UKF v Nitre.

---

## Záznam zmien

### Verzia 1.0.0 (2025-01-13)

**Prvé vydanie**

- Kompletný systém správy odborných praxí
- Viacúrovňová autentifikácia a autorizácia
- Študentský, firemný a garantský dashboard
- Správa pracovného postupu praxe
- Systém nahrávania a schvaľovania dokumentov
- Systém e-mailových notifikácií
- Pracovný postup schvaľovania firiem
- Štatistiky a reportovanie
- Integrácia s externým API
- CSV export údajov
- Generovanie PDF dohôd
- Responzívny dizajn pre všetky zariadenia

---

## Poďakovanie

- **Univerzita:** Univerzita Konštantína Filozofa v Nitre
- **Predmet:** Softvérové inžinierstvo
- **Framework:** Laravel od Taylora Otwella a Laravel komunity
- **Frontend:** Vue.js od Evana Youa a Vue tímu

---

**Pre podrobné inštalačné pokyny a API dokumentáciu pozrite:**
- [Backend README](backend/README.sk.md)
- [Frontend README](frontend/README.sk.md)
