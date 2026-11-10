<div class="discharge-summary-document surgical-doc" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 9pt; line-height: 1.35; color: #000;">
    <style>
        .surgical-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
            border: 1px solid #334155;
        }
        .surgical-table th, .surgical-table td {
            border: 1px solid #64748b;
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .surgical-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
        }
    </style>

    {{-- Document Title --}}
    <div style="text-align: center; font-size: 15pt; font-weight: bold; color: #111; letter-spacing: 0.5px; margin: 0 0 10px 0; text-transform: uppercase;">
        DISCHARGE SUMMARY
    </div>

    {{-- Top Demographics Grid --}}
    <table class="surgical-table">
        <tr>
            <td style="width: 12%; font-weight: bold; background: #f8fafc;">NAME</td>
            <td style="width: 44%; font-weight: bold; font-size: 9.5pt;">{{ strtoupper($admission->patient_name) }}</td>
            <td style="width: 10%; font-weight: bold; text-align: center;">{{ $admission->age }}</td>
            <td style="width: 6%; font-weight: bold; text-align: center;">{{ $admission->sex ? strtoupper(substr($admission->sex, 0, 1)) : '—' }}</td>
            <td style="width: 14%; font-weight: bold; background: #f8fafc;">CONSULTANT</td>
            <td style="width: 14%; font-weight: bold;">{{ strtoupper($admission->consultant ?: ($admission->surgeon ?: '—')) }}</td>
        </tr>
    </table>

    <table class="surgical-table">
        <tr>
            <td style="width: 14%; font-weight: bold; background: #f8fafc; vertical-align: top;">ADDRESS</td>
            <td style="width: 36%; vertical-align: top;">
                {{ str_replace(["\r\n", "\r", "\n"], ' ', $admission->address ?? '—') }}
            </td>
            <td style="width: 18%; font-weight: bold; background: #f8fafc; vertical-align: top;">FINAL DIAGNOSIS</td>
            <td style="width: 32%; font-weight: bold; vertical-align: top;">
                {{ strtoupper($admission->diagnosis) }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">PHONE</td>
            <td>{{ $admission->contact ?? '—' }}</td>
            <td style="font-weight: bold; background: #f8fafc;">PROCEDURE DONE</td>
            <td style="font-weight: bold;">{{ strtoupper($admission->procedure_done ?: ($admission->procedure_name ?: '—')) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">MRD No</td>
            <td>{{ $admission->mrd_no ?: '—' }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background: #ffffff; border: none; padding: 0;"></td>
            <td style="font-weight: bold; background: #f8fafc;">IP NO :</td>
            <td style="font-weight: bold;">{{ $admission->ip_no }}</td>
        </tr>
    </table>

    {{-- Dates Row --}}
    <table class="surgical-table">
        <tr>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOA</td>
            <td style="width: 23%;">{{ $admission->admission_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOO</td>
            <td style="width: 23%;">{{ $admission->procedure_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
            <td style="width: 10%; font-weight: bold; background: #f8fafc;">DOD</td>
            <td style="width: 24%;">{{ $admission->discharge_at?->format('d.m.Y \a\t h.i A') ?? '—' }}</td>
        </tr>
    </table>

    {{-- PRESENTING COMPLAINTS --}}
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">PRESENTING COMPLAINTS</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt;">
                {{ $admission->clinical_history ? strtok($admission->clinical_history, "\n") : '—' }}
            </td>
        </tr>
    </table>

    {{-- PAST MEDICAL & SURGICAL HISTORY --}}
    @php
        $hist = $admission->formatted_surgical_past_history;
    @endphp
    <table class="surgical-table">
        <tr>
            <th style="width: 60%; background: #f1f5f9; text-align: left; font-size: 8.5pt;">PAST MEDICAL HISTORY</th>
            <th style="width: 40%; background: #f1f5f9; text-align: left; font-size: 8.5pt;">PAST SURGICAL HISTORY</th>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">DM</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">HT</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 20%;">ASTHMA</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 25%;">TUBERCULOSIS</td>
                        <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold; width: 15%;">Other</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['dm'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['ht'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['asthma'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['tb'] }}</td>
                        <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $hist['other'] }}</td>
                    </tr>
                </table>
            </td>
            <td style="padding: 4px 6px; font-size: 8.5pt; vertical-align: top;">
                {{ $hist['past_surgical'] }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">ALLERGIES:</span> {{ $hist['allergies'] }}
            </td>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">MEDICATIONS:</span> {{ $hist['medications'] }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">PULSE:</span> {{ $hist['pulse'] }}
            </td>
            <td style="padding: 3px 6px;">
                <span style="font-weight: bold; font-size: 7.5pt; margin-right: 6px;">BP:</span> {{ $hist['bp'] }}
            </td>
        </tr>
    </table>

    {{-- CLINICAL EXAMINATION --}}
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">CLINICAL EXAMINATION</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt;">
                {{ $admission->examination ?: '—' }}
            </td>
        </tr>
    </table>

    {{-- INVESTIGATIONS --}}
    @php
        $inv = $admission->formatted_surgical_investigations;
    @endphp
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="2">INVESTIGATIONS</th>
        </tr>
        <tr>
            <td style="width: 55%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold;">{{ $k }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group1'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td style="width: 45%; padding: 4px 6px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 7.5pt; font-weight: bold;">{{ $k }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($inv['group2'] as $k => $v)
                            <td style="border: none; padding: 1px 2px; font-size: 8.5pt;">{{ $v }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
        @if(!empty($inv['imaging']))
            @foreach($inv['imaging'] as $imgKey => $imgVal)
                <tr>
                    <td style="font-weight: bold; background: #f8fafc; font-size: 8pt; width: 25%;">{{ $imgKey }}</td>
                    <td style="font-weight: bold; font-size: 8.5pt;">{{ $imgVal }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    {{-- THEATER NOTES --}}
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="4">THEATER NOTES</th>
        </tr>
        <tr>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">SURGERY</td>
            <td colspan="3" style="font-weight: bold;">{{ strtoupper($admission->procedure_name ?: ($admission->procedure_done ?: '—')) }}</td>
        </tr>
        <tr>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">DATE</td>
            <td style="width: 35%;">{{ $admission->procedure_at?->format('d.m.Y @ h.iA') ?? '—' }}</td>
            <td style="width: 15%; font-weight: bold; background: #f8fafc;">ANAESTHESIA</td>
            <td style="width: 35%;">{{ $admission->anaesthesia_type ?: '—' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">SURGEON</td>
            <td>{{ strtoupper($admission->surgeon ?: '—') }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ANESTHETIST</td>
            <td>{{ strtoupper($admission->anaesthetist ?: '—') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">ASSISTED BY</td>
            <td>{{ strtoupper($admission->assisted_by ?: ($admission->assistant ?: '—')) }}</td>
            <td style="font-weight: bold; background: #f8fafc;">ICD CODE</td>
            <td>{{ $admission->icd_code ?: '—' }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 6px 8px; font-size: 8.5pt; line-height: 1.4; text-align: justify;">
                {!! nl2br(e($admission->procedure_note)) !!}
            </td>
        </tr>
    </table>

    {{-- COURSE IN THE HOSPITAL --}}
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;">COURSE IN THE HOSPITAL</th>
        </tr>
        <tr>
            <td style="padding: 5px 8px; font-size: 8.8pt; line-height: 1.4; text-align: justify;">
                {!! $admission->treatment_course ? nl2br(e($admission->treatment_course)) : '—' !!}
            </td>
        </tr>
    </table>

    {{-- CONDITION AT DISCHARGE --}}
    <table class="surgical-table">
        <tr>
            <td style="width: 25%; font-weight: bold; background: #f1f5f9; font-size: 8.5pt;">CONDITION AT DISCHARGE</td>
            <td style="width: 75%; font-weight: bold; font-size: 9pt;">{{ strtoupper($admission->discharge_condition ? strtok($admission->discharge_condition, "\n-:") : '—') }}</td>
        </tr>
    </table>

    {{-- DISCHARGE ADVICE --}}
    @php
        $surgDrugs = $admission->formatted_surgical_drugs;
        $surgAdvice = $admission->formatted_surgical_advice;
    @endphp
    <table class="surgical-table">
        <tr>
            <th style="background: #f1f5f9; text-align: left; font-size: 8.5pt;" colspan="4">DISCHARGE ADVICE</th>
        </tr>
        @if(count($surgDrugs) > 0)
            <tr>
                <td rowspan="{{ count($surgDrugs) + 1 }}" style="width: 12%; font-weight: bold; background: #f8fafc; vertical-align: top; text-align: center;">DRUGS</td>
                <td style="width: 48%; font-weight: bold; font-size: 8pt; background: #f8fafc;">DRUG</td>
                <td style="width: 25%; font-weight: bold; font-size: 8pt; background: #f8fafc;">DOSAGE</td>
                <td style="width: 15%; font-weight: bold; font-size: 8pt; background: #f8fafc; text-align: center;">NO</td>
            </tr>
            @foreach($surgDrugs as $drug)
                <tr>
                    <td style="font-size: 8.8pt; font-weight: 500;">{{ $drug['drug'] }}</td>
                    <td style="font-size: 8.8pt;">{{ $drug['dosage'] }}</td>
                    <td style="font-size: 8.8pt; text-align: center;">{{ $drug['no'] }}</td>
                </tr>
            @endforeach
        @endif
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">IMPLANT</td>
            <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['implant'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">DIET</td>
            <td colspan="3" style="font-size: 8.8pt;">{{ $surgAdvice['diet'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">EXERCISE</td>
            <td colspan="3" style="font-size: 8.8pt;">{{ $surgAdvice['exercise'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">EMERGENCY CONTACT</td>
            <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['emergency_contact'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background: #f8fafc;">WHEN TO CONTACT</td>
            <td colspan="3" style="font-size: 8.8pt; color: #b91c1c; font-weight: 600;">{{ $surgAdvice['when_to_contact'] }}</td>
        </tr>
        @if(!empty($surgAdvice['review']))
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">REVIEW</td>
                <td colspan="3" style="font-weight: bold; font-size: 8.8pt;">{{ $surgAdvice['review'] }}</td>
            </tr>
        @endif
        @if(!empty($surgAdvice['tamil_note']))
            <tr>
                <td style="background: #f8fafc;"></td>
                <td colspan="3" style="font-size: 8.5pt; font-family: 'Nirmala UI', 'Nirmala', 'Arial Unicode MS', sans-serif; line-height: 1.4; color: #1e293b;">
                    {{ $surgAdvice['tamil_note'] }}
                </td>
            </tr>
        @endif
    </table>

    {{-- Signatures --}}
    <table class="surgical-table" style="margin-top: 15px; border: none;">
        <tr>
            <td style="width: 50%; border: none; padding: 10px 8px 0 8px;">
                <div style="font-size: 8pt; font-weight: bold; color: #475569;">PREPARED BY</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000; margin-top: 2px;">
                    {{ strtoupper($admission->handover_name ?: ($admission->consultant ?: ($admission->surgeon ?: '—'))) }}
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 10px 8px 0 8px; text-align: right;">
                <div style="font-size: 8pt; font-weight: bold; color: #475569;">COUNTERSIGNED BY</div>
                <div style="font-size: 9.5pt; font-weight: bold; color: #000; margin-top: 2px;">
                    {{ strtoupper($admission->consultant ?: ($admission->surgeon ?: '—')) }}
                </div>
            </td>
        </tr>
    </table>
</div>
