import { createRouter, createWebHistory } from "vue-router";
import RegisterUser from "../views/RegisterUser.vue";
import LoginUser from "../views/LoginUser.vue";
import LoginResult from "../views/LoginResult.vue";
import ForgotPassword from "../views/ForgotPassword.vue";
import ResetPassword from "../views/ResetPassword.vue";
import PasswordResult from "../views/PasswordResult.vue";
import EmailVerification from "../views/EmailVerification.vue";

const routes = [
  {
    path: "/",
    redirect: "/login",
  },
  {
    path: "/register",
    name: "RegisterUser",
    component: RegisterUser,
  },
  {
    path: "/email-verification",
    name: "EmailVerification",
    component: EmailVerification,
  },
  {
    path: "/login",
    name: "LoginUser",
    component: LoginUser,
  },
  {
    path: "/login-result",
    name: "LoginResult",
    component: LoginResult,
  },
  {
    path: "/forgot-password",
    name: "ForgotPassword",
    component: ForgotPassword,
  },
  {
    path: "/reset-password",
    name: "ResetPassword",
    component: ResetPassword,
  },
  {
    path: "/password-result",
    name: "PasswordResult",
    component: PasswordResult,
  },
  {
    path: "/dashboard",
    name: "DashboardUser",
    component: {
      template: "<h1 style='padding: 40px'>Dashboard User</h1>",
    },
  },
  {
    path: "/dashboard-komunitas",
    name: "DashboardKomunitas",
    component: {
      template: "<h1 style='padding: 40px'>Dashboard Komunitas</h1>",
    },
  },
  {
    path: "/dashboard-superadmin",
    name: "DashboardSuperadmin",
    component: {
      template: "<h1 style='padding: 40px'>Dashboard Superadmin</h1>",
    },
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

export default router;
