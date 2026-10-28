<h2 class="text-center">GURU HOSPITAL</h2><h3 class="text-center">OPERATION THEATRE NOTES</h3>
<p class="text-center text-danger"><strong>DRAFT TEMPLATE — verify and obtain surgeon authorisation</strong></p>
<table class="table table-sm table-bordered">
<tr><th>Name</th><td>{{ $admission->patient_name }}</td><th>IP No.</th><td>{{ $admission->ip_no }}</td></tr>
<tr><th>Age/Sex</th><td>{{ $admission->age ?: 'Not recorded' }} / {{ $admission->sex ?: 'Not recorded' }}</td><th>UHID</th><td>{{ $admission->uhid }}</td></tr>
<tr><th>Consultant</th><td>{{ $admission->consultant ?: 'Not recorded' }}</td><th>Date</th><td>{{ $admission->procedure_at?->format('d/m/Y H:i') ?? 'Not recorded' }}</td></tr>
<tr><th>Diagnosis</th><td colspan="3">{{ $admission->diagnosis }}</td></tr>
<tr><th>Procedure / Surgery</th><td colspan="3">{{ $admission->procedure_name ?: 'Not recorded' }}</td></tr>
<tr><th>Surgeon</th><td>{{ $admission->surgeon ?: 'Not recorded' }}</td><th>Anaesthetist</th><td>{{ $admission->anaesthetist ?: 'Not recorded' }}</td></tr>
<tr><th>Assistant by S/N</th><td>{{ $admission->assistant ?: 'Not recorded' }}</td><th>Anaesthesia Type</th><td>{{ $admission->anaesthesia_type ?: 'Not recorded' }}</td></tr>
</table>
<h4>Procedure Note</h4><p>{{ $admission->procedure_note ?: 'Not recorded' }}</p>
<h4>Incision</h4><p>{{ $admission->incision ?: 'Not recorded' }}</p>
<h4>Findings</h4><p>{{ $admission->findings ?: 'Not recorded' }}</p>
<h4>Complications</h4><p>{{ $admission->complications ?: 'Not recorded' }}</p>
<table class="table table-sm table-bordered"><tr><th>Blood Loss (mL)</th><td>{{ $admission->blood_loss_ml ?? 'Not recorded' }}</td><th>Specimen</th><td>{{ $admission->specimen ?: 'Not recorded' }}</td><th>Drain</th><td>{{ $admission->drain ?: 'Not recorded' }}</td></tr></table>
@if($admission->isDelivery())
<h4>Baby / Birth Details</h4>
@if($admission->newborns->isEmpty())<p>Newborn details not entered — verify before approval.</p>@else
@foreach($admission->newborns as $i=>$baby)<p><strong>Newborn {{ $i+1 }}:</strong> Sex {{ $baby->sex ?: 'Not recorded' }}; Birth {{ $baby->birth_at?->format('d/m/Y H:i') ?? 'Not recorded' }}; Weight {{ $baby->birth_weight_kg ?? 'Not recorded' }} kg; Apgar {{ $baby->apgar_1_min ?? 'Not recorded' }}/{{ $baby->apgar_5_min ?? 'Not recorded' }}; Neonatal care: {{ $baby->neonatal_care ?: 'Not recorded' }}.</p>@endforeach
@endif
@endif
<h4>Post-operative Orders</h4><p>{{ $admission->postoperative_orders ?: 'Not recorded' }}</p>
<p class="mt-5">SURGEON SEAL & SIGNATURE: __________________________________________</p>
