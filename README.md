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

**Phase 1 (Foundation)** is in progress: Laravel API scaffold, Sanctum auth, Docker Compose, React client, and CI workflow.

## Quick start (Docker)

```bash
cp backend/.env.example backend/.env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- App (via Nginx): http://localhost:8080  
- Vite dev server: http://localhost:5173  
- Reverb: ws://localhost:8081  

Seed users: `user@example.com` / `admin@example.com` (password: `password`).

## Local development (Laragon / host PHP)

Your `.env` must use **`127.0.0.1`**, not `postgres` (that hostname only works inside Docker).

### Option A — PostgreSQL (recommended, matches production)

1. **Enable the PHP driver** in `D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini` (adjust path to your PHP version):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
   Restart the terminal, then verify: `php -m` should list `pdo_pgsql`.

2. **Start Postgres** (Docker example):
   ```bash
   docker compose up -d postgres redis
   ```

3. **Configure `.env`** (from `backend/.env.example` — already uses `DB_HOST=127.0.0.1`):
   ```bash
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

### Option B — SQLite (quick start, no `pdo_pgsql`)

In `backend/.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Then:

```bash
cd backend
type nul > database\database.sqlite
php artisan migrate --seed
```

`GET /ready` expects Redis; use Option A or Docker for full stack parity.

### Frontend & Sanctum auth

The SPA must hit Laravel for `/sanctum/*` and `/api/*`, not the Vite HTML server alone.

| How you open the app | API requests |
|----------------------|--------------|
| http://localhost:5173 (`npm run dev`) | Relative URLs + **Vite proxy** → `VITE_PROXY_TARGET` (default `http://127.0.0.1:8080`) |
| http://localhost:8080 (Docker nginx) | Relative URLs → nginx → Laravel (no extra config) |

Do **not** set `VITE_API_BASE_URL` unless you fully control cross-origin CORS/cookies. Sanctum CSRF cookies must be same-origin with the page (proxy or nginx).

```bash
cd frontend
cp .env.example .env.development   # optional; .env.development is loaded in dev
npm install
npm run dev
```

Docker Compose sets `VITE_PROXY_TARGET=http://nginx:80` for the `frontend` service.

## Run artisan inside Docker

If PHP on the host has no Postgres driver, run migrations in the container:

```bash
docker compose up -d postgres redis app
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Use `backend/.env.docker.example` for in-container hostnames (`postgres`, `redis`).