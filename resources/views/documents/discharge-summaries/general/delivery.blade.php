{-- DRAFT PLACEHOLDER: replace with the hospital-approved GENERAL delivery layout. --}
<h2 class="text-center">GURU HOSPITAL</h2>
<h3 class="text-center">GENERAL DISCHARGE SUMMARY</h3>
<p class="text-center text-danger"><strong>DRAFT PLACEHOLDER LAYOUT — NOT YET HOSPITAL-APPROVED</strong></p>
<table class="table table-sm table-bordered">
<tr><th>Patient Name</th><td>{{ $admission->patient_name }}</td><th>Age / Sex</th><td>{{ $admission->age ?: 'Not recorded' }} / {{ $admission->sex ?: 'Not recorded' }}</td></tr>
<tr><th>UHID</th><td>{{ $admission->uhid }}</td><th>IP No.</th><td>{{ $admission->ip_no }}</td></tr>
<tr><th>Admission</th><td>{{ $admission->admission_at?->format('d/m/Y H:i') }}</td><th>Discharge</th><td>{{ $admission->discharge_at?->format('d/m/Y H:i') }}</td></tr>
<tr><th>Consultant</th><td colspan="3">{{ $admission->consultant ?: 'Not recorded' }}</td></tr>
<tr><th>Diagnosis</th><td colspan="3">{{ $admission->diagnosis }}</td></tr>
</table>
<h3>Presenting Complaints / History</h3><p>{{ $admission->clinical_history ?: 'Not recorded' }}</p>
<h3>Clinical Examination</h3><p>{{ $admission->examination ?: 'Not recorded' }}</p>
<h3>Procedure / Hospital Course</h3><p>{{ $admission->procedure_name ?: 'Not recorded' }} — {{ $admission->treatment_course ?: 'Not recorded' }}</p>

<h3>Birth / Newborn Details</h3>
@if($admission->newborns->isEmpty())
<p><strong>Newborn details:</strong> Not entered — verify before approval.</p>
@else
@foreach($admission->newborns as $i=>$baby)
<table class="table table-sm table-bordered"><tr><th colspan="2">Newborn {{ $i+1 }}</th></tr>
<tr><th>Sex</th><td>{{ $baby->sex ?: 'Not recorded' }}</td></tr>
<tr><th>Birth date/time</th><td>{{ $baby->birth_at?->format('d/m/Y H:i') ?? 'Not recorded' }}</td></tr>
<tr><th>Birth weight (kg)</th><td>{{ $baby->birth_weight_kg ?? 'Not recorded' }}</td></tr>
<tr><th>Apgar 1 / 5 min</th><td>{{ $baby->apgar_1_min ?? 'Not recorded' }} / {{ $baby->apgar_5_min ?? 'Not recorded' }}</td></tr>
<tr><th>Neonatal care</th><td>{{ $baby->neonatal_care ?: 'Not recorded' }}</td></tr>
<tr><th>Notes</th><td>{{ $baby->notes ?: 'Not recorded' }}</td></tr></table>
@endforeach
@endif

<h3>Investigations</h3><p>Investigations field not wired in this starter; verify against source records.</p>
<h3>Condition at Discharge</h3><p>{{ $admission->discharge_condition ?: 'Not recorded' }}</p>
<h3>Medication Orders</h3><p>{{ $admission->medication_orders ?: 'Not recorded' }}</p>
<h3>Advice / Follow-up</h3><p>{{ $admission->discharge_advice ?: 'Not recorded' }}</p><p>{{ $admission->follow_up ?: 'Not recorded' }}</p>
<hr><p>Prepared by: ____________________ &nbsp; Clinician review/signature: ____________________</p>
