# Praximoron - Internship Management System

A comprehensive CRM-like system for managing student internships ("Odborná prax") at UKF Nitra. Built with Laravel 12 and Vue 3.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)
![Vue](https://img.shields.io/badge/Vue-3.5-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)
![License](https://img.shields.io/badge/license-Proprietary-lightgrey.svg)

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Documentation](#documentation)
- [Development](#development)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

**Praximoron** is a modern web-based system designed to streamline the management of student internships at Constantine the Philosopher University in Nitra (UKF). The system integrates and automates existing processes, improves record-keeping, and provides a unified database of companies and students.

### Purpose

- **Integrate** existing internship management processes
- **Automate** routine administrative tasks
- **Improve** visibility and tracking of active and archived internships
- **Centralize** company and student database
- **Facilitate** communication between students, companies, and guarantors

### User Roles

The system supports **5 distinct user roles**:

1. **Anonymous User** - Access to public landing page
2. **Student** - Create and manage internship applications
3. **Company** - Review and approve internship applications
4. **Guarantor** - Administrative oversight of all internships
5. **External System** - API access for integration with other systems

---

## Features

### Core Functionality

- **Multi-role Authentication** - Token-based authentication with role-specific permissions
- **Internship Lifecycle Management** - Complete workflow from creation to completion
- **Document Management** - Upload, approve, and track internship documents
- **Email Notifications** - Automated notifications for key events
- **Company Approval Workflow** - Guarantor-managed company registration
- **Statistics & Reporting** - Visual dashboards and CSV exports
- **External API Integration** - OAuth2-compatible tokens for external systems
- **PDF Generation** - Automatic generation of internship agreements

### Student Features

- View personal internship history
- Create new internship applications
- Select companies from searchable database
- Upload required documents and timesheets
- Download generated internship agreements
- Track internship status in real-time

### Company Features

- View internship applications
- Confirm or reject student applications
- Approve or reject uploaded timesheets
- Access student contact information
- Track confirmed internships

### Guarantor Features

- Full administrative access to all internships
- Approve or reject company registrations
- Change internship statuses manually
- Filter and search internships by multiple criteria
- Generate reports and statistics
- Export data to CSV
- Manage external system API tokens

---

## Technology Stack

### Backend
- **Framework:** Laravel 12.0
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+ (supports PostgreSQL, SQLite)
- **Authentication:** Laravel Sanctum
- **PDF Generation:** DomPDF
- **Email:** Laravel Mail with SMTP

### Frontend
- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite 7
- **Router:** Vue Router 4
- **State Management:** Pinia 3
- **HTTP Client:** Axios 1.12
- **Charts:** Chart.js 4.5

### Development Tools
- **Testing:** PHPUnit
- **Code Quality:** ESLint, Prettier
- **Debugging:** Laravel Pail, Vue DevTools

---

## System Requirements

### Software Requirements

- **PHP:** >= 8.2
- **Composer:** >= 2.0
- **Node.js:** >= 20.19.0
- **npm:** >= 9.0 or **yarn:** >= 1.22
- **Database:** MySQL >= 8.0, PostgreSQL >= 13, or SQLite >= 3.35
- **Web Server:** Apache 2.4+ or Nginx 1.18+

### PHP Extensions

- BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Browser Support

- Chrome/Edge >= 90
- Firefox >= 88
- Safari >= 14

---

## Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd system-odbornej-praxe
```

### 2. Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env
# On Windows: copy .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
# DB_DATABASE=praximoron
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Start development server
php artisan serve

# In a separate terminal, start queue worker
php artisan queue:work
```

The backend API will be available at: `http://localhost:8000`

### 3. Frontend Setup

```bash
# Open a new terminal and navigate to frontend directory
cd frontend

# Install Node dependencies
npm install

# Create environment file
echo "VITE_API_BASE_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
```

The frontend application will be available at: `http://localhost:5173`

### 4. Access the Application

Open your browser and navigate to `http://localhost:5173`

**Default Test Users (after seeding):**

| Role | Email | Password |
|------|-------|----------|
| Student | student@student.ukf.sk | password |
| Company | company@example.com | password |
| Guarantor | guarantor@ukf.sk | password |

---

## Project Structure

```
system-odbornej-praxe/
├── backend/                    # Laravel 12 REST API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/    # API Controllers
│   │   ├── Models/             # Eloquent Models
│   │   ├── Mail/               # Email Classes
│   │   └── Services/           # Business Logic
│   ├── database/
│   │   ├── migrations/         # Database Migrations
│   │   └── seeders/            # Database Seeders
│   ├── routes/
│   │   └── api.php             # API Routes
│   ├── storage/                # Logs, Uploads
│   ├── .env                    # Environment Config
│   └── README.md               # Backend Documentation
│
├── frontend/                   # Vue 3 SPA
│   ├── src/
│   │   ├── components/         # Reusable Components
│   │   ├── router/             # Vue Router Config
│   │   ├── views/              # Page Components
│   │   │   ├── PublicViews/
│   │   │   ├── StudentViews/
│   │   │   ├── CompanyViews/
│   │   │   └── GuarantorViews/
│   │   ├── api.js              # Axios API Client
│   │   └── main.js             # Application Entry
│   ├── .env                    # Environment Config
│   └── README.md               # Frontend Documentation
│
├── additional_files/           # Documentation
│   └── Specifikacia.pdf        # System Specification
│
└── README.md                   # This File
```

---

## Documentation

Detailed documentation is available for each component:

- **[Backend Documentation](backend/README.md)** - API reference, database schema, deployment
- **[Frontend Documentation](frontend/README.md)** - Component guide, routing, building
- **[System Specification](additional_files/Specifikacia.pdf)** - Functional and non-functional requirements (Slovak)

---

## Development

### Backend Development

```bash
cd backend

# Start development server
php artisan serve

# Start queue worker
php artisan queue:work

# View logs in real-time
php artisan pail

# Run tests
php artisan test

# Create new migration
php artisan make:migration CreateTableName

# Create new controller
php artisan make:controller ControllerName

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Frontend Development

```bash
cd frontend

# Start development server
npm run dev

# Lint code
npm run lint

# Format code
npm run format

# Build for production
npm run build

# Preview production build
npm run preview
```

### Database Management

```bash
cd backend

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database and reseed
php artisan migrate:fresh --seed

# Create new seeder
php artisan make:seeder SeederName

# Show database structure
php artisan db:show
php artisan db:table users
```

---

## Deployment

### Production Checklist

#### Backend Deployment

1. **Server Requirements**
   - PHP 8.2+, Composer 2.0+
   - MySQL 8.0+ or PostgreSQL 13+
   - Web server (Apache/Nginx)
   - SSL certificate

2. **Environment Configuration**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

3. **Optimize Application**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

5. **Set Permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Configure Queue Worker** (Supervisor/Systemd)
   ```bash
   php artisan queue:work --sleep=3 --tries=3
   ```

#### Frontend Deployment

1. **Build Production Bundle**
   ```bash
   npm run build
   ```

2. **Deploy `dist/` Directory**
   - Static hosting (Netlify, Vercel, GitHub Pages)
   - Web server (Apache, Nginx)
   - CDN (CloudFront, Cloudflare)

3. **Configure Web Server**

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

### Deployment Platforms

- **Backend:** Laravel Forge, Laravel Vapor, AWS EC2, DigitalOcean, Heroku
- **Frontend:** Netlify, Vercel, AWS S3+CloudFront, Firebase Hosting
- **Database:** AWS RDS, DigitalOcean Managed Databases, PlanetScale

---

## API Documentation

### Base URL

```
http://localhost:8000/api (development)
https://api.your-domain.com/api (production)
```

### Authentication

Most endpoints require Bearer token authentication:

```bash
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Key Endpoints

#### Authentication
- `POST /register-student` - Register student
- `POST /register-company` - Register company
- `POST /login` - User login
- `POST /logout` - User logout
- `POST /forgot-password` - Request password reset
- `POST /reset-password` - Reset password

#### Internships
- `GET /student-internships/{id}` - Get student internships
- `POST /internships` - Create internship
- `PUT /internships/{id}` - Update internship
- `GET /company-internships/{id}` - Get company internships
- `POST /internships/{id}/confirm` - Confirm internship
- `POST /internships/{id}/reject` - Reject internship
- `GET /guarantor/internships` - Get all internships (with filters)
- `POST /guarantor/internships/{id}/change-status` - Change status

#### Documents
- `POST /student/internships/{id}/documents` - Upload document
- `GET /internships/{id}/generate-dohoda` - Generate PDF agreement
- `POST /documents/{id}/approve-timesheet` - Approve timesheet
- `POST /documents/{id}/reject-timesheet` - Reject timesheet

#### Guarantor
- `GET /guarantor/pending-companies` - Get pending companies
- `POST /guarantor/companies/{id}/approve` - Approve company
- `POST /guarantor/companies/{id}/reject` - Reject company
- `POST /guarantor/internships/export` - Export to CSV
- `GET /guarantor/external-system-tokens` - Manage API tokens

For complete API documentation, see [backend/README.md](backend/README.md#api-documentation).

---

## Troubleshooting

### Common Issues

#### Database Connection Error

```bash
# Check database configuration in .env
# Verify database exists
mysql -u root -p
CREATE DATABASE praximoron;

# Clear config cache
php artisan config:clear
```

#### CORS Errors

```bash
# Backend .env
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173

# Clear cache
php artisan config:clear
```

#### Port Already in Use

```bash
# Backend (change port)
php artisan serve --port=8001

# Frontend (change port)
npm run dev -- --port 3000
```

#### Queue Not Processing

```bash
# Start queue worker
php artisan queue:work

# Check failed jobs
php artisan queue:failed
php artisan queue:retry all
```

#### Email Not Sending

```bash
# Development: Use log driver
MAIL_MAILER=log

# Check logs
tail -f storage/logs/laravel.log

# Production: Verify SMTP settings
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

---

## Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AuthenticationTest.php
```

### Frontend Tests

```bash
cd frontend

# Lint code
npm run lint

# Format code
npm run format
```

---

## Security

### Security Features

- **Password Hashing:** bcrypt with automatic rehashing
- **Token Authentication:** Laravel Sanctum with expiring tokens
- **CSRF Protection:** Built-in Laravel CSRF protection
- **SQL Injection Prevention:** Eloquent ORM with prepared statements
- **XSS Protection:** Vue automatic escaping
- **Rate Limiting:** API rate limiting configured
- **Soft Deletes:** GDPR-compliant data retention

### Best Practices

- Always use HTTPS in production
- Keep dependencies up to date
- Use environment variables for sensitive data
- Enable 2FA for guarantor accounts (if implemented)
- Regular security audits
- Backup database regularly

---

## Performance Optimization

### Backend

- Route caching: `php artisan route:cache`
- Config caching: `php artisan config:cache`
- View caching: `php artisan view:cache`
- Opcode caching: Enable OPcache in PHP
- Database indexing: Proper indexes on foreign keys
- Query optimization: Eager loading, pagination

### Frontend

- Lazy loading routes
- Code splitting with Vite
- Asset optimization (minification, compression)
- CDN for static assets
- Browser caching headers

---

## Contributing

### Development Workflow

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**
   - Follow coding standards (PSR-12 for PHP, Vue Style Guide)
   - Write tests for new features
   - Update documentation

3. **Commit Changes**
   ```bash
   git add .
   git commit -m "feat: add your feature description"
   ```

4. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

### Code Style

- **PHP:** Follow PSR-12, use Laravel conventions
- **JavaScript:** Use ESLint and Prettier configurations
- **Vue:** Follow Vue 3 Composition API patterns
- **Commits:** Use conventional commit messages

---

## Support & Resources

### Documentation

- **Laravel:** https://laravel.com/docs/12.x
- **Vue 3:** https://vuejs.org/
- **Vite:** https://vite.dev/
- **Laravel Sanctum:** https://laravel.com/docs/12.x/sanctum
- **Vue Router:** https://router.vuejs.org/
- **Chart.js:** https://www.chartjs.org/

### Community

- Report issues on GitHub
- Contact development team for support

---

## License

This project is proprietary software developed for internal use at Constantine the Philosopher University in Nitra.

**Copyright © 2025 UKF Nitra. All rights reserved.**

---

## Contributors

Developed for the **Software Engineering** course at UKF Nitra.

---

## Changelog

### Version 1.0.0 (2025-01-13)

**Initial Release**

- Complete internship management system
- Multi-role authentication and authorization
- Student, Company, and Guarantor dashboards
- Internship workflow management
- Document upload and approval system
- Email notification system
- Company approval workflow
- Statistics and reporting
- External API integration
- CSV data export
- PDF agreement generation
- Responsive design for all devices

---

## Acknowledgments

- **University:** Constantine the Philosopher University in Nitra
- **Course:** Software Engineering (Softvérové Inžinierstvo)
- **Framework:** Laravel by Taylor Otwell and the Laravel community
- **Frontend:** Vue.js by Evan You and the Vue team

---

**For detailed setup instructions and API documentation, see:**
- [Backend README](backend/README.md)
- [Frontend README](frontend/README.md)
