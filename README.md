# Technical Assessment – Lumen API, Node Cache, React Frontend

Modern microservice-style app with:
- Backend (Lumen, PHP 8.2) with JWT auth and REST endpoints
- Cache layer (Node.js + Redis) for fast post retrieval
- MySQL database for users and posts (via migrations)
- Frontend (React) consuming the APIs
- Docker Compose for local orchestration
- GitHub Actions CI for tests and image builds

## Architecture

- Services
	- Lumen API (PHP 8.2 + Apache)
		- JWT-based auth using tymon/jwt-auth
		- Posts endpoints backed by storage JSON (simple demo) and DB migrations for users/posts
	- Node Cache (Express + redis client)
		- Endpoints proxy to API and cache results in Redis
	- React Frontend (Create React App)
		- Calls API and cache endpoints
	- Redis (7-alpine)
	- MySQL (8)

- Ports
	- API: http://localhost:8000
	- Cache API: http://localhost:5000
	- Frontend: http://localhost:3000
	- Redis: 6379

## Endpoints

Lumen API (base: http://localhost:8000)
- POST /api/register – body: { name, email, password }
- POST /api/login – body: { email, password } -> returns JWT token (for DB-backed users)
- GET /api/posts – list posts
- GET /api/posts/{id} – fetch one post
- POST /api/posts – body: { title, content }

Node Cache API (base: http://localhost:5000)
- GET /cache/posts – returns cached posts (pulls from API on miss)
- GET /cache/posts/{id} – returns a cached single post (pulls from API on miss)

## Local development with Docker

Prerequisites: Docker Desktop

Start services
```sh
docker compose up -d --build
```

Stopping
```sh
docker compose down
```

Seed the database (inside containers)
```sh
# Run migrations
docker compose exec api php artisan migrate --force

# Seed data (creates a default user and sample posts)
docker compose exec api php artisan db:seed --force
```

Default seeded user for testing
- email: test@example.com
- password: password

Environment
- API reads env from `backend-lumen/.env`. For Docker, DB_HOST=mysql and CACHE_DRIVER=file are set by compose. Redis is available as `redis`.
- Node cache uses env:
	- PORT=5000
	- REDIS_URL=redis://redis:6379
	- API_BASE_URL=http://api (service DNS in compose)

## Project layout

```
backend-lumen/    # Lumen API (PHP)
node-cache/       # Node + Redis cache
frontend/         # React app
docker-compose.yml
```

Key files
- backend-lumen/Dockerfile – PHP 8.2 + Apache + phpredis
- node-cache/Dockerfile – Node 22-alpine, ts-node
- frontend/Dockerfile – Node 22-alpine dev server
- .github/workflows/ci.yml – CI jobs (PHP/Node tests, build images)

## Running tests locally

PHPUnit (API)
```sh
docker compose exec api ./vendor/bin/phpunit --colors=always
```

Jest (Node cache)
```sh
docker compose exec cache npm test -- --runInBand --colors
```

## CI/CD

GitHub Actions workflow `.github/workflows/ci.yml`:
- backend-php-tests
	- Spins up MySQL and Redis services
	- Installs PHP 8.2 extensions
	- composer install, artisan migrate, phpunit
- node-tests
	- Node v22, npm ci, jest
- docker-build
	- Builds api, cache, and frontend images (no push)

To enable container image publishing, add a registry login step and use build-push-action with tags.

## Notes / Future improvements
- Add JWT middleware protection to post creation and sensitive routes
- Add Redis cache provider config to Lumen if switching CACHE_DRIVER=redis in production
- Add Docker multi-stage React build and serve static assets via Nginx for prod
- Add GitHub Actions deploy job (e.g., to Azure Web App for Containers)

## Troubleshooting
- MySQL healthcheck: compose waits for MySQL before starting the API
- If migrations fail due to cache store, ensure CACHE_DRIVER=file in `.env` during setup
- Port conflicts: if you already run Redis or MySQL locally, remove the host port binds or change ports in compose
