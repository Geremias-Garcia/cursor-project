# Realtime Auction Platform

A production-oriented realtime auction platform focused on scalability, transactional consistency, and clean architecture.

## Goals

This project is being developed as a modern fullstack system to study and implement:

- Realtime bidding systems
- WebSocket communication
- Scalable backend architecture
- Dockerized infrastructure
- Secure transactional operations
- Clean architecture principles
- AI-assisted software engineering workflows

## Stack

### Backend
- Laravel 11
- PostgreSQL
- Redis
- Laravel Sanctum
- Laravel Reverb

### Frontend
- React
- Vite
- TailwindCSS

### Infrastructure
- Docker Compose
- Nginx

## Core Features

- User authentication
- Auction management
- Live bidding
- Realtime updates
- Notifications
- Bid history
- Watchlists
- Anti-sniping system
- Admin dashboard

## Architecture

The project follows:
- Service-oriented backend architecture
- Thin controllers
- Reusable frontend components
- Transaction-safe bidding operations
- Realtime event-driven communication

## Documentation

| Resource | Description |
|----------|-------------|
| [docs/DOCUMENTATION.md](docs/DOCUMENTATION.md) | Index and canonical source policy |
| [docs/adr/](docs/adr/) | Architecture Decision Records (bidding, auth, realtime) |
| [docs/roadmap.md](docs/roadmap.md) | Implementation phases |
| [docs/infrastructure.md](docs/infrastructure.md) | Docker Compose and observability |

## Development Status

Architecture specification complete (ADRs + infrastructure docs). Application scaffolding starts at **Phase 1** per roadmap.