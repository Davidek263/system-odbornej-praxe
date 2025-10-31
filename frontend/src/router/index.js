import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import FirmaView from '../views/FirmaView.vue'
import SetPasswordView from '../views/SetPassword.vue' 

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/forgot-password', name: 'forgot', component: ForgotPasswordView },
  { path: '/firmaview', name: 'firmaview', component: FirmaView},
  { path: '/set-password', name: 'setpassword', component: SetPasswordView }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
