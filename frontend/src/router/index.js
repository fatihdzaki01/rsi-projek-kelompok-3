import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/LoginView.vue'
import DashboardView from '@/views/DashboardView.vue'
import CampaignDetailView from '@/views/CampaignDetailView.vue'
import MonitoringInternalView from '@/views/MonitoringInternalView.vue'
import CampaignApprovalView from '@/views/CampaignApprovalView.vue'
import DisbursementView from '@/views/DisbursementView.vue'


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