import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ForgotPasswordView from '../views/ForgotPasswordView.vue'
import SetPasswordView from '../views/SetPassword.vue' 
import LandingPageView from '../views/LandingView.vue'
import StudentInfoView from '../views/StudentInfoView.vue'
import CompanyInfoView from '../views/CompanyInfoView.vue'
import InternshipInfoView from '../views/InternshipInfoView.vue'
import GuarantorInfoView from '../views/GuarantorInfoView.vue'

const routes = [
  { path: '/', redirect: '/home' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/forgot-password', name: 'forgot', component: ForgotPasswordView },
  { path: '/set-password', name: 'setpassword', component: SetPasswordView },
  { path: '/home', name: 'home', component: LandingPageView },
  { path: '/student-info', name: 'studentinfo', component: StudentInfoView },
  { path: '/company-info', name: 'companyinfo', component: CompanyInfoView },
  { path: '/internship-info', name: 'internshipinfo', component: InternshipInfoView },
  { path: '/guarantor-info', name: 'guarantorinfo', component: GuarantorInfoView },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
