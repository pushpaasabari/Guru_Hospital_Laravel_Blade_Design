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
        font-family: 'Helvetica', 'Helvetica Neue', Arial, 'Nirmala UI', 'Nirmala', sans-serif;
        font-size: 10.3pt;
        font-weight: 400;
        line-height: 1.25;
        color: #1B2A2F;
    }

    /* Page 1: Pre-printed letterhead geometry:
       223mm x 304mm sheet, 70mm blank top, 30mm blank bottom => 204mm printable height */
    .a4-page {
        width: 223mm;
        height: 304mm;
        padding: 70mm 13mm 30mm 13mm;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }
    .a4-page-1 {
        page-break-after: always;
        break-after: page;
    }
    /* Page 2: Continuation sheet utilizing the full 223mm x 304mm page */
    .a4-page-2 {
        padding: 12mm 13mm 12mm 13mm;
        page-break-after: auto;
        break-after: auto;
    }

    .a4-page-content {
        width: 100%;
        height: 204mm;
        max-height: 204mm;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .a4-page-2 .a4-page-content {
        height: 280mm;
        max-height: 280mm;
    }
    .a4-page-content > * {
        flex-shrink: 0;
    }

    /* 1. Clean Header (Uppercase Centered Title, No Background Fill) */
    .a4-header-band {
        width: 100%;
        background: transparent;
        color: #1B2A2F;
        padding: 0 0 4px 0;
        border-bottom: 1.5pt solid #0F5C63;
        margin-bottom: 5px;
    }
    .a4-header-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .a4-header-left {
        width: 25%;
        vertical-align: middle;
        text-align: left;
    }
    .a4-header-center {
        width: 50%;
        vertical-align: middle;
        text-align: center;
    }
    .a4-header-right {
        width: 25%;
        vertical-align: middle;
        text-align: right;
    }
    .a4-doc-title {
        font-size: 16pt;
        font-weight: 700;
        color: #0F5C63;
        text-transform: uppercase;
        text-align: center;
        letter-spacing: 0.8px;
        margin: 0 0 1px 0;
        line-height: 1.1;
    }
    .a4-doc-subtitle {
        font-size: 9.5pt;
        font-weight: 500;
        color: #556970;
        text-align: center;
        letter-spacing: 0.2px;
        line-height: 1.2;
    }
    .a4-meta-pill {
        margin-bottom: 1.5px;
    }
    .a4-meta-pill:last-child {
        margin-bottom: 0;
    }
    .a4-meta-lbl {
        display: block;
        font-size: 8.2pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6B7B80;
        line-height: 1.1;
    }
    .a4-meta-val {
        display: block;
        font-size: 10.3pt;
        font-weight: 700;
        color: #1B2A2F;
        line-height: 1.2;
    }

    /* Section Headings */
    .a4-section-hdr {
        font-size: 11pt;
        font-weight: 700;
        color: #0F5C63;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        border-bottom: 1.1pt solid #0F5C63;
        padding-bottom: 1.5px;
        margin-top: 4px;
        margin-bottom: 3px;
        page-break-after: avoid;
        break-after: avoid;
    }

    .a4-lbl {
        display: block;
        font-size: 8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6B7B80;
        line-height: 1.12;
        margin-bottom: 1px;
    }
    .a4-val {
        display: block;
        font-size: 10.3pt;
        font-weight: 400;
        color: #1B2A2F;
        line-height: 1.24;
        word-wrap: break-word;
    }
    .a4-val-bold {
        font-weight: 700;
    }

    /* 2. Patient Banner */
    .a4-patient-card {
        border: 0.75pt solid #D5E0E2;
        margin-bottom: 4px;
        background: #ffffff;
    }
    .a4-patient-top {
        display: grid;
        grid-template-columns: 1.7fr 1fr 1fr 1fr;
        border-bottom: 0.75pt solid #D5E0E2;
    }
    .a4-patient-cell {
        padding: 3.5px 7px;
        border-right: 0.75pt solid #D5E0E2;
    }
    .a4-patient-cell:last-child {
        border-right: none;
    }
    .a4-patient-name {
        font-size: 12pt;
        font-weight: 700;
        color: #1B2A2F;
        line-height: 1.15;
    }
    .a4-patient-bottom {
        display: grid;
        grid-template-columns: 1fr 2.5fr;
        background: transparent;
    }
    .a4-patient-bottom .a4-patient-cell {
        padding: 3px 7px;
    }

    /* 3 & 4 & 8. 3-Column Label/Value Clean Grid (No Background Fill) */
    .a4-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border: 0.75pt solid #D5E0E2;
        margin-bottom: 4px;
        background: transparent;
    }
    .a4-grid-cell {
        background: transparent;
        padding: 3px 6px;
        border-right: 0.75pt solid #D5E0E2;
        border-bottom: 0.75pt solid #D5E0E2;
    }
    .a4-grid-3 > .a4-grid-cell:nth-child(3n) {
        border-right: none;
    }
    .a4-grid-3 > .a4-grid-cell:nth-last-child(-n+3) {
        border-bottom: none;
    }
    .a4-status-approved {
        color: #15803D;
        font-weight: 700;
    }

    /* 5. Diagnosis & Clinical Blocks (No Background Fill) */
    .a4-diagnosis-card {
        background: transparent;
        border: 0.75pt solid #D5E0E2;
        border-left: 3pt solid #0F5C63;
        padding: 3.5px 8px;
        margin-bottom: 3.5px;
    }
    .a4-diagnosis-val {
        font-size: 12.5pt;
        font-weight: 700;
        color: #0F5C63;
        line-height: 1.22;
    }
    .a4-clinical-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        border-bottom: 0.6pt solid #D5E0E2;
        padding: 2.5px 2px;
    }
    .a4-clinical-block {
        border-bottom: 0.6pt solid #D5E0E2;
        padding: 2.5px 2px;
    }
    .a4-outlined-card .a4-val,
    .a4-clinical-row .a4-val,
    .a4-clinical-block .a4-val {
        font-size: 10.5pt;
        font-weight: 400;
    }
    .a4-history-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4px 8px;
        padding: 2.5px 2px;
        border-bottom: 0.6pt solid #D5E0E2;
    }

    /* 6. Investigations Grid (No Background Fill) */
    /* 6. Investigations 5-Card Grid */
    .a4-inv-grid-5 {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 3.5px;
        margin-bottom: 4px;
        background: transparent;
    }
    .a4-inv-card {
        border: 0.75pt solid #D5E0E2;
        border-radius: 5px;
        padding: 3.5px 4.5px;
        text-align: center;
        background: #ffffff;
        min-height: 48px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
    }
    .a4-inv-card-hdr {
        font-size: 7.6pt;
        font-weight: 700;
        color: #1B2A2F;
        text-transform: uppercase;
        line-height: 1.15;
        margin-bottom: 1.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .a4-inv-card-name {
        font-weight: 700;
        color: #1B2A2F;
    }
    .a4-inv-card-unit {
        font-size: 7pt;
        font-weight: 500;
        color: #64748B;
        text-transform: none;
    }
    .a4-inv-card-val {
        font-size: 11pt;
        font-weight: 700;
        color: #0F5C63;
        line-height: 1.15;
        margin: 1.5px 0;
    }
    .a4-inv-card-ref {
        font-size: 6.8pt;
        font-weight: 500;
        color: #64748B;
        line-height: 1.1;
    }

    .a4-inv-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        border: 0.75pt solid #D5E0E2;
        margin-bottom: 4px;
        background: transparent;
    }
    .a4-inv-box {
        background: transparent;
        padding: 3px 5px;
        border-right: 0.75pt solid #D5E0E2;
        border-bottom: 0.75pt solid #D5E0E2;
    }
    .a4-inv-box .a4-lbl {
        font-size: 7.8pt;
    }
    .a4-inv-box .a4-val {
        font-size: 9.8pt;
        line-height: 1.16;
    }
    .a4-inv-unit {
        font-size: 8.2pt;
        font-weight: 500;
        color: #475569;
    }
    .a4-ref {
        display: block;
        font-size: 6.8pt;
        color: #64748b;
        font-weight: 500;
        line-height: 1.1;
        margin-top: 1px;
    }

    /* 7 & 10. Clean Header Tables (No Background Fill, Clean Borders) */
    .a4-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 6px;
    }
    .a4-table th {
        background: transparent;
        color: #0F5C63;
        font-size: 8.8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        text-align: left;
        padding: 3.5px 6px;
        border: 0.6pt solid #D5E0E2;
        border-bottom: 1.2pt solid #0F5C63;
    }
    .a4-table td {
        padding: 3px 6px;
        font-size: 10.3pt;
        color: #1B2A2F;
        border-bottom: 0.6pt solid #D5E0E2;
        border-left: 0.6pt solid #D5E0E2;
        border-right: 0.6pt solid #D5E0E2;
        background: transparent;
        vertical-align: top;
        word-wrap: break-word;
    }
    .a4-table tbody tr:nth-child(even) td {
        background: transparent;
    }

    /* Operative / Procedure Note Justified */
    .a4-procedure-note {
        text-align: justify;
        text-justify: inter-word;
    }
    .a4-procedure-note p {
        text-align: justify;
        margin: 0 0 2.5px 0;
    }
    .a4-procedure-note p:last-child {
        margin-bottom: 0;
    }

    /* Intra-operative Findings Bulleted List */
    .a4-findings-list {
        margin: 1px 0 0 0;
        padding-left: 14px;
        list-style-type: disc;
    }
    .a4-findings-list li {
        margin-bottom: 1.5px;
        line-height: 1.22;
    }
    .a4-findings-list li:last-child {
        margin-bottom: 0;
    }

    /* Immediate Postoperative Orders Table */
    .a4-postop-table {
        margin-bottom: 4px;
    }
    .a4-postop-table th {
        font-size: 8.4pt;
        padding: 3px 6px;
    }
    .a4-postop-table td {
        font-size: 10pt;
        padding: 2.5px 6px;
        line-height: 1.22;
    }

    /* Outlined Cards */
    .a4-outlined-card {
        border: 0.75pt solid #D5E0E2;
        padding: 5px 8px;
        margin-bottom: 6px;
        background: transparent;
    }

    /* 9. Newborn Twin Cards Side by Side */
    .a4-newborn-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 6px;
    }
    .a4-newborn-card {
        border: 0.75pt solid #D5E0E2;
        background: transparent;
    }
    .a4-newborn-title {
        background: transparent;
        color: #0F5C63;
        font-size: 10.5pt;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3.5px 8px;
        border-bottom: 0.75pt solid #D5E0E2;
    }
    .a4-newborn-body {
        padding: 4px 8px;
        font-size: 10.3pt;
        font-weight: 400;
        line-height: 1.3;
    }
    .a4-nb-line {
        margin-bottom: 1.5px;
    }
    .a4-nb-lbl {
        color: #6B7B80;
        font-weight: 700;
        font-size: 8.6pt;
        text-transform: uppercase;
        margin-right: 4px;
    }

    /* 11. Condition at Discharge 4-Column Grid (No Background Fill) */
    .a4-grid-4 {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr 0.8fr 2fr;
        border: 0.75pt solid #D5E0E2;
        margin-bottom: 5px;
        background: transparent;
    }
    .a4-grid-4 .a4-grid-cell {
        background: transparent;
        padding: 3px 6px;
        border-right: 0.75pt solid #D5E0E2;
        border-bottom: none;
    }
    .a4-grid-4 .a4-grid-cell:last-child {
        border-right: none;
    }

    /* Bilingual Tamil Notice inside Instructions/Follow-up */
    .a4-tamil-card {
        border: 0.75pt solid #D5E0E2;
        border-left: 2.5pt solid #0F5C63;
        padding: 4px 8px;
        margin-bottom: 5px;
        font-family: 'Nirmala', 'Nirmala UI', 'Helvetica', Arial, sans-serif;
        font-size: 10.3pt;
        font-weight: 400;
        line-height: 1.34;
        color: #1B2A2F;
        background: transparent;
    }

    /* 12. Warning Box (No Background Fill) */
    .a4-warning-box {
        background: transparent;
        border: 0.75pt solid #FCA5A5;
        border-left: 3.5pt solid #B3261E;
        padding: 4px 8px;
        margin-top: 3px;
        margin-bottom: 4px;
    }
    .a4-warning-title {
        font-size: 10.3pt;
        font-weight: 700;
        color: #B3261E;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .a4-warning-text {
        font-size: 10.3pt;
        font-weight: 400;
        color: #1B2A2F;
        line-height: 1.28;
    }

    /* 13. Signature Block */
    .a4-signature-row {
        display: table;
        width: 100%;
        table-layout: fixed;
        margin-top: 3px;
        margin-bottom: 3px;
    }
    .a4-sig-left {
        display: table-cell;
        width: 45%;
        vertical-align: bottom;
        font-size: 9.8pt;
        color: #6B7B80;
    }
    .a4-sig-right {
        display: table-cell;
        width: 55%;
        text-align: right;
        vertical-align: bottom;
    }
    .a4-sig-space {
        height: 3mm;
    }
    .a4-sig-line {
        display: inline-block;
        min-width: 65mm;
        border-top: 0.8pt solid #1B2A2F;
        padding-top: 3px;
        text-align: center;
    }
    .a4-sig-name {
        font-size: 10.5pt;
        font-weight: 700;
        color: #1B2A2F;
    }
    .a4-sig-desig {
        font-size: 8.6pt;
        color: #6B7B80;
    }

    /* Doctor Stamp Pad Impression (Authentic Rubber Stamp Box) */
    .a4-stamp-box {
        display: block;
        margin: 4px auto 0 auto;
        padding: 3px 6px;
        border: 1.2pt solid #1e3a8a;
        border-radius: 2px;
        text-align: center;
        background: transparent;
        color: #1e3a8a;
    }
    .a4-stamp-line {
        line-height: 1.18;
    }
    .a4-stamp-name {
        font-size: 7.6pt;
        font-weight: 700;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }
    .a4-stamp-reg {
        font-size: 7pt;
        font-weight: 600;
        letter-spacing: 0.2px;
    }
    .a4-stamp-desig,
    .a4-stamp-sub {
        font-size: 7pt;
        font-weight: 600;
        letter-spacing: 0.1px;
    }
    .a4-stamp-loc {
        font-size: 7pt;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    /* Page Footer Hairline anchored to bottom of printable margin frame */
    .a4-page-bottom {
        margin-top: auto;
    }
    .a4-footer-hairline {
        border-top: 0.75pt solid #D5E0E2;
        padding-top: 3px;
        margin-top: 3px;
        display: table;
        width: 100%;
        font-size: 8.4pt;
        font-weight: 400;
        color: #6B7B80;
    }
    .a4-footer-left {
        display: table-cell;
        text-align: left;
    }
    .a4-footer-right {
        display: table-cell;
        text-align: right;
        font-weight: 400;
    }

    /* Page 2 utilizes full 223mm x 304mm continuation sheet (280mm printable height) */
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-section-hdr {
        margin-top: 8px;
        margin-bottom: 5px;
    }
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-grid-3,
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-table,
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-outlined-card,
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-newborn-grid,
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-grid-4 {
        margin-bottom: 7px;
    }
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-grid-cell {
        padding: 4.5px 7px;
    }
    .a4-page-2:not(.a4-density-spacing):not(.a4-compact-1):not(.a4-compact-2) .a4-val {
        line-height: 1.32;
    }

    .a4-continued-note {
        font-size: 7.2pt;
        font-weight: 700;
        color: #0F5C63;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-top: 2px;
        margin-bottom: 1px;
    }

    /* Draft Watermark */
    .a4-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 48pt;
        color: rgba(179, 38, 30, 0.08);
        font-weight: 700;
        text-transform: uppercase;
        pointer-events: none;
        z-index: 0;
    }

    /* Progressive spacing optimization before font shrinkage (preserves 70mm top & 30mm bottom blank areas) */
    .a4-density-spacing .a4-header-band { padding-bottom: 2.5px; margin-bottom: 3.5px; }
    .a4-density-spacing .a4-section-hdr { margin-top: 3px; margin-bottom: 2px; padding-bottom: 1px; }
    .a4-density-spacing .a4-patient-card,
    .a4-density-spacing .a4-grid-3,
    .a4-density-spacing .a4-diagnosis-card,
    .a4-density-spacing .a4-inv-grid,
    .a4-density-spacing .a4-table,
    .a4-density-spacing .a4-outlined-card { margin-bottom: 2.5px; }
    .a4-density-spacing .a4-patient-cell,
    .a4-density-spacing .a4-grid-cell,
    .a4-density-spacing .a4-inv-box { padding: 2px 5px; }
    .a4-density-spacing .a4-clinical-row,
    .a4-density-spacing .a4-clinical-block,
    .a4-density-spacing .a4-history-grid { padding: 1.5px 2px; }
    .a4-density-spacing .a4-table td,
    .a4-density-spacing .a4-table th { padding: 2px 5px; }
    .a4-density-spacing .a4-footer-hairline { margin-top: 2px; padding-top: 2px; }

    .a4-compact-1 .a4-header-band { padding-bottom: 2px; margin-bottom: 3px; }
    .a4-compact-1 .a4-doc-title { font-size: 14pt; }
    .a4-compact-1 .a4-section-hdr { margin-top: 2.5px; margin-bottom: 1.5px; font-size: 8.8pt; }
    .a4-compact-1 .a4-val, .a4-compact-1 .a4-table td { font-size: 8.2pt; line-height: 1.2; }
    .a4-compact-1 .a4-sig-space { height: 8mm; }

    .a4-compact-2 .a4-header-band { padding-bottom: 1.5px; margin-bottom: 2px; }
    .a4-compact-2 .a4-doc-title { font-size: 13pt; }
    .a4-compact-2 .a4-patient-name { font-size: 10pt; }
    .a4-compact-2 .a4-diagnosis-val { font-size: 9.8pt; }
    .a4-compact-2 .a4-val, .a4-compact-2 .a4-table td, .a4-compact-2 .a4-warning-text { font-size: 7.8pt; line-height: 1.16; }
    .a4-compact-2 .a4-sig-space { height: 6mm; }
</style>

