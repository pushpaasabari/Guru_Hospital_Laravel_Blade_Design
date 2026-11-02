import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import AdmissionStageNav from '../../Components/AdmissionStageNav';

interface Props {
  clinicalStaff?: ClinicalStaffMember[];
}

export default function AdmissionsCreate({ clinicalStaff = [] }: Props) {
  const [customConsultant, setCustomConsultant] = useState(false);
  const { data, setData, post, processing, errors } = useForm({
    patient_name: '',
    uhid: '',
    ip_no: '',
    picme_no: '',
    age: '',
    sex: '',
    address: '',
    contact: '',
    admission_at: '',
    consultant: '',
    department: '',
    ward_room_no: '',
    format: 'general',
    surgery_type: 'other',
    diagnosis: '',
    mrd_no: '',
    payment_mode: 'Cash',
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

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admissions');
  };

  return (
    <AppLayout>
      <Head title="New Admission (Stage 1) — Guru Hospital" />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="admission" admission={null} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 1: Patient Admission &amp; Case Setup
          </h1>
          <p className="text-muted mb-0">
            Enter patient demographics, admission details, format, and provisional diagnosis.
          </p>
        </div>
        <Link href="/admissions" className="btn btn-outline-secondary">
          <i className="bi bi-arrow-left me-1"></i> Back to Archive
        </Link>
      </div>

      <form onSubmit={submit} id="admissionForm">
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
                  placeholder="e.g. Mrs. Anitha"
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
                  placeholder="e.g. 2023/3931"
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
                  placeholder="e.g. 2026/567"
                  required
                />
                {errors.ip_no && <div className="invalid-feedback">{errors.ip_no}</div>}
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

              <div className="col-md-2">
                <label className="form-label">Age</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.age}
                  onChange={(e) => setData('age', e.target.value)}
                  placeholder="e.g. 23"
                />
              </div>
              <div className="col-md-2">
                <label className="form-label required">Sex</label>
                <select
                  className={`form-select ${errors.sex ? 'is-invalid' : ''}`}
                  value={data.sex}
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
              <div className="col-md-4">
                <label className="form-label">Phone / Contact</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.contact}
                  onChange={(e) => setData('contact', e.target.value)}
                  placeholder="e.g. 7373667465"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Address</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.address}
                  onChange={(e) => setData('address', e.target.value)}
                  placeholder="e.g. W/O Sarath Veerasamur"
                />
              </div>
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
                  placeholder="e.g. Ward A / Bed 04"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">MRD File Number</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.mrd_no}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                  placeholder="e.g. MRD-2026-0567"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Payment / Insurance Mode</label>
                <input
                  type="text"
                  className="form-control"
                  value={data.payment_mode}
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
                  placeholder="e.g. G3P1L1A1 / TWINS / Preterm / PROM"
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
              type="submit"
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Proceed to Stage 2: Surgery / Labour</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
