# Database Rules

Schema conventions for PostgreSQL. Bidding tables and locks: [ADR-001](./adr/001-bidding.md).

- Use UUIDs for public entities (`uuid` column, unique index).
- Never expose sequential internal IDs in API responses.
- Use timestamps everywhere (`created_at`, `updated_at`; `placed_at` on bids).
- Use soft deletes when appropriate — **never** on `bids` (immutable).
- Use foreign keys internally (integer PKs OK internally).
- Bid records must be immutable (insert-only).
- Use transactions for all bidding operations.
- Use indexes intentionally (`auction_id` on bids, unique `(user_id, client_bid_id)`).
- `auctions.revision` integer, incremented on each state change (ADR-003).
- Anti-sniping columns on `auctions`: `snipe_window_seconds`, `extension_seconds`, `max_extensions`, `extension_count`.
- Keep migrations reversible.
- Prefer PostgreSQL-native features when useful (e.g. `decimal` for money).
