# Roadmap

Implementation follows [ADRs](./adr/README.md). Security, tests, and CI are continuous from Phase 1 — not deferred to the end.

## Phase 0 — Specification (complete when ADRs merged)

- [x] ADR-001 Bidding
- [x] ADR-002 Anti-sniping
- [x] ADR-003 Realtime
- [x] ADR-004 Auth
- [x] Docker Compose specification
- [ ] OpenAPI / shared types (optional, alongside Phase 1 API work)

## Phase 1 — Foundation

**Goal:** Runnable stack, auth, roles, CI baseline.

- Project setup (Laravel 11, React + Vite, monorepo layout)
- Docker environment per [infrastructure.md](./infrastructure.md)
- **CI:** GitHub Actions — `composer test`, `npm run build`, lint (PHP Pint, ESLint optional)
- Authentication (Sanctum SPA per ADR-004)
- User model + `user` / `admin` roles + policies skeleton
- **Security:** CSRF, rate-limited login, `.env.example` only in repo
- **Tests:** Auth feature tests (login, CSRF, 403 admin routes)
- Health endpoints: `GET /health`, `GET /ready`

## Phase 2 — Auctions (HTTP)

**Goal:** Create and browse auctions without realtime.

- Auction CRUD (UUID public ids)
- Categories
- Image uploads (S3-compatible or local volume per environment; validated MIME/size)
- Auction list/detail API + React pages (REST only)
- **Tests:** Auction CRUD, authorization, policy matrix
- **Security:** IDOR checks on all auction routes; upload path not web-served

## Phase 3 — Bidding + Realtime

**Goal:** Correct bids first on HTTP, then WebSocket fan-out.

### 3a — HTTP bidding (before WebSockets)

- `BidPlacementService` per ADR-001
- Anti-sniping per ADR-002
- Idempotency + pessimistic lock
- **Tests:** concurrent bids, idempotent replay, anti-sniping edge cases (required gate)

### 3b — Realtime

- Laravel Reverb + Redis broadcaster per ADR-003
- Echo client on auction detail page
- Reconnect + revision merge
- **Tests:** broadcast after commit; channel authorization

### 3c — Notifications

- Database notifications + queue (mail optional)
- `private-user.{uuid}` channel
- No synchronous mail on bid path

## Phase 4 — Admin

- Admin dashboard (role-gated)
- Moderation (force-close auction, suspend user)
- Analytics (basic counts; avoid heavy OLTP queries)
- Audit-friendly actions (log moderation events)

## Phase 5 — Production hardening

- Production Compose / deployment topology
- Observability (structured logs, metrics hooks)
- Rate limits on bid endpoint (per user + per auction)
- Dependency audit in CI (`composer audit`, `npm audit`)
- Backup / DR documentation
- Load smoke tests on bid path
- CSP and security header tightening

## Dependency graph

```
Phase 0 (ADRs)
    → Phase 1 (Docker, Auth, CI)
        → Phase 2 (Auctions HTTP)
            → Phase 3a (Bidding HTTP + tests)  ← gate
                → Phase 3b (Reverb)
                → Phase 3c (Notifications)
                    → Phase 4 (Admin)
                        → Phase 5 (Hardening)
```

## Parallel work (safe after Phase 1)

| Track A | Track B | Requires |
|---------|---------|----------|
| Backend auction API | Frontend layout + API client | ADR-004, API v1 prefix |
| Categories backend | Category UI filters | Auction list API stable |
| CI workflows | README runbook | Repo structure |

**Not parallel:** bidding service vs anti-sniping (same transaction); WebSocket payloads vs bid commit semantics.
