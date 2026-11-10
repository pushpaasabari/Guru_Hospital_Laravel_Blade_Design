# Guru Hospital — Clinical Document Workflow & Discharge Automation

A production clinical document automation system built with **Laravel 12**, **React + Inertia.js**, **MySQL**, and **Playwright + Chromium**. Hospital staff enter shared patient, admission, surgery, lab, and discharge records once to generate three coordinated print-ready clinical documents:

1. **Discharge Summary** (`223 mm × 304 mm` two-page portrait layout designed for Guru Hospital's pre-printed stationery with `70 mm` top and `30 mm` bottom blank margins).
2. **Operation Theatre (OT) Notes** (`A4` portrait).
3. **Medical Records Department (MRD) Form** (`A4` portrait) and combined **3-in-1 Dossier Pack**.

---

## Clinical Formats & Workflows

### Formats & Insurance Tracking
- **General**: Standard cash / general inpatient summary.
- **CMCHISTN**: Chief Minister's Comprehensive Health Insurance Scheme (Tamil Nadu Govt.).
- **Private Insurance / TPA**: Private mediclaim / TPA with policy, pre-auth, and holder metadata.
- **Employee**: Hospital employee healthcare benefit format.

### Clinical Workflows
- **Delivery / Obstetric**: Supports single and multiple newborn birth entries (sex, birth time, weight, APGAR 1m/5m, neonatal care) and mandatory Tamil Nadu PICME / RCH ID.
- **Other Surgery**: General / laparoscopic / urological surgical procedures (including DJ stent Tamil removal notice support) with newborn fields suppressed.
- **Medical Case**: Non-surgical conservative medical admissions.

### Normalized Clinical Inputs
- Automatically enforces **UPPERCASE** persistence for `patient_name`, `address_line1..3` (and combined `address`), `consultant`, and Stage 2 **Surgical & Anaesthesia Team** fields.
- Enforces **digits-only (`0-9`)** restriction and validation on `Phone / Contact`.

---

## Security, Roles & Approval Lifecycle

- **`administrator`**: Full access, staff users, clinical staff directory, anaesthesia types, templates, and audit logs.
- **`doctor`**: Clinical review, document approvals, return for correction, revision creation, and PDF export.
- **`nursing_staff` / `ot_staff` / `mrd_staff`**: Stage-by-stage admission intake, editing, and PDF generation.
- **`auditor`**: Read-only inspection and audit trail access.
- **Audit Trail & Private Storage**: Key actions log actor, IP, user agent, and diffs into `audit_logs`. Generated PDFs are archived privately under `storage/app/private/documents/` behind authenticated policy-checked endpoints.

---

## Automated Tests

```bash
php artisan test
```

*(Uses isolated in-memory SQLite via `phpunit.xml`, or pass `DB_CONNECTION=mysql` with a dedicated test schema to test directly against MySQL.)*

---

## Local Development Setup

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   npx playwright install chromium
   ```
2. **Configure `.env` (MySQL):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Create the MySQL database (`guru_hospital`) and run migrations + seeders:**
   ```bash
   php artisan migrate --seed
   ```
4. **Build frontend or run dev servers:**
   ```bash
   npm run build
   php artisan serve
   ```

---

## Production Deployment Runbook (AWS / Linux / Nginx / Apache)

1. **Server Prerequisites:**
   - PHP `>= 8.2` (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`), Composer, MySQL 8 / MariaDB 10.4+, Node.js `>= 20`, and Playwright Chromium system dependencies (`npx playwright install-deps chromium`).
2. **Environment Configuration (`.env`):**
   ```env
   APP_NAME="Guru Hospital"
   APP_ENV=production
   APP_KEY=...
   APP_DEBUG=false
   APP_URL=https://your-domain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=guru_hospital
   DB_USERNAME=hospital_db_user
   DB_PASSWORD=StrongPasswordHere

   SESSION_DRIVER=database
   SESSION_ENCRYPT=true
   QUEUE_CONNECTION=database
   CACHE_STORE=database
   LOG_CHANNEL=daily
   LOG_LEVEL=warning
   TRUSTED_PROXIES=*
   ```
3. **Install, Build, Migrate & Optimize:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   npx playwright install chromium
   rm -f public/hot
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize
   ```
4. **File Permissions & Verification:**
   - Ensure web server / PHP-FPM user (`www-data`) owns or has write access to `storage/` and `bootstrap/cache/`.
   - Confirm `public/fonts/Nirmala.ttf` and `public/fonts/NirmalaB.ttf` are present for Tamil stent notice rendering.
   - Verify health endpoint `GET /up` returns HTTP `200`.
