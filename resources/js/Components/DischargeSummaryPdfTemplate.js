import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import React from "react";
function DischargeSummaryPdfTemplate({ data }) {
  const {
    patient_name = "",
    age_sex = "",
    ip_no = "",
    uhid = "",
    phone = "",
    admission_at = "",
    discharge_at = "",
    address = "",
    picme_no = "",
    consultant = "",
    department = "",
    procedure_at = "",
    surgeon = "",
    anaesthetist = "",
    assisted_by = "",
    reg_no = "",
    diagnosis = "",
    surgery_performed = "",
    presenting_complaints = "",
    clinical_examination = "",
    procedure_note_p1 = "",
    procedure_note_p2 = "",
    babies = [],
    treatment_given = [],
    investigations = [],
    condition_on_discharge = {
      general: "\u2014",
      bp: "\u2014",
      pulse: "\u2014",
      pa: "\u2014"
    },
    medications = [],
    advice_list = [],
    review_text = "",
    review_date = "",
    tamil_note = null,
    emergency_contacts = "04342-262266, +91-7418084266"
  } = data || {};
  const invChunks = [];
  for (let i = 0; i < investigations.length; i += 5) {
    invChunks.push(investigations.slice(i, i + 5));
  }
  return /* @__PURE__ */ jsxs("html", { lang: "en", children: [
    /* @__PURE__ */ jsxs("head", { children: [
      /* @__PURE__ */ jsx("meta", { charSet: "UTF-8" }),
      /* @__PURE__ */ jsx("title", { children: `Discharge Summary - ${patient_name} (${ip_no})` }),
      /* @__PURE__ */ jsx("style", { dangerouslySetInnerHTML: { __html: `
                    @page {
                        size: A4 portrait;
                        margin: 0;
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, Helvetica, sans-serif;
                        color: #000000;
                        background: #ffffff;
                        font-size: 8.8pt;
                        line-height: 1.35;
                    }
                    .sheet {
                        width: 210mm;
                        height: 297mm;
                        max-height: 297mm;
                        padding: 0 16mm;
                        box-sizing: border-box;
                        position: relative;
                        background: #ffffff;
                        overflow: hidden;
                        page-break-after: always;
                        break-after: page;
                    }
                    .sheet-1 {
                        padding-top: 45mm; /* Hospital pre-printed letterhead reserved space */
                        padding-bottom: 18mm;
                    }
                    .sheet-2 {
                        padding-top: 14mm;
                        padding-bottom: 18mm;
                    }
                    .doc-title {
                        text-align: center;
                        font-size: 14.5pt;
                        font-weight: bold;
                        color: #111111;
                        letter-spacing: 0.5px;
                        margin: 0 0 6px 0;
                        text-transform: uppercase;
                    }
                    .grid-table {
                        width: 100%;
                        border-collapse: collapse;
                        table-layout: fixed;
                        margin-bottom: 4.5px;
                        border: 0.6pt solid #94a3b8;
                    }
                    .grid-table td, .grid-table th {
                        border: 0.6pt solid #cbd5e1;
                        padding: 2.2px 5px;
                        vertical-align: top;
                    }
                    .bg-gray {
                        background-color: #f1f5f9;
                    }
                    .bg-white {
                        background-color: #ffffff;
                    }
                    .cell-lbl {
                        font-size: 6.8pt;
                        font-weight: bold;
                        color: #475569;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                        display: block;
                        margin-bottom: 0px;
                    }
                    .cell-val {
                        font-size: 8.8pt;
                        font-weight: bold;
                        color: #000000;
                        line-height: 1.2;
                        display: block;
                    }
                    .section-header {
                        font-size: 9.5pt;
                        font-weight: bold;
                        color: #111111;
                        text-transform: uppercase;
                        border-bottom: 1.2pt solid #222222;
                        padding-bottom: 1.5px;
                        margin-top: 4.5px;
                        margin-bottom: 2px;
                    }
                    .prose-text {
                        font-size: 8.5pt;
                        line-height: 1.26;
                        color: #000000;
                        text-align: justify;
                        margin-bottom: 3px;
                    }
                    .highlight-box {
                        border: 0.6pt solid #cbd5e1;
                        border-left: 3.5pt solid #000000;
                        background-color: #f1f5f9;
                        padding: 3.5px 6px;
                        margin-bottom: 4.5px;
                    }
                    .baby-cards-container {
                        width: 100%;
                        display: table;
                        table-layout: fixed;
                        border-collapse: collapse;
                        border: 0.6pt solid #000000;
                        margin-top: 2px;
                    }
                    .baby-col {
                        display: table-cell;
                        vertical-align: top;
                        width: 50%;
                        border-right: 0.6pt solid #000000;
                    }
                    .baby-col:last-child {
                        border-right: none;
                    }
                    .baby-header {
                        background-color: #000000;
                        color: #ffffff;
                        font-size: 7.8pt;
                        font-weight: bold;
                        padding: 2.5px 6px;
                        letter-spacing: 0.3px;
                    }
                    .baby-body {
                        padding: 3px 5px;
                        font-size: 8pt;
                    }
                    .baby-row {
                        margin-bottom: 1px;
                        line-height: 1.25;
                    }
                    .running-header {
                        width: 100%;
                        display: table;
                        table-layout: fixed;
                        border-bottom: 0.6pt solid #94a3b8;
                        padding-bottom: 3px;
                        margin-bottom: 6px;
                        font-size: 8.5pt;
                    }
                    .running-header-left {
                        display: table-cell;
                        font-weight: bold;
                        text-transform: uppercase;
                        color: #111111;
                        width: 35%;
                    }
                    .running-header-right {
                        display: table-cell;
                        text-align: right;
                        color: #444444;
                        width: 65%;
                    }
                    .data-table {
                        width: 100%;
                        border-collapse: collapse;
                        table-layout: fixed;
                        margin-bottom: 5px;
                        border: 0.6pt solid #94a3b8;
                    }
                    .data-table th {
                        background-color: #000000;
                        color: #ffffff;
                        font-size: 7.5pt;
                        font-weight: bold;
                        padding: 2.5px 5px;
                        text-align: left;
                        border: 0.6pt solid #000000;
                    }
                    .data-table td {
                        border: 0.6pt solid #cbd5e1;
                        padding: 2.5px 5px;
                        font-size: 8.5pt;
                        vertical-align: top;
                    }
                    .review-bar {
                        border: 1pt solid #000000;
                        background-color: #f1f5f9;
                        padding: 4px 8px;
                        display: table;
                        width: 100%;
                        table-layout: fixed;
                        margin-top: 4px;
                        margin-bottom: 5px;
                    }
                    .emergency-box {
                        border: 0.6pt solid #cbd5e1;
                        border-left: 3.5pt solid #000000;
                        background-color: #ebebeb;
                        padding: 4.5px 8px;
                        margin-top: 4px;
                        margin-bottom: 6px;
                    }
                    .sig-table {
                        width: 100%;
                        border-collapse: collapse;
                        border-top: 0.8pt solid #444444;
                        margin-top: 10px;
                        padding-top: 4px;
                    }
                    .sig-table td {
                        padding-top: 4px;
                        vertical-align: top;
                    }
                    .running-footer-p2 {
                        position: absolute;
                        bottom: 14mm;
                        left: 16mm;
                        right: 16mm;
                        border-top: 0.5pt solid #94a3b8;
                        padding-top: 3.5px;
                        display: table;
                        width: calc(100% - 32mm);
                        font-size: 8pt;
                        color: #555555;
                    }
                ` } })
    ] }),
    /* @__PURE__ */ jsxs("body", { children: [
      /* @__PURE__ */ jsxs("div", { className: "sheet sheet-1", children: [
        /* @__PURE__ */ jsx("div", { className: "doc-title", children: "DISCHARGE SUMMARY" }),
        /* @__PURE__ */ jsx("table", { className: "grid-table bg-gray", children: /* @__PURE__ */ jsxs("tbody", { children: [
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsxs("td", { style: { width: "25%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "PATIENT NAME" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: patient_name })
            ] }),
            /* @__PURE__ */ jsxs("td", { style: { width: "25%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "AGE / SEX" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: age_sex })
            ] }),
            /* @__PURE__ */ jsxs("td", { style: { width: "25%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "IP NO." }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: ip_no })
            ] }),
            /* @__PURE__ */ jsxs("td", { style: { width: "25%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "UHID NO." }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: uhid })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsxs("td", { children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "PHONE" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: phone || "\u2014" })
            ] }),
            /* @__PURE__ */ jsxs("td", { colSpan: 2, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "ADMISSION" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: admission_at })
            ] }),
            /* @__PURE__ */ jsxs("td", { children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "DISCHARGE" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: discharge_at })
            ] })
          ] }),
          /* @__PURE__ */ jsx("tr", { children: /* @__PURE__ */ jsxs("td", { colSpan: 4, children: [
            /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "ADDRESS" }),
            /* @__PURE__ */ jsxs("span", { className: "cell-val", children: [
              address,
              " ",
              picme_no && /* @__PURE__ */ jsxs("span", { style: { fontWeight: "normal", color: "#475569", fontSize: "8pt" }, children: [
                "\xA0(PICME: ",
                picme_no,
                ")"
              ] })
            ] })
          ] }) })
        ] }) }),
        /* @__PURE__ */ jsx("table", { className: "grid-table bg-white", children: /* @__PURE__ */ jsxs("tbody", { children: [
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsxs("td", { style: { width: "33.3%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "CONSULTANT DOCTOR" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: consultant || "\u2014" })
            ] }),
            /* @__PURE__ */ jsxs("td", { style: { width: "33.3%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "DEPARTMENT" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: department || "\u2014" })
            ] }),
            /* @__PURE__ */ jsxs("td", { style: { width: "33.4%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "DATE OF SURGERY" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: procedure_at || "\u2014" })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsxs("td", { children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "SURGEON" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: surgeon || "\u2014" })
            ] }),
            /* @__PURE__ */ jsxs("td", { children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "ANAESTHETIST" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: anaesthetist || "\u2014" })
            ] }),
            /* @__PURE__ */ jsxs("td", { children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "ASSISTED BY" }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: assisted_by || "\u2014" })
            ] })
          ] })
        ] }) }),
        /* @__PURE__ */ jsx("div", { className: "highlight-box", children: /* @__PURE__ */ jsx("table", { style: { width: "100%", borderCollapse: "collapse" }, children: /* @__PURE__ */ jsxs("tbody", { children: [
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsx("td", { style: { width: "22%", verticalAlign: "top", padding: "1px 0" }, children: /* @__PURE__ */ jsx("span", { className: "cell-lbl", style: { fontSize: "7.2pt" }, children: "DIAGNOSIS" }) }),
            /* @__PURE__ */ jsx("td", { style: { width: "78%", verticalAlign: "top", padding: "1px 0" }, children: /* @__PURE__ */ jsx("span", { className: "cell-val", style: { fontSize: "9.2pt" }, children: diagnosis || "\u2014" }) })
          ] }),
          /* @__PURE__ */ jsxs("tr", { children: [
            /* @__PURE__ */ jsx("td", { style: { verticalAlign: "top", padding: "1px 0" }, children: /* @__PURE__ */ jsx("span", { className: "cell-lbl", style: { fontSize: "7.2pt" }, children: "SURGERY PERFORMED" }) }),
            /* @__PURE__ */ jsx("td", { style: { verticalAlign: "top", padding: "1px 0" }, children: /* @__PURE__ */ jsx("span", { className: "cell-val", style: { fontSize: "9pt" }, children: surgery_performed || "\u2014" }) })
          ] })
        ] }) }) }),
        presenting_complaints && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "PRESENTING COMPLAINTS" }),
          /* @__PURE__ */ jsx("div", { className: "prose-text", style: { whiteSpace: "pre-line" }, children: presenting_complaints })
        ] }),
        clinical_examination && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "CLINICAL EXAMINATION" }),
          /* @__PURE__ */ jsx("div", { className: "prose-text", style: { whiteSpace: "pre-line" }, children: clinical_examination })
        ] }),
        (procedure_note_p1 || procedure_note_p2) && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "PROCEDURE NOTE" }),
          procedure_note_p1 && /* @__PURE__ */ jsx("div", { className: "prose-text", children: procedure_note_p1 }),
          procedure_note_p2 && /* @__PURE__ */ jsx("div", { className: "prose-text", children: procedure_note_p2 })
        ] }),
        babies && babies.length > 0 && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", style: { marginTop: "3.5px" }, children: "BABY / NEWBORN DETAILS" }),
          /* @__PURE__ */ jsx("div", { className: "baby-cards-container", children: babies.map((baby, idx) => /* @__PURE__ */ jsxs("div", { className: "baby-col", children: [
            /* @__PURE__ */ jsx("div", { className: "baby-header", children: baby.title }),
            /* @__PURE__ */ jsxs("div", { className: "baby-body", children: [
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "SEX:" }),
                " ",
                baby.sex
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "BORN:" }),
                " ",
                baby.born
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "MODE:" }),
                " ",
                baby.mode
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "WEIGHT:" }),
                " ",
                baby.weight
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "APGAR:" }),
                " ",
                baby.apgar
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "baby-row", children: [
                /* @__PURE__ */ jsx("strong", { children: "NEONATAL CARE:" }),
                " ",
                baby.neonatal_care
              ] })
            ] })
          ] }, idx)) })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "sheet sheet-2", children: [
        /* @__PURE__ */ jsxs("div", { className: "running-header", children: [
          /* @__PURE__ */ jsx("div", { className: "running-header-left", children: "DISCHARGE SUMMARY" }),
          /* @__PURE__ */ jsxs("div", { className: "running-header-right", children: [
            patient_name,
            " \xA0|\xA0 IP No. ",
            ip_no,
            " \xA0|\xA0 UHID ",
            uhid
          ] })
        ] }),
        treatment_given && treatment_given.length > 0 && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "TREATMENT GIVEN" }),
          /* @__PURE__ */ jsxs("table", { className: "data-table", children: [
            /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { children: [
              /* @__PURE__ */ jsx("th", { style: { width: "60%" }, children: "Medication / fluid" }),
              /* @__PURE__ */ jsx("th", { style: { width: "40%" }, children: "Dose & route" })
            ] }) }),
            /* @__PURE__ */ jsx("tbody", { children: treatment_given.map((row, idx) => /* @__PURE__ */ jsxs("tr", { style: { background: idx % 2 === 1 ? "#f8fafc" : "#ffffff" }, children: [
              /* @__PURE__ */ jsx("td", { style: { fontWeight: 600 }, children: row.medicine }),
              /* @__PURE__ */ jsx("td", { children: row.dose })
            ] }, idx)) })
          ] })
        ] }),
        investigations && investigations.length > 0 && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "INVESTIGATIONS" }),
          /* @__PURE__ */ jsx("table", { className: "grid-table bg-white", children: /* @__PURE__ */ jsx("tbody", { children: invChunks.map((chunk, rIdx) => /* @__PURE__ */ jsxs("tr", { children: [
            chunk.map((inv, cIdx) => /* @__PURE__ */ jsxs("td", { style: { width: "20%" }, children: [
              /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: inv.label }),
              /* @__PURE__ */ jsx("span", { className: "cell-val", children: inv.value })
            ] }, cIdx)),
            chunk.length < 5 && Array.from({ length: 5 - chunk.length }).map((_, emptyIdx) => /* @__PURE__ */ jsx("td", { style: { width: "20%" }, children: "\xA0" }, emptyIdx))
          ] }, rIdx)) }) })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "section-header", children: "CONDITION ON DISCHARGE" }),
        /* @__PURE__ */ jsx("table", { className: "grid-table bg-white", children: /* @__PURE__ */ jsx("tbody", { children: /* @__PURE__ */ jsxs("tr", { children: [
          /* @__PURE__ */ jsxs("td", { style: { width: "22%" }, children: [
            /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "General condition" }),
            /* @__PURE__ */ jsx("span", { className: "cell-val", children: condition_on_discharge?.general || "\u2014" })
          ] }),
          /* @__PURE__ */ jsxs("td", { style: { width: "18%" }, children: [
            /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "BP" }),
            /* @__PURE__ */ jsx("span", { className: "cell-val", children: condition_on_discharge?.bp || "\u2014" })
          ] }),
          /* @__PURE__ */ jsxs("td", { style: { width: "18%" }, children: [
            /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "Pulse" }),
            /* @__PURE__ */ jsx("span", { className: "cell-val", children: condition_on_discharge?.pulse || "\u2014" })
          ] }),
          /* @__PURE__ */ jsxs("td", { style: { width: "42%" }, children: [
            /* @__PURE__ */ jsx("span", { className: "cell-lbl", children: "P/A" }),
            /* @__PURE__ */ jsx("span", { className: "cell-val", children: condition_on_discharge?.pa || "\u2014" })
          ] })
        ] }) }) }),
        medications && medications.length > 0 && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "DISCHARGE MEDICATIONS" }),
          /* @__PURE__ */ jsxs("table", { className: "data-table", children: [
            /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { children: [
              /* @__PURE__ */ jsx("th", { style: { width: "45%" }, children: "Medicine" }),
              /* @__PURE__ */ jsx("th", { style: { width: "18%" }, children: "Frequency" }),
              /* @__PURE__ */ jsx("th", { style: { width: "17%" }, children: "Duration" }),
              /* @__PURE__ */ jsx("th", { style: { width: "20%" }, children: "Timing" })
            ] }) }),
            /* @__PURE__ */ jsx("tbody", { children: medications.map((med, idx) => /* @__PURE__ */ jsxs("tr", { style: { background: idx % 2 === 1 ? "#f8fafc" : "#ffffff" }, children: [
              /* @__PURE__ */ jsx("td", { style: { fontWeight: 600 }, children: med.medicine }),
              /* @__PURE__ */ jsx("td", { children: med.frequency }),
              /* @__PURE__ */ jsx("td", { children: med.duration }),
              /* @__PURE__ */ jsx("td", { children: med.timing })
            ] }, idx)) })
          ] })
        ] }),
        advice_list && advice_list.length > 0 && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "ADVICE" }),
          /* @__PURE__ */ jsx("div", { style: { fontSize: "8.5pt", lineHeight: 1.35, margin: "2px 0 4px 0" }, children: advice_list.map((item, idx) => /* @__PURE__ */ jsxs("span", { style: { display: "inline-block", marginRight: "16px" }, children: [
            /* @__PURE__ */ jsx("strong", { children: "\u2022" }),
            " ",
            item
          ] }, idx)) })
        ] }),
        (review_text || review_date) && /* @__PURE__ */ jsxs(Fragment, { children: [
          /* @__PURE__ */ jsx("div", { className: "section-header", children: "REVIEW & SUTURE REMOVAL" }),
          /* @__PURE__ */ jsxs("div", { className: "review-bar", children: [
            /* @__PURE__ */ jsx("div", { style: { display: "table-cell", fontWeight: "bold", fontSize: "9pt", verticalAlign: "middle" }, children: review_text || "Review" }),
            /* @__PURE__ */ jsx("div", { style: { display: "table-cell", textAlign: "right", fontWeight: "bold", fontSize: "10.5pt", verticalAlign: "middle" }, children: review_date })
          ] })
        ] }),
        tamil_note && /* @__PURE__ */ jsxs("div", { style: {
          marginTop: "3px",
          marginBottom: "4px",
          padding: "4px 8px",
          background: "#f8fafc",
          border: "1px solid #111111",
          borderLeft: "3px solid #111111",
          fontFamily: "'Nirmala UI', 'Nirmala', 'Arial Unicode MS', sans-serif",
          fontSize: "8pt",
          lineHeight: 1.35,
          color: "#111111"
        }, children: [
          /* @__PURE__ */ jsx("strong", { style: { fontSize: "7.5pt", display: "block", marginBottom: "1px" }, children: "\u0BB8\u0BCD\u0B9F\u0BC6\u0BA3\u0BCD\u0B9F\u0BCD \u0B95\u0BC1\u0BB1\u0BBF\u0BAA\u0BCD\u0BAA\u0BC1 (STENT REMOVAL NOTICE):" }),
          tamil_note
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "emergency-box", children: [
          /* @__PURE__ */ jsxs("div", { style: { marginBottom: "2.5px" }, children: [
            /* @__PURE__ */ jsx("span", { style: { fontSize: "7.5pt", fontWeight: "bold", color: "#111111", width: "130px", display: "inline-block", verticalAlign: "top" }, children: "WHEN TO CONTACT" }),
            /* @__PURE__ */ jsx("span", { style: { fontSize: "8.5pt", fontWeight: "bold", color: "#000000", display: "inline-block" }, children: "TO REPORT IMMEDIATELY, IF THERE IS BLEEDING, FEVER, ABDOMINAL PAIN, (OR) OTHER EMERGENCIES." })
          ] }),
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("span", { style: { fontSize: "7.5pt", fontWeight: "bold", color: "#111111", width: "130px", display: "inline-block", verticalAlign: "middle" }, children: "EMERGENCY CONTACT NO:" }),
            /* @__PURE__ */ jsx("span", { style: { fontSize: "9.8pt", fontWeight: "bold", color: "#000000", display: "inline-block", verticalAlign: "middle" }, children: emergency_contacts })
          ] })
        ] }),
        /* @__PURE__ */ jsx("table", { className: "sig-table", children: /* @__PURE__ */ jsx("tbody", { children: /* @__PURE__ */ jsx("tr", { children: /* @__PURE__ */ jsxs("td", { style: { width: "100%", textAlign: "right" }, children: [
          /* @__PURE__ */ jsx("div", { style: { fontSize: "9pt", fontWeight: "bold" }, children: consultant || "\u2014" }),
          reg_no && /* @__PURE__ */ jsxs("div", { style: { fontSize: "7.5pt", color: "#475569" }, children: [
            "Reg: ",
            reg_no
          ] })
        ] }) }) }) }),
        /* @__PURE__ */ jsxs("div", { className: "running-footer-p2", children: [
          /* @__PURE__ */ jsxs("div", { style: { display: "table-cell", textAlign: "left" }, children: [
            "GURU HOSPITAL - Discharge Summary (IP: ",
            ip_no,
            ") - ",
            patient_name
          ] }),
          /* @__PURE__ */ jsx("div", { style: { display: "table-cell", textAlign: "right" }, children: "Page 2 of 2" })
        ] })
      ] })
    ] })
  ] });
}
export {
  DischargeSummaryPdfTemplate as default
};
