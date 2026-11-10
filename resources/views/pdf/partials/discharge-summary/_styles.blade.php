<style>
    @font-face {
        font-family: 'Nirmala';
        font-style: normal;
        font-weight: normal;
        src: url('{{ str_replace('\\', '/', public_path('fonts/Nirmala.ttf')) }}') format('truetype');
    }
    @font-face {
        font-family: 'Nirmala';
        font-style: normal;
        font-weight: bold;
        src: url('{{ str_replace('\\', '/', public_path('fonts/NirmalaB.ttf')) }}') format('truetype');
    }

    @page {
        size: 223mm 304mm;
        margin: 0;
    }

    * {
        box-sizing: border-box;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 223mm;
        background: #ffffff;
        font-family: 'Aptos', Arial, 'Helvetica Neue', Helvetica, 'Nirmala UI', 'Nirmala', sans-serif;
        font-size: 8.6pt;
        line-height: 1.3;
        color: #0f172a;
    }

    /* Physical pre-printed stationery geometry:
       223mm x 304mm sheet, 70mm blank top, 30mm blank bottom => 204mm printable height */
    .ds-page {
        width: 223mm;
        height: 304mm;
        padding: 70mm 13mm 30mm 13mm;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }
    .ds-page-1 {
        page-break-after: always;
        break-after: page;
    }
    .ds-page-2 {
        page-break-after: auto;
        break-after: auto;
    }
    .ds-page-content {
        width: 100%;
        height: 204mm;
        max-height: 204mm;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .ds-page-content > * {
        flex-shrink: 0;
    }

    /* Typography & Titles */
    .ds-title-wrap {
        text-align: center;
        border-bottom: 1.4pt solid #0f172a;
        padding-bottom: 2.5px;
        margin-bottom: 5px;
    }
    .ds-title {
        font-size: 13pt;
        font-weight: 700;
        letter-spacing: 0.9px;
        color: #0f172a;
        text-transform: uppercase;
        margin: 0;
        line-height: 1.15;
    }
    .ds-format-tag {
        font-size: 7.4pt;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-top: 1px;
    }

    /* Section Headings */
    .ds-section-hdr {
        font-size: 8.2pt;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        background: #f1f5f9;
        border-left: 2.5pt solid #1e293b;
        border-bottom: 0.6pt solid #cbd5e1;
        padding: 2px 6px;
        margin-top: 4px;
        margin-bottom: 3px;
    }

    /* Structured Clinical Tables */
    .ds-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 3.5px;
        border: 0.6pt solid #cbd5e1;
    }
    .ds-grid th,
    .ds-grid td {
        border: 0.5pt solid #cbd5e1;
        padding: 2.2px 5px;
        vertical-align: top;
        word-wrap: break-word;
    }
    .ds-grid.bg-tint {
        background: #f8fafc;
    }

    .ds-lbl {
        display: block;
        font-size: 6.5pt;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.25px;
        line-height: 1.1;
        margin-bottom: 0.5px;
    }
    .ds-val {
        display: block;
        font-size: 8.5pt;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.22;
    }
    .ds-val-normal {
        font-weight: 400;
    }

    .ds-prose {
        font-size: 8.4pt;
        line-height: 1.28;
        color: #0f172a;
        text-align: justify;
        padding: 2px 4px;
        margin-bottom: 3px;
    }

    /* Clinical Data Tables (Medications, Treatment, Newborns) */
    .ds-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 4px;
        border: 0.6pt solid #94a3b8;
    }
    .ds-table th {
        background: #1e293b;
        color: #ffffff;
        font-size: 7.3pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.25px;
        padding: 2.5px 5px;
        text-align: left;
        border: 0.6pt solid #1e293b;
    }
    .ds-table td {
        border: 0.5pt solid #cbd5e1;
        padding: 2.2px 5px;
        font-size: 8.3pt;
        line-height: 1.24;
        vertical-align: top;
        color: #0f172a;
        word-wrap: break-word;
    }
    .ds-table tr:nth-child(even) td {
        background: #f8fafc;
    }

    /* Follow-up, Tamil Notice, Emergency, Signatures */
    .ds-followup-box {
        border: 0.8pt solid #334155;
        background: #f8fafc;
        padding: 4px 8px;
        margin-bottom: 4px;
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .ds-tamil-box {
        border: 0.6pt solid #334155;
        border-left: 2.5pt solid #0f172a;
        background: #f8fafc;
        padding: 4px 7px;
        margin-bottom: 4px;
        font-family: 'Nirmala UI', 'Nirmala', 'Aptos', Arial, sans-serif;
        font-size: 8.1pt;
        line-height: 1.35;
        color: #0f172a;
    }
    .ds-emergency-box {
        background: #f1f5f9;
        border-left: 3pt solid #1e293b;
        padding: 4.5px 8px;
        margin-bottom: 5px;
        font-size: 8.2pt;
    }

    .ds-page2-bottom {
        margin-top: auto;
        padding-top: 3px;
    }
    .ds-signature-row {
        display: table;
        width: 100%;
        table-layout: fixed;
        border-top: 0.6pt solid #cbd5e1;
        padding-top: 4px;
    }
    .ds-sig-box {
        display: table-cell;
        vertical-align: bottom;
    }
    .ds-sig-space {
        height: 10mm;
    }
    .ds-sig-line {
        display: inline-block;
        min-width: 55mm;
        border-top: 0.8pt solid #334155;
        padding-top: 2px;
        text-align: center;
    }

    /* Page 2 minimal continuation header (kept inside printable area below 70mm blank) */
    .ds-p2-idbar {
        display: table;
        width: 100%;
        table-layout: fixed;
        border-bottom: 0.7pt solid #94a3b8;
        padding-bottom: 2px;
        margin-bottom: 4px;
        font-size: 7.8pt;
        color: #334155;
    }
    .ds-p2-idbar-left {
        display: table-cell;
        font-weight: 700;
        text-transform: uppercase;
        color: #0f172a;
        width: 35%;
    }
    .ds-p2-idbar-right {
        display: table-cell;
        text-align: right;
        width: 65%;
    }

    .continued-note {
        font-size: 7.2pt;
        font-style: italic;
        color: #475569;
        margin-bottom: 1px;
    }

    .ds-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-28deg);
        font-size: 46pt;
        color: rgba(220, 53, 69, 0.08);
        font-weight: 700;
        text-transform: uppercase;
        pointer-events: none;
        z-index: 0;
    }

    /* Graduated compaction classes applied only if content > 204mm */
    .density-compact-1 .ds-section-hdr { margin-top: 2.5px; margin-bottom: 2px; padding: 1.5px 5px; font-size: 7.9pt; }
    .density-compact-1 .ds-grid td, .density-compact-1 .ds-table td { padding: 1.6px 4px; font-size: 8.1pt; }
    .density-compact-1 .ds-prose { font-size: 8.1pt; line-height: 1.22; padding: 1px 3px; margin-bottom: 2px; }
    .density-compact-1 .ds-sig-space { height: 8mm; }

    .density-compact-2 .ds-section-hdr { margin-top: 2px; margin-bottom: 1.5px; padding: 1px 4px; font-size: 7.6pt; }
    .density-compact-2 .ds-grid td, .density-compact-2 .ds-table td { padding: 1.2px 3.5px; font-size: 7.9pt; line-height: 1.16; }
    .density-compact-2 .ds-val { font-size: 8pt; line-height: 1.15; }
    .density-compact-2 .ds-prose { font-size: 7.9pt; line-height: 1.18; }
    .density-compact-2 .ds-sig-space { height: 6.5mm; }

    .density-compact-3 .ds-title-wrap { margin-bottom: 3px; }
    .density-compact-3 .ds-section-hdr { margin-top: 1.5px; margin-bottom: 1px; font-size: 7.4pt; }
    .density-compact-3 .ds-grid, .density-compact-3 .ds-table { margin-bottom: 2px; }
    .density-compact-3 .ds-grid td, .density-compact-3 .ds-table td { padding: 1px 3px; font-size: 7.7pt; line-height: 1.14; }
    .density-compact-3 .ds-val { font-size: 7.8pt; line-height: 1.12; }
    .density-compact-3 .ds-prose { font-size: 7.8pt; line-height: 1.15; }
    .density-compact-3 .ds-sig-space { height: 5.5mm; }

    .density-compact-4 .ds-title { font-size: 11.5pt; }
    .density-compact-4 .ds-title-wrap { margin-bottom: 2px; padding-bottom: 1.5px; }
    .density-compact-4 .ds-section-hdr { margin-top: 1px; margin-bottom: 1px; padding: 1px 4px; font-size: 7.2pt; }
    .density-compact-4 .ds-grid, .density-compact-4 .ds-table { margin-bottom: 1.5px; }
    .density-compact-4 .ds-grid td, .density-compact-4 .ds-table td, .density-compact-4 .ds-table th { padding: 0.8px 3px; font-size: 7.4pt; line-height: 1.1; }
    .density-compact-4 .ds-val { font-size: 7.5pt; line-height: 1.1; }
    .density-compact-4 .ds-lbl { font-size: 6pt; }
    .density-compact-4 .ds-prose { font-size: 7.5pt; line-height: 1.12; padding: 1px 3px; margin-bottom: 1.5px; }
    .density-compact-4 .ds-followup-box, .density-compact-4 .ds-tamil-box, .density-compact-4 .ds-emergency-box { padding: 2px 5px; margin-bottom: 2px; font-size: 7.5pt; line-height: 1.15; }
    .density-compact-4 .ds-sig-space { height: 4mm; }
</style>
