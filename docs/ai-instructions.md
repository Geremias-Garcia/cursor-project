# AI Development Instructions

You are working on a production-oriented real-time auction platform.

**Before coding:** read [DOCUMENTATION.md](./DOCUMENTATION.md) and relevant [ADRs](./adr/README.md).

You must follow these rules strictly:

- Never create duplicated business logic.
- Always separate concerns properly.
- Use service classes for business rules.
- Controllers must remain thin.
- Use Form Requests for validation.
- Use DTOs when appropriate.
- Follow SOLID principles.
- Prefer clean architecture patterns.
- Use repository pattern only when justified.
- Write scalable code.
- Prioritize readability over cleverness.
- Always explain architectural decisions.
- Never modify unrelated files.
- Avoid generating fake implementations.
- Always ask before introducing breaking architectural changes.
- All realtime features must support concurrency safely.
- Consider race conditions in bidding systems.
- Every bid operation must be transactional.
- Never trust client-side validation.