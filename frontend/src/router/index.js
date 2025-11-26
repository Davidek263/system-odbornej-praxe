import { createRouter, createWebHistory } from 'vue-router'

import LandingPageView from '../views/PublicViews/LandingPage.vue'
import LoginView from '../views/PublicViews/LoginView.vue'
import RegisterView from '../views/PublicViews/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import CompanyDashboard from '../views/CompanyViews/CompanyDashboard.vue'
import StudentDashboard from '../views/StudentViews/StudentDashboard.vue'
import SetPasswordView from '../views/SetPassword.vue' 
import StudentInfoView from '../views/PublicViews/StudentInfo.vue'
import CompanyInfoView from '../views/PublicViews/CompanyInfo.vue'
import InternshipInfoView from '../views/PublicViews/InternshipInfo.vue'
import GuarantorInfoView from '../views/PublicViews/GuarantorInfo.vue'
import CreateInternship from '../views//StudentViews/CreateInternship.vue'
import GuarantorDashboard from '../views/GuarantorViews/GuarantorDashboard.vue'

const routes = [
  { path: '/', redirect: '/home' },
  { path: '/:pathMatch(.*)*', redirect: '/home' },
  { path: '/home', name: 'home', component: LandingPageView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/forgot-password', name: 'forgot', component: ForgotPasswordView },
  { path: '/company-dashboard', name: 'companydashboard', component: CompanyDashboard, meta: { requiresAuth: true , roles: ['company']} },
  { path: '/student-dashboard', name: 'studentdashboard', component: StudentDashboard, meta: { requiresAuth: true , roles: ['student']} },
  { path: '/guarantor-dashboard', name: 'guarantordashboard', component: GuarantorDashboard, meta: { requiresAuth: true , roles: ['guarantor']} },
  { path: '/set-password', name: 'setpassword', component: SetPasswordView },
  { path: '/student-info', name: 'studentinfo', component: StudentInfoView },
  { path: '/internship-info', name: 'internshipinfo', component: InternshipInfoView },
  { path: '/guarantor-info', name: 'guarantorinfo', component: GuarantorInfoView },
  { path: '/company-info', name: 'companyinfo', component: CompanyInfoView },
  { path: '/create-internship', name: 'createinternship', component: CreateInternship, meta: { requiresAuth: true, roles: ['student'] }
},
  //example { path: '/company-info', name: 'companyinfo', component: CompanyInfoView, meta: { requiresAuth: true , roles: ['admin', 'company']} },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // If the user clicked back/forward button, restore their scroll position
    if (savedPosition) {
      return savedPosition
    }
    // Otherwise, scroll to top for all route changes
    return { top: 0, left: 0, behavior: 'instant' }
  }
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
