import React from 'react';
import {
  formatAdviceAndReview,
  formatConditionOnDischarge,
  formatInvestigations,
  formatMedications,
  formatNewborns,
  formatSurgeryPerformed,
  formatSurgicalAdvice,
  getInsuranceDetailsList,
  groupInvestigationsForGrid,
} from '../utils/dischargeFormatter';
import { resolveMrdNo } from '../utils/inputNormalizers';
import { isNaturalDeliveryCase } from '../utils/deliveryMode';
import { formatDate as formatWallDate, parseWallClockParts } from '../utils/dateFormatter';

export interface DigitalSummaryPayload {
  resolved_mrd_no?: string;
  admission_at_formatted?: string;
  procedure_at_formatted?: string;
  discharge_at_formatted?: string;
  consultant_with_degree?: string;
  doctor_stamp_details?: {
    name: string;
    reg_no: string;
    line1: string;
    line2?: string | null;
    location: string;
  } | null;
  insurance_details?: Array<{ label: string; value: string }>;
  clinical_sections?: {
    complaints: string;
    hpi: string;
    hospital_course?: string;
  };
  investigation_rows?: Array<
    Array<{
      label: string;
      value: string;
      unit?: string;
      normal_range?: string;
    }>
  >;
  surgery_performed?: string;
  postoperative_orders?: Array<{
    sno: number;
    order: string;
    instruction: string;
  }>;
  has_stent?: boolean;
  stent_removal_date_formatted?: string;
  newborn_cards?: Array<{
    title: string;
    sex: string;
    born: string;
    mode: string;
    weight: string;
    discharge_weight?: string | null;
    apgar: string;
    neonatal_care: string;
    notes?: string;
    birth_vaccines_text?: string;
  }>;
  medications?: Array<{
    medicine: string;
    frequency: string;
    duration: string;
    timing: string;
  }>;
  condition?: {
    general?: string;
    bp?: string;
    pulse?: string;
    spo2?: string;
    pa?: string;
  };
  advice_and_review?: {
    advice_items?: string[];
    review_text?: string;
    review_date?: string;
    tamil_note?: string;
  };
  surgical_advice?: {
    implant?: string;
    diet?: string;
    exercise?: string;
    when_to_contact?: string;
    emergency_contact?: string;
    review?: string;
    tamil_note?: string;
  } | null;
}

interface Props {
  admission: any;
  digitalSummary?: DigitalSummaryPayload;
  formatDateTime: (dateStr?: string | null) => string;
}

const isFilled = (val?: string | null): boolean => {
  const t = String(val ?? '').trim();
  return t !== '' && t !== '—' && t !== '-' && !/^(nil|none)$/i.test(t);
};

const formatDateDot = (dateStr?: string | null): string => {
  if (!dateStr) return '';
  const parts = parseWallClockParts(dateStr);
  if (parts) {
    return formatWallDate(dateStr);
  }
  return String(dateStr).replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, '$1.$2.$3');
};

const addCalendarDaysFormatted = (dateStr?: string | null, days = 30): string => {
  const parts = parseWallClockParts(dateStr);
  const utc = parts
    ? new Date(Date.UTC(parts.year, parts.month - 1, parts.day + days))
    : new Date(Date.now() + days * 86400000);
  const d = String(utc.getUTCDate()).padStart(2, '0');
  const m = String(utc.getUTCMonth() + 1).padStart(2, '0');
  const y = utc.getUTCFullYear();
  return `${d}.${m}.${y}`;
};

