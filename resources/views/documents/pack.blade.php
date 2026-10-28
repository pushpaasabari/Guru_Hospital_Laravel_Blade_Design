@extends('layouts.app')
@section('title','Review Document Pack')
@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 no-print"><div><h1 class="h3">Review document pack</h1><p class="text-muted mb-0">Draft — not approved for issue.</p></div><button class="btn btn-outline-secondary" onclick="window.print()">Print / Save as PDF</button></div>
<div class="alert alert-warning no-print">This browser print view is a draft preview. The final PDF endpoint remains disabled until a PDF engine and clinician-approval workflow are configured.</div>
<div class="document page-break">
    @include('documents.discharge-summaries.' . $admission->format . '.'.($admission->surgery_type === 'delivery' ? 'delivery' : 'other'), ['admission'=>$admission])
</div>
<div class="document page-break">@include('documents.operation-theatre.notes',['admission'=>$admission])</div>
<div class="document page-break">@include('documents.mrd.form',['admission'=>$admission])</div>
<style>
.document{background:#fff;padding:18mm;margin:0 auto 18px;max-width:210mm;min-height:270mm;border:1px solid #d6dce5}
.page-break{break-before:page}
@media print{body{background:#fff}.document{border:0;margin:0;padding:12mm;max-width:none;min-height:0;box-shadow:none}.page-break{break-before:page}.no-print{display:none!important}}
</style>
@endsection
