@extends('layouts.app')
@section('title','New Admission — Guru Hospital')
@section('content')
<h1 class="h3 mb-1">New admission / document pack</h1>
<p class="text-muted">Enter shared details once. Save as draft, then review the discharge summary, OT notes and MRD form together.</p>
<form method="POST" action="{{ route('admissions.store') }}" id="admissionForm">
@csrf
<div class="card mb-3"><div class="card-header bg-white fw-bold">1. Shared patient and admission details</div><div class="card-body"><div class="row g-3">
@php($shared = [
 ['patient_name','Patient name','text',true],['uhid','UHID / MRN','text',true],['ip_no','IP No.','text',true],
 ['age','Age','text',false],['sex','Sex','select',false],['address','Address','text',false],['contact','Contact','text',false],
 ['admission_at','Admission date/time','datetime-local',true],['procedure_at','Procedure / delivery date/time','datetime-local',false],
 ['discharge_at','Discharge date/time','datetime-local',true],['consultant','Consultant','text',false],['department','Department','text',false]
])
@foreach($shared as [$name,$label,$type,$required])
<div class="col-md-4"><label class="form-label {{ $required?'required':'' }}">{{ $label }}</label>
@if($type==='select')
<select name="{{ $name }}" class="form-select"><option value="">Select</option>@foreach(['Female','Male','Other','Not recorded'] as $v)<option value="{{ $v }}" @selected(old($name)===$v)>{{ $v }}</option>@endforeach</select>
@else
<input class="form-control" name="{{ $name }}" type="{{ $type }}" value="{{ old($name) }}" {{ $required?'required':'' }}>
@endif</div>
@endforeach
<div class="col-md-6"><label class="form-label required">Discharge summary format</label><select name="format" class="form-select" required>@foreach(['general'=>'General','cmchistn'=>'CMCHISTN','private'=>'Private','employee'=>'Employee'] as $v=>$label)<option value="{{ $v }}" @selected(old('format','general')===$v)>{{ $label }}</option>@endforeach</select><div class="form-text">Layouts are draft placeholders until the hospital supplies/approves each exact format.</div></div>
<div class="col-md-6"><label class="form-label required">Surgery workflow</label><select name="surgery_type" id="surgeryType" class="form-select" required><option value="delivery" @selected(old('surgery_type','delivery')==='delivery')>Delivery / Obstetric</option><option value="other" @selected(old('surgery_type')==='other')>Other surgery</option></select></div>
<div class="col-12"><label class="form-label required">Diagnosis</label><textarea name="diagnosis" class="form-control" rows="2" required>{{ old('diagnosis') }}</textarea></div>
</div></div></div>
<div class="card mb-3"><div class="card-header bg-white fw-bold">2. Discharge summary clinical details</div><div class="card-body row g-3">
@foreach(['clinical_history'=>'Presenting complaints / relevant history','examination'=>'Clinical examination / vital signs','treatment_course'=>'Treatment and hospital course','discharge_condition'=>'Condition at discharge','medication_orders'=>'Authorised discharge medication orders','discharge_advice'=>'Discharge advice / warning signs','follow_up'=>'Follow-up'] as $name=>$label)
<div class="col-md-6"><label class="form-label">{{ $label }}</label><textarea name="{{ $name }}" class="form-control" rows="2">{{ old($name) }}</textarea></div>
@endforeach
<div class="col-12"><label class="form-label">Investigations (Lab Reports, Pathology & Diagnostics)</label><textarea name="investigations" class="form-control" rows="4" placeholder="e.g.&#10;Hb: 10.6mgs%&#10;HIV: Negative | VDRL: Negative | HBsAG: Negative&#10;BG: B positive&#10;ECG: Normal&#10;RBS: 101.6 mg/dl | Urea: 24.3 mg/dl | Creatinine: 0.56 mg/dl | Uric Acid: 2.69 mg/dl">{{ old('investigations') }}</textarea></div>
</div></div>
<div class="card mb-3"><div class="card-header bg-white fw-bold">3. Operation Theatre Notes</div><div class="card-body row g-3">
<div class="col-md-4"><label class="form-label">Procedure / surgery</label><input class="form-control" name="procedure_name" value="{{ old('procedure_name') }}"></div>
<div class="col-md-4"><label class="form-label">Surgeon</label><input class="form-control" name="surgeon" value="{{ old('surgeon') }}"></div>
<div class="col-md-4"><label class="form-label">Consultant Pediatrician</label><input class="form-control" name="pediatrician_name" value="{{ old('pediatrician_name') }}" placeholder="e.g. Dr. Nithyapriya (Paediatrician)"></div>
<div class="col-md-4"><label class="form-label">Anaesthetist</label><input class="form-control" name="anaesthetist" value="{{ old('anaesthetist') }}"></div>
<div class="col-md-4"><label class="form-label">Assistant by S/N</label><input class="form-control" name="assisted_by" value="{{ old('assisted_by') }}" placeholder="e.g. Miss Soudnharya"></div>
<div class="col-md-4"><label class="form-label">Anaesthesia type</label><input class="form-control" name="anaesthesia_type" value="{{ old('anaesthesia_type') }}" placeholder="e.g. SA, GA"></div>
@foreach(['procedure_note'=>'Procedure note','incision'=>'Incision','findings'=>'Findings','complications'=>'Complications','blood_loss_ml'=>'Blood loss (mL)','specimen'=>'Specimen','drain'=>'Drain','postoperative_orders'=>'Post-operative orders'] as $name=>$label)
<div class="{{ in_array($name,['procedure_note','incision','findings','complications','postoperative_orders'])?'col-md-6':'col-md-4' }}"><label class="form-label">{{ $label }}</label>
@if(in_array($name,['procedure_note','incision','findings','complications','postoperative_orders']))<textarea class="form-control" rows="2" name="{{ $name }}">{{ old($name) }}</textarea>@else<input class="form-control" name="{{ $name }}" value="{{ old($name) }}" type="{{ $name==='blood_loss_ml'?'number':'text' }}" {{ $name==='blood_loss_ml'?'min=0 step=0.1':'' }}>@endif
</div>
@endforeach
</div></div>
<div class="card mb-3" id="deliveryFields"><div class="card-header bg-white fw-bold">4. Birth / newborn details — delivery only</div><div class="card-body">
<p class="small text-muted">Add one row per newborn; for twins, enter two separate newborn records. Do not infer missing values.</p>
<div id="newbornRows">
@php($newborns=old('newborns',[[]]))
@foreach($newborns as $i=>$baby)
<div class="border rounded p-3 mb-3 newborn-row"><div class="d-flex justify-content-between"><strong>Newborn <span class="baby-number">{{ $i+1 }}</span></strong><button type="button" class="btn btn-sm btn-outline-danger remove-baby no-print">Remove</button></div><div class="row g-2 mt-1">
<div class="col-md-2"><label class="form-label">Sex</label><select class="form-select" name="newborns[{{ $i }}][sex]"><option value="">Select</option>@foreach(['Female','Male','Other','Not recorded'] as $v)<option @selected(($baby['sex']??'')===$v)>{{ $v }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Birth date/time</label><input class="form-control" type="datetime-local" name="newborns[{{ $i }}][birth_at]" value="{{ $baby['birth_at']??'' }}"></div>
<div class="col-md-2"><label class="form-label">Weight (kg)</label><input class="form-control" type="number" step="0.01" min="0.1" max="8" name="newborns[{{ $i }}][birth_weight_kg]" value="{{ $baby['birth_weight_kg']??'' }}"></div>
<div class="col-md-2"><label class="form-label">Apgar 1 min</label><input class="form-control" type="number" min="0" max="10" name="newborns[{{ $i }}][apgar_1_min]" value="{{ $baby['apgar_1_min']??'' }}"></div>
<div class="col-md-2"><label class="form-label">Apgar 5 min</label><input class="form-control" type="number" min="0" max="10" name="newborns[{{ $i }}][apgar_5_min]" value="{{ $baby['apgar_5_min']??'' }}"></div>
<div class="col-md-6"><label class="form-label">Neonatal care</label><textarea class="form-control" rows="2" name="newborns[{{ $i }}][neonatal_care]">{{ $baby['neonatal_care']??'' }}</textarea></div>
<div class="col-md-6"><label class="form-label">Other documented notes</label><textarea class="form-control" rows="2" name="newborns[{{ $i }}][notes]">{{ $baby['notes']??'' }}</textarea></div>
</div></div>
@endforeach
</div><button type="button" class="btn btn-outline-primary no-print" id="addBaby">+ Add newborn</button>
</div></div>
<div class="card mb-3"><div class="card-header bg-white fw-bold">5. MRD fields</div><div class="card-body row g-3">
<div class="col-md-4"><label class="form-label">MRD No.</label><input class="form-control" name="mrd_no" value="{{ old('mrd_no') }}"></div>
<div class="col-md-4"><label class="form-label">Payment mode</label><input class="form-control" name="payment_mode" value="{{ old('payment_mode') }}"></div>
<p class="text-muted small mb-0">Page count is calculated only after the final pack PDF is rendered. Signature fields must be completed by the responsible staff and are not simulated by this form.</p>
</div></div>
<div class="d-flex gap-2 no-print"><button class="btn btn-primary btn-lg" type="submit">Save draft & preview three documents</button></div>
</form>
<template id="newbornTemplate">
<div class="border rounded p-3 mb-3 newborn-row"><div class="d-flex justify-content-between"><strong>Newborn <span class="baby-number"></span></strong><button type="button" class="btn btn-sm btn-outline-danger remove-baby no-print">Remove</button></div><div class="row g-2 mt-1">
<div class="col-md-2"><label class="form-label">Sex</label><select class="form-select" name="newborns[__INDEX__][sex]"><option value="">Select</option><option>Female</option><option>Male</option><option>Other</option><option>Not recorded</option></select></div>
<div class="col-md-3"><label class="form-label">Birth date/time</label><input class="form-control" type="datetime-local" name="newborns[__INDEX__][birth_at]"></div>
<div class="col-md-2"><label class="form-label">Weight (kg)</label><input class="form-control" type="number" step="0.01" min="0.1" max="8" name="newborns[__INDEX__][birth_weight_kg]"></div>
<div class="col-md-2"><label class="form-label">Apgar 1 min</label><input class="form-control" type="number" min="0" max="10" name="newborns[__INDEX__][apgar_1_min]"></div>
<div class="col-md-2"><label class="form-label">Apgar 5 min</label><input class="form-control" type="number" min="0" max="10" name="newborns[__INDEX__][apgar_5_min]"></div>
<div class="col-md-6"><label class="form-label">Neonatal care</label><textarea class="form-control" rows="2" name="newborns[__INDEX__][neonatal_care]"></textarea></div>
<div class="col-md-6"><label class="form-label">Other documented notes</label><textarea class="form-control" rows="2" name="newborns[__INDEX__][notes]"></textarea></div>
</div></div>
</template>
<script>
(() => {
 const type=document.getElementById('surgeryType'), fields=document.getElementById('deliveryFields');
 function toggle(){ fields.hidden = type.value !== 'delivery'; }
 type.addEventListener('change',toggle); toggle();
 const list=document.getElementById('newbornRows'), tmpl=document.getElementById('newbornTemplate');
 function renumber(){ [...list.querySelectorAll('.newborn-row')].forEach((r,i)=>r.querySelector('.baby-number').textContent=i+1); }
 document.getElementById('addBaby').addEventListener('click',()=>{
   const idx=Date.now(); const wrapper=document.createElement('div');
   wrapper.innerHTML=tmpl.innerHTML.replaceAll('__INDEX__',idx);
   list.appendChild(wrapper.firstElementChild); renumber();
 });
 list.addEventListener('click',e=>{ if(e.target.classList.contains('remove-baby')){e.target.closest('.newborn-row').remove();renumber();} });
})();
</script>
@endsection
