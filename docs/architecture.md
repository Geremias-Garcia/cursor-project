# Architecture

High-level stack overview. **Authoritative design decisions** live in [ADRs](./adr/README.md) and [infrastructure.md](./infrastructure.md).

## Backend

- Laravel 11
- REST API **`/api/v1`**
- Service / Action layer (thin controllers)
- Form Requests, API Resources, Policies
- Repository pattern only when justified (see [ai-instructions.md](./ai-instructions.md))
- PostgreSQL + Redis
- Laravel Sanctum (SPA mode — [ADR-004](./adr/004-auth.md))
- Laravel Reverb ([ADR-003](./adr/003-realtime.md))

### Critical paths

| Concern | Document |
|---------|----------|
| Bid placement | [ADR-001](./adr/001-bidding.md) |
| Anti-sniping | [ADR-002](./adr/002-anti-sniping.md) |
| Realtime events | [ADR-003](./adr/003-realtime.md) |
| Auth / roles | [ADR-004](./adr/004-auth.md) |

## Frontend

- React + Vite + TailwindCSS
- Layering: `api/`, `features/`, `realtime/`, `types/`
- React Query (or equivalent) for server state
- Laravel Echo + `pusher-js` for Reverb
- HTTP authoritative for writes; WebSocket for fan-out ([ADR-003](./adr/003-realtime.md))

## Infrastructure

- Docker Compose — [infrastructure.md](./infrastructure.md)
- Nginx, PostgreSQL, Redis, Reverb, queue workers

## Realtime

- WebSockets via Laravel Reverb
- Redis broadcast driver for horizontal scale
- Private channels only

## Queues

- Redis-backed queues for notifications and media
- Bid HTTP path remains synchronous

## Authentication

- Sanctum SPA cookies + CSRF ([ADR-004](./adr/004-auth.md))

## API contracts

- REST and event payloads: [api-contracts.md](./api-contracts.md)

## Implementation order

See [roadmap.md](./roadmap.md).
