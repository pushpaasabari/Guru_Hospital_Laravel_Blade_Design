# Required approval workflow (not implemented by the starter)
1. Draft author saves admission and document fields.
2. Submit for review transitions `draft` → `awaiting_review`.
3. A clinician with the correct permission reviews all three documents.
4. Approval action records `approved_by`, `approved_at`, and an audit event, and freezes the approved data version.
5. PDF generation is allowed only for that approved version.
6. Corrections after approval create a new version/addendum; never silently overwrite signed documents.
