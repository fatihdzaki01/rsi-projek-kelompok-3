# AGENTS.md — Berbagive Project Knowledge Base

## Project Overview

**Berbagive** — Sistem Informasi Penyaluran dan Pengelolaan Bantuan Sosial Berbasis Komunitas.
Platform donasi dan penyaluran bantuan sosial berbasis web yang menghubungkan lembaga/komunitas penggalang dana dengan masyarakat donatur.

| Aspek | Detail |
|-------|--------|
| Mata Kuliah | Rekayasa Sistem Informasi — Semester Gasal 2025 |
| Kelas/Kelompok | A / 3 |
| Dosen | Bambang Widoyono |
| Universitas | Universitas Sebelas Maret |

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Frontend | Vue 3.5 + Vite 6 + Tailwind CSS 3 + Pinia + Vue Router 5 |
| Backend | Laravel 12/13 + PHP 8.3 |
| Database | PostgreSQL 16 |
| Auth | Laravel Sanctum (token-based) |
| HTTP Client | Axios |
| PDF | DomPDF |

## Directory Structure

```
berbagive/
├── backend/
│   ├── app/
│   │   ├── Exports/
│   │   ├── Helpers/           # ApiResponse helper
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/       # API Controllers
│   │   │   │   └── DonationController.php
│   │   │   ├── Middleware/    # CheckRole, RoleMiddleware
│   │   │   └── Requests/      # Form Request validators
│   │   ├── Models/            # 16 Eloquent models
│   │   ├── Notifications/
│   │   ├── Providers/
│   │   └── Services/          # 5 Service classes
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php            # MAIN API route definitions
│   │   ├── web.php
│   │   └── console.php
│   └── resources/views/
├── frontend/
│   ├── src/
│   │   ├── api/               # Axios config + interceptors
│   │   ├── components/        # Shared UI components
│   │   │   ├── auth/
│   │   │   ├── campaign/
│   │   │   ├── community/
│   │   │   ├── donation/
│   │   │   ├── shared/        # Navbar, Footer, etc.
│   │   │   └── ui/            # CategoryDropdown, PaginationBar, etc.
│   │   ├── router/            # Vue Router + guards
│   │   ├── stores/            # Pinia stores (auth, campaign, donation, notification)
│   │   └── views/
│   │       ├── admin/         # Superadmin pages
│   │       ├── auth/          # Login, Register, Forgot/Reset Password
│   │       ├── campaigns/     # Campaign list, detail
│   │       ├── community/     # Community profile, dashboard, etc.
│   │       ├── dashboard/     # Superadmin dashboard, monitoring
│   │       ├── donations/     # Donation history, detail
│   │       ├── notifications/
│   │       ├── payments/      # Checkout, VA
│   │       ├── profile/       # User profile edit
│   │       ├── search/
│   │       └── user/          # User profile page
│   └── package.json
└── docs/                      # FSD, TSD documents
```

## Role System (RBAC)

Three roles with constants in `User.php`:

| Role | Constant | Frontend Check | Description |
|------|----------|----------------|-------------|
| DONATUR | `User::ROLE_DONATUR` | `authStore.isDonor` | Regular donor users |
| KOMUNITAS | `User::ROLE_KOMUNITAS` | `authStore.isCommunity` | Community organizations |
| SUPERADMIN | `User::ROLE_SUPERADMIN` | `authStore.isAdmin` | Platform administrators |

**Important:** Role checks in controllers MUST use `User::ROLE_DONATUR` (not string `'user'`). The role string stored in DB is `'DONATUR'`, `'KOMUNITAS'`, `'SUPERADMIN'`.

**Middleware:**
- `auth:sanctum` — any authenticated user
- `role:KOMUNITAS` — only komunitas
- `role:SUPERADMIN` — only superadmin
- Combinations: `['auth:sanctum', 'role:SUPERADMIN']`

## Auth Flow (Frontend)

**Auth Store** (`stores/auth.js`):
- Token + user persisted in `localStorage`
- `login()` → POST `/auth/login` → save token + user
- `logout()` → POST `/auth/logout` → clear localStorage
- `fetchMe()` → fetch profile by role
- Getters: `isLoggedIn`, `userRole`, `isAdmin`, `isCommunity`, `isDonor`

**Login Redirects** (in `LoginPage.vue` / `LoginUser.vue`):
```
SUPERADMIN → /dashboard
KOMUNITAS  → /communities/dashboard
DONATUR    → /campaigns
```

**Route Guards** (`router/index.js`):
- `meta.requiresAuth` → check localStorage token
- `meta.role` → compare with user.role from localStorage
- Mismatch → redirect `/forbidden`

## API Patterns

**Base URL:** `/api/v1/`

**Standard Response Format:**
```json
{
  "status": "success",
  "data": {},
  "message": "Operasi berhasil",
  "errors": null
}
```

**Error Response:**
```json
{
  "status": "error",
  "data": null,
  "message": "...",
  "errors": { "code": "ERR-XXX-XX" }
}
```

**Auth Header:** `Authorization: Bearer <token>`

**HTTP Status Codes:**
200 OK, 201 Created, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict, 422 Validation Error, 429 Rate Limit

## Common Bug Patterns (from experience)

1. **Enum values:** PostgreSQL `payment_status` enum uses `'berhasil'` / `'gagal'` / `'pending'` — NOT `'success'` / `'failed'`
2. **Role check:** Always use `User::ROLE_DONATUR` (not `'user'`)
3. **Tables not migrated:** `laporan_campaign` table must exist before using CampaignReportController
4. **Monitoring endpoints:** `/campaigns/{id}/monitoring` = public (any auth), `/campaigns/{id}/internal` = SUPERADMIN only
5. **Community profile routes:** All `/communities/profile*`, `/communities/campaigns*`, `/communities/bank-account*`, `/communities/dashboard` must use `middleware('role:KOMUNITAS')`

