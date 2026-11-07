# Empresia ERP on Vercel — container build

The imported repository is **PHP Dolibarr**, not Python. Vercel's
`PYTHON_ENTRYPOINT_NOT_FOUND` build failure means it picked the wrong framework.

`Dockerfile.vercel` selects Vercel's Container runtime and uses the upstream
Dolibarr image. On the Vercel dashboard, verify the project's **Root Directory**
is `./` and **Framework Preset** is `Container`, not Python; clear any Python
build/install/output overrides. On pushing the branch, Vercel should build
a container image rather than searching for a Python entrypoint.

**This fixes framework detection only. It does not provide a working ERP.**
The container uses an ephemeral filesystem and cannot safely hold MariaDB or
Dolibarr documents. A dedicated MySQL/MariaDB database and durable document
storage are mandatory. The Dolibarr container's default document storage is
local (`/var/www/documents`), not persistent on Vercel. Solve that explicitly
before using the application. Do not enter real clients or billing data.

Relevant Dolibarr variables once those services exist (configure in Vercel
Environment Variables, never in GitHub):
- DOLI_INSTALL_AUTO=1
- DOLI_DB_HOST, DOLI_DB_NAME, DOLI_DB_USER, DOLI_DB_PASSWORD
- DOLI_ADMIN_LOGIN, DOLI_ADMIN_PASSWORD
- DOLI_INSTANCE_UNIQUE_ID
- DOLI_URL_ROOT = exact HTTPS deployment URL
- DOLI_CRON=0

**Security and readiness gate:** pin a tested Dolibarr version instead of
`latest`, verify TLS to the database, durable document persistence and backups,
image startup behaviour under autoscaling, lock down the installer, test login,
third parties, quotes, document uploads, and persistence after instance recycle.
Not certified for production until all checks pass.

The container build uses the official image, **not** the imported
`dolibarr/` folder. Follow-up work should build a custom image of a pinned
release with Empresia modules, preserving upstream licenses.
