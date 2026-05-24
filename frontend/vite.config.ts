import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import type { ProxyOptions } from "vite";

function apiProxy(target: string): ProxyOptions {
  return {
    target,
    // Keep the browser Host/Origin so Sanctum treats requests as stateful SPA traffic.
    changeOrigin: false,
    secure: false,
    cookieDomainRewrite: "",
    cookiePathRewrite: "/",
  };
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "");
  const proxyTarget =
    env.VITE_PROXY_TARGET?.trim() || "http://127.0.0.1:8080";

  return {
    plugins: [react(), tailwindcss()],
    server: {
      host: true,
      port: 5173,
      proxy: {
        "/api": apiProxy(proxyTarget),
        "/sanctum": apiProxy(proxyTarget),
        "/broadcasting": apiProxy(proxyTarget),
      },
    },
  };
});
