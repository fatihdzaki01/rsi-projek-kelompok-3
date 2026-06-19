import { createRouter, createWebHistory } from 'vue-router'
import RegisterUser from '@/views/auth/RegisterUser.vue'
import RegisterKomunitas from '@/views/auth/RegisterKomunitas.vue'
import LoginPage from '@/views/auth/LoginPage.vue'
import LoginResult from '@/views/auth/LoginResult.vue'
import ForgotPassword from '@/views/auth/ForgotPassword.vue'
import ResetPassword from '@/views/auth/ResetPassword.vue'
import PasswordResult from '@/views/auth/PasswordResult.vue'
import EmailVerification from '@/views/auth/EmailVerification.vue'
import CampaignDetailPage from '@/views/campaigns/CampaignDetailPage.vue'
import CampaignListPage from '@/views/campaigns/CampaignListPage.vue'
import HomePage from '@/views/HomePage.vue'
import DonationSuccessPage from '@/views/donations/DonationSuccessPage.vue'
import DonationFailedPage from '@/views/donations/DonationFailedPage.vue'
import CommunityListPage from '@/views/community/CommunityListPage.vue'
import PublicCommunityProfilePage from '@/views/community/PublicCommunityProfilePage.vue'

const routes = [
  { path: '/', name: 'Beranda', component: HomePage },
  { path: '/register', name: 'RegisterUser', component: RegisterUser },
  { path: '/register/komunitas', name: 'RegisterKomunitas', component: RegisterKomunitas, meta: { requiresAuth: true, role: 'DONATUR' } },
  { path: '/email-verification', name: 'EmailVerification', component: EmailVerification },
  { path: '/login', name: 'Login', component: LoginPage },
  { path: '/login-result', name: 'LoginResult', component: LoginResult },
  { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
  { path: '/password-result', name: 'PasswordResult', component: PasswordResult },
  { path: '/campaigns', name: 'CampaignList', component: CampaignListPage },
  { path: '/campaigns/:id', component: CampaignDetailPage },
  { path: '/donations/success/:id', component: DonationSuccessPage },
  { path: '/donations/failed/:id', component: DonationFailedPage },
  { path: '/communities', name: 'CommunityList', component: CommunityListPage },
  // Community static routes harus sebelum :id agar tidak konflik
  { path: '/communities/dashboard', component: () => import('@/views/community/CommunityDashboardPage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/campaigns/updates/create', component: () => import('@/views/community/CampaignUpdatePage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/profile/edit', component: () => import('@/views/community/CommunityProfileEditPage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/campaigns/create', component: () => import('@/views/community/CampaignCreatePage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/withdrawals', component: () => import('@/views/campaigns/PencairanCampaignList.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/campaigns/:id/withdrawals', component: () => import('@/views/community/WithdrawalRequestPage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/bank-account', component: () => import('@/views/community/CommunityBankSettingsPage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/campaigns/history', component: () => import('@/views/community/CommunityHistoryPage.vue'), meta: { requiresAuth: true, role: 'KOMUNITAS' } },
  { path: '/communities/:id', name: 'CommunityProfile', component: PublicCommunityProfilePage },
  { path: '/search', component: () => import('@/views/search/SearchResultsPage.vue') },

  { path: '/profile', component: () => import('@/views/user/UserProfilePage.vue'), meta: { requiresAuth: true, role: 'DONATUR' } },
  { path: '/profile/edit', component: () => import('@/views/profile/EditProfilePage.vue'), meta: { requiresAuth: true } },
  { path: '/profile/change-password', component: () => import('@/views/profile/ChangePasswordPage.vue'), meta: { requiresAuth: true } },
  { path: '/donations/history', component: () => import('@/views/donations/DonationHistory.vue'), meta: { requiresAuth: true } },
  { path: '/donations/:id', component: () => import('@/views/donations/DonationDetailPage.vue'), meta: { requiresAuth: true } },
  { path: '/notifications', name: 'Notifications', component: () => import('@/views/notifications/NotificationPage.vue'), meta: { requiresAuth: true } },
  { path: '/campaigns/:id/monitoring', name: 'CampaignMonitoring', component: () => import('@/views/dashboard/MonitoringInternalView.vue') },
  { path: '/payments/checkout/:id', component: () => import('@/views/payments/PaymentCheckoutPage.vue'), meta: { requiresAuth: true } },
  { path: '/payments/va/:id', component: () => import('@/views/payments/PaymentVAPage.vue'), meta: { requiresAuth: true } },

  // Superadmin routes
  { path: '/dashboard', name: 'Dashboard', component: () => import('@/views/dashboard/DashboardView.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/profile', component: () => import('@/views/admin/AdminProfilePage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },

  // Donatur dashboard
  { path: '/dashboard/donors', component: () => import('@/views/admin/DonorListPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/donors/:id', component: () => import('@/views/admin/DonorDetailPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/communities', component: () => import('@/views/admin/CommunityManagePage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/communities/:id', component: () => import('@/views/admin/CommunityDetailPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/community-registrations', component: () => import('@/views/admin/CommunityRegistrationPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/campaign-categories', component: () => import('@/views/admin/CampaignCategoriesPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/bank-account-changes', component: () => import('@/views/admin/BankAccountChangesPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/campaign-reports', component: () => import('@/views/admin/CampaignReportsPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/document-templates', component: () => import('@/views/admin/DocumentTemplatesPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/analytics', component: () => import('@/views/admin/PlatformAnalyticsPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/audit-logs', component: () => import('@/views/admin/AuditLogsPage.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },

  // Campaign approval (SUPERADMIN)
  { path: '/campaigns/approval', name: 'CampaignApproval', component: () => import('@/views/campaigns/CampaignApprovalView.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/campaign-approvals', redirect: '/campaigns/approval' },
  { path: '/campaigns/:id/review', name: 'CampaignReview', component: () => import('@/views/campaigns/CampaignDetailView.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/campaigns/:id/internal', name: 'CampaignMonitoringInternal', component: () => import('@/views/dashboard/CampaignMonitoringInternalView.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/disbursements', name: 'Disbursements', component: () => import('@/views/dashboard/DisbursementView.vue'), meta: { requiresAuth: true, role: 'SUPERADMIN' } },
  { path: '/dashboard/disbursements', redirect: '/disbursements' },

  // Error pages
  { path: '/forbidden', name: 'Forbidden', component: () => import('@/views/errors/ForbiddenPage.vue') },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: () => import('@/views/errors/NotFoundPage.vue') },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0, behavior: 'smooth' }
  },
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('token')
    if (!token) {
      return next('/login')
    }

    if (to.meta.role) {
      try {
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        if (user.role !== to.meta.role) {
          return next('/forbidden')
        }
      } catch {
        return next('/login')
      }
    }
  }

  next()
})

export default router
