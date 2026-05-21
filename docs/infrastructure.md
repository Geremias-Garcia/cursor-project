# Infrastructure

Docker Compose layout for development and production. Canonical for DevOps implementation.

## Development (`docker-compose.yml`)

### Services

| Service | Image / build | Purpose | Ports (host) |
|---------|---------------|---------|--------------|
| `nginx` | nginx:alpine | Reverse proxy, static frontend (prod build) or proxy to Vite | `8080:80` |
| `app` | PHP 8.3-FPM (Dockerfile) | Laravel application | internal |
| `queue` | same as `app` | `php artisan queue:work` | — |
| `scheduler` | same as `app` | `php artisan schedule:work` (optional) | — |
| `reverb` | same as `app` | `php artisan reverb:start` | `8081:8080` |
| `postgres` | postgres:16-alpine | Primary database | `5432:5432` |
| `redis` | redis:7-alpine | Cache, sessions, queues, broadcast | `6379:6379` |
| `frontend` | node:20-alpine (dev only) | Vite dev server with HMR | `5173:5173` |

### Networking

- Single bridge network: `auction-net`
- `nginx` → `app:9000` (FastCGI), `nginx` → `frontend:5173` (dev), `nginx` → static (prod)
- `app`, `queue`, `reverb`, `scheduler` → `postgres`, `redis`

### Volumes

| Volume | Mounted by | Purpose |
|--------|------------|---------|
| `postgres_data` | `postgres` | Persistent DB |
| `redis_data` | `redis` | Optional persistence for dev |
| `./backend` | `app`, `queue`, `reverb` | Bind mount code (dev only) |
| `./frontend` | `frontend` | Bind mount (dev only) |

Do not bind-mount application code in production images.

### Environment (dev)

- `APP_ENV=local`, `APP_DEBUG=true`
- `DB_*` pointing to `postgres` service
- `REDIS_HOST=redis`
- `BROADCAST_CONNECTION=reverb`, `REVERB_*` shared with Echo client
- `SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8080`
- `SESSION_DRIVER=redis`

## Production (`docker-compose.prod.yml`)

### Differences from dev

| Concern | Production approach |
|---------|---------------------|
| Images | Multi-stage build; no bind mounts |
| `frontend` service | Removed; `npm run build` baked into `nginx` image or artifact |
| `APP_DEBUG` | `false` |
| Database | Compose `postgres` for small deploys; managed RDS/Cloud SQL recommended |
| Object storage | S3-compatible for auction images (not local disk) |
| Secrets | Orchestrator / host env injection — never commit `.env` |
| Replicas | `app` and `reverb` scalable behind load balancer; **Redis required** for broadcast |
| TLS | Terminated at load balancer or `nginx` with certificates |

### Production services (minimum)

`nginx`, `app` (N replicas), `queue` (N workers), `reverb` (N), `redis`, `postgres` (or external).

### Health checks

| Endpoint | Service | Pass criteria |
|----------|---------|---------------|
| `GET /health` | `app` | `200`, `{ "status": "ok" }` |
| `GET /ready` | `app` | `200` only if DB + Redis reachable |

Configure Docker `healthcheck` on `app` using `curl -f http://127.0.0.1/health`.

## Queue topology

| Queue name | Workers | Jobs |
|------------|---------|------|
| `default` | 1+ | Notifications, general |
| `media` | 1 | Image processing, virus scan (future) |

Bid placement stays **synchronous** in HTTP request (ADR-001). Only post-commit broadcast and notifications use queues/events.

## Observability

### Logging

- Structured JSON logs in production (`LOG_CHANNEL=stack` → stderr for container collectors)
- Never log passwords, tokens, or full CSRF secrets
- Log security events: failed login bursts, bid rejected (rate), admin moderation

### Metrics (Phase 5+)

Hook points (Prometheus-compatible or APM):

- `bid_placement_duration_ms` histogram
- `bid_rejected_total` counter by reason
- `websocket_connections` gauge (Reverb)
- `queue_depth` per queue

### Tracing

Deferred; use request ID middleware (`X-Request-Id`) from Phase 1 for log correlation.

## Nginx routing (sketch)

```
/           → frontend (static or Vite proxy)
/api        → app (PHP-FPM)
/broadcasting/auth → app
/sanctum/csrf-cookie → app
```

WebSocket upgrade for Reverb on dedicated path or subdomain per Reverb docs (e.g. `ws://localhost:8081` in dev).

## Disaster recovery (documentation)

- Postgres: daily logical backup; PITR for production
- Redis: treat as ephemeral except queues in flight; jobs should be retryable
- Reverb outage: clients fall back to polling per ADR-003

## References

- [ADR-003 Realtime](./adr/003-realtime.md)
- [ADR-004 Auth](./adr/004-auth.md)
- [.cursor/rules/devops.mdc](../.cursor/rules/devops.mdc)
