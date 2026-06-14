import { createRouter, createWebHistory } from 'vue-router'
import DonationHistory from '@/views/donations/DonationHistory.vue'
import CampaignDetailPage from '@/views/campaigns/CampaignDetailPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/donations/history', component: DonationHistory },
    { path: '/campaigns/:id', component: CampaignDetailPage },
    { path: '/', redirect: '/donations/history' },
  ],
})

export default router
