import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import { toUpperCaseValue } from '../../utils/inputNormalizers';
import { joinMultiValues, parseMultiValues } from '../../utils/multiSelect';
import { getClinicalTeamLabels, isNaturalDeliveryCase } from '../../utils/deliveryMode';
import AnesthesiaSelect, { AnesthesiaTypeItem } from '../../Components/AnesthesiaSelect';
import QuickAddTeamModal, { QuickAddModalConfig } from '../../Components/QuickAddTeamModal';
import QuickAddDropdownModal, {
  QuickAddDropdownConfig,
  SavedDropdownOption,
} from '../../Components/QuickAddDropdownModal';
import AdmissionStageNav from '../../Components/AdmissionStageNav';
import { PROCEDURE_TEMPLATES } from '../../constants/procedureTemplates';
import Icd10SelectorModal from '../../Components/Icd10SelectorModal';
import { ICD10_CATALOG } from '../../constants/icd10Catalog';
import { formatDate } from '../../utils/dateFormatter';

const BIRTH_VACCINES = ['Vitamin K', 'BCG', 'OPV', 'HEP-B1'] as const;

interface PostopOrderGroup {
  group: string;
  is_custom?: boolean;
  options: string[];
}

const DEFAULT_POSTOP_ORDER_GROUPS: PostopOrderGroup[] = [
  {
    group: 'Post-Op Medications & Injections',
    is_custom: false,
    options: [
      'Inj - Cefbact 1.5g BD IV',
      'Inj - Diclo IM (sos)',
      'Inj - Metrogyl 100mg TDS IV',
      'Inj - Pan 40mg IV BD',
      'Inj. Tramadol 50mg IV SOS (severe pain)',
      'Inj. Ondansetron 4mg IV SOS (vomiting)',
      'Inj - Oxytocin 10 IU in 500ml RL (post-LSCS)',
    ],
  },
  {
    group: 'IV Fluids',
    is_custom: false,
    options: [
      'IV Fluids: RL @ 100 ml/hr',
      'IV Fluids: DNS @ 100 ml/hr',
      'IV Fluids: NS 500ml @ 75 ml/hr',
    ],
  },
  {
    group: 'Nursing & Patient Care Orders',
    is_custom: false,
    options: [
      'NPO for 6 hours',
      'Sips of water after 6 hours if bowel sounds present',
      'Monitor vitals q1h x 4h, then q4h (BP, Pulse, SpO2, Temp)',
      'Catheter care & monitor hourly urine output',
      'Check dressing site for soakage / bleeding',
      'Semi-fowler position',
    ],
  },
];

interface Newborn {
  id?: number;
  sex?: string;
  birth_at?: string;
  birth_weight_kg?: string | number;
  discharge_weight_kg?: string | number;
  apgar_1_min?: number;
  apgar_5_min?: number;
  neonatal_care?: string;
  notes?: string;
  birth_vaccines?: string[];
  vaccine_date?: string;
  vaccine_dates?: Record<string, string>;
}

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  picme_no?: string | null;
  age?: string | null;
  sex?: string | null;
  address?: string | null;
  contact?: string | null;
  admission_at: string;
  procedure_at?: string | null;
  discharge_at?: string | null;
  consultant?: string | null;
  department?: string | null;
  ward_room_no?: string | null;
  format: string;
  surgery_type: string;
  diagnosis: string;
  investigations?: string | null;
  procedure_name?: string | null;
  procedure_done?: string | null;
  icd_code?: string | null;
  surgeon?: string | null;
  assisted_surgeon?: string | null;
  pediatrician_name?: string | null;
  anaesthetist?: string | null;
  assisted_anaesthetist?: string | null;
  assistant?: string | null;
  assisted_by?: string | null;
  staff_nurse?: string | null;
  ot_technician?: string | null;
  anaesthesia_type?: string | null;
  procedure_note?: string | null;
  incision?: string | null;
  findings?: string | null;
  complications?: string | null;
  blood_loss_ml?: string | number | null;
  specimen?: string | null;
  drain?: string | null;
  postoperative_orders?: string | null;
  stent_removal_date?: string | null;
  status: string;
  newborns?: Newborn[];
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
  anesthesiaTypes?: AnesthesiaTypeItem[];
  postopOrderGroups?: PostopOrderGroup[];
}

