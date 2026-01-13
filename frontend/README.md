# Praximoron - Frontend

Modern, responsive Vue 3 frontend for the Praximoron internship management system.

## Table of Contents

- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Project Structure](#project-structure)
- [Features](#features)
- [Development](#development)
- [Building for Production](#building-for-production)
- [Troubleshooting](#troubleshooting)

---

## Overview

The Praximoron frontend is a modern single-page application (SPA) built with Vue 3 and Vite. It provides an intuitive interface for students, companies, and guarantors to manage internships, documents, and related workflows.

**Key Capabilities:**
- Role-based dashboards (Student, Company, Guarantor)
- Internship creation and management
- Document upload and approval
- Company approval workflow
- Statistics and reporting
- Responsive design for desktop, tablet, and mobile

---

## Technology Stack

- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite 7
- **Router:** Vue Router 4
- **State Management:** Pinia 3
- **HTTP Client:** Axios 1.12
- **Charts:** Chart.js 4.5
- **Development Tools:**
  - ESLint (code linting)
  - Prettier (code formatting)
  - Vue DevTools (debugging)

---

## System Requirements

- **Node.js:** >= 20.19.0 or >= 22.12.0
- **npm:** >= 9.0 or **yarn:** >= 1.22
- **Modern Browser:**
  - Chrome/Edge >= 90
  - Firefox >= 88
  - Safari >= 14

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd system-odbornej-praxe/frontend
```

### 2. Install Dependencies

```bash
npm install
```

Or with Yarn:

```bash
yarn install
```

---

## Configuration

### Environment Variables

Create a `.env` file in the frontend root directory:

```env
# API Base URL (backend)
VITE_API_BASE_URL=http://localhost:8000/api

# Application Name
VITE_APP_NAME=Praximoron
```

**Important:** The `.env` file is not tracked in Git. Copy from `.env.example` if available.

### API Configuration

The API client is configured in [src/api.js](src/api.js):

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

**Features:**
- Automatic token injection from localStorage
- 401 redirect to login on unauthorized requests
- Error handling for common HTTP status codes

---

## Running the Application

### Development Server

Start the development server with hot module replacement:

```bash
npm run dev
```

The application will be available at: `http://localhost:5173`

### Development with Backend

Ensure the backend API is running first:

```bash
# In backend directory
php artisan serve
php artisan queue:work
```

Then start the frontend:

```bash
# In frontend directory
npm run dev
```

---

## Project Structure

```
frontend/
├── public/                     # Static assets
├── src/
│   ├── assets/                 # Images, styles, fonts
│   ├── components/             # Reusable Vue components
│   │   ├── HeaderNav.vue       # Navigation header
│   │   └── ...
│   ├── router/                 # Vue Router configuration
│   │   └── index.js            # Routes and navigation guards
│   ├── stores/                 # Pinia state management (if used)
│   ├── views/                  # Page components
│   │   ├── PublicViews/        # Public pages
│   │   │   ├── LandingPage.vue
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── StudentViews/       # Student pages
│   │   │   └── StudentDashboard.vue
│   │   ├── CompanyViews/       # Company pages
│   │   │   └── CompanyDashboard.vue
│   │   ├── GuarantorViews/     # Guarantor pages
│   │   │   ├── GuarantorDashboard.vue
│   │   │   ├── GuarantorStatistics.vue
│   │   │   ├── GuarantorTokens.vue
│   │   │   └── PendingCompanies.vue
│   │   ├── ProfileView.vue     # User profile
│   │   └── ...
│   ├── api.js                  # Axios API client
│   ├── main.js                 # Application entry point
│   └── App.vue                 # Root component
├── .env                        # Environment variables (create this)
├── .gitignore                  # Git ignore file
├── index.html                  # HTML entry point
├── package.json                # Dependencies and scripts
├── vite.config.js              # Vite configuration
├── eslint.config.js            # ESLint configuration
└── README.md                   # This file
```

---

## Features

### Public Pages

#### Landing Page
- Accessible without authentication
- Information about the internship program
- Links to login and registration

#### Login
- Email and password authentication
- "Forgot Password" link
- Redirects to role-specific dashboard after login

#### Registration
- Separate forms for students and companies
- Student registration fields:
  - Name, surname, address
  - Student email (`@student.ukf.sk`)
  - Alternative email, phone
  - Study field selection
- Company registration fields:
  - Company name, address
  - Contact person (name, email, phone)

#### Password Reset
- Request password reset via email
- Set new password with reset token

---

### Role-Based Dashboards

#### Student Dashboard
- View list of own internships with status
- Create new internship:
  - Select company from dropdown
  - Enter dates, academic year, semester
- Edit internship details (only in "Vytvorená" status)
- Upload documents (timesheets, agreements)
- Download generated internship agreements (PDF)
- Track internship status changes

#### Company Dashboard
- View internships in "Vytvorená" status
- Confirm or reject internship applications
- View confirmed internships
- Approve or reject student-uploaded timesheets
- View student information

#### Guarantor Dashboard
- View all internships across the system
- Filter internships by:
  - Academic year
  - Semester
  - Status
  - Company
  - Student
  - Study field
- Edit any internship details
- Change internship status manually
- Export internship data to CSV

#### Guarantor Statistics
- Visual charts and graphs
- Internship counts by status
- Internship counts by company
- Semester statistics
- Academic year comparisons

#### Guarantor Token Management
- Create API tokens for external systems
- View existing tokens
- Delete tokens
- Token permissions management

#### Pending Companies
- View companies awaiting approval
- Approve companies:
  - Auto-generates credentials
  - Sends activation email
- Reject companies with reason

---

### Common Features

#### Profile Management
- View and edit user profile
- Change email address (with verification)
- Update alternative email
- Update address
- Change password

#### Navigation
- Role-aware navigation menu
- User dropdown with profile and logout
- Breadcrumb navigation (where applicable)

#### Responsive Design
- Mobile-friendly interface
- Touch-optimized controls
- Adaptive layouts for all screen sizes

#### Error Handling
- User-friendly error messages
- Automatic logout on token expiration
- Form validation feedback

---

## Development

### Code Linting

Lint and auto-fix code style issues:

```bash
npm run lint
```

### Code Formatting

Format code with Prettier:

```bash
npm run format
```

### IDE Setup

**Recommended IDE:** VS Code

**Extensions:**
- [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) - Vue language support
- [ESLint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint) - Code linting
- [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode) - Code formatting

**Browser Extensions:**
- **Chrome/Edge:** [Vue.js devtools](https://chromewebstore.google.com/detail/vuejs-devtools/nhdogjmejiglipccpnnnanhbledajbpd)
- **Firefox:** [Vue.js devtools](https://addons.mozilla.org/en-US/firefox/addon/vue-js-devtools/)

### Debugging

**Vue DevTools:**
Enable custom object formatters in browser DevTools:
- **Chrome:** Settings → Console → Enable custom formatters
- **Firefox:** [Custom Object Formatters](https://fxdx.dev/firefox-devtools-custom-object-formatters/)

**Network Debugging:**
Use browser DevTools Network tab to inspect API requests and responses.

### Component Development

**Create a new component:**

```vue
<!-- src/components/MyComponent.vue -->
<template>
  <div class="my-component">
    <h1>{{ title }}</h1>
  </div>
</template>

<script>
export default {
  name: 'MyComponent',
  props: {
    title: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.my-component {
  padding: 20px;
}
</style>
```

**Use the component:**

```vue
<template>
  <MyComponent title="Hello World" />
</template>

<script>
import MyComponent from '@/components/MyComponent.vue'

export default {
  components: {
    MyComponent
  }
}
</script>
```

---

## Building for Production

### Build Optimized Bundle

```bash
npm run build
```

This creates an optimized production build in the `dist/` directory with:
- Minified JavaScript and CSS
- Code splitting
- Asset optimization
- Source maps (optional)

### Preview Production Build

Test the production build locally:

```bash
npm run preview
```

This starts a local server serving the `dist/` directory.

### Deployment

The `dist/` directory contains static files that can be deployed to:

#### Static Hosting Platforms
- **Netlify:** Drag and drop `dist/` folder or connect Git
- **Vercel:** Connect Git repository
- **GitHub Pages:** Deploy `dist/` folder
- **AWS S3 + CloudFront:** Upload to S3 bucket
- **Firebase Hosting:** `firebase deploy`

#### Web Server (Apache/Nginx)

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

This ensures proper routing for Vue Router's history mode.

---

## Troubleshooting

### Port Already in Use

If port 5173 is occupied:

```bash
# Run on different port
npm run dev -- --port 3000
```

Or update `vite.config.js`:

```javascript
export default defineConfig({
  server: {
    port: 3000
  }
})
```

### API Connection Issues

**Problem:** API requests fail with CORS errors

**Solution:**
1. Verify backend CORS configuration in `backend/config/cors.php`
2. Ensure `FRONTEND_URL` in backend `.env` matches frontend URL
3. Check `SANCTUM_STATEFUL_DOMAINS` includes frontend domain

**Problem:** 401 Unauthorized errors

**Solution:**
1. Check if token exists in localStorage
2. Verify token is valid (not expired)
3. Ensure backend is running and accessible
4. Clear cache and login again

### Build Issues

**Problem:** Build fails with out of memory error

**Solution:**
```bash
# Increase Node.js memory limit
export NODE_OPTIONS=--max_old_space_size=4096
npm run build
```

**Problem:** Module not found errors

**Solution:**
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Development Server Issues

**Problem:** Hot reload not working

**Solution:**
1. Restart development server
2. Clear browser cache
3. Check browser console for errors

**Problem:** Changes not reflecting

**Solution:**
```bash
# Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
# Or restart dev server
npm run dev
```

---

## Environment-Specific Configuration

### Development

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### Staging

```env
VITE_API_BASE_URL=https://staging-api.praximoron.ukf.sk/api
```

### Production

```env
VITE_API_BASE_URL=https://api.praximoron.ukf.sk/api
```

---

## Available Scripts

```bash
# Install dependencies
npm install

# Start development server (default port 5173)
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint

# Format code with Prettier
npm run format
```

---

## Browser Compatibility

The application is compatible with:

- **Chrome:** >= 90
- **Firefox:** >= 88
- **Safari:** >= 14
- **Edge:** >= 90

**Note:** Internet Explorer is not supported.

---

## Performance Optimization

### Lazy Loading Routes

Routes are lazy-loaded for optimal performance:

```javascript
const StudentDashboard = () => import('@/views/StudentViews/StudentDashboard.vue')
```

### Code Splitting

Vite automatically splits code into chunks for faster initial load.

### Asset Optimization

- Images are automatically optimized during build
- CSS is minified and extracted
- JavaScript is minified with tree-shaking

---

## Security Considerations

- **Token Storage:** Tokens are stored in localStorage (consider more secure options for production)
- **XSS Protection:** Vue escapes all rendered content by default
- **CSRF Protection:** Backend uses Laravel Sanctum CSRF protection
- **Secure Communication:** Always use HTTPS in production

---

## Contributing

### Code Style

- Follow Vue 3 Composition API patterns
- Use ESLint and Prettier configurations
- Write self-documenting code with clear variable names
- Add comments for complex logic

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/your-feature-name

# Make changes and commit
git add .
git commit -m "feat: add your feature"

# Push to remote
git push origin feature/your-feature-name

# Create pull request
```

---

## Support & Documentation

- **Vue 3:** https://vuejs.org/
- **Vite:** https://vite.dev/
- **Vue Router:** https://router.vuejs.org/
- **Pinia:** https://pinia.vuejs.org/
- **Axios:** https://axios-http.com/
- **Chart.js:** https://www.chartjs.org/

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
- Complete role-based interface
- Student, Company, and Guarantor dashboards
- Internship management
- Document upload and approval
- Statistics and reporting
- Responsive design
