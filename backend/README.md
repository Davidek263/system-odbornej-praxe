# Praximoron - Backend API

RESTful API backend for the Praximoron internship management system built with Laravel 12.

## Table of Contents

- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)

---

## Overview

The Praximoron backend is a Laravel 12-based REST API that manages the complete lifecycle of student internships, including:
- Multi-role user authentication and authorization
- Internship workflow management
- Document upload and approval
- Email notifications
- Company approval processes
- External system integration
- CSV data export for reporting

---

## Technology Stack

- **Framework:** Laravel 12.0
- **PHP Version:** 8.2+
- **Authentication:** Laravel Sanctum (token-based)
- **Database:** MySQL (also supports PostgreSQL, SQLite)
- **PDF Generation:** DomPDF
- **Email:** Laravel Mail with SMTP support
- **API Architecture:** RESTful
- **Additional Tools:**
  - Laravel Pail (log viewer)
  - Laravel Tinker (REPL)
  - PHPUnit (testing)

---

## System Requirements

- **PHP:** >= 8.2
- **Composer:** >= 2.0
- **MySQL:** >= 8.0 (or PostgreSQL >= 13, SQLite >= 3.35)
- **Node.js:** >= 20.19.0 (for development tools)
- **Extensions:**
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

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd system-odbornej-praxe/backend
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Copy Environment File

```bash
cp .env.example .env
# On Windows:
copy .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

---

## Configuration

### Environment Variables

Edit the `.env` file to configure your application:

#### Application Settings

```env
APP_NAME="Praximoron"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Europe/Bratislava
APP_URL=http://localhost:8000
```

#### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=praximoron
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Mail Configuration

**For Development (Logs emails to file):**

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@praximoron.ukf.sk"
MAIL_FROM_NAME="${APP_NAME}"
```

**For Production (Gmail SMTP):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** For Gmail, you need to generate an [App Password](https://support.google.com/accounts/answer/185833).

#### CORS & Sanctum Configuration

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5173
FRONTEND_URL=http://localhost:5173
```

#### Custom Settings

```env
# Password Reset Token Expiry (in minutes)
PASSWORD_RESET_EXPIRY=1440

# Account Activation Token Expiry (in hours)
ACTIVATION_TOKEN_EXPIRY=48

# File Upload Settings
MAX_FILE_UPLOAD_SIZE=10240
ALLOWED_FILE_TYPES=pdf,doc,docx,jpg,jpeg,png

# Academic Year Settings
CURRENT_ACADEMIC_YEAR=2024/2025
CURRENT_SEMESTER=1

# Security Settings
API_RATE_LIMIT=60
LOGIN_RATE_LIMIT=50
TOKEN_LIFETIME=1440
```

---

## Database Setup

### 1. Create Database

Create a MySQL database for the application:

```sql
CREATE DATABASE praximoron CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- users
- roles
- companies
- internships
- documents
- internship_status
- internship_status_changes
- timesheet_status
- study_fields
- addresses
- email_notifications
- audit_logs
- password_resets
- and more...

### 3. Seed Database

Populate the database with initial data (roles, statuses, sample data):

```bash
php artisan db:seed
```

This will seed:
- User roles (student, company, guarantor)
- Internship statuses (Vytvorená, Potvrdená, Schválená, Obhájená, Neobhájená, Zamietnutá)
- Timesheet statuses (Nový, Potvrdený, Zamietnutý)
- Document types
- Study fields
- Sample users and companies (for development)

### 4. Fresh Migration (Optional)

To reset the database and reseed:

```bash
php artisan migrate:fresh --seed
```

**Warning:** This will drop all tables and recreate them, losing all data.

---

## Running the Application

### Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The API will be available at: `http://localhost:8000`

### With Queue Worker (Recommended)

For processing queued jobs (emails, notifications):

```bash
# Terminal 1: Start server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work
```

### Using Laravel Pail (Log Viewer)

Monitor logs in real-time:

```bash
php artisan pail
```

### All-in-One Development Command

Run server, queue worker, and logs simultaneously:

```bash
composer dev
```

This requires `concurrently` (npm package).

---

## API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

Most endpoints require authentication using Laravel Sanctum tokens.

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Public Endpoints

#### Authentication

- `POST /register-student` - Register a new student
- `POST /register-company` - Register a new company
- `GET /activate-account` - Activate user account
- `POST /resend-activation` - Resend activation email
- `POST /set-initial-password` - Set initial password
- `POST /login` - User login
- `POST /forgot-password` - Request password reset
- `POST /reset-password` - Reset password with token
- `GET /study-fields` - Get list of study fields

#### Email Verification

- `GET /verify-email-change` - Verify email change request

### Protected Endpoints

#### User Management

- `GET /user` - Get current authenticated user
- `POST /logout` - Logout user
- `POST /change-password` - Change password
- `POST /request-student-email-change` - Request student email change
- `POST /request-company-email-change` - Request company email change
- `POST /update-alternative-email` - Update alternative email

#### Student Endpoints

- `GET /student/companies` - Get list of companies
- `GET /student-internships/{studentId}` - Get student's internships
- `GET /student/document-types` - Get document types
- `GET /student/internships/{id}/documents` - Get internship documents
- `POST /student/internships/{id}/documents` - Upload document
- `GET /student/documents/{id}/download` - Download document
- `DELETE /student/documents/{id}` - Delete document

#### Internship Management

- `POST /internships` - Create new internship (Student)
- `GET /internships/{id}` - Get single internship details
- `PUT /internships/{id}` - Update internship (Student - only in "Vytvorená" status)
- `POST /internships/{id}/confirm` - Confirm internship (Company)
- `POST /internships/{id}/reject` - Reject internship (Company)
- `GET /internships/{id}/generate-dohoda` - Generate internship agreement PDF

#### Company Endpoints

- `GET /company-internships/{companyId}` - Get company's internships

#### Document/Timesheet Management

- `POST /documents/{id}/approve-timesheet` - Approve timesheet (Company)
- `POST /documents/{id}/reject-timesheet` - Reject timesheet (Company)

#### Guarantor Endpoints

- `GET /guarantor/internships` - Get all internships with filters
- `PUT /guarantor/internships/{id}` - Update any internship
- `POST /guarantor/internships/{id}/change-status` - Change internship status
- `GET /guarantor/students` - Get all students
- `GET /guarantor/companies` - Get all companies
- `GET /guarantor/pending-companies` - Get pending company approvals
- `POST /guarantor/companies/{id}/approve` - Approve company
- `POST /guarantor/companies/{id}/reject` - Reject company
- `GET /guarantor/external-system-tokens` - Get API tokens
- `POST /guarantor/external-system-tokens` - Create API token
- `DELETE /guarantor/external-system-tokens/{id}` - Delete API token
- `POST /guarantor/internships/export` - Export internships to CSV

#### External System API

- `POST /external/mark-defended/{id}` - Mark internship as defended (requires special token)

### Example API Requests

#### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "student@example.com",
    "password": "password123"
  }'
