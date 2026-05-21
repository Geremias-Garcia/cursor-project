# ADR-004: Authentication (Laravel Sanctum)

**Status:** Accepted  
**Date:** 2026-05-21  
**Deciders:** Architecture plan (pre-implementation)

## Context

The SPA (React + Vite) and WebSocket client must authenticate consistently. Cookie-based SPA mode vs token API affects CSRF, XSS, and Echo authorization. Admin roles are required from Phase 1 per architecture review.

## Decision

### Mode: Sanctum SPA (cookie-based)

- **Primary client:** Same-site SPA served via Nginx (dev: Vite proxy to API; prod: static assets + API same registrable domain or parent domain).
- **Session driver:** Redis (or database) in production; file acceptable for local dev only.
- **Guard:** `web` session for SPA; API routes use `auth:sanctum` with stateful SPA middleware.

Token-only API (mobile/third-party) is **deferred**; when added, use separate `POST /api/v1/tokens` with abilities — do not mix with SPA cookie flow on same routes without explicit design.

### CSRF

- Enable Sanctum SPA middleware on API routes used by frontend.
- Frontend calls `GET /sanctum/csrf-cookie` before login and before first mutating request per session.
- Axios/fetch client sends `X-XSRF-TOKEN` from cookie and `credentials: 'include'`.
- React app never stores session token in `localStorage`.

### CORS (development)

- Vite dev server origin whitelisted in `config/cors.php` and `SANCTUM_STATEFUL_DOMAINS`.
- Production: same-site or explicit subdomain list; no `*` with credentials.

### Session policy

| Setting | Value |
|---------|-------|
| Session lifetime | `120` minutes idle |
| Remember me | Optional; `400` minute cookie if enabled |
| Regenerate session | On login |
| Invalidate other sessions | Deferred (Fortify/UI optional later) |

### Roles

| Role | Slug | Capabilities |
|------|------|--------------|
| User | `user` | Default; create auctions, bid, watchlist |
| Admin | `admin` | Moderation, user suspend, auction force-close |

Stored on `users.role` enum or `users.is_admin` boolean. Policies check `User::isAdmin()` for admin routes.

### Authorization

- Laravel Policies on `Auction`, `Bid`, `User` models.
- WebSocket channels use same policies (ADR-003).
- Never rely on frontend route guards alone; always enforce server-side.

### Auth API (v1)

| Method | Path | Purpose |
|--------|------|---------|
| `POST` | `/api/v1/register` | Create user |
| `POST` | `/api/v1/login` | Session login |
| `POST` | `/api/v1/logout` | Session logout |
| `GET` | `/api/v1/user` | Current user |

Password hashing: bcrypt (Laravel default). Rate limit login: `5/minute` per IP + email.

### WebSocket auth

- Echo uses Sanctum session cookie on `/broadcasting/auth`.
- Reverb configured with same app key/secret; auth endpoint on Laravel app.
- Unauthenticated socket cannot subscribe to private channels.

### Security headers (Nginx)

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- CSP baseline in production hardening phase; dev may relax for Vite HMR.

## Consequences

### Positive

- Simpler XSS surface than localStorage tokens.
- Echo and API share session.

### Negative

- Requires careful CORS/stateful domain config in dev.
- Mobile native clients need future ADR for token mode.

## Testing requirements

- Feature test: login sets session; `GET /api/v1/user` returns user.
- Feature test: CSRF required on POST without token fails `419`.
- Feature test: admin-only route returns `403` for user role.

## References

- [ADR-003 Realtime](./003-realtime.md)
- [.cursor/rules/security.mdc](../../.cursor/rules/security.mdc)
