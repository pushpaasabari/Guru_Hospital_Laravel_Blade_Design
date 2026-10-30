import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';

interface Newborn {
  id?: number;
  sex?: string;
  birth_at?: string;
  birth_weight_kg?: string | number;
  apgar_1_min?: number;
  apgar_5_min?: number;
  neonatal_care?: string;
  notes?: string;
}

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string;
  sex?: string;
  address?: string;
  contact?: string;
  admission_at: string;
  procedure_at?: string;
  discharge_at: string;
  consultant?: string;
  department?: string;
  ward_room_no?: string;
  format: string;
  surgery_type: string;
  diagnosis: string;
  clinical_history?: string;
  examination?: string;
  treatment_course?: string;
  discharge_condition?: string;
  medication_orders?: string;
  discharge_advice?: string;
  follow_up?: string;
  investigations?: string;
  procedure_name?: string;
  procedure_done?: string;
  icd_code?: string;
  surgeon?: string;
  assisted_surgeon?: string;
  pediatrician_name?: string;
  anaesthetist?: string;
  assisted_anaesthetist?: string;
  assistant?: string;
  assisted_by?: string;
  staff_nurse?: string;
  ot_technician?: string;
  anaesthesia_type?: string;
  procedure_note?: string;
  incision?: string;
  findings?: string;
  complications?: string;
  blood_loss_ml?: number;
  specimen?: string;
  drain?: string;
  postoperative_orders?: string;
  mrd_no?: string;
  payment_mode?: string;
  status: string;
  rejection_reason?: string;
  newborns: Newborn[];
}

interface Props {
  admission: AdmissionData;
  clinicalStaff?: ClinicalStaffMember[];
}