export default function AdmissionsSurgery({
  admission,
  clinicalStaff = [],
  anesthesiaTypes = [],
  postopOrderGroups = [],
}: Props) {
  const isDelivery = admission.surgery_type === 'delivery';
  const isMedical = admission.surgery_type === 'medical';

  const initialNewborns: Newborn[] =
    admission.newborns && admission.newborns.length > 0
      ? admission.newborns.map((nb) => {
        const selectedVax = Array.isArray(nb.birth_vaccines) ? nb.birth_vaccines : [];
        const fallbackDate = nb.vaccine_date ? String(nb.vaccine_date).slice(0, 10) : '';
        const perVaxDates: Record<string, string> = {};
        if (nb.vaccine_dates && typeof nb.vaccine_dates === 'object') {
          Object.entries(nb.vaccine_dates).forEach(([k, v]) => {
            if (v) perVaxDates[k] = String(v).slice(0, 10);
          });
        }
        selectedVax.forEach((v) => {
          if (!perVaxDates[v] && fallbackDate) {
            perVaxDates[v] = fallbackDate;
          }
        });

        return {
          ...nb,
          birth_at: nb.birth_at ? nb.birth_at.slice(0, 16) : '',
          discharge_weight_kg: nb.discharge_weight_kg ?? '',
          birth_vaccines: selectedVax,
          vaccine_date: fallbackDate,
          vaccine_dates: perVaxDates,
        };
      })
      : isDelivery
        ? [
          {
            sex: '',
            birth_at: '',
            birth_weight_kg: '',
            discharge_weight_kg: '',
            apgar_1_min: undefined,
            apgar_5_min: undefined,
            neonatal_care: 'Routine newborn care',
            notes: '',
            birth_vaccines: [],
            vaccine_date: '',
            vaccine_dates: {},
          },
        ]
        : [];

  const { data, setData, put, transform, processing, errors } = useForm({
    picme_no: admission.picme_no || '',
    procedure_at: admission.procedure_at ? admission.procedure_at.slice(0, 16) : '',
    procedure_name: admission.procedure_name || '',
    procedure_done: admission.procedure_done || '',
    icd_code: admission.icd_code || '',
    surgeon: toUpperCaseValue(admission.surgeon || ''),
    assisted_surgeon: toUpperCaseValue(admission.assisted_surgeon || ''),
    pediatrician_name: toUpperCaseValue(admission.pediatrician_name || ''),
    anaesthetist: toUpperCaseValue(admission.anaesthetist || ''),
    assisted_anaesthetist: toUpperCaseValue(admission.assisted_anaesthetist || ''),
    assistant: toUpperCaseValue(admission.assistant || ''),
    assisted_by: toUpperCaseValue(admission.assisted_by || ''),
    staff_nurse: toUpperCaseValue(admission.staff_nurse || ''),
    ot_technician: toUpperCaseValue(admission.ot_technician || ''),
    anaesthesia_type: admission.anaesthesia_type || '',
    procedure_note: admission.procedure_note || '',
    incision: admission.incision || '',
    findings: admission.findings || '',
    complications: admission.complications || '',
    blood_loss_ml: admission.blood_loss_ml || '',
    specimen: admission.specimen || '',
    drain: admission.drain || '',
    postoperative_orders: admission.postoperative_orders || '',
    stent_removal_date: admission.stent_removal_date ? String(admission.stent_removal_date).slice(0, 10) : '',
    newborns: initialNewborns,
    next_step: 'discharge',
  });

  // Parse existing postoperative orders into individual rows
  const parseOrders = (raw?: string | null): string[] => {
    if (!raw || !raw.trim()) {
      return [''];
    }
    const lines = raw
      .split(/\r?\n/)
      .map((s) => s.replace(/^[-*•\d+.\s]+/, '').trim())
      .filter(Boolean);
    if (lines.length === 0) {
      const commaParts = raw
        .split(',')
        .map((s) => s.replace(/^[-*•\d+.\s]+/, '').trim())
        .filter(Boolean);
      if (commaParts.length > 1) return commaParts;
      return [raw.trim()];
    }
    return lines;
  };

  const [orderRows, setOrderRows] = useState<string[]>(() => parseOrders(admission.postoperative_orders));

  // Stent detection in operative procedure
  const hasStentInText = Boolean(
    (admission.procedure_name || '').match(/stent/i) ||
    (admission.procedure_done || '').match(/stent/i) ||
    (admission.procedure_note || '').match(/stent/i) ||
    (admission.findings || '').match(/stent/i) ||
    (admission.drain || '').match(/stent/i)
  );
  const [hasStent, setHasStent] = useState<boolean>(hasStentInText);
  const [selectedTemplateId, setSelectedTemplateId] = useState<string>('');
  const [showIcdModal, setShowIcdModal] = useState<boolean>(false);
  const [staffOptions, setStaffOptions] = useState<ClinicalStaffMember[]>(clinicalStaff);
  const [anesthesiaOptions, setAnesthesiaOptions] = useState<AnesthesiaTypeItem[]>(anesthesiaTypes);
  const [quickAddConfig, setQuickAddConfig] = useState<QuickAddModalConfig | null>(null);
  const [orderDropdownGroups, setOrderDropdownGroups] = useState<PostopOrderGroup[]>(() =>
    postopOrderGroups && postopOrderGroups.length > 0 ? postopOrderGroups : DEFAULT_POSTOP_ORDER_GROUPS
  );
  const [quickAddOrderModal, setQuickAddOrderModal] = useState<QuickAddDropdownConfig | null>(null);
  const [quickAddOrderRowIdx, setQuickAddOrderRowIdx] = useState<number | null>(null);

  const flatOrderOptions = orderDropdownGroups.flatMap((grp) => grp.options);
  const knownOrderKeys = new Set(flatOrderOptions.map((item) => item.trim().toLowerCase()));
  const [deliveryMode, setDeliveryMode] = useState<'surgical' | 'natural'>(() =>
    isNaturalDeliveryCase(admission) ? 'natural' : 'surgical'
  );

  const isNaturalDelivery =
    isDelivery &&
    (isNaturalDeliveryCase({
      surgery_type: admission.surgery_type,
      procedure_name: data.procedure_name,
      procedure_done: data.procedure_done,
    }) ||
      (deliveryMode === 'natural' && !/\b(lscs|caesarean|cesarean|c-section)\b/i.test(`${data.procedure_done} ${data.procedure_name}`)));

  const teamLabels = getClinicalTeamLabels(isNaturalDelivery);

  const handleDeliveryModeSwitch = (mode: 'surgical' | 'natural') => {
    setDeliveryMode(mode);
    if (mode === 'natural' && !data.procedure_name.trim() && !data.procedure_done.trim()) {
      setData((prev) => ({
        ...prev,
        procedure_name: 'Normal Vaginal Delivery (FTND)',
        procedure_done: 'Normal Vaginal Delivery (FTND)',
      }));
    }
  };

  type TeamFieldKey =
    | 'surgeon'
    | 'assisted_surgeon'
    | 'anaesthetist'
    | 'assisted_anaesthetist'
    | 'anaesthesia_type'
    | 'staff_nurse'
    | 'ot_technician'
    | 'pediatrician_name'
    | 'assisted_by';

  const handleStaffAdded = (fieldKey: string, staff: ClinicalStaffMember, useUpper: boolean) => {
    setStaffOptions((prev) => {
      const idx = prev.findIndex((item) => item.id === staff.id);
      if (idx >= 0) {
        const next = [...prev];
        next[idx] = staff;
        return next;
      }
      return [...prev, staff];
    });

    const typedKey = fieldKey as TeamFieldKey;
    const normFn = useUpper ? toUpperCaseValue : (v: string) => v.trim();
    const existing = parseMultiValues(data[typedKey], [], normFn);
    setData(typedKey, joinMultiValues([...existing, staff.name], normFn));
  };

  const handleAnesthesiaAdded = (fieldKey: string, item: AnesthesiaTypeItem) => {
    setAnesthesiaOptions((prev) => {
      const idx = prev.findIndex((existingItem) => existingItem.id === item.id);
      if (idx >= 0) {
        const next = [...prev];
        next[idx] = item;
        return next;
      }
      return [...prev, item];
    });

    const typedKey = fieldKey as TeamFieldKey;
    const existing = parseMultiValues(data[typedKey], []);
    setData(typedKey, joinMultiValues([...existing, item.name]));
  };

  const handleLoadProcedureTemplate = (templateId: string) => {
    setSelectedTemplateId(templateId);
    if (!templateId) return;
    const tpl = PROCEDURE_TEMPLATES.find((t) => t.id === templateId);
    if (!tpl) return;

    setData((prev) => ({
      ...prev,
      procedure_note: tpl.procedureNote,
      procedure_name: tpl.procedureName || prev.procedure_name,
      procedure_done: tpl.procedureDone || prev.procedure_done,
      incision: tpl.incision || prev.incision,
      findings: tpl.findings || prev.findings,
      blood_loss_ml: tpl.bloodLossMl !== undefined ? String(tpl.bloodLossMl) : prev.blood_loss_ml,
      icd_code: tpl.icdCode || prev.icd_code,
      anaesthesia_type: tpl.anesthesiaType || prev.anaesthesia_type,
    }));

    if (tpl.hasStent) {
      setHasStent(true);
    }

    if (isDelivery) {
      setDeliveryMode(
        isNaturalDeliveryCase({
          surgery_type: admission.surgery_type,
          procedure_name: tpl.procedureName,
          procedure_done: tpl.procedureDone,
        })
          ? 'natural'
          : 'surgical'
      );
    }
  };

  const serializeOrders = (rows: string[]): string => {
    const valid = rows.map((r) => r.trim()).filter(Boolean);
    return valid.map((r, i) => `${i + 1}. ${r}`).join('\n');
  };

  const updateOrderRow = (idx: number, val: string) => {
    const updated = [...orderRows];
    updated[idx] = val;
    setOrderRows(updated);
  };

  const addOrderRow = (text = '') => {
    setOrderRows((prev) => {
      if (text.trim() && prev.length === 1 && !prev[0].trim()) {
        return [text];
      }
      return [...prev, text];
    });
  };

  const openQuickAddOrderModal = (prefill = '', rowIdx: number | null = null) => {
    setQuickAddOrderRowIdx(rowIdx);
    setQuickAddOrderModal({
      type: 'postop_order',
      title: 'Add New Immediate Postoperative Order',
      fieldLabel: 'Postoperative Order Instruction',
      placeholder: 'e.g. Inj. Ceftriaxone 1g IV BD or Check drain output q4h',
      helperText: 'Saves to Common Orders dropdown immediately & persists for future surgeries',
      submitLabel: rowIdx !== null ? 'Save to Dropdown' : 'Save & Add Order Row',
      initialValue: prefill,
    });
  };

  const handleSavedOrderOption = (option: SavedDropdownOption) => {
    const clean = option.label.trim();
    if (!clean) return;
    const key = clean.toLowerCase();

    setOrderDropdownGroups((prev) => {
      const exists = prev.some((g) => g.options.some((o) => o.trim().toLowerCase() === key));
      if (exists) return prev;

      const targetGroup = option.group_label || 'Saved Hospital Orders (Custom)';
      const customIdx = prev.findIndex((g) => g.group === targetGroup);
      if (customIdx >= 0) {
        return prev.map((g, i) => (i === customIdx ? { ...g, options: [clean, ...g.options] } : g));
      }
      return [{ group: targetGroup, is_custom: true, options: [clean] }, ...prev];
    });

    if (quickAddOrderRowIdx !== null) {
      updateOrderRow(quickAddOrderRowIdx, clean);
      setQuickAddOrderRowIdx(null);
      return;
    }

    addOrderRow(clean);
  };

  const removeOrderRow = (idx: number) => {
    const updated = orderRows.filter((_, i) => i !== idx);
    setOrderRows(updated.length > 0 ? updated : ['']);
  };

  const moveOrderRow = (idx: number, direction: 'up' | 'down') => {
    const targetIdx = direction === 'up' ? idx - 1 : idx + 1;
    if (targetIdx < 0 || targetIdx >= orderRows.length) return;
    const updated = [...orderRows];
    const [moved] = updated.splice(idx, 1);
    updated.splice(targetIdx, 0, moved);
    setOrderRows(updated);
  };

  const addBaby = () => {
    setData('newborns', [
      ...data.newborns,
      {
        sex: '',
        birth_at: '',
        birth_weight_kg: '',
        discharge_weight_kg: '',
        apgar_1_min: undefined,
        apgar_5_min: undefined,
        neonatal_care: 'Routine newborn care',
        notes: '',
        birth_vaccines: [],
        vaccine_date: '',
        vaccine_dates: {},
      },
    ]);
  };

  const removeBaby = (index: number) => {
    setData(
      'newborns',
      data.newborns.filter((_, i) => i !== index)
    );
  };

  const updateBaby = (index: number, field: keyof Newborn, val: any) => {
    const updated = [...data.newborns];
    updated[index] = { ...updated[index], [field]: val };
    setData('newborns', updated);
  };

  const toggleBabyVaccine = (index: number, vaccine: string) => {
    const currentBaby = data.newborns[index] || {};
    const current = Array.isArray(currentBaby.birth_vaccines) ? currentBaby.birth_vaccines : [];
    const nextDates = { ...(currentBaby.vaccine_dates || {}) };
    const isRemoving = current.includes(vaccine);
    const next = isRemoving
      ? current.filter((v) => v !== vaccine)
      : BIRTH_VACCINES.filter((v) => v === vaccine || current.includes(v));

    if (isRemoving) {
      delete nextDates[vaccine];
    }

    const updated = [...data.newborns];
    updated[index] = { ...currentBaby, birth_vaccines: next, vaccine_dates: nextDates };
    setData('newborns', updated);
  };

  const updateBabyVaccineDate = (index: number, vaccine: string, dateValue: string) => {
    const currentBaby = data.newborns[index] || {};
    const current = Array.isArray(currentBaby.birth_vaccines) ? currentBaby.birth_vaccines : [];
    const nextDates = { ...(currentBaby.vaccine_dates || {}) };

    if (dateValue) {
      nextDates[vaccine] = dateValue;
    } else {
      delete nextDates[vaccine];
    }

    const next =
      dateValue && !current.includes(vaccine)
        ? BIRTH_VACCINES.filter((v) => v === vaccine || current.includes(v))
        : current;

    const updated = [...data.newborns];
    updated[index] = { ...currentBaby, birth_vaccines: next, vaccine_dates: nextDates };
    setData('newborns', updated);
  };

  const submitStage = (step: 'stay' | 'discharge', e: React.FormEvent) => {
    e.preventDefault();
    transform((current) => ({
      ...current,
      postoperative_orders: serializeOrders(orderRows),
      next_step: step,
    }));
    put(`/admissions/${admission.id}/surgery`);
  };

  const handleSaveOnly = (e: React.FormEvent) => submitStage('stay', e);

  const handleSaveAndNext = (e: React.FormEvent) => submitStage('discharge', e);

  return (
    <AppLayout>
      <Head title={`Stage 4: ${isMedical ? 'Clinical Notes' : 'Labour / Surgery'} — ${admission.patient_name}`} />

      {/* 5-Stage Navigation Header */}
      <AdmissionStageNav currentStage="surgery" admission={admission} />

      {isMedical && (
        <div className="alert alert-info d-flex flex-wrap align-items-center justify-content-between mb-4 border-0 shadow-sm p-3">
          <div className="d-flex align-items-center gap-2">
            <i className="bi bi-info-circle-fill fs-5 text-info"></i>
            <div>
              <strong>Medical Case (Non-Surgical):</strong> This patient is admitted for medical / conservative management. Operation theatre notes and newborn details are optional. You may record bedside notes or proceed directly to Stage 4.
            </div>
          </div>
          <Link
            href={`/admissions/${admission.id}/discharge`}
            className="btn btn-sm btn-info text-white text-nowrap fw-semibold mt-2 mt-md-0"
          >
            Skip to Stage 4: Discharge Summary <i className="bi bi-arrow-right ms-1"></i>
          </Link>
        </div>
      )}

      {admission.investigations && admission.investigations.trim().length > 0 && (
        <div className="card mb-4 border-0 shadow-sm bg-light">
          <div className="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div className="d-flex align-items-center gap-2">
              <span className="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                <i className="bi bi-clipboard2-pulse me-1"></i> Stage 2 Pre-Op Labs
              </span>
              <span className="text-secondary small fw-medium text-truncate" style={{ maxWidth: '650px' }}>
                {admission.investigations.replace(/\r?\n/g, '  |  ')}
              </span>
            </div>
            <Link
              href={`/admissions/${admission.id}/test-reports`}
              className="btn btn-sm btn-link text-decoration-none text-primary fw-semibold p-0"
            >
              <i className="bi bi-pencil-square me-1"></i> Edit Lab Tests
            </Link>
          </div>
        </div>
      )}

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 4: {isMedical ? 'Clinical & Bedside Notes' : 'Labour / LSCS / Surgery Notes'}
          </h1>
          <p className="text-muted mb-0">
            {isMedical
              ? 'Record any bedside procedures, conservative management notes, or clinical team details.'
              : 'Record Operation Theatre notes, surgical team members, procedure details, and newborn birth records.'}
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/test-reports`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
          </Link>
          <Link href={`/admissions/${admission.id}/documents/preview`} className="btn btn-outline-secondary">
            <i className="bi bi-file-earmark-text me-1"></i> Preview Documents
          </Link>
        </div>
      </div>

      <form id="surgeryForm">
        {/* Section 1: Procedure Info & Timing */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">4.1</span> Operation Theatre &amp; Procedure Schedule
            </div>
            <span className="text-muted small">Step 4 of 5</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label">Procedure / Delivery Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className={`form-control ${errors.procedure_at ? 'is-invalid' : ''}`}
                  value={data.procedure_at}
                  onChange={(e) => setData('procedure_at', e.target.value)}
                />
                {errors.procedure_at && <div className="invalid-feedback">{errors.procedure_at}</div>}
              </div>

              {isDelivery && (
                <div className="col-md-4">
                  <label className="form-label required">
                    RCH ID (PICME No)
                    <span className="badge bg-success-subtle text-success border border-success-subtle ms-2" style={{ fontSize: '0.68rem' }}>
                      DELIVERY / OBSTETRIC
                    </span>
                  </label>
                  <input
                    type="text"
                    className={`form-control ${errors.picme_no ? 'is-invalid' : ''}`}
                    value={data.picme_no}
                    onChange={(e) => setData('picme_no', e.target.value)}
                    placeholder="e.g. 331024567890 (12-digit PICME / RCH ID)"
                    maxLength={25}
                  />
                  {errors.picme_no && <div className="invalid-feedback">{errors.picme_no}</div>}
                </div>
              )}

              <div className={isDelivery ? 'col-md-4' : 'col-md-5'}>
                <label className="form-label">Procedure / Surgery Name</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.procedure_name}
                  onChange={(e) => {
                    const val = e.target.value;
                    setData((prev) => ({
                      ...prev,
                      procedure_name: val,
                      procedure_done: !prev.procedure_done || prev.procedure_done === prev.procedure_name ? val : prev.procedure_done,
                    }));
                  }}
                  placeholder="e.g. Emergency Lower Segment Caesarean Section (LSCS)"
                />
              </div>

              <div className={isDelivery ? 'col-md-4' : 'col-md-3'}>
                <label className="form-label">ICD-10 Code</label>
                <div className="input-group">
                  <input
                    type="text"
                    list="icd10-quick-datalist"
                    className="form-control border-end-0"
                    value={data.icd_code}
                    onChange={(e) => setData('icd_code', e.target.value)}
                    placeholder="e.g. O82 / N20.1 / K35.80"
                  />
                  <button
                    type="button"
                    className="btn bg-white border border-start-0 text-primary d-inline-flex align-items-center gap-1 px-2"
                    style={{ fontSize: '0.78rem', borderColor: 'var(--bs-border-color, #dee2e6)' }}
                    onClick={() => setShowIcdModal(true)}
                    title="Search ICD-10 Codes"
                    aria-label="Search ICD-10"
                  >
                    <i className="bi bi-search"></i>
                    <span className="d-none d-xl-inline">Search</span>
                  </button>
                </div>
                <datalist id="icd10-quick-datalist">
                  {ICD10_CATALOG.filter((item) => {
                    const q = (data.icd_code || '').trim().toLowerCase();
                    if (!q) return ['O80', 'O82', 'O84.2', 'N20.0', 'N20.1', 'N40.1', 'K35.80', 'K80.00', 'K40.90', 'D25.9'].includes(item.code);
                    return item.code.toLowerCase().includes(q) || item.description.toLowerCase().includes(q);
                  }).slice(0, 30).map((item) => (
                    <option key={item.code} value={item.code}>
                      {item.code} — {item.description}
                    </option>
                  ))}
                </datalist>
              </div>

              <div className="col-12">
                <label className="form-label">Procedure Done / Operation Summary</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.procedure_done}
                  onChange={(e) => setData('procedure_done', e.target.value)}
                  placeholder="e.g. Emergency LSCS with Pfannenstiel Incision & Bilateral Tubal Ligation"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 2: Surgical / Delivery Team */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <span className="badge bg-primary me-2">4.2</span> {teamLabels.sectionTitle}
            </div>
            <div className="d-flex flex-wrap align-items-center gap-2">
              {isDelivery && (
                <div className="btn-group btn-group-sm" role="group" aria-label="Delivery mode">
                  <button
                    type="button"
                    className={`btn ${!isNaturalDelivery ? 'btn-primary' : 'btn-outline-secondary'}`}
                    onClick={() => handleDeliveryModeSwitch('surgical')}
                  >
                    Surgical / LSCS
                  </button>
                  <button
                    type="button"
                    className={`btn ${isNaturalDelivery ? 'btn-success' : 'btn-outline-secondary'}`}
                    onClick={() => handleDeliveryModeSwitch('natural')}
                  >
                    Natural / Vaginal Delivery
                  </button>
                </div>
              )}
              <span className="badge bg-light text-muted border fw-normal" style={{ fontSize: '0.72rem' }}>
                Multiple choice enabled &bull; Open any dropdown to select or add new
              </span>
            </div>
          </div>
          <div className="card-body">
            <div className="row g-3">
              {([
                {
                  key: 'surgeon',
                  label: teamLabels.primaryDoctorLabel,
                  role: isNaturalDelivery ? 'delivery_conductor' : 'surgeon',
                  placeholder: isNaturalDelivery ? '-- Select Delivery Doctor(s) / Midwife --' : '-- Select Surgeon(s) --',
                },
                {
                  key: 'assisted_surgeon',
                  label: teamLabels.assistantDoctorLabel,
                  role: isNaturalDelivery ? 'delivery_assistant' : 'assisted_surgeon',
                  placeholder: isNaturalDelivery ? '-- Select Assisted Doctor(s) / Nurse --' : '-- Select Assisted Surgeon(s) --',
                },
                { key: 'anaesthetist', label: teamLabels.anaesthetistLabel, role: 'anaesthetist', placeholder: '-- Select Anaesthetist(s) --' },
                { key: 'assisted_anaesthetist', label: teamLabels.assistedAnaesthetistLabel, role: 'assisted_anaesthetist', placeholder: '-- Select Assisted Anaesthetist(s) --' },
                {
                  key: 'anaesthesia_type',
                  label: teamLabels.anaesthesiaTypeLabel,
                  kind: 'anesthesia',
                  placeholder: isNaturalDelivery ? '-- Select Pain Relief / Local (if given) --' : '-- Select Anaesthesia Type(s) --',
                },
                { key: 'staff_nurse', label: teamLabels.staffNurseLabel, role: 'staff_nurse', placeholder: '-- Select Staff Nurse(s) --' },
                { key: 'ot_technician', label: teamLabels.otTechLabel, role: 'ot_technician', placeholder: '-- Select Technician(s) / Assistant --' },
                { key: 'pediatrician_name', label: teamLabels.pediatricianLabel, role: 'consultant_pediatrician', placeholder: '-- Select Pediatrician(s) --' },
                { key: 'assisted_by', label: teamLabels.otherHelpLabel, role: 'all', placeholder: '-- Select Assistant(s) / Clinical Help --' },
              ] as const).map((field) => (
                <div key={field.key} className="col-md-4">
                  {'kind' in field && field.kind === 'anesthesia' ? (
                    <AnesthesiaSelect
                      label={field.label}
                      value={data.anaesthesia_type}
                      onChange={(val) => setData('anaesthesia_type', val)}
                      types={anesthesiaOptions}
                      anesthesiaList={anesthesiaOptions}
                      placeholder={field.placeholder}
                      multiple
                      onRequestAddNew={() =>
                        setQuickAddConfig({
                          fieldKey: field.key,
                          fieldLabel: field.label,
                          mode: 'anesthesia',
                        })
                      }
                    />
                  ) : (
                    <StaffSelect
                      label={field.label}
                      role={field.role}
                      value={data[field.key]}
                      onChange={(val) => setData(field.key, val)}
                      staffList={staffOptions}
                      placeholder={field.placeholder}
                      uppercase
                      multiple
                      onRequestAddNew={() =>
                        setQuickAddConfig({
                          fieldKey: field.key,
                          fieldLabel: field.label,
                          mode: 'staff',
                          defaultRole: field.role,
                          uppercase: true,
                        })
                      }
                    />
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Section 3: Operative Notes & Findings */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <span className="badge bg-primary me-2">4.3</span> Operative Narrative &amp; Clinical Findings
            </div>
            <div className="d-flex align-items-center gap-2">
              <label className="small text-muted mb-0 fw-semibold d-none d-md-inline" htmlFor="procedure-template-select">
                <i className="bi bi-magic me-1 text-primary"></i> Quick Load Template:
              </label>
              <select
                id="procedure-template-select"
                className="form-select form-select-sm fg-responsive-select"
                style={{ minWidth: '270px', borderRadius: '8px' }}
                value={selectedTemplateId}
                onChange={(e) => handleLoadProcedureTemplate(e.target.value)}
                aria-label="Quick Load Operative Procedure Template"
              >
                <option value="">-- Quick Load Standard Note (Max 5 Lines) --</option>
                {Array.from(new Set(PROCEDURE_TEMPLATES.map((t) => t.category))).map((cat) => (
                  <optgroup key={cat} label={cat}>
                    {PROCEDURE_TEMPLATES.filter((t) => t.category === cat).map((tpl) => (
                      <option key={tpl.id} value={tpl.id}>
                        {tpl.name}
                      </option>
                    ))}
                  </optgroup>
                ))}
              </select>
            </div>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-12">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label mb-0">Detailed Operative Procedure Note</label>
                  <span className="text-muted" style={{ fontSize: '11.5px' }}>
                    Standard concise format (max 5 lines recommended)
                  </span>
                </div>
                <textarea
                  className="form-control"
                  rows={5}
                  value={data.procedure_note}
                  onChange={(e) => setData('procedure_note', e.target.value)}
                  placeholder="Describe step-by-step operative technique, closure, and findings (or select from Quick Load Template above)..."
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Incision</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.incision}
                  onChange={(e) => setData('incision', e.target.value)}
                  placeholder="e.g. Pfannenstiel incision / Right lower paramedian"
                />
              </div>

              <div className="col-md-6">
                <label className="form-label">Operative Findings</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.findings}
                  onChange={(e) => setData('findings', e.target.value)}
                  placeholder="e.g. Single live male infant, clear liquor, intact placenta"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Blood Loss (ml)</label>
                <input
                  type="number"
                  step="0.1"
                  className="form-control"
                  value={data.blood_loss_ml}
                  onChange={(e) => setData('blood_loss_ml', e.target.value)}
                  placeholder="e.g. 350"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Complications</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.complications}
                  onChange={(e) => setData('complications', e.target.value)}
                  placeholder="e.g. Nil / None"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Specimen Sent</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.specimen}
                  onChange={(e) => setData('specimen', e.target.value)}
                  placeholder="e.g. Placenta / Appendix in formalin / Nil"
                />
              </div>

              <div className="col-md-3">
                <label className="form-label">Drain Placed</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.drain}
                  onChange={(e) => setData('drain', e.target.value)}
                  placeholder="e.g. Nil / Romovac drain in pelvic cavity"
                />
              </div>

              {/* Stent Placement Detection / Toggle */}
              <div className="col-12 mt-2 pt-2 border-top">
                <div className="card bg-light-subtle border">
                  <div className="card-body p-3">
                    <div className="form-check form-switch mb-1">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="hasStentSwitch"
                        checked={hasStent}
                        onChange={(e) => {
                          const val = e.target.checked;
                          setHasStent(val);
                          if (val && !data.findings.toLowerCase().includes('stent')) {
                            const newFindings = data.findings ? `${data.findings}. DJ stent in situ.` : 'DJ stent in situ.';
                            setData('findings', newFindings);
                          }
                        }}
                      />
                      <label className="form-check-label fw-bold text-dark" htmlFor="hasStentSwitch">
                        <i className="bi bi-shield-plus text-primary me-1"></i> Patient has STENT (e.g. DJ Stent inserted)
                      </label>
                    </div>

                    {hasStent && (
                      <div className="alert alert-warning py-3 px-3 mb-0 border-0 rounded-2 mt-2">
                        <div className="row g-3 align-items-center">
                          <div className="col-md-5">
                            <label className="form-label fw-bold text-dark required mb-1">
                              <i className="bi bi-calendar-event me-1 text-danger"></i> Stent Removal Date
                            </label>
                            <input
                              type="date"
                              className="form-control fw-bold border-warning"
                              value={data.stent_removal_date || ''}
                              onChange={(e) => setData('stent_removal_date', e.target.value)}
                              required={hasStent}
                            />
                            <div className="form-text text-muted small">
                              Target date when DJ Stent must be removed at hospital.
                            </div>
                          </div>
                          <div className="col-md-7">
                            <div className="small text-secondary">
                              <div className="fw-bold text-dark mb-1">Stent Removal Bilingual Notice (English &amp; Tamil):</div>
                              <div className="p-2 bg-white rounded border">
                                <div className="mb-1 text-dark">
                                  &bull; <strong>En:</strong> The DJ Stent placed inside needs to be removed on <strong>{data.stent_removal_date ? formatDate(data.stent_removal_date) : '(Date)'}</strong>. Please contact the hospital one day prior to confirm the time.
                                </div>
                                <div className="text-dark">
                                  &bull; <strong>Ta:</strong> வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு <strong>{data.stent_removal_date ? formatDate(data.stent_removal_date) : '(Date)'}</strong> அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>

              {/* Immediate Postoperative Orders (Dropdown & Row by Row) */}
              <div className="col-12 mt-3">
                <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                  <label className="form-label fw-bold text-dark mb-0">
                    <i className="bi bi-list-check text-primary me-1"></i> Immediate Postoperative Orders (Row by Row)
                  </label>
                  <div className="d-flex flex-wrap align-items-center gap-2">
                    <select
                      className="form-select form-select-sm fg-responsive-select"
                      style={{ minWidth: '280px' }}
                      defaultValue=""
                      onChange={(e) => {
                        if (e.target.value) {
                          addOrderRow(e.target.value);
                          e.target.value = '';
                        }
                      }}
                      aria-label="Select Common Postoperative Order to Add"
                    >
                      <option value="">-- Add Order from Common Orders Dropdown --</option>
                      {orderDropdownGroups.map((grp, gIdx) => (
                        <optgroup key={gIdx} label={grp.group}>
                          {grp.options.map((opt, optIdx) => (
                            <option key={optIdx} value={opt}>
                              {opt}
                            </option>
                          ))}
                        </optgroup>
                      ))}
                    </select>
                    <button
                      type="button"
                      onClick={() => openQuickAddOrderModal('', null)}
                      className="btn btn-sm btn-outline-success fw-semibold"
                      title="Save a new reusable postoperative order to this dropdown"
                    >
                      <i className="bi bi-bookmark-plus me-1"></i> New Order
                    </button>
                    <button
                      type="button"
                      onClick={() => addOrderRow()}
                      className="btn btn-sm btn-outline-primary fw-semibold"
                    >
                      <i className="bi bi-plus-circle me-1"></i> Add Row
                    </button>
                  </div>
                </div>
                <div className="small text-muted mb-2">
                  Tip: Any custom postoperative order you type &amp; save here will automatically appear in the Common Orders dropdown next time.
                </div>

                {/* Order Rows Repeater */}
                <div className="bg-light p-2.5 rounded-3 border">
                  {orderRows.map((order, oIdx) => {
                    const trimmedOrder = order.trim();
                    const isNewOrder = trimmedOrder.length > 0 && !knownOrderKeys.has(trimmedOrder.toLowerCase());
                    return (
                      <div key={oIdx} className="input-group mb-2 shadow-xs">
                        <span className="input-group-text bg-white fw-bold text-secondary" style={{ width: '48px', justifyContent: 'center' }}>
                          #{oIdx + 1}
                        </span>
                        <input
                          type="text"
                          className="form-control"
                          value={order}
                          onChange={(e) => updateOrderRow(oIdx, e.target.value)}
                          list="common-postop-orders-list"
                          placeholder={`Order #${oIdx + 1} (e.g. Monitor vitals q1h x 4h, IV fluids RL @ 100ml/hr)`}
                        />
                        {isNewOrder && (
                          <button
                            type="button"
                            onClick={() => openQuickAddOrderModal(trimmedOrder, oIdx)}
                            className="btn btn-outline-success"
                            title="Save this order to the Common Orders dropdown now"
                          >
                            <i className="bi bi-bookmark-plus"></i>
                          </button>
                        )}
                        <button
                          type="button"
                          onClick={() => moveOrderRow(oIdx, 'up')}
                          disabled={oIdx === 0}
                          className="btn btn-outline-secondary"
                          title="Move Up"
                        >
                          <i className="bi bi-arrow-up"></i>
                        </button>
                        <button
                          type="button"
                          onClick={() => moveOrderRow(oIdx, 'down')}
                          disabled={oIdx === orderRows.length - 1}
                          className="btn btn-outline-secondary"
                          title="Move Down"
                        >
                          <i className="bi bi-arrow-down"></i>
                        </button>
                        <button
                          type="button"
                          onClick={() => removeOrderRow(oIdx)}
                          disabled={orderRows.length <= 1 && !order.trim()}
                          className="btn btn-outline-danger"
                          title="Remove Order Row"
                        >
                          <i className="bi bi-trash"></i>
                        </button>
                      </div>
                    );
                  })}

                  <datalist id="common-postop-orders-list">
                    {flatOrderOptions.map((opt, optIdx) => (
                      <option key={optIdx} value={opt} />
                    ))}
                  </datalist>

                  <div className="mt-2 text-end">
                    <button
                      type="button"
                      onClick={() => addOrderRow()}
                      className="btn btn-sm btn-primary fw-semibold"
                    >
                      <i className="bi bi-plus-lg me-1"></i> Add Another Order Row
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Section 4: Newborn Details (Delivery Only) */}
        {isDelivery && (
          <div className="card mb-4 border-0 shadow-sm border-start border-4 border-primary">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center">
              <div>
                <span className="badge bg-primary me-2">3.4</span> Newborn / Birth Information
                <span className="text-muted small ms-2">(Delivery / Obstetric Workflow)</span>
              </div>
              <button type="button" onClick={addBaby} className="btn btn-sm btn-outline-primary">
                <i className="bi bi-plus-circle me-1"></i> Add Another Baby (Twins / Multiples)
              </button>
            </div>
            <div className="card-body">
              {data.newborns.length === 0 ? (
                <div className="text-center py-4 text-muted">
                  <p className="mb-2">No newborn baby records added yet.</p>
                  <button type="button" onClick={addBaby} className="btn btn-primary btn-sm">
                    <i className="bi bi-plus me-1"></i> Add Newborn Baby Details
                  </button>
                </div>
              ) : (
                data.newborns.map((baby, idx) => (
                  <div key={idx} className="p-3 bg-light rounded-3 mb-3 border">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                      <h6 className="fw-bold mb-0 text-dark">
                        <i className="bi bi-person-heart text-primary me-2"></i>
                        Newborn Baby #{idx + 1}
                      </h6>
                      {data.newborns.length > 1 && (
                        <button
                          type="button"
                          onClick={() => removeBaby(idx)}
                          className="btn btn-sm btn-outline-danger"
                        >
                          <i className="bi bi-trash me-1"></i> Remove Baby #{idx + 1}
                        </button>
                      )}
                    </div>

                    <div className="row g-3">
                      <div className="col-md-4">
                        <label className="form-label required">Sex</label>
                        <select
                          className="form-select"
                          value={baby.sex || ''}
                          onChange={(e) => updateBaby(idx, 'sex', e.target.value)}
                          required
                        >
                          <option value="">Select Sex</option>
                          <option value="Female">Female</option>
                          <option value="Male">Male</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>

                      <div className="col-md-4">
                        <label className="form-label">Birth Date &amp; Time</label>
                        <input
                          type="datetime-local"
                          className="form-control"
                          value={baby.birth_at || ''}
                          onChange={(e) => updateBaby(idx, 'birth_at', e.target.value)}
                        />
                      </div>

                      <div className="col-md-4">
                        <label className="form-label">Birth Weight (kg)</label>
                        <input
                          type="number"
                          step="0.01"
                          min="0.1"
                          max="8"
                          className="form-control"
                          value={baby.birth_weight_kg || ''}
                          onChange={(e) => updateBaby(idx, 'birth_weight_kg', e.target.value)}
                          placeholder="e.g. 2.85"
                        />
                      </div>

                      <div className="col-md-4">
                        <label className="form-label">Baby Weight on Discharge (kg)</label>
                        <input
                          type="number"
                          step="0.01"
                          min="0.1"
                          max="8"
                          className="form-control"
                          value={baby.discharge_weight_kg || ''}
                          onChange={(e) => updateBaby(idx, 'discharge_weight_kg', e.target.value)}
                          placeholder="e.g. 2.75"
                        />
                      </div>

                      <div className="col-md-4">
                        <label className="form-label">Apgar (1 min)</label>
                        <input
                          type="number"
                          min="0"
                          max="10"
                          className="form-control"
                          value={baby.apgar_1_min ?? ''}
                          onChange={(e) => updateBaby(idx, 'apgar_1_min', e.target.value ? parseInt(e.target.value) : undefined)}
                          placeholder="0-10"
                        />
                      </div>

                      <div className="col-md-4">
                        <label className="form-label">Apgar (5 min)</label>
                        <input
                          type="number"
                          min="0"
                          max="10"
                          className="form-control"
                          value={baby.apgar_5_min ?? ''}
                          onChange={(e) => updateBaby(idx, 'apgar_5_min', e.target.value ? parseInt(e.target.value) : undefined)}
                          placeholder="0-10"
                        />
                      </div>

                      <div className="col-md-6">
                        <label className="form-label">Neonatal Care / Immediate Outcome</label>
                        <input
                          type="text"
                          className="form-control"
                          value={baby.neonatal_care || ''}
                          onChange={(e) => updateBaby(idx, 'neonatal_care', e.target.value)}
                          placeholder="e.g. Routine newborn care, baby cried immediately at birth"
                        />
                      </div>

                      <div className="col-md-6">
                        <label className="form-label">Baby Notes / Presentation</label>
                        <input
                          type="text"
                          className="form-control"
                          value={baby.notes || ''}
                          onChange={(e) => updateBaby(idx, 'notes', e.target.value)}
                          placeholder="e.g. Cephalic presentation, clear liquor, no birth injuries"
                        />
                      </div>

                      <div className="col-12">
                        <div className="bg-white border rounded-3 p-3">
                          <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                              <label className="form-label fw-semibold mb-0">
                                <i className="bi bi-shield-plus text-success me-1"></i>
                                Birth Vaccines Given &amp; Administration Dates
                              </label>
                              <div className="text-muted" style={{ fontSize: '11.5px' }}>
                                Select each vaccine given and enter its own administration date (e.g. Vitamin K on birth date, BCG / OPV / HEP-B1 on discharge date).
                              </div>
                            </div>
                            <div className="d-flex align-items-center gap-2">
                              <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none"
                                onClick={() => {
                                  const allSelected = BIRTH_VACCINES.every((v) =>
                                    (baby.birth_vaccines || []).includes(v)
                                  );
                                  if (allSelected) {
                                    const updated = [...data.newborns];
                                    updated[idx] = { ...baby, birth_vaccines: [], vaccine_dates: {} };
                                    setData('newborns', updated);
                                  } else {
                                    updateBaby(idx, 'birth_vaccines', [...BIRTH_VACCINES]);
                                  }
                                }}
                              >
                                {BIRTH_VACCINES.every((v) => (baby.birth_vaccines || []).includes(v))
                                  ? 'Clear All'
                                  : 'Select All'}
                              </button>
                            </div>
                          </div>

                          <div className="row g-2">
                            {BIRTH_VACCINES.map((vaccine) => {
                              const checked = (baby.birth_vaccines || []).includes(vaccine);
                              const checkboxId = `newborn_${idx}_vax_${vaccine.replace(/[^a-zA-Z0-9]/g, '_')}`;
                              const vaxDateVal = baby.vaccine_dates?.[vaccine] || '';
                              const birthDateSrc = String(baby.birth_at || data.procedure_at || '').slice(0, 10);

                              return (
                                <div key={vaccine} className="col-md-6">
                                  <div className={`border rounded-2 p-2 d-flex align-items-center justify-content-between gap-2 ${checked ? 'border-success bg-success-subtle bg-opacity-10' : 'bg-light'}`}>
                                    <div className="form-check m-0">
                                      <input
                                        id={checkboxId}
                                        type="checkbox"
                                        className="form-check-input"
                                        checked={checked}
                                        onChange={() => toggleBabyVaccine(idx, vaccine)}
                                      />
                                      <label
                                        htmlFor={checkboxId}
                                        className="form-check-label fw-semibold text-dark"
                                        style={{ cursor: 'pointer' }}
                                      >
                                        {vaccine}
                                      </label>
                                    </div>

                                    <div className="d-flex align-items-center gap-1">
                                      {birthDateSrc && (
                                        <button
                                          type="button"
                                          className="btn btn-outline-secondary btn-sm py-0 px-2"
                                          style={{ fontSize: '11px', height: '30px' }}
                                          title={`Set ${vaccine} date to Birth Date (${birthDateSrc})`}
                                          onClick={() => updateBabyVaccineDate(idx, vaccine, birthDateSrc)}
                                        >
                                          Birth Date
                                        </button>
                                      )}
                                      <input
                                        type="date"
                                        className="form-control form-control-sm"
                                        style={{ width: '145px' }}
                                        aria-label={`${vaccine} given date`}
                                        value={vaxDateVal}
                                        onChange={(e) => updateBabyVaccineDate(idx, vaccine, e.target.value)}
                                      />
                                    </div>
                                  </div>
                                </div>
                              );
                            })}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        )}

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href={`/admissions/${admission.id}/test-reports`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 3: Test Reports
          </Link>
          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              Save Stage 4 Changes
            </button>
            <button
              type="button"
              onClick={handleSaveAndNext}
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Next: Stage 5 (Discharge Summary)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>

      <Icd10SelectorModal
        isOpen={showIcdModal}
        onClose={() => setShowIcdModal(false)}
        onSelect={(code) => setData('icd_code', code)}
        currentCode={data.icd_code}
      />

      <QuickAddTeamModal
        config={quickAddConfig}
        onClose={() => setQuickAddConfig(null)}
        onStaffAdded={handleStaffAdded}
        onAnesthesiaAdded={handleAnesthesiaAdded}
      />

      <QuickAddDropdownModal
        config={quickAddOrderModal}
        onClose={() => {
          setQuickAddOrderModal(null);
          setQuickAddOrderRowIdx(null);
        }}
        onSaved={handleSavedOrderOption}
      />
    </AppLayout>
  );
}

