<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Guru Hospital — Complete Day Report ({{ $periodLabel }})</title>
    <style>
        @if(is_file(public_path('fonts/OpenSans-Regular.ttf')))
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(public_path('fonts/OpenSans-Regular.ttf'))) }}') format('truetype');
        }
        @endif
        @if(is_file(public_path('fonts/OpenSans-Bold.ttf')))
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 700;
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(public_path('fonts/OpenSans-Bold.ttf'))) }}') format('truetype');
        }
        @endif
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            font-family: 'Open Sans', 'Segoe UI', Arial, sans-serif;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .report-sheet {
            width: 1580px;
            background: #ffffff;
            padding: 24px 28px 22px 28px;
        }
        .header-band {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f5c63;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-logo {
            height: 46px;
            width: auto;
            object-fit: contain;
        }
        .hospital-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f5c63;
            letter-spacing: -0.01em;
            margin: 0 0 2px 0;
        }
        .report-subtitle {
            font-size: 13px;
            color: #475569;
            margin: 0;
        }
        .header-right {
            text-align: right;
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }
        .period-pill {
            display: inline-block;
            background: #e6f4f5;
            color: #0f5c63;
            border: 1px solid #b8dde0;
            border-radius: 6px;
            padding: 3px 10px;
            font-weight: 700;
            font-size: 12.5px;
            margin-bottom: 3px;
        }
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .kpi-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            background: #f8fafc;
        }
        .kpi-lbl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .kpi-val {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }
        table.day-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11.5px;
        }
        table.day-table th {
            background: #0f5c63;
            color: #ffffff;
            text-align: left;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 8px 8px;
            border: 1px solid #0d4f55;
            vertical-align: middle;
        }
        table.day-table td {
            padding: 8px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            line-height: 1.4;
        }
        table.day-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .col-sno { width: 44px; text-align: center; }
        .col-patient { width: 195px; }
        .col-doa { width: 88px; }
        .col-doo { width: 115px; }
        .col-dod { width: 96px; }
        .col-proc { width: 235px; }
        .col-team { width: 270px; }
        .col-delivery { width: 480px; }

        .pt-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .meta-line {
            font-size: 11px;
            color: #475569;
        }
        .mono {
            font-family: Consolas, 'Courier New', monospace;
            color: #0f172a;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-info {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .badge-boy {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .badge-girl {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-neutral {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .team-row {
            margin-bottom: 2px;
        }
        .team-lbl {
            color: #64748b;
            font-weight: 700;
        }
        .delivery-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 5px;
            padding: 4px 7px;
            margin-bottom: 5px;
        }
        .baby-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 4px 7px;
            margin-bottom: 4px;
        }
        .baby-box:last-child {
            margin-bottom: 0;
        }
        .footer-bar {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="report-sheet">
    <div class="header-band">
        <div class="header-left">
            @if(!empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="Guru Hospital" class="header-logo" />
            @endif
            <div>
                <h1 class="hospital-title">GURU HOSPITAL — DAY REPORT</h1>
                <p class="report-subtitle">Complete Daily Clinical, Operative, Discharge &amp; Obstetric Delivery Register</p>
            </div>
        </div>
        <div class="header-right">
            <div class="period-pill">{{ $periodLabel }} &bull; Basis: {{ $basisLabel }}</div>
            <div>Generated: {{ now('Asia/Kolkata')->format('d.m.Y h:i A') }} IST &bull; Total Records: <strong>{{ $metrics['total_cases'] }}</strong></div>
        </div>
    </div>

    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-lbl">Total Cases</div>
            <div class="kpi-val">{{ $metrics['total_cases'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Surgical OT</div>
            <div class="kpi-val" style="color: #b45309;">{{ $metrics['surgeries_count'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Deliveries</div>
            <div class="kpi-val" style="color: #15803d;">{{ $metrics['deliveries_count'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Newborn Babies</div>
            <div class="kpi-val" style="color: #0369a1;">{{ $metrics['babies_count'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Discharged / Medical</div>
            <div class="kpi-val" style="color: #0f5c63;">{{ $metrics['discharges_count'] }} <span style="font-size: 12px; font-weight: 400; color: #64748b;">/ {{ $metrics['medical_count'] }} Med</span></div>
        </div>
    </div>

    <table class="day-table">
        <thead>
            <tr>
                <th class="col-sno" style="text-align: center;">S.No</th>
                <th class="col-patient">Name / UHID / IPNO</th>
                <th class="col-doa">DOA</th>
                <th class="col-doo">DOO</th>
                <th class="col-dod">DOD</th>
                <th class="col-proc">Procedure Done &amp; Incision</th>
                <th class="col-team">Surgical &amp; Anesthesia Team</th>
                <th class="col-delivery">Delivery &amp; Baby Details (If Delivery)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $item)
                @php
                    $isDelivery = $item->surgery_type === 'delivery' || $item->newborns->isNotEmpty();
                    $procLabel = $item->procedure_done
                        ?: ($item->procedure_name ?: ($item->surgery_type === 'medical'
                            ? 'Medical Case' . ($item->diagnosis ? ': ' . $item->diagnosis : '')
                            : '—'));
                @endphp
                <tr>
                    <td class="col-sno" style="font-weight: 700;">{{ $idx + 1 }}</td>
                    <td class="col-patient">
                        <div class="pt-name">{{ $item->patient_name }}</div>
                        <div class="meta-line">UHID: <span class="mono">{{ $item->uhid }}</span></div>
                        <div class="meta-line">IPNO: <span class="mono">{{ $item->ip_no }}</span></div>
                        @if($item->age || $item->sex)
                            <div class="meta-line">{{ collect([$item->age ? $item->age . ' Yrs' : null, $item->sex])->filter()->implode(' • ') }}</div>
                        @endif
                    </td>
                    <td class="col-doa">{{ $item->admission_at ? $item->admission_at->format('d.m.Y') : '—' }}</td>
                    <td class="col-doo">{{ $item->procedure_at ? $item->procedure_at->format('d.m.Y h:i A') : '—' }}</td>
                    <td class="col-dod">
                        @if($item->discharge_at)
                            <strong>{{ $item->discharge_at->format('d.m.Y') }}</strong>
                        @else
                            <span class="badge badge-info">In Admission</span>
                        @endif
                    </td>
                    <td class="col-proc">
                        <div style="font-weight: 700; color: #0f172a;">{{ $procLabel }}</div>
                        @if($item->incision)
                            <div class="meta-line" style="margin-top: 2px;"><strong>Incision:</strong> {{ $item->incision }}</div>
                        @endif
                    </td>
                    <td class="col-team">
                        @if($item->surgeon)
                            <div class="team-row"><span class="team-lbl">Surgeon:</span> {{ $item->surgeon }}</div>
                        @endif
                        @if($item->assisted_surgeon)
                            <div class="team-row"><span class="team-lbl">Asst. Surgeon:</span> {{ $item->assisted_surgeon }}</div>
                        @endif
                        @if($item->consultant)
                            <div class="team-row"><span class="team-lbl">Consultant:</span> {{ $item->consultant }}</div>
                        @endif
                        @if($item->pediatrician_name)
                            <div class="team-row"><span class="team-lbl">Pediatrician:</span> {{ $item->pediatrician_name }}</div>
                        @endif
                        @if($item->anaesthetist)
                            <div class="team-row">
                                <span class="team-lbl">Anaesthetist:</span> {{ $item->anaesthetist }}
                                @if($item->assisted_anaesthetist) (Asst: {{ $item->assisted_anaesthetist }}) @endif
                            </div>
                        @endif
                        @if($item->anaesthesia_type)
                            <div class="team-row"><span class="badge badge-neutral">{{ $item->anaesthesia_type }}</span></div>
                        @endif
                        @if($item->staff_nurse || $item->ot_technician)
                            <div class="meta-line">
                                {{ collect([
                                    $item->staff_nurse ? 'Nurse: ' . $item->staff_nurse : null,
                                    $item->ot_technician ? 'OT Tech: ' . $item->ot_technician : null,
                                ])->filter()->implode(' | ') }}
                            </div>
                        @endif
                        @if(!$item->surgeon && !$item->consultant && !$item->anaesthetist)
                            <span class="meta-line">—</span>
                        @endif
                    </td>
                    <td class="col-delivery">
                        @if(!$isDelivery)
                            <span class="meta-line">—</span>
                        @else
                            <div class="delivery-box">
                                <strong style="color: #15803d;">Delivery: {{ $item->procedure_done ?: ($item->procedure_name ?: 'Obstetric Delivery') }}</strong>
                                @if($item->picme_no)
                                    <div class="meta-line">PICME / RCH ID: <span class="mono">{{ $item->picme_no }}</span></div>
                                @endif
                            </div>
                            @if($item->newborns->isNotEmpty())
                                @foreach($item->newborns as $bIdx => $baby)
                                    @php
                                        $sexLower = strtolower(trim((string) $baby->sex));
                                        $isBoy = in_array($sexLower, ['male', 'boy'], true);
                                        $isGirl = in_array($sexLower, ['female', 'girl'], true);
                                        $badgeCls = $isBoy ? 'badge-boy' : ($isGirl ? 'badge-girl' : 'badge-neutral');
                                        $sexLbl = $isBoy ? 'Boy' : ($isGirl ? 'Girl' : ($baby->sex ?: 'Not Recorded'));
                                        $formattedVaccines = \App\Models\Newborn::formatBirthVaccinesText(
                                            $baby->birth_vaccines,
                                            $baby->vaccine_dates,
                                            $baby->vaccine_date
                                        );
                                    @endphp
                                    <div class="baby-box">
                                        <span class="badge {{ $badgeCls }}">{{ $sexLbl }}</span>
                                        @if($baby->birth_at)
                                            <span class="meta-line" style="margin-left: 4px;">{{ $baby->birth_at->format('d.m.Y h:i A') }}</span>
                                        @endif
                                        <div style="margin-top: 2px;">
                                            <strong>Birth Wt:</strong> {{ $baby->birth_weight_kg ? number_format((float) $baby->birth_weight_kg, 2) . ' kg' : '—' }}
                                            @if($baby->discharge_weight_kg)
                                                &nbsp;|&nbsp; <strong>Disch Wt:</strong> {{ number_format((float) $baby->discharge_weight_kg, 2) }} kg
                                            @endif
                                            @if($baby->apgar_1_min !== null || $baby->apgar_5_min !== null)
                                                &nbsp;|&nbsp; <strong>APGAR:</strong> {{ $baby->apgar_1_min ?? '—' }}/{{ $baby->apgar_5_min ?? '—' }}
                                            @endif
                                        </div>
                                        @if($baby->neonatal_care)
                                            <div class="meta-line"><strong>Care:</strong> {{ $baby->neonatal_care }}</div>
                                        @endif
                                        @if($baby->notes)
                                            <div style="color: #0f172a;"><strong>Notes / Presentation:</strong> {{ $baby->notes }}</div>
                                        @endif
                                        @if($formattedVaccines !== '')
                                            <div style="color: #15803d;">
                                                <strong>Vaccines:</strong> {{ $formattedVaccines }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span class="meta-line">Newborn details not recorded</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 28px; color: #64748b;">
                        No clinical records matched the selected day filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-bar">
        <div>Guru Hospital — Complete Day Report Snapshot</div>
        <div>Showing all {{ $records->count() }} matching record(s)</div>
    </div>
</div>
</body>
</html>
