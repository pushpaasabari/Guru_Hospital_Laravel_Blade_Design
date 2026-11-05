import React from 'react';

export default function DischargeSummaryPdfTemplate({ data }) {
    const {
        patient_name = 'Mrs. Anitha',
        age_sex = '23 / Female',
        ip_no = '2026/567',
        uhid = '2023/3931',
        phone = '7373667465',
        admission_at = '26.08.2026 at 02.45 AM',
        discharge_at = '29.08.2026 at 04.00 PM',
        address = 'W/O Sarath, Veerasanur',
        picme_no = '',
        consultant = 'Dr. Ambika MS OG',
        department = 'Obstetrics & Gynaecology',
        procedure_at = '26.08.2026 at 03:18 AM',
        surgeon = 'Dr. Ambika MS OG',
        anaesthetist = 'Dr. M.S Karthick (SA)',
        assisted_by = 'Staff Nurse Anitha',
        diagnosis = 'G3P1L1A1 / TWINS / Preterm / PROM',
        surgery_performed = 'Twin A: Labour natural with episiotomy | Twin B: Emergency LSCS',
        presenting_complaints = 'G3P1L1A1 / TWINS / EDD 22-9-2026 / FH Good / admitted for safe confinement\nC/O abdominal Pain',
        clinical_examination = 'O/E – A febrile, No Pallor, No Pedal edema / CVS & RS – NAD\nP/A: Uterus Overdistended, Not Acting, Both FH good',
        procedure_note_p1 = 'Labour natural with episiotomy with good uterine contraction, full cervix dilated. After putting episiotomy, Patient Delivered a alive Twin A male baby By Breech, Baby Handed over to pediatrician. Twin B baby not progressed Delivered Hence Planned To proceed C-Section.',
        procedure_note_p2 = 'Under SA, Abdomen opened in layers by SPT incision. LSCS done to deliver a alive Twin B male baby, cried soon after birth. Baby Handed over to pediatrician AMTSL followed. Uterine wound closed in 2 layers. After securing perfect Hemostasis, Abdomen closed in layer. AMTSL Followed. Uterus firm & contracted well. Episiotomy wound closed in layers. No Undue Bleeding P/V.',
        babies = [
            {
                title: 'Newborn #1 - Twin A',
                sex: 'Alive male baby',
                born: '26.08.2026 at 03:18 AM',
                mode: 'Labour natural breech with episiotomy',
                weight: '2.250 kg',
                apgar: '7/10, 8/10',
                neonatal_care: 'RDS (+). Handed over to pediatrician; shifted to NICU.'
            },
            {
                title: 'Newborn #2 - Twin B',
                sex: 'Alive male baby',
                born: '26.08.2026 at 03:44 AM',
                mode: 'Emergency LSCS under SA',
                weight: '2.250 kg',
                apgar: '8/10, 9/10',
                neonatal_care: 'Cried soon after birth. Baby handed over to pediatrician. AMTSL followed.'
            }
        ],
        treatment_given = [
            { medicine: 'IV Fluids', dose: '—' },
            { medicine: 'Inj. Pipzo', dose: '4.5 g BD IV' },
            { medicine: 'Inj. Metrogyl', dose: '100 mg BD' },
            { medicine: 'Inj. Pan', dose: '40 IV BD' }
        ],
        investigations = [
            { label: 'HB', value: '12.9 mgs %' },
            { label: 'BLOOD GROUP', value: 'O positive' },
            { label: 'HIV', value: 'Negative' },
            { label: 'HCV', value: 'Negative' },
            { label: 'VDRL', value: 'Negative' },
            { label: 'HBSAG', value: 'Negative' },
            { label: 'UREA', value: '9.7 g/dl' },
            { label: 'CREATININE', value: '0.86 mg/dl' },
            { label: 'URIC ACID', value: '4.3 mg/dl' },
            { label: 'RBS', value: '100.8 mg/dl' }
        ],
        condition_on_discharge = {
            general: 'Pt’s general condition good',
            bp: '110/70 mmHg',
            pulse: '86 b/min',
            pa: 'Uterus contracted well; no undue bleeding pv'
        },
        medications = [
            { medicine: 'Tab. Cefixime + Sulbactam', frequency: '1-0-1', duration: '7 days', timing: 'After food' },
            { medicine: 'Tab. Acendol-P', frequency: '1-0-1', duration: '7 days', timing: 'After food' },
            { medicine: 'Cap. Cyra-D', frequency: '1-0-1', duration: '7 days', timing: 'Before food' }
        ],
        advice_list = [
            'Immunisation',
            'Exclusive breast feeding for 6 months',
            'Post Natal Exercise'
        ],
        review_date = '03/09/2026',
        tamil_note = null,
        emergency_contacts = '04342-262266, +91-7418084266'
    } = data || {};

    const invChunks = [];
    for (let i = 0; i < investigations.length; i += 5) {
        invChunks.push(investigations.slice(i, i + 5));
    }

    return (
        <html lang="en">
            <head>
                <meta charSet="UTF-8" />
                <title>{`Discharge Summary - ${patient_name} (${ip_no})`}</title>
                <style dangerouslySetInnerHTML={{ __html: `
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
                `}} />
            </head>
            <body>
                {/* ================= PAGE 1 ================= */}
                <div className="sheet sheet-1">
                    <div className="doc-title">DISCHARGE SUMMARY</div>

                    {/* Patient Info Grid */}
                    <table className="grid-table bg-gray">
                        <tbody>
                            <tr>
                                <td style={{ width: '25%' }}>
                                    <span className="cell-lbl">PATIENT NAME</span>
                                    <span className="cell-val">{patient_name}</span>
                                </td>
                                <td style={{ width: '25%' }}>
                                    <span className="cell-lbl">AGE / SEX</span>
                                    <span className="cell-val">{age_sex}</span>
                                </td>
                                <td style={{ width: '25%' }}>
                                    <span className="cell-lbl">IP NO.</span>
                                    <span className="cell-val">{ip_no}</span>
                                </td>
                                <td style={{ width: '25%' }}>
                                    <span className="cell-lbl">UHID NO.</span>
                                    <span className="cell-val">{uhid}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span className="cell-lbl">PHONE</span>
                                    <span className="cell-val">{phone || '—'}</span>
                                </td>
                                <td colSpan={2}>
                                    <span className="cell-lbl">ADMISSION</span>
                                    <span className="cell-val">{admission_at}</span>
                                </td>
                                <td>
                                    <span className="cell-lbl">DISCHARGE</span>
                                    <span className="cell-val">{discharge_at}</span>
                                </td>
                            </tr>
                            <tr>
                                <td colSpan={4}>
                                    <span className="cell-lbl">ADDRESS</span>
                                    <span className="cell-val">
                                        {address} {picme_no && <span style={{ fontWeight: 'normal', color: '#475569', fontSize: '8pt' }}>&nbsp;(PICME: {picme_no})</span>}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {/* Doctors Grid */}
                    <table className="grid-table bg-white">
                        <tbody>
                            <tr>
                                <td style={{ width: '33.3%' }}>
                                    <span className="cell-lbl">CONSULTANT DOCTOR</span>
                                    <span className="cell-val">{consultant}</span>
                                </td>
                                <td style={{ width: '33.3%' }}>
                                    <span className="cell-lbl">DEPARTMENT</span>
                                    <span className="cell-val">{department}</span>
                                </td>
                                <td style={{ width: '33.4%' }}>
                                    <span className="cell-lbl">DATE OF SURGERY</span>
                                    <span className="cell-val">{procedure_at}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span className="cell-lbl">SURGEON</span>
                                    <span className="cell-val">{surgeon}</span>
                                </td>
                                <td>
                                    <span className="cell-lbl">ANAESTHETIST</span>
                                    <span className="cell-val">{anaesthetist}</span>
                                </td>
                                <td>
                                    <span className="cell-lbl">ASSISTED BY</span>
                                    <span className="cell-val">{assisted_by}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {/* Highlight Box: Diagnosis & Surgery */}
                    <div className="highlight-box">
                        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                            <tbody>
                                <tr>
                                    <td style={{ width: '22%', verticalAlign: 'top', padding: '1px 0' }}>
                                        <span className="cell-lbl" style={{ fontSize: '7.2pt' }}>DIAGNOSIS</span>
                                    </td>
                                    <td style={{ width: '78%', verticalAlign: 'top', padding: '1px 0' }}>
                                        <span className="cell-val" style={{ fontSize: '9.2pt' }}>{diagnosis}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style={{ verticalAlign: 'top', padding: '1px 0' }}>
                                        <span className="cell-lbl" style={{ fontSize: '7.2pt' }}>SURGERY PERFORMED</span>
                                    </td>
                                    <td style={{ verticalAlign: 'top', padding: '1px 0' }}>
                                        <span className="cell-val" style={{ fontSize: '9pt' }}>{surgery_performed}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {/* Presenting Complaints */}
                    <div className="section-header">PRESENTING COMPLAINTS</div>
                    <div className="prose-text" style={{ whiteSpace: 'pre-line' }}>{presenting_complaints}</div>

                    {/* Clinical Examination */}
                    <div className="section-header">CLINICAL EXAMINATION</div>
                    <div className="prose-text" style={{ whiteSpace: 'pre-line' }}>{clinical_examination}</div>

                    {/* Procedure Note */}
                    <div className="section-header">PROCEDURE NOTE</div>
                    <div className="prose-text">{procedure_note_p1}</div>
                    <div className="prose-text">{procedure_note_p2}</div>

                    {/* Baby / Newborn Details */}
                    {babies && babies.length > 0 && (
                        <>
                            <div className="section-header" style={{ marginTop: '3.5px' }}>BABY / NEWBORN DETAILS</div>
                            <div className="baby-cards-container">
                                {babies.map((baby, idx) => (
                                    <div key={idx} className="baby-col">
                                        <div className="baby-header">{baby.title}</div>
                                        <div className="baby-body">
                                            <div className="baby-row"><strong>SEX:</strong> {baby.sex}</div>
                                            <div className="baby-row"><strong>BORN:</strong> {baby.born}</div>
                                            <div className="baby-row"><strong>MODE:</strong> {baby.mode}</div>
                                            <div className="baby-row"><strong>WEIGHT:</strong> {baby.weight}</div>
                                            <div className="baby-row"><strong>APGAR:</strong> {baby.apgar}</div>
                                            <div className="baby-row"><strong>NEONATAL CARE:</strong> {baby.neonatal_care}</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </>
                    )}
                </div>

                {/* ================= PAGE 2 ================= */}
                <div className="sheet sheet-2">
                    {/* Running Header */}
                    <div className="running-header">
                        <div className="running-header-left">DISCHARGE SUMMARY</div>
                        <div className="running-header-right">{patient_name} &nbsp;|&nbsp; IP No. {ip_no} &nbsp;|&nbsp; UHID {uhid}</div>
                    </div>

                    {/* Treatment Given */}
                    <div className="section-header">TREATMENT GIVEN</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th style={{ width: '60%' }}>Medication / fluid</th>
                                <th style={{ width: '40%' }}>Dose &amp; route</th>
                            </tr>
                        </thead>
                        <tbody>
                            {treatment_given.map((row, idx) => (
                                <tr key={idx} style={{ background: idx % 2 === 1 ? '#f8fafc' : '#ffffff' }}>
                                    <td style={{ fontWeight: 600 }}>{row.medicine}</td>
                                    <td>{row.dose}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Investigations */}
                    <div className="section-header">INVESTIGATIONS</div>
                    <table className="grid-table bg-white">
                        <tbody>
                            {invChunks.map((chunk, rIdx) => (
                                <tr key={rIdx}>
                                    {chunk.map((inv, cIdx) => (
                                        <td key={cIdx} style={{ width: '20%' }}>
                                            <span className="cell-lbl">{inv.label}</span>
                                            <span className="cell-val">{inv.value}</span>
                                        </td>
                                    ))}
                                    {chunk.length < 5 && Array.from({ length: 5 - chunk.length }).map((_, emptyIdx) => (
                                        <td key={emptyIdx} style={{ width: '20%' }}>&nbsp;</td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Condition on Discharge */}
                    <div className="section-header">CONDITION ON DISCHARGE</div>
                    <table className="grid-table bg-white">
                        <tbody>
                            <tr>
                                <td style={{ width: '22%' }}>
                                    <span className="cell-lbl">General condition</span>
                                    <span className="cell-val">{condition_on_discharge.general}</span>
                                </td>
                                <td style={{ width: '18%' }}>
                                    <span className="cell-lbl">BP</span>
                                    <span className="cell-val">{condition_on_discharge.bp}</span>
                                </td>
                                <td style={{ width: '18%' }}>
                                    <span className="cell-lbl">Pulse</span>
                                    <span className="cell-val">{condition_on_discharge.pulse}</span>
                                </td>
                                <td style={{ width: '42%' }}>
                                    <span className="cell-lbl">P/A</span>
                                    <span className="cell-val">{condition_on_discharge.pa}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {/* Discharge Medications */}
                    <div className="section-header">DISCHARGE MEDICATIONS</div>
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th style={{ width: '45%' }}>Medicine</th>
                                <th style={{ width: '18%' }}>Frequency</th>
                                <th style={{ width: '17%' }}>Duration</th>
                                <th style={{ width: '20%' }}>Timing</th>
                            </tr>
                        </thead>
                        <tbody>
                            {medications.map((med, idx) => (
                                <tr key={idx} style={{ background: idx % 2 === 1 ? '#f8fafc' : '#ffffff' }}>
                                    <td style={{ fontWeight: 600 }}>{med.medicine}</td>
                                    <td>{med.frequency}</td>
                                    <td>{med.duration}</td>
                                    <td>{med.timing}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Advice */}
                    <div className="section-header">ADVICE</div>
                    <div style={{ fontSize: '8.5pt', lineHeight: 1.35, margin: '2px 0 4px 0' }}>
                        {advice_list.map((item, idx) => (
                            <span key={idx} style={{ display: 'inline-block', marginRight: '16px' }}>
                                <strong>&bull;</strong> {item}
                            </span>
                        ))}
                    </div>

                    {/* Review & Suture Removal */}
                    <div className="section-header">REVIEW &amp; SUTURE REMOVAL</div>
                    <div className="review-bar">
                        <div style={{ display: 'table-cell', fontWeight: 'bold', fontSize: '9pt', verticalAlign: 'middle' }}>
                            Review after one week
                        </div>
                        <div style={{ display: 'table-cell', textAlign: 'right', fontWeight: 'bold', fontSize: '10.5pt', verticalAlign: 'middle' }}>
                            {review_date}
                        </div>
                    </div>

                    {tamil_note && (
                        <div style={{
                            marginTop: '3px',
                            marginBottom: '4px',
                            padding: '4px 8px',
                            background: '#f8fafc',
                            border: '1px solid #111111',
                            borderLeft: '3px solid #111111',
                            fontFamily: "'Nirmala', 'Arial Unicode MS', sans-serif",
                            fontSize: '8pt',
                            lineHeight: 1.35,
                            color: '#111111'
                        }}>
                            <strong style={{ fontSize: '7.5pt', display: 'block', marginBottom: '1px' }}>
                                ஸ்டெண்ட் குறிப்பு (STENT REMOVAL NOTICE):
                            </strong>
                            {tamil_note}
                        </div>
                    )}

                    {/* When to Contact / Emergency Box */}
                    <div className="emergency-box">
                        <div style={{ marginBottom: '2.5px' }}>
                            <span style={{ fontSize: '7.5pt', fontWeight: 'bold', color: '#111111', width: '130px', display: 'inline-block', verticalAlign: 'top' }}>
                                WHEN TO CONTACT
                            </span>
                            <span style={{ fontSize: '8.5pt', fontWeight: 'bold', color: '#000000', display: 'inline-block' }}>
                                TO REPORT IMMEDIATELY, IF THERE IS BLEEDING, FEVER, ABDOMINAL PAIN, (OR) OTHER EMERGENCIES.
                            </span>
                        </div>
                        <div>
                            <span style={{ fontSize: '7.5pt', fontWeight: 'bold', color: '#111111', width: '130px', display: 'inline-block', verticalAlign: 'middle' }}>
                                EMERGENCY CONTACT NO:
                            </span>
                            <span style={{ fontSize: '9.8pt', fontWeight: 'bold', color: '#000000', display: 'inline-block', verticalAlign: 'middle' }}>
                                {emergency_contacts}
                            </span>
                        </div>
                    </div>

                    {/* Signatures */}
                    <table className="sig-table">
                        <tbody>
                            <tr>
                                <td style={{ width: '28%' }}>
                                    <div style={{ fontSize: '7.5pt', color: '#475569', fontWeight: 'bold' }}>Prepared by</div>
                                    <div style={{ fontSize: '9pt', fontWeight: 'bold', marginTop: '2px' }}>Staff Nurse Anitha</div>
                                </td>
                                <td style={{ width: '44%' }}>
                                    <div style={{ fontSize: '9pt', fontWeight: 'bold' }}>Dr. Karthik Rajan MS, MCh</div>
                                    <div style={{ fontSize: '7.5pt', color: '#475569' }}>Medical Officer / Resident Consultant Surgeon / O&amp;G</div>
                                </td>
                                <td style={{ width: '28%' }}>
                                    <div style={{ fontSize: '9pt', fontWeight: 'bold' }}>{consultant}</div>
                                    <div style={{ fontSize: '7.5pt', color: '#475569' }}>Reg: TNMC-64821</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {/* Running Footer Page 2 */}
                    <div className="running-footer-p2">
                        <div style={{ display: 'table-cell', textAlign: 'left' }}>
                            Guru Hospital - Discharge Summary (IP: {ip_no}) - {patient_name}
                        </div>
                        <div style={{ display: 'table-cell', textAlign: 'right' }}>
                            Page 2 of 2
                        </div>
                    </div>
                </div>
            </body>
        </html>
    );
}

