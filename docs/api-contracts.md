# API Contracts

Summary of v1 REST and WebSocket payloads. Full rules in ADRs.

## REST prefix

All JSON API routes: `/api/v1`

## Auth

See [ADR-004](./adr/004-auth.md) for login/register and CSRF flow.

## Auctions (Phase 2)

Standard CRUD resources with UUID in URLs. Responses wrap in `{ "data": ... }`.

## Bids

See [ADR-001](./adr/001-bidding.md) for `POST /api/v1/auctions/{auction_uuid}/bids` request, success, and error codes.

## WebSocket events

See [ADR-003](./adr/003-realtime.md) for channel names and `BidPlaced`, `AuctionExtended`, `AuctionEnded`, `NotificationCreated` payloads.

## TypeScript types (implementation)

Mirror API Resources and event payloads in `frontend/src/types/`. Prefer generating from OpenAPI once Phase 2 endpoints stabilize.

### Shared fields

| Field | Type | Notes |
|-------|------|-------|
| `revision` | `number` | Monotonic per auction; merge WS + HTTP by max |
| Public ids | `string` (UUID) | Never expose internal integer PKs in API |

## Versioning

- Breaking changes require `/api/v2` and deprecation notice in README.
- Event payload changes increment documented `revision` semantics only for auction state — add new event types rather than breaking existing ones when possible.
