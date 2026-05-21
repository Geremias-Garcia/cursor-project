# Product Rules

Business rules for auctions and bidding. Implementation details: [ADRs](./adr/README.md).

## Auctions

- Auctions have start and end dates (`starts_at`, `ends_at` UTC).
- Auctions may extend automatically when bids occur near the end ([ADR-002](./adr/002-anti-sniping.md): 30s window, 120s extension, max 10 extensions by default).
- Auctions cannot accept bids after ending.
- Minimum bid increment is configurable per auction.
- Users cannot bid on their own auctions.

## Users

- Users can create auctions.
- Users can watch auctions.
- Users receive notifications (async, queued).
- Roles: `user` (default), `admin` ([ADR-004](./adr/004-auth.md)).

## Bidding

- All bids must be transactional ([ADR-001](./adr/001-bidding.md)).
- Highest bid wins.
- Bid history must be immutable.
- Concurrent bids must be handled safely (`FOR UPDATE` + idempotency keys).
- Server computes minimum next bid; client amount is validated inside the transaction.

## Soft deletes

- **Bids:** never soft-deleted.
- **Auctions:** soft-delete allowed only when policy permits (e.g. no active bids or admin moderation).
