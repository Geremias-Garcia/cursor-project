# Documentation Index

**Canonical source of truth:** `docs/` (especially `docs/adr/`).  
**Cursor rules:** `.cursor/rules/*.mdc` are short enforcement summaries; when they conflict with docs, **docs win**.

## Start here

| Document | Purpose |
|----------|---------|
| [architecture.md](./architecture.md) | Stack overview + links to ADRs |
| [roadmap.md](./roadmap.md) | Phased implementation order |
| [infrastructure.md](./infrastructure.md) | Docker Compose dev/prod, observability |
| [adr/README.md](./adr/README.md) | Accepted architecture decisions |

## ADRs (required reading before coding)

1. [004-auth.md](./adr/004-auth.md) — Sanctum SPA, CSRF, roles  
2. [001-bidding.md](./adr/001-bidding.md) — Transactions, locking, idempotency  
3. [002-anti-sniping.md](./adr/002-anti-sniping.md) — Extension rules  
4. [003-realtime.md](./adr/003-realtime.md) — Channels, events, reconnect  

## Domain & rules

- [domain.md](./domain.md)
- [product-rules.md](./product-rules.md)
- [database-rules.md](./database-rules.md)
- [api-contracts.md](./api-contracts.md)
- [non-functional-requirements.md](./non-functional-requirements.md)

## Development

- [coding-standards.md](./coding-standards.md)
- [technical-decisions.md](./technical-decisions.md)
- [ai-instructions.md](./ai-instructions.md)

## Cursor rules map

| Rule file | Mirrors |
|-----------|---------|
| `architecture.mdc` | architecture.md, ADRs |
| `backend.mdc` | coding-standards, ADR-001/003 |
| `frontend.mdc` | architecture.md, ADR-003 |
| `security.mdc` | ADR-004, product-rules |
| `database.mdc` | database-rules.md, ADR-001 |
| `devops.mdc` | infrastructure.md |
| `testing.mdc` | roadmap test gates |
| `workflow.mdc` | ai-instructions.md |