export default function AdmissionsEdit({ admission, clinicalStaff = [] }: Props) {
  const { data, setData, put, processing, errors } = useForm({
    patient_name: admission.patient_name || '',
    uhid: admission.uhid || '',
    ip_no: admission.ip_no || '',
    age: admission.age || '',
    sex: admission.sex || '',
    address: admission.address || '',
    contact: admission.contact || '',
    admission_at: admission.admission_at ? admission.admission_at.substring(0, 16) : '',
    procedure_at: admission.procedure_at ? admission.procedure_at.substring(0, 16) : '',
    discharge_at: admission.discharge_at ? admission.discharge_at.substring(0, 16) : '',
    consultant: admission.consultant || '',
    department: admission.department || '',
    ward_room_no: admission.ward_room_no || '',
    format: admission.format || 'general',
    surgery_type: admission.surgery_type || 'delivery',
    diagnosis: admission.diagnosis || '',
    clinical_history: admission.clinical_history || '',
    examination: admission.examination || '',
    treatment_course: admission.treatment_course || '',
    discharge_condition: admission.discharge_condition || '',
    medication_orders: admission.medication_orders || '',
    discharge_advice: admission.discharge_advice || '',
    follow_up: admission.follow_up || '',
    investigations: admission.investigations || '',
    procedure_name: admission.procedure_name || '',
    procedure_done: admission.procedure_done || '',
    icd_code: admission.icd_code || '',
    surgeon: admission.surgeon || '',
    assisted_surgeon: admission.assisted_surgeon || '',
    pediatrician_name: admission.pediatrician_name || '',
    anaesthetist: admission.anaesthetist || '',
    assisted_anaesthetist: admission.assisted_anaesthetist || '',
    assisted_by: admission.assisted_by || '',
    staff_nurse: admission.staff_nurse || '',
    ot_technician: admission.ot_technician || '',
    anaesthesia_type: admission.anaesthesia_type || '',
    procedure_note: admission.procedure_note || '',
    incision: admission.incision || '',
    findings: admission.findings || '',
    complications: admission.complications || '',
    blood_loss_ml: admission.blood_loss_ml ?? '',
    specimen: admission.specimen || '',
    drain: admission.drain || '',
    postoperative_orders: admission.postoperative_orders || '',
    mrd_no: admission.mrd_no || '',
    payment_mode: admission.payment_mode || '',
    newborns: (admission.newborns && admission.newborns.length > 0)
      ? admission.newborns.map((b) => ({
          sex: b.sex || '',
          birth_at: b.birth_at ? b.birth_at.substring(0, 16) : '',
          birth_weight_kg: b.birth_weight_kg || '',
          apgar_1_min: b.apgar_1_min || '',
          apgar_5_min: b.apgar_5_min || '',
          neonatal_care: b.neonatal_care || '',
          notes: b.notes || '',
        }))
      : [
          {
            sex: '',
            birth_at: '',
            birth_weight_kg: '',
            apgar_1_min: '',
            apgar_5_min: '',
            neonatal_care: '',
            notes: '',
          },
        ],
  });

  const addBaby = () => {
    setData('newborns', [
      ...data.newborns,
      {
        sex: '',
        birth_at: '',
        birth_weight_kg: '',
        apgar_1_min: '',
        apgar_5_min: '',
        neonatal_care: '',
        notes: '',
      },
    ]);
  };

  const removeBaby = (index: number) => {
    setData(
      'newborns',
      data.newborns.filter((_, i) => i !== index)
    );
  };

  const updateBaby = (index: number, field: string, val: any) => {
    const updated = [...data.newborns];
    (updated[index] as any)[field] = val;
    setData('newborns', updated);
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admissions/${admission.id}`);
  };

  return (
    <AppLayout>
      <Head title={`Edit Admission — ${admission.patient_name}`} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Edit Admission / Document Pack
          </h1>
          <p className="text-muted mb-0">
            Updating admission records for <strong>{admission.patient_name}</strong> (IP: {admission.ip_no})
          </p>
        </div>
        <Link href={`/documents/${admission.id}/preview`} className="btn btn-outline-secondary">
          <i className="bi bi-arrow-left me-1"></i> Back to Document Preview
        </Link>
      </div>

      {admission.status === 'returned_for_correction' && admission.rejection_reason && (
        <div className="alert alert-danger shadow-sm mb-4">
          <div className="d-flex align-items-center mb-1">
            <i className="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
            <h6 className="mb-0 fw-bold">Document Pack Returned for Clinical Correction</h6>
          </div>
          <p className="mb-0 ps-4">{admission.rejection_reason}</p>
        </div>
      )}

      <form onSubmit={submit} id="admissionEditForm">
        {/* Section 1: Demographics */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-dark me-2">1</span> Shared Patient &amp; Admission Demographics
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label required">Patient Full Name</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.patient_name}
                  onChange={(e) => setData('patient_name', e.target.value)}
                  required
                />
                {errors.patient_name && <div className="text-danger small">{errors.patient_name}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label required">UHID / MRN</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.uhid}
                  onChange={(e) => setData('uhid', e.target.value)}
                  required
                />
                {errors.uhid && <div className="text-danger small">{errors.uhid}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label required">Inpatient No. (IP No)</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.ip_no}
                  onChange={(e) => setData('ip_no', e.target.value)}
                  required
                />
                {errors.ip_no && <div className="text-danger small">{errors.ip_no}</div>}
              </div>

              <div className="col-md-2">
                <label className="form-label">Age</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.age}
                  onChange={(e) => setData('age', e.target.value)}
                />
              </div>
              <div className="col-md-2">
                <label className="form-label">Sex</label>
                <select className="form-select" value={data.sex} onChange={(e) => setData('sex', e.target.value)}>
                  <option value="">Select</option>
                  <option value="Female">Female</option>
                  <option value="Male">Male</option>
                  <option value="Other">Other</option>
                  <option value="Not recorded">Not recorded</option>
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label">Contact Number</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.contact}
                  onChange={(e) => setData('contact', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Address</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.address}
                  onChange={(e) => setData('address', e.target.value)}
                />
              </div>

              <div className="col-md-4">
                <label className="form-label required">Admission Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className="form-control"
                  value={data.admission_at}
                  onChange={(e) => setData('admission_at', e.target.value)}
                  required
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Procedure / Delivery Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className="form-control"
                  value={data.procedure_at}
                  onChange={(e) => setData('procedure_at', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label required">Discharge Date &amp; Time</label>
                <input
                  type="datetime-local"
                  className="form-control"
                  value={data.discharge_at}
                  onChange={(e) => setData('discharge_at', e.target.value)}
                  required
                />
              </div>

              <div className="col-md-4">
                <StaffSelect
                  label="Treating Consultant / Doctor"
                  role="consultant_doctor"
                  value={data.consultant}
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
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Department / Specialty</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.department}
                  onChange={(e) => setData('department', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Ward / Bed Number</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.ward_room_no}
                  onChange={(e) => setData('ward_room_no', e.target.value)}
                />
              </div>

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
              </div>
              <div className="col-md-6">
                <label className="form-label required">Surgery Workflow</label>
                <select
                  className="form-select"
                  value={data.surgery_type}
                  onChange={(e) => setData('surgery_type', e.target.value)}
                  required
                >
                  <option value="delivery">Delivery / Obstetric</option>
                  <option value="other">Other Surgery</option>
                </select>
              </div>

              <div className="col-12">
                <label className="form-label required">Diagnosis</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.diagnosis}
                  onChange={(e) => setData('diagnosis', e.target.value)}
                  required
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 2: Clinical Details */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-dark me-2">2</span> Discharge Summary Clinical Details
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">Presenting Complaints / History</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.clinical_history}
                  onChange={(e) => setData('clinical_history', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Clinical Examination / Vital Signs</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.examination}
                  onChange={(e) => setData('examination', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Treatment Course</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.treatment_course}
                  onChange={(e) => setData('treatment_course', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Condition on Discharge</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.discharge_condition}
                  onChange={(e) => setData('discharge_condition', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Medication Orders</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.medication_orders}
                  onChange={(e) => setData('medication_orders', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Discharge Advice</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.discharge_advice}
                  onChange={(e) => setData('discharge_advice', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Follow-up</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.follow_up}
                  onChange={(e) => setData('follow_up', e.target.value)}
                />
              </div>
              <div className="col-12">
                <label className="form-label">Investigations</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.investigations}
                  onChange={(e) => setData('investigations', e.target.value)}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 3: OT Notes */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-dark me-2">3</span> Operation Theatre Notes
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label">Procedure / Surgery</label>
                <input
                  className="form-control"
                  value={data.procedure_name}
                  onChange={(e) => setData('procedure_name', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Procedure Done</label>
                <input
                  className="form-control"
                  value={data.procedure_done}
                  onChange={(e) => setData('procedure_done', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">ICD Code</label>
                <input
                  className="form-control"
                  value={data.icd_code}
                  onChange={(e) => setData('icd_code', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Surgeon"
                  role="surgeon"
                  value={data.surgeon}
                  onChange={(val) => setData('surgeon', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Surgeon --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Assisted Surgeon"
                  role="assisted_surgeon"
                  value={data.assisted_surgeon}
                  onChange={(val) => {
                    setData((prev) => ({
                      ...prev,
                      assisted_surgeon: val,
                      assisted_by: prev.assisted_by ? prev.assisted_by : val,
                    }));
                  }}
                  staffList={clinicalStaff}
                  placeholder="-- Select Assisted Surgeon --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Anaesthetist"
                  role="anaesthetist"
                  value={data.anaesthetist}
                  onChange={(val) => setData('anaesthetist', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Anaesthetist --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Assisted Anaesthetist"
                  role="assisted_anaesthetist"
                  value={data.assisted_anaesthetist}
                  onChange={(val) => setData('assisted_anaesthetist', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Asst. Anaesthetist --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Staff Nurse"
                  role="staff_nurse"
                  value={data.staff_nurse}
                  onChange={(val) => {
                    setData((prev) => ({
                      ...prev,
                      staff_nurse: val,
                      assisted_by: prev.assisted_by ? prev.assisted_by : val,
                    }));
                  }}
                  staffList={clinicalStaff}
                  placeholder="-- Select Staff Nurse --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="OT Technician"
                  role="ot_technician"
                  value={data.ot_technician}
                  onChange={(val) => setData('ot_technician', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select OT Technician --"
                />
              </div>
              <div className="col-md-4">
                <StaffSelect
                  label="Consultant Pediatrician"
                  role="consultant_doctor"
                  value={data.pediatrician_name}
                  onChange={(val) => setData('pediatrician_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Pediatrician --"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Anaesthesia Type</label>
                <input
                  className="form-control"
                  value={data.anaesthesia_type}
                  onChange={(e) => setData('anaesthesia_type', e.target.value)}
                  placeholder="e.g. SA, GA"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Blood Loss (mL)</label>
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  className="form-control"
                  value={data.blood_loss_ml}
                  onChange={(e) => setData('blood_loss_ml', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Procedure Note</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.procedure_note}
                  onChange={(e) => setData('procedure_note', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Incision</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.incision}
                  onChange={(e) => setData('incision', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Findings</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.findings}
                  onChange={(e) => setData('findings', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Complications</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.complications}
                  onChange={(e) => setData('complications', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Post-operative Orders</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.postoperative_orders}
                  onChange={(e) => setData('postoperative_orders', e.target.value)}
                />
              </div>
              <div className="col-md-3">
                <label className="form-label">Specimen</label>
                <input
                  className="form-control"
                  value={data.specimen}
                  onChange={(e) => setData('specimen', e.target.value)}
                />
              </div>
              <div className="col-md-3">
                <label className="form-label">Drain</label>
                <input
                  className="form-control"
                  value={data.drain}
                  onChange={(e) => setData('drain', e.target.value)}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Section 4: Birth Details (Delivery Only) */}
        {data.surgery_type === 'delivery' && (
          <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header bg-white py-3 fw-bold text-dark">
              <span className="badge bg-dark me-2">4</span> Birth / Newborn Details — Delivery Only
            </div>
            <div className="card-body">
              {data.newborns.map((baby, i) => (
                <div key={i} className="border rounded p-3 mb-3 bg-light">
                  <div className="d-flex justify-content-between align-items-center mb-2">
                    <strong>Newborn <span className="badge bg-dark">#{i + 1}</span></strong>
                    {data.newborns.length > 1 && (
                      <button
                        type="button"
                        onClick={() => removeBaby(i)}
                        className="btn btn-sm btn-outline-danger"
                      >
                        Remove
                      </button>
                    )}
                  </div>
                  <div className="row g-2">
                    <div className="col-md-2">
                      <label className="form-label">Sex</label>
                      <select
                        className="form-select"
                        value={baby.sex}
                        onChange={(e) => updateBaby(i, 'sex', e.target.value)}
                      >
                        <option value="">Select</option>
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                        <option value="Other">Other</option>
                        <option value="Not recorded">Not recorded</option>
                      </select>
                    </div>
                    <div className="col-md-3">
                      <label className="form-label">Birth Date/Time</label>
                      <input
                        type="datetime-local"
                        className="form-control"
                        value={baby.birth_at}
                        onChange={(e) => updateBaby(i, 'birth_at', e.target.value)}
                      />
                    </div>
                    <div className="col-md-2">
                      <label className="form-label">Weight (kg)</label>
                      <input
                        type="number"
                        step="0.01"
                        min="0.1"
                        max="8"
                        className="form-control"
                        value={baby.birth_weight_kg}
                        onChange={(e) => updateBaby(i, 'birth_weight_kg', e.target.value)}
                      />
                    </div>
                    <div className="col-md-2">
                      <label className="form-label">Apgar 1 min</label>
                      <input
                        type="number"
                        min="0"
                        max="10"
                        className="form-control"
                        value={baby.apgar_1_min}
                        onChange={(e) => updateBaby(i, 'apgar_1_min', e.target.value)}
                      />
                    </div>
                    <div className="col-md-2">
                      <label className="form-label">Apgar 5 min</label>
                      <input
                        type="number"
                        min="0"
                        max="10"
                        className="form-control"
                        value={baby.apgar_5_min}
                        onChange={(e) => updateBaby(i, 'apgar_5_min', e.target.value)}
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Neonatal Care</label>
                      <textarea
                        className="form-control"
                        rows={2}
                        value={baby.neonatal_care}
                        onChange={(e) => updateBaby(i, 'neonatal_care', e.target.value)}
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Other Documented Notes</label>
                      <textarea
                        className="form-control"
                        rows={2}
                        value={baby.notes}
                        onChange={(e) => updateBaby(i, 'notes', e.target.value)}
                      />
                    </div>
                  </div>
                </div>
              ))}

              <button type="button" onClick={addBaby} className="btn btn-outline-secondary">
                <i className="bi bi-plus"></i> Add Newborn
              </button>
            </div>
          </div>
        )}

        {/* Section 5: MRD Fields */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-dark me-2">5</span> MRD Fields
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label">MRD No.</label>
                <input
                  className="form-control"
                  value={data.mrd_no}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Payment Mode</label>
                <input
                  className="form-control"
                  value={data.payment_mode}
                  onChange={(e) => setData('payment_mode', e.target.value)}
                />
              </div>
            </div>
          </div>
        </div>

        <div className="d-flex gap-2 mb-5">
          <button type="submit" className="btn btn-primary btn-lg" disabled={processing}>
            {processing ? 'Saving Changes…' : 'Save Changes & Return to Preview'}
          </button>
          <Link href={`/documents/${admission.id}/preview`} className="btn btn-outline-secondary btn-lg">
            Cancel
          </Link>
        </div>
      </form>
    </AppLayout>
  );
}

