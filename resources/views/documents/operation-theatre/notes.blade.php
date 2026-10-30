<div class="operation-theatre-document" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 11pt; line-height: 1.4; color: #000;">
    {{-- Space for Pre-printed Letterhead (First page only) --}}
    <div style="height: 35mm;"></div>

    <div class="text-center mb-4 pt-2">
        <h3 class="fw-bold text-decoration-underline text-uppercase mb-0" style="letter-spacing: 0.5px; font-size: 14pt;">OPERATION THEATRE NOTES</h3>
    </div>

    <style>
        .ot-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .ot-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .ot-lbl {
            font-weight: bold;
            color: #000;
            white-space: nowrap;
        }
        .ot-sep {
            font-weight: bold;
            padding: 0 6px !important;
            width: 15px;
            text-align: center;
        }
        .ot-val {
            color: #000;
        }
        .ot-section-title {
            font-weight: bold;
            color: #000;
            font-size: 10.5pt;
            margin-top: 6px;
            margin-bottom: 2px;
        }
        .ot-narrative {
            font-size: 10.5pt;
            text-align: justify;
            margin-bottom: 6px;
            line-height: 1.45;
        }
        .ot-inline-item {
            display: inline-block;
            margin-right: 25px;
            font-size: 10.5pt;
        }
    </style>

    {{-- Metadata 2-column block --}}
    <table class="ot-table">
        <tr>
            <td class="ot-lbl" style="width: 15%;">NAME</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 35%;">{{ $admission->patient_name }}</td>
            <td class="ot-lbl" style="width: 15%;">IP.NO</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 33%;">{{ $admission->ip_no }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">AGE/SEX</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->age ? $admission->age . ' / ' . ($admission->sex ?? 'Female') : ($admission->sex ?? 'Female') }}</td>
            <td class="ot-lbl">UHID NO</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->uhid }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">CONSULTANT</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->consultant ?: ($admission->surgeon ?: '') }}</td>
            <td class="ot-lbl">PHONE</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->contact ?? '' }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">ICD CODE</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->icd_code ?? '' }}</td>
            <td class="ot-lbl">DATE</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->procedure_at?->format('d/m/Y') ?? ($admission->admission_at?->format('d/m/Y') ?? now()->format('d/m/Y')) }}</td>
        </tr>
    </table>

    {{-- Address & Date of Surgery --}}
    <table class="ot-table mt-1">
        <tr>
            <td class="ot-lbl" style="width: 15%;">ADDRESS</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 83%;">{{ $admission->address ?? '' }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">DATE OF SURGERY</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->procedure_at?->format('d/m/Y h:i a') ?? '' }}</td>
        </tr>
    </table>

    {{-- Diagnosis & Procedure Done --}}
    <table class="ot-table mt-2">
        <tr>
            <td class="ot-lbl" style="width: 15%;">DIAGNOSIS</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 83%;">{{ $admission->diagnosis }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">PROCEDURE DONE</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->procedure_done ?? '' }}</td>
        </tr>
    </table>

    {{-- Surgery & Team Block --}}
    <table class="ot-table mt-2">
        <tr>
            <td class="ot-lbl" style="width: 15%;">SURGERY</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" colspan="4"><strong>{{ $admission->procedure_name ?: 'ELECTIVE LSCS' }}</strong></td>
        </tr>
        <tr>
            <td class="ot-lbl" style="width: 15%;">SURGEON</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 35%;">{{ $admission->surgeon ?: ($admission->consultant ?: '') }}</td>
            <td class="ot-lbl" style="width: 15%;">ANESTHETIST</td>
            <td class="ot-sep">:</td>
            <td class="ot-val" style="width: 33%;">{{ $admission->anaesthetist ?? '' }}</td>
        </tr>
        <tr>
            <td class="ot-lbl">ASSISTANT BY S/N</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->assisted_by ?: ($admission->assistant ?: '') }}</td>
            <td class="ot-lbl">ANESTHESIA TYPE</td>
            <td class="ot-sep">:</td>
            <td class="ot-val">{{ $admission->anaesthesia_type ?: 'Spinal' }}</td>
        </tr>
    </table>

    {{-- Procedure Note & Incision --}}
    <div class="mt-3">
        <div class="ot-section-title">PROCEDURE NOTE :</div>
        @if($admission->procedure_note)
            <div class="ot-narrative">
                {!! nl2br(e($admission->procedure_note)) !!}
            </div>
        @endif
        <div class="ot-section-title mt-2">
            INCISION : @if($admission->incision)<span style="font-weight: normal;">{!! nl2br(e($admission->incision)) !!}</span>@endif
        </div>
    </div>

    {{-- Findings --}}
    <div class="mt-2">
        <div class="ot-section-title">FINDINGS :</div>
        @if($admission->findings)
            <div class="ot-narrative">{!! nl2br(e($admission->findings)) !!}</div>
        @endif
    </div>

    {{-- Complications --}}
    <div class="mt-2">
        <div class="ot-section-title">COMPLICATIONS :</div>
        @if($admission->complications)
            <div class="ot-narrative">{!! nl2br(e($admission->complications)) !!}</div>
        @endif
    </div>

    {{-- Blood Loss, Specimen, Drain (Horizontal across row) --}}
    <table class="ot-table mt-2 mb-2">
        <tr>
            <td style="width: 35%;">
                <span class="ot-lbl">BLOOD LOSS (ML) :</span>
                <span class="ot-val">{{ $admission->blood_loss_ml ? $admission->blood_loss_ml . ' ml' : '' }}</span>
            </td>
            <td style="width: 35%;">
                <span class="ot-lbl">SPECIMEN :</span>
                <span class="ot-val">{{ $admission->specimen ?? '' }}</span>
            </td>
            <td style="width: 30%;">
                <span class="ot-lbl">DRAIN :</span>
                <span class="ot-val">{{ $admission->drain ?? '' }}</span>
            </td>
        </tr>
    </table>

    {{-- Baby Details (Conditional for Delivery) --}}
    @if($admission->isDelivery())
    <table class="ot-table mt-2">
        <tr>
            <td class="ot-lbl" style="width: 15%; vertical-align: top;">BABY</td>
            <td class="ot-sep" style="vertical-align: top;">:</td>
            <td class="ot-val" style="width: 83%;">
                @if($admission->newborns->isNotEmpty())
                    @foreach($admission->newborns as $i => $baby)
                        <div class="mb-1">
                            Alive {{ $baby->sex ?? 'Female' }} baby on {{ $baby->birth_at?->format('d/m/Y @ h.iA') ?? ($admission->procedure_at?->format('d/m/Y @ h.iA') ?? '') }}<br>
                            Baby Weight {{ $baby->birth_weight_kg ? number_format($baby->birth_weight_kg, 3) . 'Kg' : '2.650Kg' }}
                            @if($baby->neonatal_care)
                                <span class="text-muted small">({{ $baby->neonatal_care }})</span>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div>
                        Alive Female baby on {{ $admission->procedure_at?->format('d/m/Y @ h.iA') ?? now()->format('d/m/Y @ h.iA') }}<br>
                        Baby Weight 2.650Kg
                    </div>
                @endif
            </td>
        </tr>
    </table>
    @endif

    {{-- Post-Operative Orders --}}
    <div class="mt-2">
        <div class="ot-section-title">POST-OPERATIVE ORDERS :</div>
        @if($admission->postoperative_orders)
            <div class="ot-narrative">{!! nl2br(e($admission->postoperative_orders)) !!}</div>
        @endif
    </div>

    {{-- Surgeon Seal & Signature --}}
    <div class="text-end mt-5 pt-4" style="page-break-inside: avoid;">
        <span class="fw-bold" style="font-size: 11pt; letter-spacing: 0.5px;">SURGEON SEAL &amp; SIGNATURE</span>
    </div>
</div>
