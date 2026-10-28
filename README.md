# Guru Hospital — Laravel + Blade Document Pack (Design Starter)

This is a **starter implementation**, not a complete production hospital system. It implements the design pattern for one admission entry producing:
1. one discharge summary in a selected format (`general`, `cmchistn`, `private`, `employee`) and surgery workflow (`delivery`, `other`);
2. Operation Theatre Notes;
3. MRD Form.

It generates one combined HTML print view and provides a Laravel PDF-service integration point. You must connect and test a PDF library in your existing Laravel app before PDF download works.

## Important clinical/source boundaries
- The actual approved discharge-summary layouts for all four formats have not been supplied in this conversation. The four Blade format views are therefore clearly marked **DRAFT PLACEHOLDER LAYOUTS**; replace them with approved hospital forms.
- The supplied OT note source has a discrepancy: procedure narrative says alive male baby, while the baby field says alive female baby. Do not copy that patient's clinical values into templates. Verify the signed source record for that case.
- No medical values or prescriptions are auto-filled from prior patients.
- This starter is not clinically approved and must not be used for live patient records until reviewed, secured, tested and approved by hospital management and clinical leadership.

## Intended environment
- Laravel 12 (adjust as needed for the actual app)
- Blade + Bootstrap 5
- MySQL
- A PDF library compatible with the Laravel version, selected and installed by the project owner
- Authentication and authorisation configured in the host application

## Integrate
1. Back up your current project and use a feature branch.
2. Copy the files from this package into the matching locations in your Laravel project.
3. Review the migration and adjust table names/columns to your existing patient/admission schema.
4. Register the routes behind your authenticated middleware.
5. Add the `DocumentPackPdfService` implementation for the PDF library you choose; do not expose private files via public URLs.
6. Run migrations only after reviewing schema compatibility.
7. Add policies/roles and tests before production use.

## Workflow
- Admission form collects shared patient/admission details once.
- User chooses format and surgery type.
- Conditional delivery section collects birth/newborn details, with support for multiple newborns.
- Other-surgery workflow hides birth fields.
- Save draft, review each document, then authorise and generate pack.
- Combined pack order: discharge summary → OT notes → MRD.
- MRD page count is calculated after rendering the final combined PDF. If the hospital's rule excludes the MRD form from the page count, configure that policy explicitly.

## Routes
- `GET /admissions/create`
- `POST /admissions`
- `GET /admissions/{admission}/documents/preview`
- `POST /admissions/{admission}/documents/pdf`

## Before live deployment
- Authentication, role-based permissions, CSRF, validation, audit logging, private file storage, backups, restore testing, retention rules and clinician authorisation are mandatory.
- Do not mark a summary approved until the responsible clinician has reviewed it.