## What Has Been Built (Feature Status)

### ✅ Authentication & Profile
- Register as DONATUR (`POST /auth/register-user`)
- Register as KOMUNITAS (`POST /auth/register-komunitas`)
- Login / Logout / Forgot Password / Reset Password / Resend Verification
- User profile: view, edit, change password
- Community profile: view public, edit own (admin protected), bank account management

### ✅ Donations
- Create donation (`POST /donations`) with `is_anonim`, `nama_tampil`, `pesan`
- Donation detail (`GET /donations/{id}`) — includes extra fields from `donasi` table
- Receipt: `GET /donations/{id}/receipt` + `GET /donations/{id}/receipt-pdf`
- Donation history list with search & filter by status
- **Donatur dashboard removed** — DONATUR redirects to `/campaigns` after login

### ✅ Campaigns
- Campaign listing with search, category filter (pill buttons), pagination
- Campaign detail page with donation sidebar
- Campaign CRUD by community, review by superadmin
- Public monitoring (`GET /campaigns/{id}/monitoring`) — progress, donatur count, recent donors
- Internal monitoring (`GET /campaigns/{id}/internal`) — SUPERADMIN only, financial data

### ✅ Communities
- Public community profile page (`GET /communities/{id}/profile`)
- Follow/unfollow (`POST/DELETE /communities/{id}/follow`)
- Followers list modal (max 25 users)
- Campaign list modals (Aktif & Selesai) — Instagram-style clickable stats

### ✅ Users (DONATUR)
- User profile page with edit, change password
- Donation history + detail page
- **Following list** — compact card with count, click → modal with community list

### 🚧 Still Pending / Known Issues
- "Lihat Bukti" button wiring needs absolute URL handling for storage
- Payment gateway integration is OUT OF SCOPE (mock payment only)
- Some views may reference `v_user_donation_history` which has limited columns

## Key Controllers & Their Responsibilities

| Controller | Responsibility |
|------------|---------------|
| `AuthController` | Register, login, logout, forgot/reset password |
| `UserController` | DONATUR profile: view, edit, change password, following list |
| `DonationController` | Create donation, history, detail, receipt, payment status |
| `KomunitasProfilController` | Community public profile, own profile, edit profile |
| `CommunityFollowController` | Follow, unfollow, followers list |
| `KomunitasCampaignController` | Community campaign CRUD, updates, clarifications |
| `CampaignPublicController` | Public campaign detail, donors, complete |
| `CampaignReportController` | Report a campaign (DONATUR only) |
| `MonitoringController` | Campaign monitoring (public + internal) |
| `SuperadminController` | Dashboard, analytics, donor/community management, reviews |
| `SearchController` | Campaign & community search |
| `DashboardController` | Community dashboard |
| `NotificationController` | User notifications |

## Key Frontend Components

| Component | Location | Purpose |
|-----------|----------|---------|
| `Navbar.vue` | `components/shared/` | Main navigation |
| `DonationSidebar.vue` | `components/donation/` | Donation form with anon toggle, message |
| `ReportCampaignModal.vue` | `components/campaign/` | Report campaign modal |
| `CategoryDropdown.vue` | `components/ui/` | Pill button horizontal category filter |
| `FollowersModal.vue` | `components/community/` | Followers list modal (no dates) |
| `FollowingModal.vue` | `components/community/` | Following list modal |
| `CampaignListModal.vue` | `components/community/` | Generic campaign list modal |
| `UnfollowModal.vue` | `components/community/` | Unfollow confirmation modal |
| `PublicCommunityProfilePage.vue` | `views/community/` | Public community profile with follow/ unfollow + clickable stats |

## Business Rules (from FSD/TSD)

### DONATUR Scope (FSD-2.x):
- Can view/edit own profile, change password
- Can view donation history and detail
- Can follow/unfollow communities
- Can report campaigns
- Can see who they follow (following list)
- **No dashboard** — redirected to `/campaigns` after login

### KOMUNITAS Scope (FSD-2.6, 5.x):
- Can edit profile (except nama_lembaga — must request superadmin)
- Can manage bank account (changes require superadmin verification)
- Can create/manage campaigns
- Can view own dashboard with stats
- Cannot change nama_lembaga independently

### SUPERADMIN Scope (FSD-11.x, 12.x):
- Dashboard with platform statistics
- Manage donors, communities, campaign categories
- Review community registrations, bank account changes, campaigns
- Review campaign reports and clarifications
- Export financial reports
- View audit logs

### Donation Rules (FSD-4.1):
- Min nominal: Rp 5.000
- DONATUR can choose anonim or display name
- DONATUR can include a message/doa
- Campaign must be `aktif` to receive donations
- Only `berhasil` donations count toward campaign progress

### Campaign Rules (FSD-5.x):
- Target dana min: Rp 10.000.000
- Status flow: `menunggu_review` → `aktif` / `ditolak` / `nonaktif` / `ditutup_permanen` → `selesai`
- Only `aktif` campaigns shown to public
- Completed campaigns auto-transition when fully funded or past end date

## Environment

- **DB:** PostgreSQL 14 on localhost:5432, database `berbagive`
- **Backend dev:** `php artisan serve` on port 8000
- **Frontend dev:** `npm run dev` on port 5173
- **Migration:** `php artisan migrate`
- **Build:** `npm run build` (frontend)

## Git Workflow

- Branch: `feat/donation-profilmanagement`
- PR target: `dev`
- Current commit: `145da73`
- Remote: `git@github.com:fatihdzaki01/rsi-projek-kelompok-3.git`
