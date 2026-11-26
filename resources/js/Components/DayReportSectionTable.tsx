import React from 'react';
import { Link } from '@inertiajs/react';
import { formatDate, formatDateTime } from '../utils/dateFormatter';
import { formatBirthVaccinesText, formatPaymentSummary } from '../utils/dischargeFormatter';

export interface NewbornItem {
  id: number;
  sex?: string | null;
  birth_at?: string | null;
  birth_weight_kg?: number | string | null;
  discharge_weight_kg?: number | string | null;
  apgar_1_min?: number | null;
  apgar_5_min?: number | null;
  neonatal_care?: string | null;
  notes?: string | null;
  birth_vaccines?: string[] | null;
  vaccine_date?: string | null;
  vaccine_dates?: Record<string, string> | null;
}

export interface DayRecord {
  id: number;
  patient_name: string;
  attender_name?: string | null;
  attender_relation?: string | null;
  uhid: string;
  ip_no: string;
  mrd_no?: string | null;
  resolved_mrd_no?: string | null;
  age?: string | null;
  sex?: string | null;
  contact?: string | null;
  address?: string | null;
  admission_at?: string | null;
  procedure_at?: string | null;
  discharge_at?: string | null;
  stay_days?: number | null;
  census_stay_days?: number | null;
  format?: string | null;
  payment_mode?: string | null;
  payment_summary?: string | null;
  insurance_type?: string | null;
  insurance_company?: string | null;
  policy_number?: string | null;
  pre_auth_no?: string | null;
  pre_auth_status?: string | null;
  approved_amount?: number | string | null;
  surgery_type: string;
  diagnosis?: string | null;
  icd_code?: string | null;
  clinical_history?: string | null;
  examination?: string | null;
  treatment_course?: string | null;
  vital_bp?: string | null;
  vital_pulse?: string | null;
  vital_spo2?: string | null;
  discharge_condition?: string | null;
  discharge_bp?: string | null;
  discharge_pulse?: string | null;
  discharge_spo2?: string | null;
  discharge_advice?: string | null;
  post_surgery_diet?: string | null;
  post_surgery_activity?: string | null;
  follow_up?: string | null;
  resolved_review_date?: string | null;
  suture_removal_date?: string | null;
  stent_removal_date?: string | null;
  picme_no?: string | null;
  vhn_name?: string | null;
  phc?: string | null;
  procedure_done?: string | null;
  procedure_name?: string | null;
  incision?: string | null;
  department?: string | null;
  ward_room_no?: string | null;
  consultant?: string | null;
  surgeon?: string | null;
  assisted_surgeon?: string | null;
  pediatrician_name?: string | null;
  anaesthetist?: string | null;
  assisted_anaesthetist?: string | null;
  anaesthesia_type?: string | null;
  staff_nurse?: string | null;
  ot_technician?: string | null;
  handover_name?: string | null;
  floor_incharge_name?: string | null;
  mrd_incharge_name?: string | null;
  status: string;
  newborns?: NewbornItem[];
}

export type DaySectionKey = 'admissions' | 'discharges' | 'inpatients';

interface SectionMeta {
  title: string;
  subtitle: string;
  icon: string;
  accentBg: string;
  accentColor: string;
  emptyText: string;
}

const SECTION_META: Record<DaySectionKey, SectionMeta> = {
  admissions: {
    title: '1. Complete Admission Details',
    subtitle: 'Patients newly admitted during the selected date / range with intake, ward, vitals & clinical details',
    icon: 'bi-person-plus-fill',
    accentBg: '#e0f2fe',
    accentColor: '#0369a1',
    emptyText: 'No new admissions recorded for the selected date/filter.',
  },
  discharges: {
    title: '2. Complete Discharge Details',
    subtitle: 'Patients discharged during the selected date / range with outcome, vitals, advice, follow-up & MRD handoff',
    icon: 'bi-box-arrow-right',
    accentBg: '#dcfce7',
    accentColor: '#15803d',
    emptyText: 'No discharges recorded for the selected date/filter.',
  },
  inpatients: {
    title: '3. Complete Inpatient Details (Active Bed Census)',
    subtitle: 'Patients occupying hospital beds as of the selected census date (including prior-day continuing admissions)',
    icon: 'bi-hospital',
    accentBg: '#fef3c7',
    accentColor: '#b45309',
    emptyText: 'No active inpatients matched the selected census date/filter.',
  },
};

