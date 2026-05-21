# ADR-002: Anti-Sniping

**Status:** Accepted  
**Date:** 2026-05-21  
**Deciders:** Architecture plan (pre-implementation)

## Context

Anti-sniping prevents last-second bids from ending an auction unfairly. The domain concept exists in [docs/domain.md](../domain.md) but parameters were undefined, causing ambiguous implementation and user disputes.

## Decision

### Parameters (MVP defaults)

| Parameter | Value | Notes |
|-----------|-------|-------|
| `snipe_window_seconds` | `30` | Bid within 30s of `ends_at` triggers extension |
| `extension_seconds` | `120` | Each trigger adds 2 minutes to `ends_at` |
| `max_extensions` | `10` | Cap per auction; after cap, bids still accepted but no further extension |
| `clock_authority` | Server UTC | All comparisons use `auctions.ends_at` in UTC; app timezone config irrelevant for rules |

Configurable per auction at creation time (columns on `auctions` table) with these defaults.

### When extension runs

- Invoked **inside** the same database transaction as bid placement (ADR-001), after bid insert and auction price update.
- Condition: `now() >= ends_at - snipe_window` AND `now() < ends_at` AND `extension_count < max_extensions`.
- Action: `ends_at = ends_at + extension_seconds`, `extension_count = extension_count + 1`, `revision = revision + 1`.

If `now() >= ends_at` before lock acquisition, bid is rejected (`409 AUCTION_NOT_ACCEPTING_BIDS`) — no extension.

### Edge cases

| Scenario | Behavior |
|----------|----------|
| Bid exactly at `ends_at - 1s` | Extension applies if within window |
| Bid at `ends_at` or after | Rejected; auction closed |
| Two concurrent bids in snipe window | Both processed serially under `FOR UPDATE`; each may increment `extension_count` if still in window after prior bid shifted `ends_at` |
| Extension pushes `ends_at` past max wall-clock | Allowed until `max_extensions` reached |
| `max_extensions` exhausted | Bid still valid if amount OK; no further time added |
| Auction not yet started | No extension; bid rejected as not accepting bids |

### API / client visibility

- Successful bid response includes updated `ends_at` and `revision`.
- Broadcast `AuctionExtended` event (ADR-003) with `{ auction_id, ends_at, revision, extension_count }`.

### Service boundary

```
app/Services/Auction/AntiSnipingService.php
  extendIfNeeded(Auction $auction, Carbon $now): bool
```

Called only from `BidPlacementService` within the open transaction. No standalone HTTP endpoint for extension.

## Consequences

### Positive

- Predictable, documentable behavior for support and tests.
- Extension atomic with bid — no orphan extensions.

### Negative

- Long snipe battles on popular items extend wall-clock duration; monitor `max_extensions` in prod.

## Testing requirements

- Bid at T-29s before end extends `ends_at` by 120s.
- Bid at T-31s before end does not extend.
- After 10 extensions in window, 11th snipe-window bid does not extend but still records if valid.
- Concurrent snipe bids: final `ends_at` consistent with serial transaction order.

## References

- [ADR-001 Bidding](./001-bidding.md)
- [ADR-003 Realtime](./003-realtime.md)
- [docs/product-rules.md](../product-rules.md)
