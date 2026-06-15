<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const showAdminMenu = ref(false)
const showLaporanMenu = ref(false)
const showKomunitasSubmenu = ref(false)
const showRekeningSubmenu = ref(false)

const toggleAdminMenu = () => {
  showAdminMenu.value = !showAdminMenu.value
  if (showAdminMenu.value) showLaporanMenu.value = false
  if (!showAdminMenu.value) {
    showKomunitasSubmenu.value = false
    showRekeningSubmenu.value = false
  }
}

const toggleLaporanMenu = () => {
  showLaporanMenu.value = !showLaporanMenu.value
  if (showLaporanMenu.value) showAdminMenu.value = false
}

const closeLaporanMenu = () => {
  showLaporanMenu.value = false
}

const toggleKomunitasSubmenu = () => {
  showKomunitasSubmenu.value = !showKomunitasSubmenu.value
  showRekeningSubmenu.value = false
}

const toggleRekeningSubmenu = () => {
  showRekeningSubmenu.value = !showRekeningSubmenu.value
  showKomunitasSubmenu.value = false
}

const closeAdminMenu = () => {
  showAdminMenu.value = false
  showLaporanMenu.value = false
  showKomunitasSubmenu.value = false
  showRekeningSubmenu.value = false
}

const handleClickOutside = (e) => {
  if (!e.target.closest('.nav-dropdown')) {
    showAdminMenu.value = false
    showLaporanMenu.value = false
    showKomunitasSubmenu.value = false
    showRekeningSubmenu.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="navbar">
    <div class="brand">BERBAGIVE</div>

    <nav>
      <RouterLink to="/dashboard">Dashboard</RouterLink>

      <div class="nav-dropdown">
        <button
          class="nav-dropdown-toggle"
          @click.stop="toggleLaporanMenu"
        >
          Laporan <span class="arrow" :class="{ open: showLaporanMenu }">▼</span>
        </button>

        <div v-if="showLaporanMenu" class="nav-dropdown-menu" @click.stop>
          <RouterLink
            to="/dashboard/campaign-reports"
            class="dropdown-item"
            @click="closeLaporanMenu"
          >
            Laporan Campaign
          </RouterLink>

          <RouterLink
            to="/dashboard/campaign-clarifications"
            class="dropdown-item"
            @click="closeLaporanMenu"
          >
            Klarifikasi Campaign
          </RouterLink>
        </div>
      </div>

      <div class="nav-dropdown">
        <button
          class="nav-dropdown-toggle"
          @click.stop="toggleAdminMenu"
        >
          Admin Panel <span class="arrow" :class="{ open: showAdminMenu }">▼</span>
        </button>

        <div v-if="showAdminMenu" class="nav-dropdown-menu" @click.stop>
          <RouterLink
            to="/dashboard/campaign-approvals"
            class="dropdown-item"
            @click="closeAdminMenu"
          >
            Approval
          </RouterLink>

          <RouterLink
            to="/dashboard/disbursements"
            class="dropdown-item"
            @click="closeAdminMenu"
          >
            Pencairan
          </RouterLink>

          <RouterLink
            to="/dashboard/donors"
            class="dropdown-item"
            @click="closeAdminMenu"
          >
            Donatur
          </RouterLink>

          <div class="submenu-wrapper">
            <button
              class="dropdown-item submenu-toggle"
              @click.stop="toggleKomunitasSubmenu"
            >
              Komunitas <span class="arrow-right">▶</span>
            </button>

            <div v-if="showKomunitasSubmenu" class="submenu">
              <RouterLink
                to="/dashboard/communities"
                class="dropdown-item"
                @click="closeAdminMenu"
              >
                Kelola Komunitas
              </RouterLink>

              <RouterLink
                to="/dashboard/community-registrations"
                class="dropdown-item"
                @click="closeAdminMenu"
              >
                Review Pendaftaran
              </RouterLink>

              <RouterLink
                to="/dashboard/community-registrations/history"
                class="dropdown-item"
                @click="closeAdminMenu"
              >
                Riwayat Review
              </RouterLink>
            </div>
          </div>

          <div class="submenu-wrapper">
            <button
              class="dropdown-item submenu-toggle"
              @click.stop="toggleRekeningSubmenu"
            >
              Rekening <span class="arrow-right">▶</span>
            </button>

            <div v-if="showRekeningSubmenu" class="submenu">
              <RouterLink
                to="/dashboard/bank-account-changes"
                class="dropdown-item"
                @click="closeAdminMenu"
              >
                Review Rekening
              </RouterLink>

              <RouterLink
                to="/dashboard/bank-account-changes/history"
                class="dropdown-item"
                @click="closeAdminMenu"
              >
                Riwayat Rekening
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <button class="logout-btn" @click="handleLogout">Logout</button>
  </header>
</template>

<style scoped>
.submenu-wrapper {
  position: relative;
}

.submenu {
  position: absolute;
  left: 100%;
  top: -4px;
  min-width: 190px;
  padding: 4px 0;
  margin-left: 6px;
  background: #fffaf2;
  border-radius: 12px;
  box-shadow: 0 12px 28px rgba(80, 49, 28, 0.14);
  z-index: 60;
}

.submenu-toggle {
  position: relative;
  padding-right: 28px;
}

.submenu-toggle .arrow-right {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 10px;
  color: #a85f20;
}
</style>
