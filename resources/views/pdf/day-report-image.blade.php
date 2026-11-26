@php
    $records = $records ?? collect();
    $resolvedSections = $sections ?? [
        'admissions' => $records->values(),
        'discharges' => $records->filter(fn ($a) => !empty($a->discharge_at))->values(),
        'inpatients' => $records->filter(fn ($a) => empty($a->discharge_at))->values(),
    ];
    $sectionFilter = $sectionFilter ?? 'all';
    $sectionCatalog = [
        'admissions' => [
            'title' => '1. Complete Admission Details',
            'subtitle' => 'New admissions with demographics, attender, ward, consultant, vitals, clinical intake, and OT/delivery details',
            'empty' => 'No new admissions recorded for the selected date/filter.',
        ],
        'discharges' => [
            'title' => '2. Complete Discharge Details',
            'subtitle' => 'Discharged patients with DOA/DOD timeline, length of stay, diagnosis, condition, vitals, advice, follow-up & MRD handoff',
            'empty' => 'No discharges recorded for the selected date/filter.',
        ],
        'inpatients' => [
            'title' => '3. Complete Inpatient Details (Active Bed Census)',
            'subtitle' => 'Active ward census occupying hospital beds as of ' . ($metrics['census_date'] ?? now()->toDateString()),
            'empty' => 'No active inpatients matched the selected census date/filter.',
        ],
    ];
    $activeKeys = in_array($sectionFilter, ['admissions', 'discharges', 'inpatients'], true)
        ? [$sectionFilter]
        : ['admissions', 'discharges', 'inpatients'];
