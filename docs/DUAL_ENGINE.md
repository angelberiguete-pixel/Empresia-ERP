# Dual ERP engine — Odoo 20 + Dolibarr 24 (staging)

**Role:** server runtimes for the Empresia CRM, **not** a replacement for the canonical CRM/ERP database.

## Architecture and isolation

| Component | Database and persistence | Purpose |
|---|---|---|
| Empresia (Vercel + Supabase) | Supabase Postgres, tenant-scoped/RLS | **Canonical** contacts, orders, inventory events, confirmed payment references, future audited journal |
| Odoo Community 20 | Independent `odoo-postgres` Postgres 17 and `odoo_filestore` | Planning, purchasing, procurement and specialist accounting assistance; writes to canonical domains prohibited until explicit policy/adapter approval |
| Dolibarr 24 | Independent `mariadb` and `dolibarr_documents`/`dolibarr_custom` | Administration, supplemental documentation/reporting; read-only adapter first |
| Integration gateway | Private Edge Functions + durable outbox, source mapping, idempotency | One direction per domain, no echo loops, no second authoritative invoice/stock move |

Both upstream ERP engines preserve their own databases for internal module metadata. **Having three databases is not the same as having three sources of truth.** The canonical financial/stock records remain in Empresia, with external IDs used only as references, and non-overlapping features delegated deliberately.

## Prerequisites

- Docker Compose v2 and hosts/VM with sufficient memory, storage, SSL certificate and backups.
- Copy `.env.example` to local `.env`; replace every `CHANGE_ME` with separate cryptographically strong secrets. Do not commit secrets.
- The images are pinned (`odoo:20.0-20261102`; pin Dolibarr to a reviewed 24.x tag after validating availability) and databases use independent credentials and persistent volumes.
- Keep both HTTP ports **bound to 127.0.0.1** during setup. Do not expose database ports.
- Configure real HTTPS proxy, access restrictions and backups before any remote APIs; prohibit public database-manager access for Odoo.
- Both services require **initial installation/administrator provisioning**; starting containers does not automatically prove business modules work.

## Local installation

```bash
cp .env.example .env
# Securely set DB_PASSWORD, DOLI_ADMIN_PASSWORD, DOLI_INSTANCE_UNIQUE_ID,
# ODOO_DB_PASSWORD, and DOLI_URL_ROOT (no credentials in Git)
docker compose --env-file .env config --quiet
docker compose --env-file .env pull
docker compose --env-file .env up -d
# FIRST INSTALL ONLY, while the Odoo database is still empty:
docker compose --env-file .env run --rm --no-deps odoo \
  --database empresia_odoo --init base --stop-after-init --without-demo=all
docker compose --env-file .env restart odoo
docker compose ps
docker compose logs --tail=80 odoo dolibarr
```

Local endpoints after healthy startup:
- Dolibarr: http://127.0.0.1:8080
- Odoo: http://127.0.0.1:8069

At first install, initialize the Odoo database `empresia_odoo` with the one-shot command above (otherwise /web/version returns HTTP 500). Then install the module dependencies actually required; ensure the database manager and initial admin password are hardened. Enable only **read-only integration credentials** for Odoo and Dolibarr initially.

## Functional acceptance before connecting Empresa customers

1. HTTP health checks pass after initial setup, and both databases preserve records across a restart.
2. A single test tenant in Empresia has distinct Odoo/Dolibarr connections; authenticates via server-side API credentials, never the browser.
3. Two read-only probes and a product import preview work with stable canonical mapping, no duplicate SKU.
4. Outbox retries are idempotent and failures remain visible; cross-tenant attempts fail.
5. Orders, inventory, payments, fiscal invoices and accounting write only to **one canonical record owner**; external systems are mirrors or specialized side modules. No bidirectional posting of accounting or inventory.
6. A true purchase/stock/reservation/order/dispatch/cobro/contabilidad scenario passes **independent tests**; neither a fake UI nor Compose containers alone qualify.
7. Restore backup, rotate credentials, disable integrations and verify rollback.

## Hosting constraint, checked 2026-11-11

The connected Railway workspace refused a new isolated project: **Free plan resource provision limit exceeded**. No runtime deployment was performed. The `docker-compose.yml` is ready for a host with sufficient allocated resources. Do not use `clipradar-live` or `tairan-fleet-manager-pilot` for ERP without separate authorized infrastructure.

The companion application-side implementation and policies live in `angelberiguete-pixel/crm`, in an ERP integration PR.