const parsePostOpFallback = (raw?: string | null) => {
  if (!raw || !raw.trim()) return [];
  const lines = raw
    .split(/\r?\n/)
    .map((l) => l.trim().replace(/^[*•-]+/, '').trim())
    .filter(Boolean);
  let sno = 1;
  return lines.map((line) => {
    const cleaned = line.replace(/^(?:#?\d+[\.\)\-:]|\([0-9]+\))\s*/, '').trim();
    if (cleaned.includes('|')) {
      const [order, instruction] = cleaned.split('|', 2).map((s) => s.trim());
      return { sno: sno++, order: order || '—', instruction: instruction || '' };
    }
    return { sno: sno++, order: cleaned, instruction: '' };
  });
};

const parseFindingsList = (raw?: string | null): string[] => {
  const txt = String(raw ?? '').trim();
  if (!isFilled(txt)) return [];
  let items = txt
    .split(/(?:\r\n|\r|\n|;)+/)
    .map((l) => l.replace(/^[\s\-\*\•\d+\.\)]+/, '').trim())
    .filter((l) => isFilled(l));
  if (items.length <= 1 && /[.!?]\s+[A-Z0-9]/.test(txt)) {
    items = txt
      .split(/(?<=[.!?])\s+(?=[A-Z0-9])/)
      .map((s) => s.trim())
      .filter(Boolean);
  }
  return items;
};

const renderMultiline = (text: string) =>
  text.split(/\r?\n/).map((line, i, arr) => (
    <React.Fragment key={i}>
      {line}
      {i < arr.length - 1 && <br />}
    </React.Fragment>
  ));

const renderProcedureNote = (text: string) => {
  const lines = text.split(/\r?\n/);
  return lines.map((line, idx) => {
    const twinMatch = line.match(/(Twin\s+[A-Z]:)(.*)/i);
    if (twinMatch) {
      return (
        <React.Fragment key={idx}>
          {idx > 0 && <br />}
          <strong>{twinMatch[1]}</strong>
          {twinMatch[2]}
          {idx < lines.length - 1 && <br />}
        </React.Fragment>
      );
    }
    return (
      <React.Fragment key={idx}>
        {line}
        {idx < lines.length - 1 && <br />}
      </React.Fragment>
    );
  });
};

export default function DigitalDischargeSummaryPreview({
  admission,
  digitalSummary,
  formatDateTime,
}: Props) {
  const schemeLabel =
    admission.format === 'cmchistn'
      ? 'CMCHISTN Government Health Insurance Scheme'
      : admission.format === 'private'
      ? 'Private Insurance / TPA Scheme'
      : admission.format === 'employee'
      ? 'Hospital Employee Healthcare Scheme'
      : 'General Discharge Record';

  const deptSubtitle = [admission.department || null, schemeLabel].filter(Boolean).join(' | ');
  const mrdNo = digitalSummary?.resolved_mrd_no || resolveMrdNo(admission.mrd_no, admission.ip_no) || '—';
  const consultantName =
    digitalSummary?.consultant_with_degree || admission.consultant || admission.surgeon || '—';
  const insItems = digitalSummary?.insurance_details ?? getInsuranceDetailsList(admission);

  const rawComplaints =
    digitalSummary?.clinical_sections?.complaints ??
    (admission.clinical_history ? admission.clinical_history.split('\n')[0] : '');
  const rawHpi =
    digitalSummary?.clinical_sections?.hpi ??
    (admission.clinical_history && admission.clinical_history.includes('\n')
      ? admission.clinical_history.split('\n').slice(1).join('\n')
      : '');

  const hasComplaints = isFilled(rawComplaints);
  const hasHpi = isFilled(rawHpi);
  const hasExam = isFilled(admission.examination);

  const invRows =
    digitalSummary?.investigation_rows ??
    groupInvestigationsForGrid(formatInvestigations(admission.investigations, admission));

  const findingsItems = parseFindingsList(admission.findings);

  const isNatDel = isNaturalDeliveryCase(admission);
  const surgeryCells: Array<{ lbl: string; val: string; bold: boolean }> = [];
  const procPerf = digitalSummary?.surgery_performed || formatSurgeryPerformed(admission);
  if (isFilled(procPerf)) {
    surgeryCells.push({ lbl: isNatDel ? 'Mode of Delivery' : 'Procedure Performed', val: procPerf, bold: true });
  }
  const surgeonVal = admission.surgeon || admission.consultant;
  if (isFilled(surgeonVal)) {
    surgeryCells.push({ lbl: isNatDel ? 'Delivered By / Obstetrician' : 'Surgeon', val: surgeonVal, bold: true });
  }
  const anaesthVal = admission.anaesthetist
    ? `${admission.anaesthetist}${admission.anaesthesia_type ? ` (${admission.anaesthesia_type})` : ''}`
    : admission.anaesthesia_type;
  if (isFilled(anaesthVal)) {
    surgeryCells.push({ lbl: isNatDel ? 'Analgesia / Anaesthesia' : 'Anaesthetist & Type', val: anaesthVal, bold: false });
  }
  const assistedVal = admission.assisted_surgeon || admission.assisted_by || admission.assistant;
  if (isFilled(assistedVal)) {
    surgeryCells.push({ lbl: isNatDel ? 'Assisted By / Staff' : 'Assisted Surgeon / Staff', val: assistedVal, bold: false });
  }
  const nurseOtVal = [admission.staff_nurse, admission.ot_technician].filter(Boolean).join(' / ');
  if (isFilled(nurseOtVal)) {
    surgeryCells.push({ lbl: isNatDel ? 'Labour Nurse / Assistant' : 'Scrub Nurse / OT Tech', val: nurseOtVal, bold: false });
  }
  if (isFilled(admission.pediatrician_name)) {
    surgeryCells.push({ lbl: 'Pediatrician', val: admission.pediatrician_name, bold: false });
  }
  const incSpecVal = [admission.incision, admission.specimen ? `Specimen: ${admission.specimen}` : null]
    .filter(Boolean)
    .join(' | ');
  if (isFilled(incSpecVal)) {
    surgeryCells.push({ lbl: isNatDel ? 'Episiotomy / Placenta' : 'Incision / Specimen', val: incSpecVal, bold: false });
  }
  const drainBloodComp = [
    admission.drain,
    admission.blood_loss_ml ? `EBL: ${admission.blood_loss_ml} ml` : null,
    admission.complications,
  ]
    .filter(Boolean)
    .join(' | ');
  if (isFilled(drainBloodComp)) {
    surgeryCells.push({ lbl: 'Drain / Blood Loss / Complications', val: drainBloodComp, bold: false });
  }
  const hasSurgeryDetails = surgeryCells.length > 0 || findingsItems.length > 0;
  const surgeryEmptyPadCount = findingsItems.length === 0 && surgeryCells.length % 3 !== 0 ? 3 - (surgeryCells.length % 3) : 0;

  // Page 2 Data
  const hasProcNote = isFilled(admission.procedure_note);
  const postOpRows = digitalSummary?.postoperative_orders ?? parsePostOpFallback(admission.postoperative_orders);
  const hasInstructionsCol = postOpRows.some((r) => Boolean(r.instruction));
  const hasPostOp = postOpRows.length > 0;

  const newbornCards = digitalSummary?.newborn_cards ?? formatNewborns(admission);
  const meds = digitalSummary?.medications ?? formatMedications(admission.medication_orders);

  const cond =
    digitalSummary?.condition ??
    formatConditionOnDischarge(admission.discharge_condition || admission.condition_at_discharge);
  const condItems: Array<{ lbl: string; val: string }> = [];
  if (isFilled(cond.general)) condItems.push({ lbl: 'General Condition', val: cond.general! });
  if (isFilled(cond.bp)) condItems.push({ lbl: 'BP', val: cond.bp! });
  if (isFilled(cond.pulse)) condItems.push({ lbl: 'Pulse', val: cond.pulse! });
  if (isFilled(cond.spo2)) condItems.push({ lbl: 'SpO2', val: cond.spo2! });
  if (isFilled(cond.pa)) condItems.push({ lbl: 'Local Exam / P/A', val: cond.pa! });

  const adviceData =
    digitalSummary?.advice_and_review ??
    formatAdviceAndReview(
      admission.discharge_advice,
      admission.follow_up,
      admission.discharge_at,
      `${admission.procedure_name || ''} ${admission.procedure_done || ''}`
    );
  const surgAdvice =
    digitalSummary?.surgical_advice ??
    (admission.surgery_type === 'other' ? formatSurgicalAdvice(admission) : null);

  const displayAdviceItems = (adviceData.advice_items || []).filter((item) => {
    if (!surgAdvice) return true;
    return !/^(Implant|Diet|Exercise|Emergency\s*Contact|When\s*to\s*Contact|Review)\s*[:\-]/i.test(item.trim());
  });

  const implantVal = surgAdvice?.implant;
  const dietVal = admission.post_surgery_diet || surgAdvice?.diet;
  const activityVal = admission.post_surgery_activity || surgAdvice?.exercise;
  const instructionParts: Array<{ title: string; value: string }> = [];
  if (isFilled(implantVal)) instructionParts.push({ title: 'Implant', value: implantVal! });
  if (isFilled(dietVal)) instructionParts.push({ title: 'Diet', value: dietVal! });
  if (isFilled(activityVal)) instructionParts.push({ title: 'Activity', value: activityVal! });

  const followTextRaw = String(adviceData.review_text || surgAdvice?.review || '').trim();
  const followDateRaw = String(adviceData.review_date || '').trim();
  const sutureDot = admission.suture_removal_date ? formatDateDot(admission.suture_removal_date) : '';
  const cleanFollowDate = (
    sutureDot && !followTextRaw.includes(sutureDot) ? sutureDot : followDateRaw
  ).replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, '$1.$2.$3');
  const cleanFollowText = followTextRaw.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, '$1.$2.$3');

  let fullReviewText = cleanFollowText || cleanFollowDate;
  if (cleanFollowText && cleanFollowDate && !cleanFollowText.includes(cleanFollowDate)) {
    if (/[:\-]\s*$/.test(cleanFollowText)) {
      fullReviewText = `${cleanFollowText.replace(/[\s:\-]+$/, '')} : ${cleanFollowDate}`;
    } else if (/\b(?:on|at)\s*$/i.test(cleanFollowText)) {
      fullReviewText = `${cleanFollowText} ${cleanFollowDate}`;
    } else {
      fullReviewText = `${cleanFollowText} (${cleanFollowDate})`;
    }
  }

  const stentDate =
    digitalSummary?.stent_removal_date_formatted ||
    (admission.stent_removal_date
      ? formatDateDot(admission.stent_removal_date)
      : addCalendarDaysFormatted(admission.discharge_at, 30));

  const hasStent =
    digitalSummary?.has_stent ??
    (Boolean(admission.stent_removal_date) ||
      /\bstent\b/i.test(
        `${admission.procedure_name || ''} ${admission.procedure_done || ''} ${admission.procedure_note || ''} ${admission.findings || ''} ${admission.drain || ''} ${admission.discharge_advice || ''} ${admission.follow_up || ''}`
      ));

  const tamilNote = adviceData.tamil_note || surgAdvice?.tamil_note || '';
  const doctorDesignation = [
    admission.department || null,
    admission.approver?.doctor_reg_no ? `Reg. No: ${admission.approver.doctor_reg_no}` : null,
  ]
    .filter(Boolean)
    .join(' | ');
  const stamp = digitalSummary?.doctor_stamp_details;

  const caseTypeLabel =
    admission.surgery_type === 'delivery'
      ? 'Obstetric / Delivery'
      : admission.surgery_type === 'other'
      ? 'Surgical'
      : admission.surgery_type === 'medical'
      ? 'Medical'
      : '—';

  const footerPatientMeta = `${admission.patient_name || '—'}  |  IP No: ${admission.ip_no || '—'}  |  UHID: ${admission.uhid || '—'}`;

  return (
    <>
      {/* PAGE 1: DIGITAL DISCHARGE SUMMARY */}
      <div className="a4-preview-sheet position-relative mb-4">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 1 OF 3: DIGITAL DISCHARGE SUMMARY — PAGE 1 ({admission.format.toUpperCase()})
        </div>

        {admission.status === 'draft' && (
          <div
            style={{
              position: 'absolute',
              top: '42%',
              left: '50%',
              transform: 'translate(-50%, -50%) rotate(-30deg)',
              fontSize: '42pt',
              color: 'rgba(179, 38, 30, 0.07)',
              fontWeight: 700,
              textTransform: 'uppercase',
              pointerEvents: 'none',
            }}
          >
            DRAFT COPY
          </div>
        )}

        {/* 1. Document Header */}
        <div className="a4-header-band">
          <table className="a4-header-table">
            <tbody>
              <tr>
                <td className="a4-header-left">
                  <span className="a4-meta-lbl">MRD FILE NO.</span>
                  <span className="a4-meta-val">{mrdNo}</span>
                </td>
                <td className="a4-header-center">
                  <h1 className="a4-doc-title">DISCHARGE SUMMARY</h1>
                  {deptSubtitle && <div className="a4-doc-subtitle">{deptSubtitle}</div>}
                </td>
                <td className="a4-header-right">
                  <span className="a4-meta-lbl">ICD CODE</span>
                  <span className="a4-meta-val">{admission.icd_code || '—'}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        {/* 2. Patient Banner */}
        <div className="a4-patient-card">
          <div className="a4-patient-top">
            <div className="a4-patient-cell">
              <span className="a4-lbl">Patient Name</span>
              <span className="a4-patient-name">{admission.patient_name || '—'}</span>
            </div>
            <div className="a4-patient-cell">
              <span className="a4-lbl">Age / Sex</span>
              <span className="a4-val a4-val-bold">
                {admission.age ? `${admission.age} Yrs / ` : ''}
                {admission.sex || '—'}
              </span>
            </div>
            <div className="a4-patient-cell">
              <span className="a4-lbl">UHID</span>
              <span className="a4-val a4-val-bold">{admission.uhid || '—'}</span>
            </div>
            <div className="a4-patient-cell">
              <span className="a4-lbl">IP No.</span>
              <span className="a4-val a4-val-bold">{admission.ip_no || '—'}</span>
            </div>
          </div>
          <div
            className="a4-patient-bottom"
            style={{
              display: 'grid',
              gridTemplateColumns: admission.attender_name
                ? 'minmax(0, 1fr) minmax(0, 1.2fr) minmax(0, 1.8fr)'
                : 'minmax(0, 1fr) minmax(0, 2.5fr)',
            }}
          >
            <div className="a4-patient-cell">
              <span className="a4-lbl">Phone / Contact</span>
              <span className="a4-val">{admission.contact || '—'}</span>
            </div>
            {admission.attender_name && (
              <div className="a4-patient-cell">
                <span className="a4-lbl">Attender / Relation</span>
                <span className="a4-val a4-val-bold">
                  {admission.attender_name}
                  {admission.attender_relation ? (
                    <span style={{ fontWeight: 500, color: '#475569' }}>
                      {' '}
                      ({admission.attender_relation})
                    </span>
                  ) : null}
                </span>
              </div>
            )}
            <div className="a4-patient-cell">
              <span className="a4-lbl">Address</span>
              <span className="a4-val">
                {admission.address ? admission.address.replace(/\r?\n/g, ', ') : '—'}
                {admission.picme_no && (
                  <span style={{ color: '#6B7B80' }}> (RCH ID: {admission.picme_no})</span>
                )}
              </span>
            </div>
          </div>
        </div>

        {/* 3. Admission Details */}
        <div className="a4-section-hdr">Admission Details</div>
        <div className="a4-grid-3">
          <div className="a4-grid-cell">
            <span className="a4-lbl">Date of Admission</span>
            <span className="a4-val a4-val-bold">
              {digitalSummary?.admission_at_formatted ?? formatDateTime(admission.admission_at)}
            </span>
          </div>
          <div className="a4-grid-cell">
            <span className="a4-lbl">Date of Procedure / Surgery</span>
            <span className="a4-val a4-val-bold">
              {digitalSummary?.procedure_at_formatted ?? formatDateTime(admission.procedure_at)}
            </span>
          </div>
          <div className="a4-grid-cell">
            <span className="a4-lbl">Date of Discharge</span>
            <span className="a4-val a4-val-bold">
              {digitalSummary?.discharge_at_formatted ?? formatDateTime(admission.discharge_at)}
            </span>
          </div>
          <div className="a4-grid-cell">
            <span className="a4-lbl">Consultant / Treating Doctor</span>
            <span className="a4-val a4-val-bold">{consultantName}</span>
          </div>
          <div className="a4-grid-cell">
            <span className="a4-lbl">Ward / Room &amp; Case Type</span>
            <span className="a4-val">
              {caseTypeLabel}
              {admission.ward_room_no ? ` • ${admission.ward_room_no}` : ''}
            </span>
          </div>
          {isFilled(admission.payment_mode) ? (
            <div className="a4-grid-cell">
              <span className="a4-lbl">Payment Mode</span>
              <span className="a4-val">{admission.payment_mode}</span>
            </div>
          ) : (
            <div className="a4-grid-cell">
              <span className="a4-lbl">&nbsp;</span>
              <span className="a4-val">&nbsp;</span>
            </div>
          )}
        </div>

        {/* 4. Insurance / TPA */}
        {insItems.length > 0 && (
          <>
            <div className="a4-section-hdr">Insurance / TPA</div>
            <div className="a4-grid-3">
              {insItems.map((item, idx) => {
                const isApprovedPreAuth =
                  item.label === 'Pre-Auth Status' && item.value.trim().toLowerCase() === 'approved';
                return (
                  <div key={idx} className="a4-grid-cell">
                    <span className="a4-lbl">{item.label}</span>
                    <span
                      className={`a4-val ${
                        isApprovedPreAuth
                          ? 'a4-status-approved'
                          : item.label === 'Approved Amount'
                          ? 'a4-val-bold'
                          : ''
                      }`}
                    >
                      {item.value || '—'}
                    </span>
                  </div>
                );
              })}
              {insItems.length % 3 !== 0 &&
                Array.from({ length: 3 - (insItems.length % 3) }).map((_, padIdx) => (
                  <div key={`ins-pad-${padIdx}`} className="a4-grid-cell">
                    <span className="a4-lbl">&nbsp;</span>
                    <span className="a4-val">&nbsp;</span>
                  </div>
                ))}
            </div>
          </>
        )}

        {/* 5. Diagnosis & Clinical Summary */}
        <div className="a4-section-hdr">Diagnosis &amp; Clinical Summary</div>
        {isFilled(admission.diagnosis) && (
          <div className="a4-diagnosis-card">
            <span className="a4-lbl">Final Diagnosis</span>
            <div className="a4-diagnosis-val">{admission.diagnosis}</div>
          </div>
        )}

        {(hasComplaints || hasHpi) && (
          <div
            className="a4-clinical-row"
            style={{
              gridTemplateColumns:
                hasComplaints && hasHpi ? 'repeat(2, minmax(0, 1fr))' : 'minmax(0, 1fr)',
            }}
          >
            {hasComplaints && (
              <div className="a4-clinical-col">
                <span className="a4-lbl">Presenting Complaints</span>
                <span className="a4-val">{renderMultiline(rawComplaints)}</span>
              </div>
            )}
            {hasHpi && (
              <div className="a4-clinical-col">
                <span className="a4-lbl">History of Present Illness</span>
                <span className="a4-val">{renderMultiline(rawHpi)}</span>
              </div>
            )}
          </div>
        )}

        {hasExam && (
          <div className="a4-clinical-block">
            <span className="a4-lbl">Clinical Examination</span>
            <span className="a4-val">{renderMultiline(admission.examination)}</span>
          </div>
        )}

        {/* 6. Investigations (3 Standardized Equal-Size Card Rows) */}
        {invRows.length > 0 && (
          <>
            <div className="a4-section-hdr">Investigations</div>
            <div className="a4-inv-grid-5">
              {invRows.map((rowCards, rowIdx) =>
                rowCards.map((inv, cardIdx) => (
                  <div
                    key={`${rowIdx}-${cardIdx}`}
                    className="a4-inv-card"
                    style={rowIdx > 0 && cardIdx === 0 ? { gridColumnStart: 1 } : undefined}
                  >
                    <div className="a4-inv-card-hdr">
                      <span className="a4-inv-card-name">{inv.label}</span>
                      {inv.unit && !String(inv.value).includes(inv.unit) && (
                        <span className="a4-inv-card-unit"> ({inv.unit})</span>
                      )}
                    </div>
                    <div className="a4-inv-card-val">{inv.value}</div>
                    <div className="a4-inv-card-ref">
                      {inv.normal_range ? `Ref: ${inv.normal_range}` : '\u00A0'}
                    </div>
                  </div>
                ))
              )}
            </div>
          </>
        )}

        {/* 7. Surgery / Delivery Details */}
        {hasSurgeryDetails && (
          <>
            <div className="a4-section-hdr">{isNatDel ? 'Delivery / Labour Details' : 'Surgery / Procedures'}</div>
            <div className="a4-grid-3">
              {surgeryCells.map((cell, idx) => (
                <div key={idx} className="a4-grid-cell">
                  <span className="a4-lbl">{cell.lbl}</span>
                  <span className={`a4-val ${cell.bold ? 'a4-val-bold' : ''}`}>{cell.val}</span>
                </div>
              ))}
              {findingsItems.length > 0 ? (
                <div
                  className="a4-grid-cell"
                  style={{
                    gridColumn: `span ${
                      surgeryCells.length % 3 === 2 ? 1 : surgeryCells.length % 3 === 1 ? 2 : 3
                    }`,
                  }}
                >
                  <span className="a4-lbl">{isNatDel ? 'Delivery / Clinical Findings' : 'Intra-operative Findings'}</span>
                  <div className="a4-val">
                    <ul className="a4-findings-list">
                      {findingsItems.map((item, idx) => (
                        <li key={idx}>{item}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              ) : (
                surgeryEmptyPadCount > 0 &&
                Array.from({ length: surgeryEmptyPadCount }).map((_, idx) => (
                  <div key={`surg-pad-${idx}`} className="a4-grid-cell">
                    <span className="a4-lbl">&nbsp;</span>
                    <span className="a4-val">&nbsp;</span>
                  </div>
                ))
              )}
            </div>
          </>
        )}

        <div className="a4-footer-hairline">
          <div>{footerPatientMeta}</div>
          <div>Page 1</div>
        </div>
      </div>

      {/* PAGE 2: DIGITAL DISCHARGE SUMMARY */}
      <div className="a4-preview-sheet position-relative mb-4 page-break">
        <div
          className="text-end text-muted small mb-2 no-print"
          style={{ fontSize: '10.5px', letterSpacing: '0.05em', fontWeight: 600 }}
        >
          DOCUMENT 1 OF 3: DIGITAL DISCHARGE SUMMARY — PAGE 2 ({admission.format.toUpperCase()})
        </div>

        {/* 8. Operative & Postoperative Notes */}
        {(hasProcNote || hasPostOp) && (
          <>
            <div className="a4-section-hdr" style={{ marginTop: 0 }}>
              {isNatDel ? 'Delivery & Post-Delivery Notes' : 'Operative & Postoperative Notes'}
            </div>

            {hasProcNote && (
              <div className="a4-outlined-card">
                <span className="a4-lbl">{isNatDel ? 'Labour / Delivery Note' : 'Operative / Procedure Note'}</span>
                <div className="a4-val a4-procedure-note">
                  {renderProcedureNote(admission.procedure_note)}
                </div>
              </div>
            )}

            {hasPostOp && (
              <div className="a4-outlined-card">
                <span className="a4-lbl" style={{ marginBottom: '4px' }}>
                  Immediate Postoperative Orders
                </span>
                <div className="table-responsive">
                  <table className="a4-table mb-0">
                    <thead>
                      <tr>
                        <th style={{ width: '7%', textAlign: 'center' }}>#</th>
                        <th style={{ width: hasInstructionsCol ? '63%' : '93%' }}>
                          Clinical Order / Medication / Fluid
                        </th>
                        {hasInstructionsCol && <th style={{ width: '30%' }}>Route / Frequency / Timing</th>}
                      </tr>
                    </thead>
                    <tbody>
                      {postOpRows.map((row, idx) => (
                        <tr key={idx}>
                          <td style={{ textAlign: 'center', fontWeight: 600 }}>{row.sno}</td>
                          <td className="a4-val-bold">{row.order}</td>
                          {hasInstructionsCol && <td>{row.instruction || '—'}</td>}
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </>
        )}

        {/* Bilingual Stent Notice */}
        {hasStent ? (
          <div className="a4-stent-card">
            <div className="a4-stent-hdr">STENT REMOVAL NOTICE / ஸ்டெண்ட் அகற்றும் குறிப்பு</div>
            <div className="a4-val mb-1">
              The DJ Stent placed inside needs to be removed on <strong>{stentDate}</strong>. Please
              contact the hospital one day prior to confirm the time.
            </div>
            <div className="a4-stent-ta a4-val">
              வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு{' '}
              <strong>{stentDate}</strong> அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு
              கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.
            </div>
          </div>
        ) : (
          tamilNote && (
            <div className="a4-tamil-card">
              <span className="a4-lbl" style={{ color: '#0F5C63' }}>
                ஸ்டெண்ட் குறிப்பு (Stent Removal Notice)
              </span>
              {tamilNote}
            </div>
          )
        )}

        {/* 9. Newborns */}
        {newbornCards.length > 0 && (
          <>
            <div className="a4-section-hdr">Newborn Details</div>
            <div
              className="a4-newborn-grid"
              style={{
                gridTemplateColumns:
                  newbornCards.length === 1 ? 'minmax(0, 1fr)' : 'repeat(2, minmax(0, 1fr))',
              }}
            >
              {newbornCards.map((card, i) => (
                <div key={i} className="a4-newborn-card">
                  <div className="a4-newborn-title">{card.title}</div>
                  <div className="a4-newborn-body">
                    <div>
                      <span className="a4-nb-lbl">Sex:</span>
                      {(() => {
                        const raw = String(card.sex || '—');
                        const match = raw.match(/^(Alive\s+)(.+?)(\s+baby)$/i);
                        if (!match) return raw;
                        return (
                          <>
                            {match[1]}
                            <strong style={{ textTransform: 'uppercase' }}>
                              {match[2].toUpperCase()}
                            </strong>
                            {match[3]}
                          </>
                        );
                      })()}
                    </div>
                    <div>
                      <span className="a4-nb-lbl">Birth Time:</span>
                      {card.born}
                    </div>
                    <div>
                      <span className="a4-nb-lbl">Mode:</span>
                      {card.mode}
                    </div>
                    <div>
                      <span className="a4-nb-lbl">Birth Weight:</span>
                      <strong>{card.weight}</strong>
                      {card.discharge_weight && (
                        <>
                          {' '}
                          &nbsp;|&nbsp; <span className="a4-nb-lbl">Discharge Weight:</span>
                          <strong>{card.discharge_weight}</strong>
                        </>
                      )}{' '}
                      &nbsp;|&nbsp; <span className="a4-nb-lbl">APGAR:</span>
                      {card.apgar}
                    </div>
                    <div>
                      <span className="a4-nb-lbl">Neonatal Care:</span>
                      {card.neonatal_care}
                    </div>
                    {card.notes && (
                      <div>
                        <span className="a4-nb-lbl">Baby Notes / Presentation:</span>
                        {card.notes}
                      </div>
                    )}
                    {card.birth_vaccines_text && (
                      <div>
                        <span className="a4-nb-lbl">Birth Vaccines:</span>
                        {card.birth_vaccines_text}
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </>
        )}

        {/* 10. Discharge Medication */}
        {meds.length > 0 && (
          <>
            <div className="a4-section-hdr">Discharge Medication</div>
            <div className="table-responsive">
              <table className="a4-table">
                <thead>
                  <tr>
                    <th style={{ width: '44%' }}>Medicine / Drug</th>
                    <th style={{ width: '18%' }}>Frequency</th>
                    <th style={{ width: '18%' }}>Duration / Qty</th>
                    <th style={{ width: '20%' }}>Timing</th>
                  </tr>
                </thead>
                <tbody>
                  {meds.map((med, i) => (
                    <tr key={i}>
                      <td className="a4-val-bold">{med.medicine || '—'}</td>
                      <td>{med.frequency || '—'}</td>
                      <td>{med.duration || '—'}</td>
                      <td>{med.timing || '—'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </>
        )}

        {/* 11. Condition at Discharge */}
        {condItems.length > 0 ? (
          <>
            <div className="a4-section-hdr">Condition at Discharge</div>
            <div
              className="a4-cond-grid"
              style={{
                display: 'grid',
                gridTemplateColumns: `repeat(${condItems.length}, minmax(0, 1fr))`,
                border: '0.75pt solid #D5E0E2',
                marginBottom: '6px',
              }}
            >
              {condItems.map((item, idx) => (
                <div
                  key={idx}
                  className="a4-grid-cell"
                  style={{
                    borderRight: idx === condItems.length - 1 ? 'none' : '0.75pt solid #D5E0E2',
                    borderBottom: 'none',
                  }}
                >
                  <span className="a4-lbl">{item.lbl}</span>
                  <span className="a4-val a4-val-bold">{item.val}</span>
                </div>
              ))}
            </div>
          </>
        ) : (
          isFilled(admission.discharge_condition) && (
            <>
              <div className="a4-section-hdr">Condition at Discharge</div>
              <div className="a4-outlined-card">
                <span className="a4-val">{renderMultiline(admission.discharge_condition)}</span>
              </div>
            </>
          )
        )}

        {/* Discharge Instructions */}
        {(instructionParts.length > 0 || displayAdviceItems.length > 0) && (
          <div className="a4-outlined-card">
            <span className="a4-lbl">Discharge Instructions</span>
            {instructionParts.length > 0 && (
              <div className="a4-val" style={{ marginBottom: displayAdviceItems.length > 0 ? '4px' : 0 }}>
                {instructionParts.map((part, idx) => (
                  <React.Fragment key={idx}>
                    {idx > 0 && '  |  '}
                    <strong>{part.title}:</strong> {part.value}
                  </React.Fragment>
                ))}
              </div>
            )}
            {displayAdviceItems.length > 0 && (
              <div className="a4-val">
                {displayAdviceItems.map((item, idx) => (
                  <span key={idx} style={{ display: 'inline-block', marginRight: '14px' }}>
                    &bull; {item}
                  </span>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Follow-up Review */}
        {fullReviewText && (
          <div className="a4-outlined-card">
            <span className="a4-lbl">Follow-up Review</span>
            <div className="a4-val a4-val-bold">{renderMultiline(fullReviewText)}</div>
          </div>
        )}

        {/* 12. Warning Signs / Emergency Advice */}
        <div className="a4-warning-box">
          <div className="a4-warning-title">Warning Signs / Emergency Advice</div>
          <div className="a4-val">
            <div>
              <strong>When to Contact:</strong>{' '}
              {surgAdvice?.when_to_contact ||
                'Report immediately if there is bleeding, fever, severe abdominal pain, wound discharge, or any other emergencies.'}
            </div>
            <div className="mt-1">
              <strong>Emergency Contact:</strong>{' '}
              {surgAdvice?.emergency_contact || '04342-262266 | +91-7418084266'}
            </div>
          </div>
        </div>

        {/* 13. Signature + Rubber Stamp */}
        <div className="a4-signature-row">
          <div>
            <div className="a4-sig-line">
              <div className="a4-sig-name">{consultantName}</div>
              <div className="a4-sig-desig">
                {doctorDesignation || 'Consultant / Treating Doctor'}
              </div>
              {stamp && (
                <div className="a4-stamp-box">
                  <div className="a4-stamp-name">{stamp.name}</div>
                  <div className="a4-stamp-reg">{stamp.reg_no}</div>
                  <div className="a4-stamp-desig">{stamp.line1}</div>
                  {stamp.line2 && <div className="a4-stamp-sub">{stamp.line2}</div>}
                  <div className="a4-stamp-loc">{stamp.location}</div>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="a4-footer-hairline">
          <div>{footerPatientMeta}</div>
          <div>Page 2</div>
        </div>
      </div>
    </>
  );
}