@endphp
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
        @if(!empty($isPdf))
        @page {
            size: A4 landscape;
            margin: 9mm 9mm 13mm 9mm;
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
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .header-logo { height: 44px; width: auto; object-fit: contain; }
        .hospital-title { font-size: 19px; font-weight: 700; color: #0f5c63; letter-spacing: -0.01em; margin: 0 0 2px 0; }
        .report-subtitle { font-size: 12.5px; color: #475569; margin: 0; }
        .header-right { text-align: right; font-size: 11.5px; color: #334155; line-height: 1.45; }
        .period-pill {
            display: inline-block;
            background: #e6f4f5;
            color: #0f5c63;
            border: 1px solid #b8dde0;
            border-radius: 6px;
            padding: 3px 10px;
            font-weight: 700;
            font-size: 12px;
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
        .kpi-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .kpi-val { font-size: 17px; font-weight: 700; color: #0f172a; margin-top: 2px; }
        .section-block { margin-bottom: 16px; }
        .section-banner {
            border-radius: 7px 7px 0 0;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            break-after: avoid;
            page-break-after: avoid;
        }
        .section-admissions { background: #eff6ff; border-color: #bfdbfe; }
        .section-discharges { background: #f0fdf4; border-color: #bbf7d0; }
        .section-inpatients { background: #fffbeb; border-color: #fde68a; }
        .section-banner-title { font-size: {{ !empty($isPdf) ? '9.5pt' : '13px' }}; font-weight: 700; color: #0f172a; }
        .section-banner-sub { font-size: {{ !empty($isPdf) ? '7.5pt' : '11px' }}; color: #475569; margin-top: 1px; }
        .section-count-pill {
            display: inline-block;
            background: #0f5c63;
            color: #ffffff;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 10.5px;
            margin-left: 6px;
        }
        table.day-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: {{ !empty($isPdf) ? '8pt' : '11px' }};
        }
        table.day-table thead { display: table-header-group; }
        table.day-table tbody tr { break-inside: avoid; page-break-inside: avoid; }
        table.day-table th {
            background: #0f5c63;
            color: #ffffff;
            text-align: left;
            font-size: {{ !empty($isPdf) ? '7.4pt' : '10px' }};
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: {{ !empty($isPdf) ? '4px 5px' : '7px 7px' }};
            border: 1px solid #0d4f55;
            vertical-align: middle;
        }
        table.day-table td {
            padding: {{ !empty($isPdf) ? '4px 5px' : '7px 7px' }};
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            line-height: 1.35;
        }
        table.day-table tbody tr:nth-child(even) td { background: #f8fafc; }

        .col-sno { width: 4%; text-align: center; }
        .col-patient { width: 17%; }
        .col-timeline { width: 17%; }
        .col-clinical { width: 22%; }
        .col-procedure { width: 30%; }
        .col-advice { width: 23%; }
        .col-admin { width: 17%; }
        .col-status { width: 10%; }

        .pt-name { font-weight: 700; color: #0f172a; font-size: {{ !empty($isPdf) ? '8.5pt' : '12px' }}; margin-bottom: 2px; }
        .meta-line { font-size: {{ !empty($isPdf) ? '7.4pt' : '10.5px' }}; color: #475569; }
        .mono { font-family: Consolas, 'Courier New', monospace; color: #0f172a; font-weight: 600; }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: {{ !empty($isPdf) ? '7pt' : '9.5px' }};
            font-weight: 700;
        }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-boy { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-girl { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-neutral { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .team-row { margin-bottom: 2px; }
        .team-lbl { color: #64748b; font-weight: 700; }
        .delivery-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 5px; padding: 4px 6px; margin-bottom: 4px; }
        .baby-box { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 5px; padding: 4px 6px; margin-bottom: 3px; }
        .baby-box:last-child { margin-bottom: 0; }
        .footer-bar {
            margin-top: 10px;
            padding-top: 7px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
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
                <p class="report-subtitle">Complete Daily Hospital Register: 1. Admissions &bull; 2. Discharges &bull; 3. Inpatients Census</p>
            </div>
        </div>
        <div class="header-right">
            <div class="period-pill">{{ $periodLabel }} &bull; {{ $basisLabel }}</div>
            <div>Generated: {{ now('Asia/Kolkata')->format('d.m.Y h:i A') }} IST</div>
        </div>
    </div>

    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-lbl">1. Admissions</div>
            <div class="kpi-val" style="color: #0369a1;">{{ $metrics['admissions_count'] ?? $resolvedSections['admissions']->count() }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">2. Discharges</div>
            <div class="kpi-val" style="color: #15803d;">{{ $metrics['discharges_section_count'] ?? $metrics['discharges_count'] ?? $resolvedSections['discharges']->count() }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">3. Active Inpatients</div>
            <div class="kpi-val" style="color: #b45309;">{{ $metrics['inpatients_count'] ?? $resolvedSections['inpatients']->count() }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-lbl">OT / Deliveries &amp; Babies</div>
            <div class="kpi-val">
                {{ $metrics['deliveries_count'] ?? 0 }} Del ({{ $metrics['babies_count'] ?? 0 }} Babies)
                <span style="font-size: 11px; font-weight: 400; color: #64748b;">
                    &bull; {{ $metrics['surgeries_count'] ?? 0 }} OT &bull; {{ $metrics['medical_count'] ?? 0 }} Med
                </span>
            </div>
        </div>
    </div>

    @foreach($activeKeys as $key)
        @include('pdf.partials.day-report-section', [
            'sectionKey' => $key,
            'sectionMeta' => $sectionCatalog[$key],
            'items' => $resolvedSections[$key] ?? collect(),
        ])
    @endforeach

    <div class="footer-bar">
        <div>Guru Hospital — Complete Daily Clinical Register (Admissions &bull; Discharges &bull; Inpatients)</div>
        <div>
            Admissions: {{ $resolvedSections['admissions']->count() }} &bull;
            Discharges: {{ $resolvedSections['discharges']->count() }} &bull;
            Active Inpatients: {{ $resolvedSections['inpatients']->count() }}
        </div>
    </div>
</div>
</body>
</html>