```

**Response:**

```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "student@example.com",
    "role_name": "student"
  }
}
```

#### Create Internship

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

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── InternshipController.php
│   │   │   ├── CompanyController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── StudentDocumentController.php
│   │   │   ├── UserController.php
│   │   │   └── ExternalSystemTokenController.php
│   │   ├── Middleware/           # Custom middleware
│   │   └── Requests/             # Form request validation
│   ├── Models/                   # Eloquent models
│   │   ├── User.php
│   │   ├── Internship.php
│   │   ├── Company.php
│   │   ├── Document.php
│   │   ├── InternshipStatus.php
│   │   └── ...
│   ├── Mail/                     # Email classes
│   ├── Services/                 # Business logic services
│   │   └── EmailNotificationService.php
│   └── Policies/                 # Authorization policies
├── database/
│   ├── migrations/               # Database migrations
│   ├── seeders/                  # Database seeders
│   │   ├── DatabaseSeeder.php
│   │   ├── RoleSeeder.php
│   │   ├── InternshipStatusSeeder.php
│   │   ├── TimesheetStatusSeeder.php
│   │   └── ...
│   └── factories/                # Model factories
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
├── config/                       # Configuration files
├── storage/
│   ├── app/                      # Uploaded files
│   │   └── documents/            # Internship documents
│   └── logs/                     # Application logs
├── tests/                        # PHPUnit tests
├── .env                          # Environment configuration
├── composer.json                 # PHP dependencies
└── artisan                       # Laravel CLI
```

---

## Key Features

### 1. Authentication & Authorization

- **Token-based authentication** using Laravel Sanctum
- **Role-based access control** (Student, Company, Guarantor, External System)
- **Account activation** via email with expiring tokens
- **Password reset** functionality
- **Forced password change** on first login

### 2. User Management

- Student registration with student email validation (`@student.ukf.sk`)
- Company registration with guarantor approval workflow
- Email change functionality with verification
- Profile management
- Soft deletes for GDPR compliance

