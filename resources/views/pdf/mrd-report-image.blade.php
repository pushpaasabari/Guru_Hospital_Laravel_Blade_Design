<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Guru Hospital — Complete MRD Report</title>
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
        @if(!empty($isPdf))
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 14mm 10mm;
        }
        @endif
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: {{ !empty($isPdf) ? '#ffffff' : '#f8fafc' }};
            font-family: 'Open Sans', 'Segoe UI', Arial, sans-serif;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
        .report-sheet {
            width: {{ !empty($isPdf) ? '100%' : '1580px' }};
            background: #ffffff;
            padding: {{ !empty($isPdf) ? '0' : '24px 28px 22px 28px' }};
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
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
        table.mrd-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: {{ !empty($isPdf) ? '8.5pt' : '11.5px' }};
        }
        table.mrd-table thead {
            display: table-header-group;
        }
        table.mrd-table tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        table.mrd-table th {
            background: #0f5c63;
            color: #ffffff;
            text-align: left;
            font-size: {{ !empty($isPdf) ? '7.8pt' : '10.5px' }};
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: {{ !empty($isPdf) ? '5px 5px' : '8px 8px' }};
            border: 1px solid #0d4f55;
            vertical-align: middle;
        }
        table.mrd-table td {
            padding: {{ !empty($isPdf) ? '5px 5px' : '8px 8px' }};
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            line-height: 1.38;
        }
        table.mrd-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        @if(!empty($isPdf))
        .col-sno { width: 4%; text-align: center; }
        .col-mrd { width: 10%; }
        .col-patient { width: 14%; }
        .col-dates { width: 11%; }
        .col-diag { width: 16%; }
        .col-proc { width: 13%; }
        .col-pay { width: 14%; }
        .col-handover { width: 12%; }
        .col-status { width: 6%; }
        .pt-name { font-size: 9pt; }
        .meta-line { font-size: 8pt; }
        .badge { font-size: 7.5pt; }
        @else
        .col-sno { width: 46px; text-align: center; }
        .col-mrd { width: 135px; }
        .col-patient { width: 205px; }
        .col-dates { width: 145px; }
        .col-diag { width: 250px; }
        .col-proc { width: 205px; }
        .col-pay { width: 220px; }
        .col-handover { width: 210px; }
        .col-status { width: 105px; }
        @endif
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
        .badge-teal {
            background: #e6f4f5;
            color: #0f5c63;
            border: 1px solid #b8dde0;
        }
        .badge-info {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .badge-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-neutral {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
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
                <h1 class="hospital-title">GURU HOSPITAL — MRD REPORT</h1>
                <p class="report-subtitle">Medical Records Department Inpatient Archive, ICD Coding &amp; File Handover Register</p>
            </div>
        </div>
        <div class="header-right">
            <div class="period-pill">{{ $periodLabel }}</div>
            <div>Generated: {{ now('Asia/Kolkata')->format('d.m.Y h:i A') }} IST &bull; Total MRD Files: <strong>{{ $metrics['total_files'] }}</strong></div>
        </div>
    </div>

    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-lbl">Total MRD Files</div>
            <div class="kpi-val">{{ $metrics['total_files'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Discharged / ICD Coded</div>
            <div class="kpi-val" style="color: #0f5c63;">{{ $metrics['discharged_count'] }} <span style="font-size: 12px; font-weight: 400; color: #64748b;">/ {{ $metrics['icd_coded_count'] }} ICD</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">MRD Signed / Generated</div>
            <div class="kpi-val" style="color: #15803d;">{{ $metrics['mrd_signed_count'] }} <span style="font-size: 12px; font-weight: 400; color: #64748b;">/ {{ $metrics['generated_count'] }} PDF</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">Insurance / Cash</div>
            <div class="kpi-val" style="color: #0369a1;">{{ $metrics['insurance_count'] }} <span style="font-size: 12px; font-weight: 400; color: #64748b;">Ins / {{ $metrics['cash_count'] }} Cash</span></div>
        </div>
    </div>

    <table class="mrd-table">
        <thead>
            <tr>
                <th class="col-sno" style="text-align: center;">S.No</th>
                <th class="col-mrd">MRD File No.</th>
                <th class="col-patient">Patient / UHID / IP No.</th>
                <th class="col-dates">DOA &amp; DOD (LOS)</th>
                <th class="col-diag">Diagnosis &amp; ICD Code</th>
                <th class="col-proc">Procedure / Consultant</th>
                <th class="col-pay">Payment / Insurance</th>
                <th class="col-handover">MRD File Handover &amp; Sign</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $item)
                @php
                    $losDays = ($item->admission_at && $item->discharge_at)
                        ? max(1, $item->admission_at->diffInDays($item->discharge_at))
                        : null;
                    $procLabel = $item->procedure_done ?: ($item->procedure_name ?: ucfirst((string) $item->surgery_type));
                @endphp
                <tr>
                    <td class="col-sno" style="font-weight: 700;">{{ $idx + 1 }}</td>
                    <td class="col-mrd">
                        <div class="mono" style="color: #0f5c63; font-size: 11.5px;">{{ $item->resolved_mrd_no ?: '—' }}</div>
                        @if($item->format)
                            <div class="meta-line" style="text-transform: uppercase;">{{ $item->format }}</div>
                        @endif
                    </td>
                    <td class="col-patient">
                        <div class="pt-name">{{ $item->patient_name }}</div>
                        <div class="meta-line">UHID: <span class="mono">{{ $item->uhid }}</span></div>
                        <div class="meta-line">IP No: <span class="mono">{{ $item->ip_no }}</span></div>
                        @if($item->age || $item->sex)
                            <div class="meta-line">{{ collect([$item->age ? $item->age . ' Yrs' : null, $item->sex])->filter()->implode(' • ') }}</div>
                        @endif
                    </td>
                    <td class="col-dates">
                        <div class="meta-line"><strong>DOA:</strong> {{ $item->admission_at ? $item->admission_at->format('d.m.Y') : '—' }}</div>
                        <div class="meta-line" style="margin-top: 2px;">
                            <strong>DOD:</strong>
                            @if($item->discharge_at)
                                {{ $item->discharge_at->format('d.m.Y') }}
                            @else
                                <span class="badge badge-info">In Admission</span>
                            @endif
                        </div>
                        @if($losDays)
                            <div class="meta-line" style="margin-top: 2px;">Stay: <strong>{{ $losDays }} Day(s)</strong></div>
                        @endif
                    </td>
                    <td class="col-diag">
                        <div style="font-weight: 600; color: #0f172a;">{{ $item->diagnosis ?: '—' }}</div>
                        @if($item->icd_code)
                            <div style="margin-top: 3px;"><span class="badge badge-teal">ICD: {{ $item->icd_code }}</span></div>
                        @endif
                    </td>
                    <td class="col-proc">
                        <div style="font-weight: 600;">{{ $procLabel }}</div>
                        @if($item->consultant)
                            <div class="meta-line" style="margin-top: 2px;"><strong>Doc:</strong> {{ $item->consultant }}</div>
                        @endif
                        @if($item->department)
                            <div class="meta-line">{{ $item->department }}</div>
                        @endif
                    </td>
                    <td class="col-pay">
                        <div style="font-weight: 600;">{{ $item->payment_summary }}</div>
                    </td>
                    <td class="col-handover">
                        <div class="meta-line"><strong>Handover:</strong> {{ $item->handover_name ?: '—' }}</div>
                        <div class="meta-line"><strong>Floor Incharge:</strong> {{ $item->floor_incharge_name ?: '—' }}</div>
                        <div class="meta-line"><strong>MRD Incharge:</strong> {{ $item->mrd_incharge_name ?: '—' }}</div>
                    </td>
                    <td class="col-status">
                        <span class="badge {{ $item->status === 'generated' ? 'badge-success' : 'badge-neutral' }}" style="text-transform: uppercase;">
                            {{ str_replace('_', ' ', (string) $item->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 28px; color: #64748b;">
                        No MRD records matched the selected filter criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-bar">
        <div>Guru Hospital — Medical Records Department (MRD) Register</div>
        <div>Showing all {{ $records->count() }} MRD record(s)</div>
    </div>
</div>
</body>
</html>
