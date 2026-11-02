import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import AdmissionStageNav from '../../Components/AdmissionStageNav';

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  picme_no?: string | null;
  age?: string | null;
  sex?: string | null;
  address?: string | null;
  address_line1?: string | null;
  address_line2?: string | null;
  address_line3?: string | null;
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
  clinical_history?: string | null;
  examination?: string | null;
  treatment_course?: string | null;
  discharge_condition?: string | null;
  medication_orders?: string | null;
  discharge_advice?: string | null;
  follow_up?: string | null;
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
  mrd_no?: string | null;
  payment_mode?: string | null;
  handover_name?: string | null;
  floor_incharge_name?: string | null;
  mrd_incharge_name?: string | null;
  status: string;
  newborns?: any[];
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
}

export default function AdmissionsEdit({ admission, clinicalStaff = [] }: Props) {
  const [customConsultant, setCustomConsultant] = useState(false);
  const { data, setData, put, processing, errors } = useForm({
    patient_name: admission.patient_name || '',
    uhid: admission.uhid || '',
    ip_no: admission.ip_no || '',
    picme_no: admission.picme_no || '',
    age: admission.age || '',
    sex: admission.sex || '',
    address: admission.address || '',
    address_line1: admission.address_line1 || admission.address || '',
    address_line2: admission.address_line2 || '',
    address_line3: admission.address_line3 || '',
    contact: admission.contact || '',
    admission_at: admission.admission_at ? admission.admission_at.slice(0, 16) : '',
    consultant: admission.consultant || '',
    department: admission.department || '',
    ward_room_no: admission.ward_room_no || '',
    format: admission.format || 'general',
    surgery_type: admission.surgery_type || 'other',
    diagnosis: admission.diagnosis || '',
    mrd_no: admission.mrd_no || '',
    payment_mode: admission.payment_mode || '',
    // Preserve existing fields
    procedure_at: admission.procedure_at ? admission.procedure_at.slice(0, 16) : '',
    procedure_name: admission.procedure_name || '',
    procedure_done: admission.procedure_done || '',
    icd_code: admission.icd_code || '',
    surgeon: admission.surgeon || '',
    assisted_surgeon: admission.assisted_surgeon || '',
    pediatrician_name: admission.pediatrician_name || '',
    anaesthetist: admission.anaesthetist || '',
    assisted_anaesthetist: admission.assisted_anaesthetist || '',
    assistant: admission.assistant || '',
    assisted_by: admission.assisted_by || '',
    staff_nurse: admission.staff_nurse || '',
    ot_technician: admission.ot_technician || '',
    anaesthesia_type: admission.anaesthesia_type || '',
    procedure_note: admission.procedure_note || '',
    incision: admission.incision || '',
    findings: admission.findings || '',
    complications: admission.complications || '',
    blood_loss_ml: admission.blood_loss_ml || '',
    specimen: admission.specimen || '',
    drain: admission.drain || '',
    postoperative_orders: admission.postoperative_orders || '',
    investigations: admission.investigations || '',
    discharge_at: admission.discharge_at ? admission.discharge_at.slice(0, 16) : '',
    discharge_condition: admission.discharge_condition || '',
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    treatment_course: admission.treatment_course || '',
    medication_orders: admission.medication_orders || '',
    discharge_advice: admission.discharge_advice || '',
    follow_up: admission.follow_up || '',
    handover_name: admission.handover_name || '',
    floor_incharge_name: admission.floor_incharge_name || '',
    mrd_incharge_name: admission.mrd_incharge_name || '',
    newborns: admission.newborns || [],
    next_step: 'surgery',
  });

  const isFemale = data.sex?.toLowerCase() === 'female';

  const handleSexChange = (newSex: string) => {
    const isNewFemale = newSex?.toLowerCase() === 'female';
    if (!isNewFemale && data.surgery_type === 'delivery') {
      setData((prev) => ({
        ...prev,
        sex: newSex,
        surgery_type: 'other',
      }));
    } else if (isNewFemale && !data.sex && data.surgery_type === 'other') {
      setData((prev) => ({
        ...prev,
        sex: newSex,
        surgery_type: 'delivery',
      }));
    } else {
      setData('sex', newSex);
    }
  };

  const handleSaveOnly = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'stay');
    put(`/admissions/${admission.id}`);
  };

  const handleSaveAndNext = (e: React.FormEvent) => {
    e.preventDefault();
    setData('next_step', 'surgery');
    put(`/admissions/${admission.id}`);
  };

  return (
    <AppLayout>
      <Head title={`Edit Admission: ${admission.patient_name} — Guru Hospital`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="admission" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 1: Patient Admission &amp; Demographics
          </h1>
          <p className="text-muted mb-0">
            Modify patient demographic details, admission schedule, format, and admission diagnosis.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/documents/preview`} className="btn btn-outline-secondary">
            <i className="bi bi-file-earmark-text me-1"></i> Preview Documents
          </Link>
          <Link href="/admissions" className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Archive
          </Link>
        </div>
      </div>

      <form id="admissionForm">
        {/* Section 1: Demographics */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">1.1</span> Patient Demographics
            </div>
            <span className="text-muted small">Step 1 of 4</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label required">Patient Full Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.patient_name ? 'is-invalid' : ''}`}
                  value={data.patient_name}
                  onChange={(e) => setData('patient_name', e.target.value)}
                  required
                />
                {errors.patient_name && <div className="invalid-feedback">{errors.patient_name}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label required">UHID / MRN</label>
                <input
                  type="text"
                  className={`form-control ${errors.uhid ? 'is-invalid' : ''}`}
                  value={data.uhid}
                  onChange={(e) => setData('uhid', e.target.value)}
                  required
                />
                {errors.uhid && <div className="invalid-feedback">{errors.uhid}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label required">IP No.</label>
                <input
                  type="text"
                  className={`form-control ${errors.ip_no ? 'is-invalid' : ''}`}
                  value={data.ip_no}
                  onChange={(e) => setData('ip_no', e.target.value)}
                  required
                />
                {errors.ip_no && <div className="invalid-feedback">{errors.ip_no}</div>}
              </div>

              <div className="col-md-2">
                <label className="form-label">Age</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.age || ''}
                  onChange={(e) => setData('age', e.target.value)}
                  placeholder="e.g. 23"
                />
              </div>
              <div className="col-md-2">
                <label className="form-label required">Sex</label>
                <select
                  className={`form-select ${errors.sex ? 'is-invalid' : ''}`}
                  value={data.sex || ''}
                  onChange={(e) => handleSexChange(e.target.value)}
                  required
                >
                  <option value="">Select Sex</option>
                  <option value="Female">Female</option>
                  <option value="Male">Male</option>
                  <option value="Other">Other</option>
                  <option value="Not recorded">Not recorded</option>
                </select>
                {errors.sex && <div className="invalid-feedback">{errors.sex}</div>}
              </div>
              <div className="col-md-8">
                <label className="form-label">Phone / Contact</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.contact || ''}
                  onChange={(e) => setData('contact', e.target.value)}
                  placeholder="e.g. 7373667465"
                />
              </div>

              <div className="col-md-4">
                <label className="form-label required">Address Line 1</label>
                <input
                  type="text"
                  className={`form-control ${errors.address_line1 ? 'is-invalid' : ''}`}
                  value={data.address_line1 || ''}
                  onChange={(e) => setData('address_line1', e.target.value)}
                  placeholder="e.g. Door No. / Street / Building"
                  required
                />
                {errors.address_line1 && <div className="invalid-feedback">{errors.address_line1}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label">Address Line 2</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.address_line2 || ''}
                  onChange={(e) => setData('address_line2', e.target.value)}
                  placeholder="e.g. Area / Locality / Village"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Address Line 3</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.address_line3 || ''}
                  onChange={(e) => setData('address_line3', e.target.value)}
                  placeholder="e.g. City / Taluk / Pincode"
                />
              </div>

              {data.surgery_type === 'delivery' && (
                <div className="col-md-4">
                  <label className="form-label required">
                    PICME Number (RCH ID)
                    <span className="badge bg-danger-subtle text-danger ms-2" style={{ fontSize: '0.68rem' }}>
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
                  <div className="form-text text-muted small">
                    Mandatory for all delivery/obstetric patients in Tamil Nadu.
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Section 2: Admission & Consultant Details */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
            <div>
              <span className="badge bg-primary me-2">1.2</span> Admission &amp; Ward Assignment
            </div>
            <button
              type="button"
              className="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1.5"
              onClick={() => setCustomConsultant(!customConsultant)}
            >
              <i className={`bi ${customConsultant ? 'bi-list-ul' : 'bi-plus-circle'}`}></i>
              <span>{customConsultant ? 'Select Doctor from List' : '+ Custom Consultant'}</span>
            </button>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label required">Admission Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className={`form-control ${errors.admission_at ? 'is-invalid' : ''}`}
                  value={data.admission_at}
                  onChange={(e) => setData('admission_at', e.target.value)}
                  required
                />
                {errors.admission_at && <div className="invalid-feedback">{errors.admission_at}</div>}
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Consultant Doctor"
                  role="consultant_doctor"
                  value={data.consultant || ''}
                  onChange={(val) => {
                    const match = clinicalStaff.find((s) => s.name === val);
                    setData((prev) => ({
                      ...prev,
                      consultant: val,
                      department: prev.department ? prev.department : (match?.department || prev.department),
                    }));
                  }}
                  staffList={clinicalStaff}
                  placeholder="-- Select Consultant Doctor --"
                  showCustomToggle={false}
                  isCustom={customConsultant}
                  onCustomToggle={setCustomConsultant}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Department / Specialty</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.department || ''}
                  onChange={(e) => setData('department', e.target.value)}
                  placeholder="e.g. Obstetrics & Gynaecology"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Ward / Bed Number</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.ward_room_no || ''}
                  onChange={(e) => setData('ward_room_no', e.target.value)}
                  placeholder="e.g. Ward A / Bed 04"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">MRD File Number</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.mrd_no || ''}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                  placeholder="e.g. MRD-2026-0567"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Payment / Insurance Mode</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.payment_mode || ''}
                  onChange={(e) => setData('payment_mode', e.target.value)}
                  placeholder="e.g. Cash / CMCHISTN / Star Health"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 3: Format & Diagnosis */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">1.3</span> Format, Workflow &amp; Diagnosis
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label required">Discharge Summary Format</label>
                <select
                  className="form-select"
                  value={data.format}
                  onChange={(e) => setData('format', e.target.value)}
                  required
                >
                  <option value="general">General (Standard Cash / TPA)</option>
                  <option value="cmchistn">CMCHISTN Government Scheme</option>
                  <option value="private">Private Insurance</option>
                  <option value="employee">Hospital Employee</option>
                </select>
                <div className="form-text text-muted small">
                  Determines the header style and layout for all generated documents.
                </div>
              </div>

              <div className="col-md-6">
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label required mb-0" htmlFor="surgeryType">Surgery / Clinical Workflow</label>
                  {!isFemale && (
                    <span className="badge bg-secondary-subtle text-secondary" style={{ fontSize: '0.72rem' }}>
                      Female only for Delivery
                    </span>
                  )}
                </div>
                <select
                  id="surgeryType"
                  className={`form-select ${errors.surgery_type ? 'is-invalid' : ''}`}
                  value={data.surgery_type}
                  onChange={(e) => setData('surgery_type', e.target.value)}
                  required
                >
                  <option value="delivery" disabled={!isFemale}>
                    Delivery / Obstetric {!isFemale ? '— (Female patients only)' : ''}
                  </option>
                  <option value="other">Other Surgery / Non-Obstetric</option>
                </select>
                {!isFemale ? (
                  <div className="form-text text-muted small">
                    <i className="bi bi-info-circle me-1"></i> Delivery / obstetric workflow is only available when patient sex is set to <strong>Female</strong>.
                  </div>
                ) : (
                  <div className="form-text text-muted small">
                    Select <strong>Delivery / Obstetric</strong> for maternal delivery &amp; birth details, or <strong>Other Surgery</strong> for general operations.
                  </div>
                )}
                {errors.surgery_type && <div className="invalid-feedback">{errors.surgery_type}</div>}
              </div>

              <div className="col-12">
                <label className="form-label required">Provisional / Admission Diagnosis</label>
                <textarea
                  className={`form-control ${errors.diagnosis ? 'is-invalid' : ''}`}
                  rows={2}
                  value={data.diagnosis}
                  onChange={(e) => setData('diagnosis', e.target.value)}
                  required
                />
                {errors.diagnosis && <div className="invalid-feedback">{errors.diagnosis}</div>}
              </div>
            </div>
          </div>
        </div>

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href="/admissions" className="btn btn-outline-secondary">
            Cancel
          </Link>
          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              Save Stage 1 Changes
            </button>
            <button
              type="button"
              onClick={handleSaveAndNext}
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Next: Stage 2 (Labour / Surgery)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
