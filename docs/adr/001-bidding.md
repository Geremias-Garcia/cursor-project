# ADR-001: Bid Placement

**Status:** Accepted  
**Date:** 2026-05-21  
**Deciders:** Architecture plan (pre-implementation)

## Context

Bid placement is the highest-risk operation in the auction platform. Concurrent bids, network retries, and client tampering must not corrupt auction state or produce duplicate bids. Product rules require transactional bids, immutable bid history, and safe concurrency handling.

## Decision

### API contract

- **Endpoint:** `POST /api/v1/auctions/{auction_uuid}/bids`
- **Auth:** Required (Sanctum). See [ADR-004](./004-auth.md).
- **Idempotency:** Client sends `Idempotency-Key` header (UUID v4, max 128 chars) or body field `client_bid_id` (UUID v4). Server enforces uniqueness per user.

#### Request body

```json
{
  "amount": "150.00",
  "client_bid_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

Either `Idempotency-Key` header **or** `client_bid_id` is required. If both are sent, they must match; otherwise `422`.

#### Success response (`201 Created`)

```json
{
  "data": {
    "id": "bid-uuid",
    "auction_id": "auction-uuid",
    "amount": "150.00",
    "placed_at": "2026-05-21T12:00:00Z",
    "is_winning": true,
    "auction": {
      "current_price": "150.00",
      "revision": 42,
      "ends_at": "2026-05-21T13:00:00Z"
    }
  }
}
```

`revision` is the auction row integer incremented on every successful state change (bid or extension). Clients and WebSocket payloads use it for ordering.

#### Error responses

| HTTP | Code | When |
|------|------|------|
| `401` | — | Unauthenticated |
| `403` | `FORBIDDEN` | Policy denied (e.g. own auction, banned user) |
| `404` | — | Auction not found |
| `409` | `AUCTION_NOT_ACCEPTING_BIDS` | Not started, ended, or cancelled |
| `409` | `BID_TOO_LOW` | Below minimum next bid (include `min_next_bid` in body) |
| `409` | `CONCURRENT_BID_LOST` | Lost race after lock; client should refresh and retry |
| `422` | `VALIDATION_ERROR` | Invalid amount format, missing idempotency key |
| `429` | — | Rate limit exceeded |

Idempotent replay of the same key + user + auction returns `200` with the original bid resource (not a new bid).

### Server-side amount validation

- **Never trust** client `amount` as the sole authority for "minimum met" without re-reading auction state inside the transaction.
- Compute `min_next_bid = current_price + minimum_increment` (or `starting_price` if no bids).
- Reject if `amount < min_next_bid`.
- Store `amount` as `decimal(12,2)` (or integer cents); use consistent rounding (half-up to 2 decimals).

### Transaction flow

Handled by `BidPlacementService` (or `PlaceBidAction`), not the controller.

```
BEGIN TRANSACTION
  SELECT auctions.* FROM auctions WHERE uuid = ? FOR UPDATE
  Validate: exists, status = active, now < ends_at, bidder != seller
  Compute min_next_bid from locked row
  Validate amount >= min_next_bid
  Check idempotency (user_id + client_bid_id) → return existing if found
  INSERT bids (immutable row)
  UPDATE auctions SET current_price, winning_bid_id, revision = revision + 1
  Invoke AntiSnipingService (ADR-002) — may UPDATE ends_at on same row
COMMIT
  Dispatch BidPlaced event (after commit — ADR-003)
```

### Locking strategy

- Use **`SELECT ... FOR UPDATE`** on the `auctions` row for the duration of the transaction.
- Do **not** rely on application-level checks without row lock.
- Default isolation: PostgreSQL `READ COMMITTED` is sufficient when combined with `FOR UPDATE`.
- Keep transactions **short** (no external HTTP, no mail) inside the transaction.

### Idempotency

- Table `bids` includes nullable `client_bid_id` (UUID) and unique index `(user_id, client_bid_id)`.
- On duplicate key with same auction context, return existing bid with `200`.
- Idempotency keys are scoped per user; two users may not share a key meaningfully.

### Immutability

- `bids` rows are **insert-only**. No `UPDATE` or soft delete on bids.
- Corrections (fraud, admin) use separate audit tables or admin-only compensating records — out of scope for MVP.

### Module layout

```
app/Actions/Bid/PlaceBidAction.php          # orchestrates single request
app/Services/Bid/BidPlacementService.php    # transaction + lock + rules
app/Events/BidPlaced.php                    # broadcast after commit
app/Policies/AuctionPolicy.php
```

Repository pattern is **not** used for bidding; Eloquent inside the service is sufficient.

## Consequences

### Positive

- Deterministic behavior under concurrency and retries.
- Clear client contract for errors and refresh-after-conflict.

### Negative

- Hot auctions serialize on one row lock; acceptable for MVP.
- Clients must implement idempotency keys for reliable UX on flaky networks.

## Testing requirements

- Feature test: sequential bids raise price correctly.
- Feature test: concurrent bids — only one wins per increment step; others get `409 CONCURRENT_BID_LOST` or succeed on retry with updated min.
- Feature test: idempotent replay returns same bid, single row in DB.
- Feature test: cannot bid on own auction, after `ends_at`, below minimum.

## References

- [docs/product-rules.md](../product-rules.md)
- [docs/database-rules.md](../database-rules.md)
- [ADR-002 Anti-sniping](./002-anti-sniping.md)
- [ADR-003 Realtime](./003-realtime.md)
