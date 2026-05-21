# Technical Decisions

## Stack choices

### Why Laravel?

Chosen for rapid backend development, first-class queues/broadcasting, and Sanctum/Reverb in the same ecosystem.

### Why PostgreSQL?

Transactional reliability, row-level locking (`FOR UPDATE`), and strong concurrency semantics for bidding (ADR-001).

### Why Redis?

Session/cache, queue backend, and **broadcast driver** so multiple app/Reverb nodes share events (ADR-003).

### Why WebSockets (Reverb)?

Realtime bidding requires low-latency fan-out; HTTP polling alone misses the &lt;1s NFR for observers.

### Why Docker?

Reproducible dev/prod parity. Service layout: [infrastructure.md](./infrastructure.md).

## Accepted ADRs

| Topic | Record |
|-------|--------|
| Bidding | [adr/001-bidding.md](./adr/001-bidding.md) |
| Anti-sniping | [adr/002-anti-sniping.md](./adr/002-anti-sniping.md) |
| Realtime | [adr/003-realtime.md](./adr/003-realtime.md) |
| Auth | [adr/004-auth.md](./adr/004-auth.md) |

## Deferred decisions

- Mobile / third-party token API (separate from Sanctum SPA)
- S3 provider choice (AWS vs MinIO) — required before Phase 2 image uploads in production
- Prometheus vs hosted APM — Phase 5
