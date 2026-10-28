# Guru Hospital — Discharge Summary, OT Notes & MRD Automation System

A production-ready clinical document automation system built with **Laravel 12**, **Blade**, **Bootstrap 5**, and **Dompdf**. This application allows hospital staff to enter shared patient and admission data once to generate three coordinated, print-ready hospital documents:

1. **Discharge Summary** (with 4 format variants and 2 surgery workflows = 8 combinations)
2. **Operation Theatre (OT) Notes**
3. **Medical Records Department (MRD) Record Form**

---

## 🏥 Hospital Format Variants & Workflows

### 4 Formats:
- **General**: Standard hospital format aligned with Guru Hospital clinical templates.
- **CMCHISTN**: Chief Minister's Comprehensive Health Insurance Scheme (Tamil Nadu Govt.) with Pre-Auth / URN tracking.
- **Private**: Private Inpatient & Mediclaim TPA with policy / claim identifiers.
- **Employee**: Hospital Employee and Dependent Healthcare Benefit format.

### 2 Clinical Workflows:
- **Delivery**: Obstetric / LSCS workflow featuring live newborn details (birth weight, Apgar 1m/5m, gender, delivery timestamp, neonatal care, pediatrician handover). Supports multiple births (twins, triplets).
- **Other Surgery**: General and specialized surgeries (e.g. Lap Appendectomy) with vitals, incisions, operative findings, drain tubes, and anesthesia monitoring. Newborn fields are strictly suppressed.

---

## 🔒 Security, Roles & Approval Lifecycle

### Role-Based Access Control:
- **`administrator`**: Full system access, staff account management, template versioning, audit trail inspection.
- **`doctor`**: Clinical review, document approval, return for correction with mandatory clinician reason, revision unlocking.
- **`nursing_staff` / `medical_records`**: Admission intake, draft creation and editing, submission for review.
- **`ot_staff`**: Intraoperative and surgical narrative documentation.
- **`auditor`**: Read-only compliance inspection; strictly blocked from clinical mutations and document approvals.

### Document State Machine:
```
[Draft] ──(Staff Submit)──> [Ready for Review] ──(Doctor Approve)──> [Approved] ──(Generate PDF)──> [Generated]
   ▲                               │
   │                               ▼
   └──(Doctor Return)────── [Returned for Correction]
```
- **Revisions**: Modifying an approved or generated document automatically archives an immutable revision snapshot (`document_revisions`) and transitions the admission back to an editable review state.
- **Audit Trail**: Every authentication event, document state transition, PDF download, and clinical revision is recorded in a tamper-resistant audit log (`audit_logs`) with IP address, user agent, actor ID, and timestamps.
- **Private PDF Storage**: Generated PDF files are stored in `storage/app/private/documents/` and served strictly via authenticated, policy-checked controller streams.

---

## 🧪 Real Hospital Test Data & Automated Tests

The repository includes real clinical seeders based on verified Guru Hospital patient cases:
- **Case 1 (Obstetric Delivery)**: `Mrs. Ashwini`, IP `2026/582`, UHID `2022/151`, Emergency LSCS performed by Dr. Ambika M.S (O&G), Dr. M.S Karthick (Anaesthetist), Paediatrician Dr. Nithyapriya.
- **Case 2 (Other Surgery)**: `Mr. Ajith kumar`, IP `2026/488`, UHID `2026/2892`, Lap Appendectomy performed by Dr. Jayaprakash M.S, Dr. S.G. ArunKumar (Anesthetist).

### Running Automated Tests:
```bash
php artisan test
```
**Test Suite Coverage:**
- 25 automated feature and unit tests (127 assertions).
- Multi-role permission checks (admin, doctor, nurse, auditor, deactivated users).
- Multi-newborn repeater persistence and validation.
- Format isolation between delivery and other-surgery workflows.
- Approval workflow state transitions and reason enforcement.
- Dompdf PDF generation, 2-pass dynamic canvas page numbering (`Page {PAGE_NUM} of {PAGE_COUNT}`), and private file streaming.

---

## 🚀 Local Development Setup

1. **Clone & Install Dependencies:**
   ```bash
   composer install
   ```
2. **Environment Configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Database Migration & Seeding:**
   ```bash
   php artisan migrate:fresh --seed
   ```
4. **Default Logins (Demo Accounts):**
   - **Administrator**: `admin@guruhospital.com` / `password123`
   - **Surgeon / Doctor**: `doctor@guruhospital.com` / `password123` (Reg: `TN-MCI-74892`)
   - **Staff Nurse**: `nurse@guruhospital.com` / `password123`
   - **MRD Officer**: `mrd@guruhospital.com` / `password123`
   - **Compliance Auditor**: `auditor@guruhospital.com` / `password123`
5. **Start Development Server:**
   ```bash
   php artisan serve
   ```
   Access at `http://127.0.0.1:8000`.

---

## 🏭 Production Deployment Runbook (Hospital Server)

### 1. Database Configuration (MySQL / MariaDB)
Update `.env` on your production server:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=guru_hospital
DB_USERNAME=hospital_db_user
DB_PASSWORD=SecurePasswordHere
```
Run production migrations:
```bash
php artisan migrate --force
```

### 2. File Permissions & Font Cache
Ensure the web server (`www-data` or Apache `SYSTEM`) has write permissions to:
```bash
chmod -R 775 storage bootstrap/cache
```
The application bundles Tamil & Indic TrueType fonts (`Nirmala.ttf` and `NirmalaB.ttf`) in `public/fonts/` and utilizes `storage/fonts/` for Dompdf UFM font metrics cache.

### 3. Production Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
