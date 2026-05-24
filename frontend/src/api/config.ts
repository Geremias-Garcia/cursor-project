/**
 * API base URL for browser fetch calls.
 *
 * - Empty (default): relative paths (`/api/...`) — same origin as the page.
 *   - Vite dev (localhost:5173): use with Vite proxy → backend.
 *   - Nginx (localhost:8080): routes `/api` and `/sanctum` to Laravel directly.
 *
 * - Set `VITE_API_BASE_URL` only when the API is on another origin and CORS
 *   is configured (Sanctum CSRF cookies will not be readable from JS on another port).
 */
export function apiBaseUrl(): string {
  const base = import.meta.env.VITE_API_BASE_URL ?? "";

  return base.replace(/\/$/, "");
}

export function apiUrl(path: string): string {
  const normalized = path.startsWith("/") ? path : `/${path}`;
  const base = apiBaseUrl();

  return base ? `${base}${normalized}` : normalized;
}

export function usesViteProxy(): boolean {
  return apiBaseUrl() === "" && import.meta.env.DEV;
}
