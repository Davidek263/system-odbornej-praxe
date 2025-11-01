import { createRouter, createWebHistory } from 'vue-router'

import LandingPageView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import SetPasswordView from '../views/SetPassword.vue' 
import StudentInfoView from '../views/StudentInfoView.vue'
import CompanyInfoView from '../views/CompanyInfoView.vue'
import InternshipInfoView from '../views/InternshipInfoView.vue'
import GuarantorInfoView from '../views/GuarantorInfoView.vue'

const routes = [
  { path: '/', redirect: '/home' },
  { path: '/:pathMatch(.*)*', redirect: '/home' },
  { path: '/home', name: 'home', component: LandingPageView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/forgot-password', name: 'forgot', component: ForgotPasswordView },
  { path: '/set-password', name: 'setpassword', component: SetPasswordView },
  { path: '/student-info', name: 'studentinfo', component: StudentInfoView },
  { path: '/internship-info', name: 'internshipinfo', component: InternshipInfoView },
  { path: '/guarantor-info', name: 'guarantorinfo', component: GuarantorInfoView },
  { path: '/company-info', name: 'companyinfo', component: CompanyInfoView },
  //example { path: '/company-info', name: 'companyinfo', component: CompanyInfoView, meta: { requiresAuth: true , roles: ['admin', 'company']} },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  // Redirect logged-in users away from login/register pages
  const guestOnlyRoutes = ['login', 'register']
  if (token && guestOnlyRoutes.includes(to.name)) {
    return next('/home')
  }

  // Require authentication for certain pages
  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  // Check roles
  if (to.meta.roles && (!user.role_name || !to.meta.roles.includes(user.role_name))) {
    return next('/home')
  }

  next()
})



export default router
