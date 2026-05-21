# ADR-003: Realtime (Laravel Reverb)

**Status:** Accepted  
**Date:** 2026-05-21  
**Deciders:** Architecture plan (pre-implementation)

## Context

Live bidding requires sub-second fan-out of state changes. HTTP responses are authoritative for writes; WebSockets are for read-model updates. Without channel naming, event schemas, ordering, and reconnect rules, clients show stale or duplicate state.

## Decision

### Stack

- **Server:** Laravel Reverb
- **Broadcast driver:** Redis (required for horizontal scale — all app nodes publish to same Redis)
- **Client:** `@laravel/echo` + `pusher-js` protocol compatible with Reverb

### Channel naming

| Channel | Type | Name pattern | Authorization |
|---------|------|--------------|---------------|
| Auction room | Private | `private-auction.{auction_uuid}` | User may `view` auction (public active/ended, or owner) |
| User notifications | Private | `private-user.{user_uuid}` | Authenticated user matches channel user |

No public channels for bid data. Presence channels deferred until viewer counts are required.

Channel authorization uses `routes/channels.php` delegating to `AuctionPolicy::view` and user identity match.

### Event catalog

All events implement `ShouldBroadcastAfterCommit` (or equivalent) so subscribers never see uncommitted bids.

#### `BidPlaced`

- **Channel:** `private-auction.{auction_uuid}`
- **Event name:** `BidPlaced`
- **Payload:**

```json
{
  "bid_id": "uuid",
  "auction_id": "uuid",
  "amount": "150.00",
  "bidder_display": "User***42",
  "placed_at": "2026-05-21T12:00:00Z",
  "current_price": "150.00",
  "winning_bid_id": "uuid",
  "revision": 42
}
```

Do not include bidder email or internal user id in broadcast (privacy).

#### `AuctionExtended`

- **Channel:** `private-auction.{auction_uuid}`
- **Event name:** `AuctionExtended`
- **Payload:**

```json
{
  "auction_id": "uuid",
  "ends_at": "2026-05-21T13:02:00Z",
  "revision": 43,
  "extension_count": 1
}
```

#### `AuctionEnded`

- **Channel:** `private-auction.{auction_uuid}`
- **Event name:** `AuctionEnded`
- **Payload:**

```json
{
  "auction_id": "uuid",
  "final_price": "500.00",
  "winning_bid_id": "uuid",
  "revision": 99,
  "ended_at": "2026-05-21T14:00:00Z"
}
```

Dispatched by scheduler/job when `ends_at` passes — not on every page load.

### Ordering and idempotency (client)

- Every payload includes `revision` (monotonic integer on `auctions`).
- Client hook merges updates: apply only if `revision > localRevision`.
- Deduplicate by `bid_id` for bid list UI.
- **HTTP response from POST bid** is authoritative for the bidder's own action; WS may arrive before or after — reconcile to max `revision`.

### Reconnect protocol

1. On mount / reconnect: `GET /api/v1/auctions/{uuid}` + `GET /api/v1/auctions/{uuid}/bids?limit=50`.
2. Set `localRevision` from auction resource.
3. Subscribe to `private-auction.{uuid}` via Echo.
4. Apply WS events only when `revision` advances.

Fallback: if WebSocket unavailable > 5s, poll auction endpoint every 3s until connected (exponential backoff max 30s).

### Notification events

- **Channel:** `private-user.{user_uuid}`
- **Event name:** `NotificationCreated`
- **Payload:** `{ id, type, title, body, read_at, created_at, data }` (minimal)

Bid events stay on auction channels only.

### Backend module layout

```
app/Events/BidPlaced.php
app/Events/AuctionExtended.php
app/Events/AuctionEnded.php
routes/channels.php
```

### Rate and payload discipline

- Payloads minimal (no full bid history over WS).
- No broadcast of validation errors or failed bids.

## Consequences

### Positive

- Clear contract for frontend `realtime/` layer and TypeScript types.
- Safe scaling path via Redis broadcaster.

### Negative

- Private channel auth must be tested; misconfiguration leaks or blocks subscriptions.
- Reconnect requires REST round-trip — acceptable for correctness.

## Testing requirements

- Channel auth: authorized user subscribed; unauthorized rejected.
- Event dispatched only after DB commit (transaction rollback → no broadcast).
- Feature test: bid triggers `BidPlaced` with expected `revision`.

## References

- [ADR-001 Bidding](./001-bidding.md)
- [ADR-002 Anti-sniping](./002-anti-sniping.md)
- [ADR-004 Auth](./004-auth.md)
- [docs/non-functional-requirements.md](../non-functional-requirements.md)
