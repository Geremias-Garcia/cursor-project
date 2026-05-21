# Architecture Decision Records (ADRs)

Accepted decisions for the realtime auction platform. Read these before implementing bidding, auth, or WebSockets.

| ADR | Topic |
|-----|-------|
| [001-bidding.md](./001-bidding.md) | Bid transaction, locking, idempotency, API errors |
| [002-anti-sniping.md](./002-anti-sniping.md) | Extension window, duration, caps, edge cases |
| [003-realtime.md](./003-realtime.md) | Reverb channels, events, reconnect |
| [004-auth.md](./004-auth.md) | Sanctum SPA, CSRF, roles, WS auth |

Implementation order: **004 → 001 → 002 → 003** (auth and data model before broadcast).
