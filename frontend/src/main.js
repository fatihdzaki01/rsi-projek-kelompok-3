import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "./assets/auth-pages.css";

createApp(App).use(router).mount("#app");