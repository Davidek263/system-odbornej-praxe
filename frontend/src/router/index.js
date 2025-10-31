import { createRouter, createWebHistory } from 'vue-router'

// Views
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import SetPasswordView from '../views/SetPassword.vue'
import DashboardView from '../views/DashboardView.vue' // 👈 pridaj túto stránku

const routes = [
  { path: '/', redirect: '/login' },

  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/forgot-password', name: 'forgot', component: ForgotPasswordView },
  { path: '/set-password', name: 'setpassword', component: SetPasswordView },

  // 👇 chránená stránka po prihlásení
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true },
  },

  // fallback pre neexistujúce cesty
  { path: '/:pathMatch(.*)*', redirect: '/login' },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// 🛡️ Middleware na ochranu chránených rout
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Ak stránka vyžaduje login a token chýba → redirect na /login
  if (to.meta.requiresAuth && !token) {
    next('/login')
  }
  // Ak je používateľ už prihlásený a ide na login/register → redirect na dashboard
  else if ((to.name === 'login' || to.name === 'register') && token) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
