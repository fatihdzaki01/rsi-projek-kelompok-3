import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/LoginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import CampaignDetailView from '@/views/CampaignDetailView.vue'
import MonitoringInternalView from '@/views/MonitoringInternalView.vue'
import CampaignApprovalView from '@/views/CampaignApprovalView.vue'
import DisbursementView from '@/views/DisbursementView.vue'
import ProfileView from '@/views/ProfileView.vue'
import DonorView from '@/views/DonorView.vue'
import CommunityManagementView from '@/views/CommunityManagementView.vue'
import RegistrationReviewView from '@/views/RegistrationReviewView.vue'
import RegistrationHistoryView from '@/views/RegistrationHistoryView.vue'
import BankAccountReviewView from '@/views/BankAccountReviewView.vue'
import BankAccountHistoryView from '@/views/BankAccountHistoryView.vue'
import CampaignReportView from '@/views/CampaignReportView.vue'
import CampaignClarificationView from '@/views/CampaignClarificationView.vue'
import DocumentTemplateView from '@/views/DocumentTemplateView.vue'
import FinancialExportView from '@/views/FinancialExportView.vue'
import NotificationView from '@/views/NotificationView.vue'
import PlatformStatsView from '@/views/PlatformStatsView.vue'
import PaymentVerificationView from '@/views/PaymentVerificationView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
    },
    {
      path: '/dashboard/campaigns/:id',
      name: 'campaign-detail',
      component: CampaignDetailView,
    },
    {
      path: '/dashboard/campaigns/:id/internal',
      name: 'campaign-internal',
      component: MonitoringInternalView,
    },
    {
      path: '/dashboard/campaign-approvals',
      name: 'campaign-approvals',
      component: CampaignApprovalView,
    },
    {
      path: '/dashboard/disbursements',
      name: 'disbursements',
      component: DisbursementView,
    },
    {
      path: '/dashboard/profile',
      name: 'profile',
      component: ProfileView,
    },
    {
      path: '/dashboard/donors',
      name: 'donors',
      component: DonorView,
    },
    {
      path: '/dashboard/communities',
      name: 'communities',
      component: CommunityManagementView,
    },
    {
      path: '/dashboard/community-registrations',
      name: 'community-registrations',
      component: RegistrationReviewView,
    },
    {
      path: '/dashboard/community-registrations/history',
      name: 'community-registrations-history',
      component: RegistrationHistoryView,
    },
    {
      path: '/dashboard/bank-account-changes',
      name: 'bank-account-changes',
      component: BankAccountReviewView,
    },
    {
      path: '/dashboard/bank-account-changes/history',
      name: 'bank-account-changes-history',
      component: BankAccountHistoryView,
    },
    {
      path: '/dashboard/campaign-reports',
      name: 'campaign-reports',
      component: CampaignReportView,
    },
    {
      path: '/dashboard/campaign-clarifications',
      name: 'campaign-clarifications',
      component: CampaignClarificationView,
    },
    {
      path: '/dashboard/document-templates',
      name: 'document-templates',
      component: DocumentTemplateView,
    },
    {
      path: '/dashboard/financial-export',
      name: 'financial-export',
      component: FinancialExportView,
    },
    {
      path: '/dashboard/notifications',
      name: 'notifications',
      component: NotificationView,
    },
    {
      path: '/dashboard/payment-verification',
      name: 'payment-verification',
      component: PaymentVerificationView,
    },
    {
      path: '/dashboard/platform-stats',
      name: 'platform-stats',
      component: PlatformStatsView,
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')

  if (to.name !== 'login' && !token) {
    return { name: 'login' }
  }

  if (to.name === 'login' && token) {
    return { name: 'dashboard' }
  }
})

export default router