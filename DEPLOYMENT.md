# Production deployment

This project includes a production Docker stack for `https://mauricare.mu`.
Vite assets are compiled into the application image, so no Vite server or
Node.js process runs in production.

## Server requirements

- Ubuntu or another Linux distribution supported by Docker
- Docker Engine with the Compose plugin
- Ports `22`, `80`, and `443` allowed through the firewall
- DNS records for `mauricare.mu` and `www.mauricare.mu` pointing to
  `102.222.106.240`

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
password placeholders with different strong passwords. Do not commit this
plaintext file.

Production settings are committed in encrypted form using SOPS and Age, matching
the encrypted production-values approach used by `pricing-api`. Encrypt the
completed environment:

```bash
./scripts/encrypt-production-env.sh
git add deploy/.env.production.enc
```

The Age recipient is public and stored in `.sops.yaml`. The corresponding
private Age identity must be stored outside Git, preferably at
`/etc/mauricare/age-key.txt` on the production server with mode `0600`.

Build and start the production services:

```bash
export SOPS_AGE_KEY_FILE=/etc/mauricare/age-key.txt
./scripts/deploy-production.sh
```

The deployment script decrypts the environment into a restricted temporary
file, validates the Compose configuration, deploys the containers, and removes
the temporary plaintext automatically. It never creates `.env.production` in
the repository.

### Deploy automatically on `git pull`

The server workflow can remain a plain `git pull`. After installing the Age
private key and committing `deploy/.env.production.enc`, enable the
repository-managed post-merge hook once:

```bash
export SOPS_AGE_KEY_FILE=/etc/mauricare/age-key.txt
./scripts/install-production-git-hook.sh
```

After this one-time setup, the production deployment command is simply:

```bash
git pull
```

When a pull merges new commits, the hook decrypts the environment temporarily,
validates the Compose configuration, rebuilds changed images, and restarts the
services. The private key and decrypted environment are never committed.

Check the deployment:

```bash
./scripts/production-compose.sh ps
./scripts/production-compose.sh logs --tail=100 app web caddy
curl -I https://mauricare.mu/up
```

The application will be available at `https://mauricare.mu`. Requests to
`http://mauricare.mu` and either form of `www.mauricare.mu` are redirected to
the canonical HTTPS address.

## Updating

```bash
git pull
docker image prune -f
```

The app container runs database migrations before it becomes healthy. The queue
worker and scheduler wait for the app health check before starting.

## Useful commands

```bash
./scripts/production-compose.sh logs -f
./scripts/production-compose.sh exec app php artisan about
./scripts/production-compose.sh exec app php artisan migrate:status
./scripts/production-compose.sh restart worker scheduler
```

## HTTPS

Caddy automatically obtains and renews publicly trusted certificates for
`mauricare.mu` and `www.mauricare.mu`. Its certificate state is retained in the
`caddy-data` Docker volume.

Both TCP ports 80 and 443 must be reachable from the internet while Caddy
requests the first certificate. Check certificate issuance with:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml logs --tail=100 caddy
curl -I https://mauricare.mu
```