function truncateText(text?: string | null, limit = 110): string {
  const clean = String(text || '').trim().replace(/\s+/g, ' ');
  if (!clean) return '';
  return clean.length > limit ? `${clean.slice(0, limit)}…` : clean;
}

function WorkflowBadge({ type }: { type: string }) {
  const cfg =
    type === 'delivery'
      ? { label: 'Delivery', cls: 'bg-success-subtle text-success border border-success-subtle' }
      : type === 'medical'
      ? { label: 'Medical Case', cls: 'bg-info-subtle text-info-emphasis border border-info-subtle' }
      : { label: 'Surgical OT', cls: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' };
  return <span className={`badge ${cfg.cls}`}>{cfg.label}</span>;
}

function StatusBadge({ status }: { status?: string | null }) {
  const clean = (status || 'draft').toLowerCase();
  const map: Record<string, { label: string; cls: string }> = {
    generated: { label: 'PDF Generated', cls: 'bg-success-subtle text-success border border-success-subtle' },
    approved: { label: 'Approved', cls: 'bg-primary-subtle text-primary border border-primary-subtle' },
    ready_for_review: { label: 'Ready for Review', cls: 'bg-info-subtle text-info-emphasis border border-info-subtle' },
    returned_for_correction: { label: 'Returned', cls: 'bg-danger-subtle text-danger border border-danger-subtle' },
    draft: { label: 'Draft', cls: 'bg-secondary-subtle text-secondary border border-secondary-subtle' },
  };
  const cfg = map[clean] || { label: clean.replace(/_/g, ' '), cls: 'bg-light text-dark border' };
  return <span className={`badge ${cfg.cls}`}>{cfg.label}</span>;
}

function VitalsBadges({ bp, pulse, spo2, title }: { bp?: string | null; pulse?: string | null; spo2?: string | null; title: string }) {
  if (!bp && !pulse && !spo2) return null;
  return (
    <div className="d-flex flex-wrap gap-1 mt-1">
      <span className="text-muted fw-semibold" style={{ fontSize: '11px' }}>{title}:</span>
      {bp && <span className="badge bg-light text-dark border">BP {bp}</span>}
      {pulse && <span className="badge bg-light text-dark border">PR {pulse}</span>}
      {spo2 && <span className="badge bg-light text-dark border">SpO₂ {spo2}</span>}
    </div>
  );
}

function PatientContactCell({ item }: { item: DayRecord }) {
  const mrd = item.resolved_mrd_no || item.mrd_no || (item.ip_no ? `MRD-${item.ip_no}` : '');
  return (
    <td>
      <div className="fw-bold text-dark">{item.patient_name}</div>
      {(item.age || item.sex) && (
        <div className="text-muted" style={{ fontSize: '11px' }}>
          {[item.age ? `${item.age} Yrs` : null, item.sex].filter(Boolean).join(' • ')}
        </div>
      )}
      <div className="text-muted small">
        UHID: <span className="font-monospace text-dark">{item.uhid}</span>
      </div>
      <div className="text-muted small">
        IPNO: <span className="font-monospace text-dark">{item.ip_no}</span>
      </div>
      {mrd && (
        <div className="text-muted small">
          MRD: <span className="font-monospace text-dark">{mrd}</span>
        </div>
      )}
      {item.attender_name && (
        <div className="text-muted" style={{ fontSize: '11px' }}>
          <strong>Attender:</strong> {item.attender_name}
          {item.attender_relation ? ` (${item.attender_relation})` : ''}
        </div>
      )}
      {item.contact && (
        <div className="text-muted" style={{ fontSize: '11px' }}>
          <i className="bi bi-telephone me-1"></i>
          {item.contact}
        </div>
      )}
      {item.address && (
        <div className="text-muted" style={{ fontSize: '11px', lineHeight: 1.3 }}>
          {item.address}
        </div>
      )}
    </td>
  );
}

function ProcedureAndTeamBlock({ item }: { item: DayRecord }) {
  const isDelivery = item.surgery_type === 'delivery' || (item.newborns && item.newborns.length > 0);
  const procLabel =
    item.procedure_done ||
    item.procedure_name ||
    (item.surgery_type === 'medical'
      ? `Medical Management${item.diagnosis ? `: ${item.diagnosis}` : ''}`
      : '—');

  return (
    <div className="d-flex flex-column gap-1 small">
      <div>
        <span className="fw-semibold text-dark">{procLabel}</span>
        {item.incision && (
          <div className="text-muted" style={{ fontSize: '11px' }}>
            <strong>Incision:</strong> {item.incision}
          </div>
        )}
      </div>

      {(item.surgeon || item.assisted_surgeon || item.anaesthetist || item.pediatrician_name) && (
        <div className="text-muted" style={{ fontSize: '11px', lineHeight: 1.35 }}>
          {item.surgeon && (
            <div>
              <strong>Surgeon:</strong> <span className="text-dark">{item.surgeon}</span>
              {item.assisted_surgeon ? ` (Asst: ${item.assisted_surgeon})` : ''}
            </div>
          )}
          {item.pediatrician_name && (
            <div>
              <strong>Pediatrician:</strong> {item.pediatrician_name}
            </div>
          )}
          {item.anaesthetist && (
            <div>
              <strong>Anaesthetist:</strong> {item.anaesthetist}
              {item.assisted_anaesthetist ? ` (Asst: ${item.assisted_anaesthetist})` : ''}
              {item.anaesthesia_type ? ` • ${item.anaesthesia_type}` : ''}
            </div>
          )}
          {(item.staff_nurse || item.ot_technician) && (
            <div>
              {[
                item.staff_nurse ? `Nurse: ${item.staff_nurse}` : null,
                item.ot_technician ? `OT Tech: ${item.ot_technician}` : null,
              ]
                .filter(Boolean)
                .join(' | ')}
            </div>
          )}
        </div>
      )}

      {isDelivery && (
        <div className="p-2 rounded bg-light border mt-1">
          <div className="fw-semibold text-success" style={{ fontSize: '11.5px' }}>
            <i className="bi bi-heart-pulse me-1"></i>
            Delivery: {item.procedure_done || item.procedure_name || 'Obstetric Delivery'}
          </div>
          {(item.picme_no || item.vhn_name || item.phc) && (
            <div className="text-muted" style={{ fontSize: '11px' }}>
              {[
                item.picme_no ? `PICME: ${item.picme_no}` : null,
                item.vhn_name ? `VHN: ${item.vhn_name}` : null,
                item.phc ? `PHC: ${item.phc}` : null,
              ]
                .filter(Boolean)
                .join(' • ')}
            </div>
          )}
          {item.newborns && item.newborns.length > 0 ? (
            item.newborns.map((baby, bIdx) => {
              const sexLower = (baby.sex || '').trim().toLowerCase();
              const isBoy = sexLower === 'male' || sexLower === 'boy';
              const isGirl = sexLower === 'female' || sexLower === 'girl';
              const badgeCls = isBoy
                ? 'bg-primary-subtle text-primary border border-primary-subtle'
                : isGirl
                ? 'bg-success-subtle text-success border border-success-subtle'
                : 'bg-secondary-subtle text-secondary border border-secondary-subtle';
              const sexLabel = isBoy ? 'Boy' : isGirl ? 'Girl' : baby.sex || 'Not Recorded';
              const vaccinesText = formatBirthVaccinesText(
                baby.birth_vaccines,
                baby.vaccine_dates,
                baby.vaccine_date
              );

              return (
                <div key={baby.id || bIdx} className="mt-1 p-1 px-2 rounded border bg-white" style={{ fontSize: '11px' }}>
                  <div className="d-flex flex-wrap align-items-center gap-2">
                    <span className={`badge ${badgeCls}`}>{sexLabel}</span>
                    {baby.birth_at && <span className="text-muted">{formatDateTime(baby.birth_at)}</span>}
                  </div>
                  <div className="mt-1">
                    <strong>Birth Wt:</strong> {baby.birth_weight_kg ? `${Number(baby.birth_weight_kg).toFixed(2)} kg` : '—'}
                    {baby.discharge_weight_kg && (
                      <> | <strong>Disch Wt:</strong> {Number(baby.discharge_weight_kg).toFixed(2)} kg</>
                    )}
                    {(baby.apgar_1_min !== null || baby.apgar_5_min !== null) && (
                      <> | <strong>APGAR:</strong> {baby.apgar_1_min ?? '—'}/{baby.apgar_5_min ?? '—'}</>
                    )}
                  </div>
                  {baby.neonatal_care && <div className="text-muted"><strong>Care:</strong> {baby.neonatal_care}</div>}
                  {baby.notes && <div className="text-dark"><strong>Notes:</strong> {baby.notes}</div>}
                  {vaccinesText && <div className="text-success"><strong>Vaccines:</strong> {vaccinesText}</div>}
                </div>
              );
            })
          ) : (
            <div className="text-muted fst-italic" style={{ fontSize: '11px' }}>
              Newborn details not recorded
            </div>
          )}
        </div>
      )}
    </div>
  );
}

interface Props {
  sectionKey: DaySectionKey;
  items: DayRecord[];
  censusDate?: string;
}

export default function DayReportSectionTable({ sectionKey, items, censusDate }: Props) {
  const meta = SECTION_META[sectionKey];

  return (
    <div className="card border-0 shadow-sm mb-4" style={{ borderRadius: '18px' }}>
      <div className="card-header bg-white py-3 px-4 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div className="d-flex align-items-center gap-2">
          <span
            className="p-2 rounded-circle"
            style={{ color: meta.accentColor, backgroundColor: meta.accentBg }}
          >
            <i className={`bi ${meta.icon}`}></i>
          </span>
          <div>
            <h5 className="card-title mb-0 fw-bold text-dark" style={{ fontSize: '15.5px' }}>
              {meta.title}
              <span
                className="badge ms-2"
                style={{ backgroundColor: meta.accentBg, color: meta.accentColor, fontSize: '11.5px' }}
              >
                {items.length}
              </span>
            </h5>
            <div className="text-muted small" style={{ fontSize: '12px' }}>
              {meta.subtitle}
              {sectionKey === 'inpatients' && censusDate ? ` (Census: ${formatDate(censusDate)})` : ''}
            </div>
          </div>
        </div>
      </div>

      <div className="table-responsive">
        <table className="table table-hover align-top mb-0" style={{ fontSize: '12.5px' }}>
          <thead className="table-light text-muted small text-uppercase" style={{ fontSize: '11px', letterSpacing: '0.04em' }}>
            <tr>
              <th className="ps-3 text-center" style={{ width: '48px' }}>S.No</th>
              <th style={{ minWidth: '195px' }}>Patient, Attender &amp; Contact</th>
              {sectionKey === 'admissions' && (
                <>
                  <th style={{ minWidth: '175px' }}>Admission, Ward &amp; Payment</th>
                  <th style={{ minWidth: '215px' }}>Diagnosis, Vitals &amp; Intake</th>
                  <th style={{ minWidth: '250px' }}>OT Procedure, Team &amp; Delivery</th>
                  <th style={{ minWidth: '115px' }}>Current Status</th>
                </>
              )}
              {sectionKey === 'discharges' && (
                <>
                  <th style={{ minWidth: '175px' }}>DOA → DOD &amp; Consultant</th>
                  <th style={{ minWidth: '245px' }}>Diagnosis, Procedure &amp; Baby</th>
                  <th style={{ minWidth: '220px' }}>Condition, Vitals &amp; Advice</th>
                  <th style={{ minWidth: '165px' }}>Payment &amp; MRD Sign-Off</th>
                </>
              )}
              {sectionKey === 'inpatients' && (
                <>
                  <th style={{ minWidth: '175px' }}>Ward Stay, DOA &amp; Payment</th>
                  <th style={{ minWidth: '215px' }}>Diagnosis, Vitals &amp; Course</th>
                  <th style={{ minWidth: '250px' }}>OT Procedure, Team &amp; Delivery</th>
                  <th style={{ minWidth: '120px' }}>Record Status</th>
                </>
              )}
              <th className="text-end pe-3 no-print" style={{ width: '95px' }}>Action</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 ? (
              <tr>
                <td colSpan={7} className="text-center py-4 text-muted">
                  <i className="bi bi-inbox fs-3 d-block mb-1 text-muted"></i>
                  {meta.emptyText}
                </td>
              </tr>
            ) : (
              items.map((item, idx) => {
                const payText = item.payment_summary || formatPaymentSummary(item);
                const stayBadge =
                  sectionKey === 'inpatients'
                    ? `Day ${item.census_stay_days || item.stay_days || 1}`
                    : `${item.stay_days || 1} day(s) LOS`;

                return (
                  <tr key={`${sectionKey}-${item.id}`}>
                    <td className="ps-3 text-center fw-semibold text-muted">{idx + 1}</td>

                    <PatientContactCell item={item} />

                    {/* Column 3: Timeline, Ward, Consultant, Format/Payment */}
                    <td>
                      <div className="small">
                        <div>
                          <span className="text-muted fw-semibold">DOA:</span>{' '}
                          <span className="text-dark fw-medium">{item.admission_at ? formatDateTime(item.admission_at) : '—'}</span>
                        </div>
                        {item.procedure_at && (
                          <div>
                            <span className="text-muted fw-semibold">DOO:</span>{' '}
                            <span>{formatDateTime(item.procedure_at)}</span>
                          </div>
                        )}
                        {sectionKey === 'discharges' && (
                          <div>
                            <span className="text-muted fw-semibold">DOD:</span>{' '}
                            <span className="text-success fw-bold">
                              {item.discharge_at ? formatDateTime(item.discharge_at) : '—'}
                            </span>
                          </div>
                        )}
                        <div className="mt-1">
                          <span className="badge bg-light text-dark border">{stayBadge}</span>{' '}
                          <span className="badge bg-secondary-subtle text-dark border text-uppercase">
                            {item.format || 'general'}
                          </span>
                        </div>
                        {(item.department || item.ward_room_no) && (
                          <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                            <strong>Ward/Dept:</strong> {[item.department, item.ward_room_no].filter(Boolean).join(' • ')}
                          </div>
                        )}
                        {item.consultant && (
                          <div className="text-dark mt-1" style={{ fontSize: '11.5px' }}>
                            <strong>Consultant:</strong> {item.consultant}
                          </div>
                        )}
                        {sectionKey !== 'discharges' && payText && payText !== '—' && (
                          <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                            <strong>Payment:</strong> {payText}
                          </div>
                        )}
                      </div>
                    </td>

                    {/* Column 4 */}
                    {sectionKey === 'discharges' ? (
                      <td>
                        <div className="fw-semibold text-dark mb-1">
                          {item.diagnosis || '—'}
                          {item.icd_code && (
                            <span className="badge bg-light text-dark border ms-1 font-monospace">
                              {item.icd_code}
                            </span>
                          )}
                        </div>
                        <div className="mb-1">
                          <WorkflowBadge type={item.surgery_type} />
                        </div>
                        <ProcedureAndTeamBlock item={item} />
                      </td>
                    ) : (
                      <td>
                        <div className="mb-1">
                          <WorkflowBadge type={item.surgery_type} />
                          {item.icd_code && (
                            <span className="badge bg-light text-dark border ms-1 font-monospace">
                              ICD: {item.icd_code}
                            </span>
                          )}
                        </div>
                        <div className="fw-semibold text-dark">{item.diagnosis || '—'}</div>
                        <VitalsBadges bp={item.vital_bp} pulse={item.vital_pulse} spo2={item.vital_spo2} title="Vitals" />
                        {sectionKey === 'admissions' && item.clinical_history && (
                          <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                            <strong>History:</strong> {truncateText(item.clinical_history)}
                          </div>
                        )}
                        {sectionKey === 'inpatients' && (item.treatment_course || item.examination) && (
                          <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                            <strong>Clinical Note:</strong> {truncateText(item.treatment_course || item.examination)}
                          </div>
                        )}
                      </td>
                    )}

                    {/* Column 5 */}
                    {sectionKey === 'discharges' ? (
                      <td>
                        <div className="small">
                          <div className="text-dark">
                            <strong>Condition:</strong> {item.discharge_condition || 'Stable'}
                          </div>
                          <VitalsBadges
                            bp={item.discharge_bp || item.vital_bp}
                            pulse={item.discharge_pulse || item.vital_pulse}
                            spo2={item.discharge_spo2 || item.vital_spo2}
                            title="Disch Vitals"
                          />
                          {item.discharge_advice && (
                            <div className="text-muted mt-1" style={{ fontSize: '11px' }}>
                              <strong>Advice:</strong> {truncateText(item.discharge_advice, 120)}
                            </div>
                          )}
                          {(item.post_surgery_diet || item.post_surgery_activity) && (
                            <div className="text-muted" style={{ fontSize: '11px' }}>
                              {[
                                item.post_surgery_diet ? `Diet: ${truncateText(item.post_surgery_diet, 50)}` : null,
                                item.post_surgery_activity ? `Activity: ${truncateText(item.post_surgery_activity, 50)}` : null,
                              ]
                                .filter(Boolean)
                                .join(' | ')}
                            </div>
                          )}
                          {(item.follow_up || item.resolved_review_date || item.suture_removal_date || item.stent_removal_date) && (
                            <div className="text-primary mt-1" style={{ fontSize: '11px' }}>
                              {[
                                item.follow_up ? `Follow-up: ${truncateText(item.follow_up, 60)}` : null,
                                item.suture_removal_date ? `Suture Removal: ${formatDate(item.suture_removal_date)}` : null,
                                item.stent_removal_date ? `Stent Removal: ${formatDate(item.stent_removal_date)}` : null,
                              ]
                                .filter(Boolean)
                                .join(' • ')}
                            </div>
                          )}
                        </div>
                      </td>
                    ) : (
                      <td>
                        <ProcedureAndTeamBlock item={item} />
                      </td>
                    )}

                    {/* Column 6 */}
                    {sectionKey === 'discharges' ? (
                      <td>
                        <div className="small d-flex flex-column gap-1">
                          <div>{payText !== '—' ? payText : <span className="text-muted">Cash / General</span>}</div>
                          {item.approved_amount && (
                            <div className="text-success fw-semibold" style={{ fontSize: '11px' }}>
                              Approved: Rs. {Number(item.approved_amount).toLocaleString('en-IN')}
                            </div>
                          )}
                          {(item.handover_name || item.floor_incharge_name || item.mrd_incharge_name) && (
                            <div className="text-muted" style={{ fontSize: '11px', lineHeight: 1.35 }}>
                              {item.handover_name && <div><strong>Handover:</strong> {item.handover_name}</div>}
                              {item.floor_incharge_name && <div><strong>Floor:</strong> {item.floor_incharge_name}</div>}
                              {item.mrd_incharge_name && <div><strong>MRD:</strong> {item.mrd_incharge_name}</div>}
                            </div>
                          )}
                          <div>
                            <StatusBadge status={item.status} />
                          </div>
                        </div>
                      </td>
                    ) : (
                      <td>
                        <div className="d-flex flex-column gap-1">
                          {item.discharge_at ? (
                            <span className="badge bg-success-subtle text-success border border-success-subtle">
                              Discharged {formatDate(item.discharge_at)}
                            </span>
                          ) : (
                            <span className="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                              In Admission
                            </span>
                          )}
                          <StatusBadge status={item.status} />
                        </div>
                      </td>
                    )}

                    <td className="text-end pe-3 no-print">
                      <Link
                        href={`/admissions/${item.id}/documents/preview`}
                        className="btn btn-sm btn-outline-secondary"
                        style={{ borderRadius: '8px', fontSize: '12px' }}
                      >
                        <i className="bi bi-eye me-1"></i> Review
                      </Link>
                    </td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
