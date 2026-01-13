# Praximoron - Backend API

RESTful API backend pre systém správy odborných praxí Praximoron postavený na Laravel 12.

## Obsah

- [Prehľad](#prehľad)
- [Technologický stack](#technologický-stack)
- [Systémové požiadavky](#systémové-požiadavky)
- [Inštalácia](#inštalácia)
- [Konfigurácia](#konfigurácia)
- [Nastavenie databázy](#nastavenie-databázy)
- [Spustenie aplikácie](#spustenie-aplikácie)
- [API dokumentácia](#api-dokumentácia)
- [Štruktúra projektu](#štruktúra-projektu)
- [Kľúčové funkcie](#kľúčové-funkcie)
- [Testovanie](#testovanie)
- [Nasadenie](#nasadenie)
- [Riešenie problémov](#riešenie-problémov)

---

## Prehľad

Backend Praximoronu je REST API založené na Laravel 12, ktoré spravuje kompletný životný cyklus študentských odborných praxí, vrátane:
- Viacúrovňovej autentifikácie a autorizácie používateľov
- Správy pracovného postupu odborných praxí
- Nahrávania a schvaľovania dokumentov
- E-mailových notifikácií
- Procesu schvaľovania firiem
- Integrácie s externými systémami
- Exportu údajov do CSV pre reporty

---

## Technologický stack

- **Framework:** Laravel 12.0
- **Verzia PHP:** 8.2+
- **Autentifikácia:** Laravel Sanctum (tokenová)
- **Databáza:** MySQL (podporuje aj PostgreSQL, SQLite)
- **Generovanie PDF:** DomPDF
- **E-mail:** Laravel Mail s podporou SMTP
- **Architektúra API:** RESTful
- **Dodatočné nástroje:**
  - Laravel Pail (prehliadač logov)
  - Laravel Tinker (REPL)
  - PHPUnit (testovanie)

---

## Systémové požiadavky

- **PHP:** >= 8.2
- **Composer:** >= 2.0
- **MySQL:** >= 8.0 (alebo PostgreSQL >= 13, SQLite >= 3.35)
- **Node.js:** >= 20.19.0 (pre vývojové nástroje)
- **Rozšírenia:**
  - BCMath PHP Extension
  - Ctype PHP Extension
  - cURL PHP Extension
  - DOM PHP Extension
  - Fileinfo PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension

---

## Inštalácia

### 1. Klonovanie repozitára

```bash
git clone <url-repozitára>
cd system-odbornej-praxe/backend
```

### 2. Inštalácia PHP závislostí

```bash
composer install
```

### 3. Kopírovanie súboru prostredia

```bash
cp .env.example .env
# Na Windows:
copy .env.example .env
```

### 4. Generovanie aplikačného klúča

```bash
php artisan key:generate
```

---

## Konfigurácia

### Premenné prostredia

Upravte súbor `.env` pre konfiguráciu aplikácie:

#### Nastavenia aplikácie

```env
APP_NAME="Praximoron"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Europe/Bratislava
APP_URL=http://localhost:8000
```

#### Konfigurácia databázy

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=praximoron
DB_USERNAME=root
DB_PASSWORD=vase_heslo
```

#### Konfigurácia e-mailu

**Pre vývoj (loguje e-maily do súboru):**

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@praximoron.ukf.sk"
MAIL_FROM_NAME="${APP_NAME}"
```

**Pre produkciu (Gmail SMTP):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=vas-email@gmail.com
MAIL_PASSWORD=heslo-aplikacie
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="vas-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Poznámka:** Pre Gmail musíte vygenerovať [Heslo aplikácie](https://support.google.com/accounts/answer/185833).

#### CORS a Sanctum konfigurácia

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5173
FRONTEND_URL=http://localhost:5173
```

#### Vlastné nastavenia

```env
# Expirácia tokenu na reset hesla (v minútach)
PASSWORD_RESET_EXPIRY=1440

# Expirácia aktivačného tokenu (v hodinách)
ACTIVATION_TOKEN_EXPIRY=48

# Nastavenia nahrávania súborov
MAX_FILE_UPLOAD_SIZE=10240
ALLOWED_FILE_TYPES=pdf,doc,docx,jpg,jpeg,png

# Nastavenia akademického roka
CURRENT_ACADEMIC_YEAR=2024/2025
CURRENT_SEMESTER=1

# Bezpečnostné nastavenia
API_RATE_LIMIT=60
LOGIN_RATE_LIMIT=50
TOKEN_LIFETIME=1440
```

---

## Nastavenie databázy

### 1. Vytvorenie databázy

Vytvorte MySQL databázu pre aplikáciu:

```sql
CREATE DATABASE praximoron CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Spustenie migrácií

```bash
php artisan migrate
```

Toto vytvorí všetky potrebné tabuľky:
- users (používatelia)
- roles (roly)
- companies (firmy)
- internships (praxe)
- documents (dokumenty)
- internship_status (stavy praxe)
- internship_status_changes (zmeny stavov)
- timesheet_status (stavy výkazov)
- study_fields (študijné odbory)
- addresses (adresy)
- email_notifications (e-mailové notifikácie)
- audit_logs (audit logy)
- password_resets (reset hesiel)
- a ďalšie...

### 3. Naplnenie databázy

Naplňte databázu počiatočnými údajmi (roly, stavy, ukážkové dáta):

```bash
php artisan db:seed
```

Toto naplní:
- Role používateľov (student, company, guarantor)
- Stavy praxe (Vytvorená, Potvrdená, Schválená, Obhájená, Neobhájená, Zamietnutá)
- Stavy výkazov (Nový, Potvrdený, Zamietnutý)
- Typy dokumentov
- Študijné odbory
- Ukážkových používateľov a firmy (pre vývoj)

### 4. Čistá migrácia (voliteľné)

Pre reset databázy a nové naplnenie:

```bash
php artisan migrate:fresh --seed
```

**Upozornenie:** Toto vymaže všetky tabuľky a vytvorí ich znova, stratíte všetky dáta.

---

## Spustenie aplikácie

### Vývojový server

Spustite vývojový Laravel server:

```bash
php artisan serve
```

API bude dostupné na: `http://localhost:8000`

### S queue workerom (odporúčané)

Pre spracovanie úloh vo fronte (e-maily, notifikácie):

```bash
# Terminál 1: Spustite server
php artisan serve

# Terminál 2: Spustite queue worker
php artisan queue:work
```

### Použitie Laravel Pail (prehliadač logov)

Monitorovanie logov v reálnom čase:

```bash
php artisan pail
```

### Všetko-v-jednom vývojový príkaz

Spustite server, queue worker a logy súčasne:

```bash
composer dev
```

Toto vyžaduje `concurrently` (npm balíček).

---

## API dokumentácia

### Základná URL

```
http://localhost:8000/api
```

### Autentifikácia

Väčšina endpointov vyžaduje autentifikáciu pomocou Laravel Sanctum tokenov.

**Hlavičky:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Verejné endpointy

#### Autentifikácia

- `POST /register-student` - Registrácia nového študenta
- `POST /register-company` - Registrácia novej firmy
- `GET /activate-account` - Aktivácia používateľského účtu
- `POST /resend-activation` - Opätovné zaslanie aktivačného e-mailu
- `POST /set-initial-password` - Nastavenie počiatočného hesla
- `POST /login` - Prihlásenie používateľa
- `POST /forgot-password` - Žiadosť o reset hesla
- `POST /reset-password` - Reset hesla pomocou tokenu
- `GET /study-fields` - Získanie zoznamu študijných odborov

#### Verifikácia e-mailu

- `GET /verify-email-change` - Verifikácia žiadosti o zmenu e-mailu

### Chránené endpointy

#### Správa používateľov

- `GET /user` - Získanie aktuálne prihláseného používateľa
- `POST /logout` - Odhlásenie používateľa
- `POST /change-password` - Zmena hesla
- `POST /request-student-email-change` - Žiadosť o zmenu študentského e-mailu
- `POST /request-company-email-change` - Žiadosť o zmenu firemného e-mailu
- `POST /update-alternative-email` - Aktualizácia alternatívneho e-mailu

#### Študentské endpointy

- `GET /student/companies` - Získanie zoznamu firiem
- `GET /student-internships/{studentId}` - Získanie praxí študenta
- `GET /student/document-types` - Získanie typov dokumentov
- `GET /student/internships/{id}/documents` - Získanie dokumentov praxe
- `POST /student/internships/{id}/documents` - Nahranie dokumentu
- `GET /student/documents/{id}/download` - Stiahnutie dokumentu
- `DELETE /student/documents/{id}` - Vymazanie dokumentu

#### Správa praxí

- `POST /internships` - Vytvorenie novej praxe (študent)
- `GET /internships/{id}` - Získanie detailov jednej praxe
- `PUT /internships/{id}` - Aktualizácia praxe (študent - len v stave "Vytvorená")
- `POST /internships/{id}/confirm` - Potvrdenie praxe (firma)
- `POST /internships/{id}/reject` - Zamietnutie praxe (firma)
- `GET /internships/{id}/generate-dohoda` - Generovanie PDF dohody o praxi

#### Firemné endpointy

- `GET /company-internships/{companyId}` - Získanie praxí firmy

#### Správa dokumentov/výkazov

- `POST /documents/{id}/approve-timesheet` - Schválenie výkazu (firma)
- `POST /documents/{id}/reject-timesheet` - Zamietnutie výkazu (firma)

#### Garantské endpointy

- `GET /guarantor/internships` - Získanie všetkých praxí s filtrami
- `PUT /guarantor/internships/{id}` - Aktualizácia ľubovoľnej praxe
- `POST /guarantor/internships/{id}/change-status` - Zmena stavu praxe
- `GET /guarantor/students` - Získanie všetkých študentov
- `GET /guarantor/companies` - Získanie všetkých firiem
- `GET /guarantor/pending-companies` - Získanie firiem čakajúcich na schválenie
- `POST /guarantor/companies/{id}/approve` - Schválenie firmy
- `POST /guarantor/companies/{id}/reject` - Zamietnutie firmy
- `GET /guarantor/external-system-tokens` - Získanie API tokenov
- `POST /guarantor/external-system-tokens` - Vytvorenie API tokenu
- `DELETE /guarantor/external-system-tokens/{id}` - Vymazanie API tokenu
- `POST /guarantor/internships/export` - Export praxí do CSV

#### API externého systému

- `POST /external/mark-defended/{id}` - Označenie praxe ako obhájenej (vyžaduje špeciálny token)

### Príklady API požiadaviek

#### Prihlásenie

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "student@example.com",
    "password": "password123"
  }'
```

**Odpoveď:**

```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "first_name": "Ján",
    "last_name": "Novák",
    "email": "student@example.com",
    "role_name": "student"
  }
}
```

#### Vytvorenie praxe

```bash
curl -X POST http://localhost:8000/api/internships \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "company_id": 1,
    "academic_year": "2024/2025",
    "semester": 1,
    "start_date": "2024-09-01",
    "end_date": "2024-12-20"
  }'
```

---

## Štruktúra projektu

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controllery
│   │   │   ├── AuthController.php
│   │   │   ├── InternshipController.php
│   │   │   ├── CompanyController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── StudentDocumentController.php
│   │   │   ├── UserController.php
│   │   │   └── ExternalSystemTokenController.php
│   │   ├── Middleware/           # Vlastný middleware
│   │   └── Requests/             # Validácia formulárových požiadaviek
│   ├── Models/                   # Eloquent modely
│   │   ├── User.php
│   │   ├── Internship.php
│   │   ├── Company.php
│   │   ├── Document.php
│   │   ├── InternshipStatus.php
│   │   └── ...
│   ├── Mail/                     # E-mailové triedy
│   ├── Services/                 # Biznis logika služieb
│   │   └── EmailNotificationService.php
│   └── Policies/                 # Autorizačné politiky
├── database/
│   ├── migrations/               # Databázové migrácie
│   ├── seeders/                  # Databázové seedery
│   │   ├── DatabaseSeeder.php
│   │   ├── RoleSeeder.php
│   │   ├── InternshipStatusSeeder.php
│   │   ├── TimesheetStatusSeeder.php
│   │   └── ...
│   └── factories/                # Model factories
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
├── config/                       # Konfiguračné súbory
├── storage/
│   ├── app/                      # Nahrané súbory
│   │   └── documents/            # Dokumenty praxí
│   └── logs/                     # Aplikačné logy
├── tests/                        # PHPUnit testy
├── .env                          # Konfigurácia prostredia
├── composer.json                 # PHP závislosti
└── artisan                       # Laravel CLI
```

---

## Kľúčové funkcie

### 1. Autentifikácia a autorizácia

- **Tokenová autentifikácia** pomocou Laravel Sanctum
- **Kontrola prístupu založená na rolách** (Študent, Firma, Garant, Externý systém)
- **Aktivácia účtu** cez e-mail s expirujúcimi tokenmi
- **Funkcia reset hesla**
- **Vynútená zmena hesla** pri prvom prihlásení

### 2. Správa používateľov

- Registrácia študentov s validáciou študentského e-mailu (`@student.ukf.sk`)
- Registrácia firiem s procesom schvaľovania garantom
- Funkcia zmeny e-mailu s verifikáciou
- Správa profilu
- Soft delete pre GDPR compliance

### 3. Správa životného cyklu praxe

- Kompletný pracovný postup: Vytvorená → Potvrdená → Schválená → Obhájená/Neobhájená
- História zmien stavu s audit trail
- E-mailové notifikácie pri zmenách stavu
- Potvrdenie/zamietnutie firmou
- Dohľad a schvaľovanie garantom

### 4. Správa dokumentov

- Nahrávanie dokumentov pre praxe
- Pracovný postup schvaľovania výkazov
- Generovanie PDF pre dohody o praxi ("Dohoda")
- Sledovanie metadát súborov
- Stav verifikácie dokumentov

### 5. Systém e-mailových notifikácií

- Automatizované notifikácie pre kľúčové udalosti:
  - Registrácia používateľa a aktivácia
  - Vytvorenie praxe a zmeny stavu
  - Schválenie/zamietnutie firmy
  - Nahranie a schválenie dokumentov
- Logovanie e-mailov pre audit trail
- Prevencia duplikátov
- Podpora pre viacero príjemcov

### 6. Proces schvaľovania firiem

- Garant posudzuje čakajúce firmy
- Automaticky generované heslá pri schválení
- Aktivačný e-mail s prihlasovacími údajmi
- Zamietnutie s notifikáciou

### 7. Reportovanie a analytika

- CSV export údajov o praxiach
- Filtrovanie podľa roka, semestra, stavu, firmy, študenta
- Endpointy pre dáta štatistického dashboardu

### 8. Integrácia s externým systémom

- OAuth2-kompatibilné API tokeny
- Správa tokenov garantom
- Oprávnenia založené na schopnostiach
- Zabezpečený endpoint pre označenie praxe ako obhájenej

---

## Testovanie

### Spustenie všetkých testov

```bash
php artisan test
```

### Spustenie konkrétnej testovacej sady

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Spustenie konkrétneho testovacieho súboru

```bash
php artisan test tests/Feature/AuthenticationTest.php
```

### S pokrytím

```bash
php artisan test --coverage
```

---

## Nasadenie

### Produkčný checklist

1. **Konfigurácia prostredia**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://vasa-domena.com
   ```

2. **Optimalizácia aplikácie**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Databázová migrácia**
   ```bash
   php artisan migrate --force
   ```

4. **Nastavenie oprávnení**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Konfigurácia queue workera**

   Nastavte supervisor alebo systemd službu pre spustenie:
   ```bash
   php artisan queue:work --sleep=3 --tries=3 --max-time=3600
   ```

6. **Konfigurácia plánovača (Cron)**

   Pridajte do crontabu:
   ```
   * * * * * cd /cesta-k-projektu && php artisan schedule:run >> /dev/null 2>&1
   ```

7. **SSL certifikát**

   Nainštalujte SSL certifikát a nakonfigurujte HTTPS

8. **Firewall a bezpečnosť**
   - Zatvorte nepoužívané porty
   - Nakonfigurujte rate limiting
   - Nastavte detekciu narušení

### Platformy pre nasadenie

Aplikáciu možno nasadiť na:
- **Tradičné VPS** (DigitalOcean, Linode, AWS EC2)
- **Platform-as-a-Service** (Laravel Forge, Laravel Vapor, Heroku)
- **Docker kontajnery** (Laravel Sail poskytuje Docker konfiguráciu)

---

## Riešenie problémov

### Problémy s pripojením k databáze

```bash
# Otestujte pripojenie k databáze
php artisan db:show

# Vymažte config cache
php artisan config:clear
```

### Chyby oprávnení

```bash
# Opravte oprávnenia storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### E-mail sa neposiela

1. Skontrolujte `.env` mail konfiguráciu
2. Overte SMTP prihlasovacie údaje
3. Skontrolujte `storage/logs/laravel.log` pre chyby
4. Testujte s `MAIL_MAILER=log` pre logovanie e-mailov do súboru

### Problémy s tokenovou autentifikáciou

```bash
# Vymažte cache
php artisan cache:clear
php artisan config:clear

# Overte, že SANCTUM_STATEFUL_DOMAINS obsahuje vašu frontend URL
```

### Queue sa nespracováva

```bash
# Skontrolujte queue pripojenie
php artisan queue:work --once

# Skontrolujte neúspešné joby
php artisan queue:failed

# Zopakujte neúspešné joby
php artisan queue:retry all
```

### Vymazanie všetkých cache

```bash
php artisan optimize:clear
```

Toto vymaže:
- Configuration cache
- Route cache
- View cache
- Application cache
- Compiled classes

---

## Užitočné príkazy

### Artisan príkazy

```bash
# Generovanie API dokumentácie (ak je nainštalovaný balíček)
php artisan route:list

# Vytvorenie nového controllera
php artisan make:controller NazovControllera

# Vytvorenie nového modelu s migráciou
php artisan make:model NazovModelu -m

# Vytvorenie nového seedera
php artisan make:seeder NazovSeedera

# Vytvorenie nového middleware
php artisan make:middleware NazovMiddleware

# Vytvorenie novej policy
php artisan make:policy NazovPolicy --model=NazovModelu

# Tinker (REPL)
php artisan tinker

# Kontrola stavu aplikácie
php artisan about

# Zobrazenie logov v reálnom čase
php artisan pail
```

### Databázové príkazy

```bash
# Spustenie migrácií
php artisan migrate

# Vrátenie poslednej migrácie
php artisan migrate:rollback

# Reset databázy a opätovné naplnenie
php artisan migrate:fresh --seed

# Zobrazenie informácií o databáze
php artisan db:show

# Zobrazenie schémy tabuľky
php artisan db:table users
```

### Režim údržby

```bash
# Vstup do režimu údržby
php artisan down

# Výstup z režimu údržby
php artisan up

# Režim údržby s tajným obídením
php artisan down --secret="moj-tajny-token"
# Prístup: https://vasa-domena.com/moj-tajny-token
```

---

## Podpora a dokumentácia

- **Laravel dokumentácia:** https://laravel.com/docs/12.x
- **Laravel Sanctum:** https://laravel.com/docs/12.x/sanctum
- **DomPDF dokumentácia:** https://github.com/barryvdh/laravel-dompdf

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
- Kompletný systém správy odborných praxí
- Viacúrovňová autentifikácia
- Správa dokumentov
- E-mailové notifikácie
- Integrácia s externým API
- Funkcia exportu CSV
