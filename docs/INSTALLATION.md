# Empresia ERP — local Docker staging

This setup boots **the official Dolibarr container** with MariaDB. It does **not** build the imported `dolibarr/` source. The imported source stays untouched as a basis for later version-controlled customization.

## Prerequisites

- Docker Engine and Docker Compose plugin
- 2+ GB RAM (recommended for testing)
- Available local port 8080

## Start

```bash
cp .env.example .env
# Edit .env: set DB_PASSWORD, DOLI_ADMIN_PASSWORD, DOLI_INSTANCE_UNIQUE_ID to distinct secure random values.
docker compose config --quiet
docker compose up -d
docker compose ps
docker compose logs --tail=120 dolibarr
```

Open http://localhost:8080 and sign in using DOLI_ADMIN_LOGIN and DOLI_ADMIN_PASSWORD.

This stack binds port 8080 **only to localhost**, intentionally. Never publish it directly over HTTP to the Internet. For a remote staging server, put a configured HTTPS reverse proxy and access control in front of it, and update DOLI_URL_ROOT. Configure backups before using real client data.

## Smoke test

```bash
docker compose ps
curl -I http://127.0.0.1:8080/
docker compose exec mariadb mariadb -u "${DB_USER:-empresia}" -p "${DB_NAME:-empresia}"
```

The database command above requires providing the password interactively. Alternatively, validate via the Dolibarr interface: log in, create a fictitious third party, prepare a draft quote, log out and restart the stack, then verify that data persists.

## Stop, resume, and upgrades

```bash
docker compose down
docker compose up -d
```

`docker compose down` keeps named volumes. **Do not use `down -v`**, which destroys persistent database and document volumes. Back up volumes and pin DOLIBARR_IMAGE to an explicit tested release before deploying beyond local experiments. On upgrade, validate database migrations and rollback procedures.

## Scope and constraints

- This setup uses a prebuilt official image. Editing `dolibarr/htdocs` will **not** change the running application.
- A later build pipeline can make a custom image from a pinned upstream version with proprietary additions in the `custom` module directory.
- No public deployment, automatic end-to-end tests, multi-tenant isolation, Dominican fiscal compliance, or production security certification is implied by the Compose configuration.
- Keep each customer's data isolated until cross-company security has been explicitly verified.
