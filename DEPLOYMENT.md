# Production deployment

Mauricare runs directly on the production host at `https://mauricare.mu`.
Laravel connects to the MySQL service installed on that same server.

## Server requirements

- A cPanel account with SSH, PHP, Composer, Node.js/npm, and MySQL
- AutoSSL enabled by the hosting provider
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
php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Paste the generated `APP_KEY` into `.env.production`. Replace the database
password placeholder with the existing host-MySQL credentials. Keep
`DB_HOST=localhost`. Do not commit this plaintext file.

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

Deploy the application:

```bash
export SOPS_AGE_KEY_FILE=/etc/mauricare/age-key.txt
./scripts/deploy-production.sh
```

The deployment script decrypts the environment into the Git-ignored `.env`
runtime file with mode `0600`, installs production dependencies, builds frontend
assets, runs migrations, optimizes Laravel, and restarts queue workers. MySQL
continues running directly on the host; no database container is used.

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

When a pull merges new commits, the hook updates the protected runtime
environment and deploys the host-installed Laravel application. The private key
and decrypted environment are never committed.

Check the deployment:

```bash
php artisan about
sudo systemctl status nginx
curl -I https://mauricare.mu/up
```

The application will be available at `https://mauricare.mu`. Requests to
`http://mauricare.mu` and either form of `www.mauricare.mu` are redirected to
the canonical HTTPS address.

## Updating

```bash
git pull
```

The deployment script runs database migrations and restarts Laravel queue
workers after every successful pull.

## Useful commands

```bash
php artisan about
php artisan migrate:status
php artisan queue:restart
tail -f storage/logs/laravel.log
```

## HTTPS

TLS and firewall access are managed by the cPanel hosting provider. In cPanel,
open **Security → SSL/TLS Status**, select `mauricare.mu` and
`www.mauricare.mu`, and run AutoSSL. After the certificate is active, open
**Domains → Domains** and enable **Force HTTPS Redirect** for `mauricare.mu`.

If AutoSSL or Force HTTPS Redirect is unavailable, ask the hosting provider to
enable AutoSSL and HTTPS for both hostnames. A jailed-shell user cannot install
Certbot, manage the firewall, or bind Caddy to ports 80 and 443.
