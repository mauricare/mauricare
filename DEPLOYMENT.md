# Production deployment

This project includes a production Docker stack for `http://102.222.106.240`.
Vite assets are compiled into the application image, so no Vite server or
Node.js process runs in production.

## Server requirements

- Ubuntu or another Linux distribution supported by Docker
- Docker Engine with the Compose plugin
- Ports `22`, `80`, and later `443` allowed through the firewall

## First deployment

Clone the repository and enter its directory:

```bash
git clone <repository-url> mauricare
cd mauricare
```

Create the production environment:

```bash
cp .env.production.example .env.production
docker run --rm php:8.3-cli php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Paste the generated `APP_KEY` into `.env.production`. Replace both database
password placeholders with different strong passwords. Do not commit this file.

Build and start the production services:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
```

Check the deployment:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml ps
docker compose --env-file .env.production -f docker-compose.prod.yml logs --tail=100 app web
curl -I http://102.222.106.240/up
```

The application will be available at `http://102.222.106.240`.

## Updating

```bash
git pull
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build
docker image prune -f
```

The app container runs database migrations before it becomes healthy. The queue
worker and scheduler wait for the app health check before starting.

## Useful commands

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml logs -f
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan about
docker compose --env-file .env.production -f docker-compose.prod.yml exec app php artisan migrate:status
docker compose --env-file .env.production -f docker-compose.prod.yml restart worker scheduler
```

## HTTPS

An IP address cannot normally receive a publicly trusted TLS certificate.
Point a domain name to `102.222.106.240`, update `APP_URL` and
`SESSION_SECURE_COOKIE=true`, then place a TLS reverse proxy such as Caddy or
Nginx with Certbot in front of this stack.