### 3. Internship Lifecycle Management

- Complete workflow: Vytvorená → Potvrdená → Schválená → Obhájená/Neobhájená
- Status change history with audit trail
- Email notifications on status changes
- Company confirmation/rejection
- Guarantor oversight and approval

### 4. Document Management

- Document upload for internships
- Timesheet approval workflow
- PDF generation for internship agreements ("Dohoda")
- File metadata tracking
- Document verification status

### 5. Email Notification System

- Automated notifications for key events:
  - User registration and activation
  - Internship creation and status changes
  - Company approval/rejection
  - Document uploads and approvals
- Email logging for audit trail
- Duplicate prevention
- Support for multiple recipients

### 6. Company Approval Process

- Guarantor reviews pending companies
- Auto-generated passwords on approval
- Activation email with credentials
- Rejection with notification

### 7. Reporting & Analytics

- CSV export of internship data
- Filtering by year, semester, status, company, student
- Statistics dashboard data endpoints

### 8. External System Integration

- OAuth2-compatible API tokens
- Token management by guarantor
- Ability-based permissions
- Secure endpoint for marking internships as defended

---

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run Specific Test File

```bash
php artisan test tests/Feature/AuthenticationTest.php
```

### With Coverage

```bash
php artisan test --coverage
```

---

## Deployment

### Production Checklist

1. **Environment Configuration**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

2. **Optimize Application**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Database Migration**
   ```bash
   php artisan migrate --force
   ```

4. **Set Permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Configure Queue Worker**

   Set up supervisor or systemd service to run:
   ```bash
   php artisan queue:work --sleep=3 --tries=3 --max-time=3600
   ```

6. **Configure Scheduler (Cron)**

   Add to crontab:
   ```
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

7. **SSL Certificate**

   Install SSL certificate and configure HTTPS

8. **Firewall & Security**
   - Close unused ports
   - Configure rate limiting
   - Set up intrusion detection

### Deployment Platforms

The application can be deployed to:
- **Traditional VPS** (DigitalOcean, Linode, AWS EC2)
- **Platform-as-a-Service** (Laravel Forge, Laravel Vapor, Heroku)
- **Docker Containers** (Laravel Sail provides Docker configuration)

---

## Troubleshooting

### Database Connection Issues

```bash
# Test database connection
php artisan db:show

# Clear config cache
php artisan config:clear
```

### Permission Errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Email Not Sending

1. Check `.env` mail configuration
2. Verify SMTP credentials
3. Check `storage/logs/laravel.log` for errors
4. Test with `MAIL_MAILER=log` to log emails to file

### Token Authentication Issues

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Verify SANCTUM_STATEFUL_DOMAINS includes your frontend URL
```

### Queue Not Processing

```bash
# Check queue connection
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Clear All Caches

```bash
php artisan optimize:clear
```

This clears:
- Configuration cache
- Route cache
- View cache
- Application cache
- Compiled classes

---

## Useful Commands

### Artisan Commands

```bash
# Generate API documentation (if package installed)
php artisan route:list

# Create new controller
php artisan make:controller ControllerName

# Create new model with migration
php artisan make:model ModelName -m

# Create new seeder
php artisan make:seeder SeederName

# Create new middleware
php artisan make:middleware MiddlewareName

# Create new policy
php artisan make:policy PolicyName --model=ModelName

# Tinker (REPL)
php artisan tinker

# Check application status
php artisan about

# View logs in real-time
php artisan pail
```

### Database Commands

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database and reseed
php artisan migrate:fresh --seed

# Show database information
php artisan db:show

# Show table schema
php artisan db:table users
```

### Maintenance Mode

```bash
# Enter maintenance mode
php artisan down

# Exit maintenance mode
php artisan up

# Maintenance mode with secret bypass
php artisan down --secret="my-secret-token"
# Access: https://your-domain.com/my-secret-token
```

---

## Support & Documentation

- **Laravel Documentation:** https://laravel.com/docs/12.x
- **Laravel Sanctum:** https://laravel.com/docs/12.x/sanctum
- **DomPDF Documentation:** https://github.com/barryvdh/laravel-dompdf

---

## License

This project is proprietary software developed for internal use.

---

## Contributors

Developed for the Software Engineering course at UKF Nitra.

---

## Changelog

### Version 1.0.0 (2025-01-13)
- Initial release
- Complete internship management system
- Multi-role authentication
- Document management
- Email notifications
- External API integration
- CSV export functionality
