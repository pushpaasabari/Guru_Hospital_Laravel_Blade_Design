import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import AnesthesiaSelect, { AnesthesiaTypeItem } from '../../Components/AnesthesiaSelect';

interface NewbornInput {
  sex: string;
  birth_at: string;
  birth_weight_kg: string;
  apgar_1_min: string;
  apgar_5_min: string;
  neonatal_care: string;
  notes: string;
}

interface Props {
  clinicalStaff?: ClinicalStaffMember[];
  anesthesiaTypes?: AnesthesiaTypeItem[];
}

export default function AdmissionsCreate({ clinicalStaff = [], anesthesiaTypes = [] }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    patient_name: '',
    uhid: '',
    ip_no: '',
    age: '',
    sex: '',
    address: '',
    contact: '',
    admission_at: '',
    procedure_at: '',
    discharge_at: '',
    consultant: '',
    department: '',
    ward_room_no: '',
    format: 'general',
    surgery_type: 'other',
    diagnosis: '',
    clinical_history: '',
    examination: '',
    treatment_course: '',
    discharge_condition: '',
    medication_orders: '',
    discharge_advice: '',
    follow_up: '',
    investigations: '',
    procedure_name: '',
    procedure_done: '',
    icd_code: '',
    surgeon: '',
    assisted_surgeon: '',
    pediatrician_name: '',
    anaesthetist: '',
    assisted_anaesthetist: '',
    assisted_by: '',
    staff_nurse: '',
    ot_technician: '',
    anaesthesia_type: '',
    procedure_note: '',
    incision: '',
    findings: '',
    complications: '',
    blood_loss_ml: '',
    specimen: '',
    drain: '',
    postoperative_orders: '',
    mrd_no: '',
    payment_mode: '',
    newborns: [
      {
        sex: '',
        birth_at: '',
        birth_weight_kg: '',
        apgar_1_min: '',
        apgar_5_min: '',
        neonatal_care: '',
        notes: '',
      },
    ] as NewbornInput[],
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

  const updateBaby = (index: number, field: keyof NewbornInput, val: string) => {
    const updated = [...data.newborns];
    updated[index][field] = val;
    setData('newborns', updated);
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admissions');
  };

  return (
    <AppLayout>
      <Head title="New Admission — Guru Hospital" />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            New Admission / Document Pack
          </h1>
          <p className="text-muted mb-0">
            Enter shared details once. Save as draft, then review the discharge summary, OT notes and MRD form together.
          </p>
        </div>
        <Link href="/admissions" className="btn btn-outline-secondary">
          <i className="bi bi-arrow-left me-1"></i> Back to Archive
        </Link>
      </div>

      <form onSubmit={submit} id="admissionForm">
        {/* Section 1: Demographics */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-dark me-2">1</span> Shared Patient &amp; Admission Details
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
                <label className="form-label required">IP No.</label>
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
                  placeholder="e.g. 26"
                />
              </div>
              <div className="col-md-2">
                <label className="form-label">Sex</label>
                <select className="form-select" value={data.sex} onChange={(e) => handleSexChange(e.target.value)}>
                  <option value="">Select</option>
                  <option value="Female">Female</option>
                  <option value="Male">Male</option>
                  <option value="Other">Other</option>
                  <option value="Not recorded">Not recorded</option>
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label">Contact</label>
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
                <label className="form-label required">Admission Date/Time</label>
                <input
                  type="datetime-local"
                  className="form-control"
                  value={data.admission_at}
                  onChange={(e) => setData('admission_at', e.target.value)}
                  required
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Procedure / Delivery Date/Time</label>
                <input
                  type="datetime-local"
                  className="form-control"
                  value={data.procedure_at}
                  onChange={(e) => setData('procedure_at', e.target.value)}
                />
              </div>
              <div className="col-md-4">
                <label className="form-label required">Discharge Date/Time</label>
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
                  label="Consultant Doctor"
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
                <label className="form-label">Department</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.department}
                  onChange={(e) => setData('department', e.target.value)}
                  placeholder="e.g. Obstetrics & Gynaecology"
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
                <label className="form-label">Presenting Complaints / Relevant History</label>
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
                <label className="form-label">Treatment &amp; Hospital Course</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.treatment_course}
                  onChange={(e) => setData('treatment_course', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Condition at Discharge</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.discharge_condition}
                  onChange={(e) => setData('discharge_condition', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Authorised Discharge Medication Orders</label>
                <textarea
                  className="form-control"
                  rows={2}
                  value={data.medication_orders}
                  onChange={(e) => setData('medication_orders', e.target.value)}
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Discharge Advice / Warning Signs</label>
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
                <label className="form-label">Investigations (Lab Reports, Pathology &amp; Diagnostics)</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={data.investigations}
                  onChange={(e) => setData('investigations', e.target.value)}
                  placeholder="e.g.&#10;Hb: 10.6mgs%&#10;HIV: Negative | VDRL: Negative | HBsAG: Negative&#10;BG: B positive&#10;ECG: Normal&#10;RBS: 101.6 mg/dl | Urea: 24.3 mg/dl | Creatinine: 0.56 mg/dl"
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
                  role="consultant_pediatrician"
                  value={data.pediatrician_name}
                  onChange={(val) => setData('pediatrician_name', val)}
                  staffList={clinicalStaff}
                  placeholder="-- Select Pediatrician --"
                />
              </div>
              <div className="col-md-4">
                <AnesthesiaSelect
                  label="Anaesthesia Type"
                  value={data.anaesthesia_type}
                  onChange={(val) => setData('anaesthesia_type', val)}
                  types={anesthesiaTypes}
                  placeholder="-- Select Anaesthesia Type --"
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
          <div className="card mb-4 border-0 shadow-sm" id="deliveryFields">
            <div className="card-header bg-white py-3 fw-bold text-dark">
              <span className="badge bg-dark me-2">4</span> Birth / Newborn Details — Delivery Only
            </div>
            <div className="card-body">
              <p className="small text-muted">
                Add one record per newborn; for twins, enter two separate newborn records.
              </p>

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
            {processing ? 'Saving Draft…' : 'Save Draft & Preview Three Documents'}
          </button>
          <Link href="/admissions" className="btn btn-outline-secondary btn-lg">
            Cancel
          </Link>
        </div>
      </form>
    </AppLayout>
  );
}

