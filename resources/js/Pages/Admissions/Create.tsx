import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StaffSelect, { ClinicalStaffMember } from '../../Components/StaffSelect';
import { defaultMrdFromIp, digitsOnlyValue, handleNormalizedInputChange, toUpperCaseValue } from '../../utils/inputNormalizers';
import AdmissionStageNav from '../../Components/AdmissionStageNav';
import PatientNameInput from '../../Components/PatientNameInput';
import PaymentInsuranceFields from '../../Components/PaymentInsuranceFields';

interface Props {
  clinicalStaff?: ClinicalStaffMember[];
}

export default function AdmissionsCreate({ clinicalStaff = [] }: Props) {
  const [customConsultant, setCustomConsultant] = useState(false);
  const { data, setData, post, processing, errors } = useForm({
    patient_name: '',
    attender_name: '',
    attender_relation: 'Husband',
    uhid: '',
    ip_no: '',
    picme_no: '',
    age: '',
    sex: '',
    address: '',
    address_line1: '',
    address_line2: '',
    address_line3: '',
    contact: '',
    admission_at: '',
    consultant: '',
    department: '',
    ward_room_no: '',
    format: 'general',
    surgery_type: '',
    diagnosis: '',
    mrd_no: '',
    payment_mode: '',
    insurance_type: '',
    insurance_company: '',
    policy_number: '',
    pre_auth_no: '',
    pre_auth_status: '',
    approved_amount: '',
    policy_holder_name: '',
    policy_holder_relation: '',
    policy_validity: '',
    next_step: 'clinical',
  });

  const isFemale = data.sex?.toLowerCase() === 'female';

  const handleSexChange = (newSex: string) => {
    const isNewFemale = newSex?.toLowerCase() === 'female';
    if (!isNewFemale && data.surgery_type === 'delivery') {
      setData((prev) => ({
        ...prev,
        sex: newSex,
        surgery_type: '',
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
            <span className="text-muted small">Step 1 of 5</span>
          </div>
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label required">Patient Full Name</label>
                <PatientNameInput
                  value={data.patient_name}
                  onChange={(fullName) => setData('patient_name', fullName)}
                  error={errors.patient_name}
                  placeholder="e.g. PATIENT NAME"
                  required
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">Attender / Care Of Name</label>
                <div className={`fg-merged-name-group ${errors.attender_name || errors.attender_relation ? 'is-invalid' : ''}`}>
                  <select
                    className="form-select fg-relation-select"
                    aria-label="Relationship to Patient"
                    value={data.attender_relation || 'Husband'}
                    onChange={(e) => setData('attender_relation', e.target.value)}
                  >
                    <option value="Husband">Husband</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Brother">Brother</option>
                    <option value="Sister">Sister</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Guardian">Guardian</option>
                    <option value="Care of (C/o)">Care of (C/o)</option>
                    <option value="Other">Other</option>
                  </select>
                  <input
                    type="text"
                    className={`form-control fg-name-text-input ${errors.attender_name ? 'is-invalid' : ''}`}
                    style={{ textTransform: 'uppercase', minWidth: 0 }}
                    aria-label="Attender / Care Of Name"
                    value={data.attender_name}
                    onChange={(e) =>
                      handleNormalizedInputChange(e, toUpperCaseValue, (val) => setData('attender_name', val))
                    }
                    placeholder="e.g. S. MURUGAN"
                  />
                </div>
                {errors.attender_name && <div className="invalid-feedback d-block">{errors.attender_name}</div>}
                {errors.attender_relation && <div className="invalid-feedback d-block">{errors.attender_relation}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label required">UHID</label>
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
                  onChange={(e) => {
                    const newIpNo = e.target.value;
                    setData((prev) => {
                      const prevAuto = defaultMrdFromIp(prev.ip_no);
                      const isAutoOrEmpty = !prev.mrd_no || prev.mrd_no === prevAuto;
                      return {
                        ...prev,
                        ip_no: newIpNo,
                        mrd_no: isAutoOrEmpty ? defaultMrdFromIp(newIpNo) : prev.mrd_no,
                      };
                    });
                  }}
                  placeholder="e.g. 2026/567"
                  required
                />
                {errors.ip_no && <div className="invalid-feedback">{errors.ip_no}</div>}
              </div>

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
                <label className="form-label required">Gender</label>
                <select
                  className={`form-select ${errors.sex ? 'is-invalid' : ''}`}
                  value={data.sex}
                  onChange={(e) => handleSexChange(e.target.value)}
                  required
                >
                  <option value="">Select Gender</option>
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
                  type="tel"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  className={`form-control ${errors.contact ? 'is-invalid' : ''}`}
                  value={data.contact}
                  onChange={(e) =>
                    handleNormalizedInputChange(e, digitsOnlyValue, (val) => setData('contact', val))
                  }
                  placeholder="e.g. 7373667465"
                />
                {errors.contact && <div className="invalid-feedback">{errors.contact}</div>}
              </div>
              <div className="col-md-4">
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
                  <option value="">Select</option>
                  <option value="delivery" disabled={!isFemale}>
                    Delivery / Obstetric {!isFemale ? '— (Female patients only)' : ''}
                  </option>
                  <option value="other">Other Surgery / Non-Obstetric</option>
                  <option value="medical">Medical Case (Non-Surgical)</option>
                </select>
                {errors.surgery_type && <div className="invalid-feedback">{errors.surgery_type}</div>}
              </div>

              <div className="col-md-4">
                <label className="form-label required">Address Line 1</label>
                <input
                  type="text"
                  className={`form-control ${errors.address_line1 ? 'is-invalid' : ''}`}
                  style={{ textTransform: 'uppercase' }}
                  value={data.address_line1}
                  onChange={(e) =>
                    handleNormalizedInputChange(e, toUpperCaseValue, (val) => setData('address_line1', val))
                  }
                  placeholder="e.g. DOOR NO. / STREET / BUILDING"
                  required
                />
                {errors.address_line1 && <div className="invalid-feedback">{errors.address_line1}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label">Address Line 2</label>
                <input
                  type="text"
                  className="form-control"
                  style={{ textTransform: 'uppercase' }}
                  value={data.address_line2}
                  onChange={(e) =>
                    handleNormalizedInputChange(e, toUpperCaseValue, (val) => setData('address_line2', val))
                  }
                  placeholder="e.g. AREA / LOCALITY / VILLAGE"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">Address Line 3</label>
                <input
                  type="text"
                  className="form-control"
                  style={{ textTransform: 'uppercase' }}
                  value={data.address_line3}
                  onChange={(e) =>
                    handleNormalizedInputChange(e, toUpperCaseValue, (val) => setData('address_line3', val))
                  }
                  placeholder="e.g. CITY / TALUK / PINCODE"
                />
              </div>

              {data.surgery_type === 'delivery' && (
                <div className="col-md-4">
                  <label className="form-label required">
                    RCH ID (PICME No)
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
                  value={data.consultant}
                  onChange={(val) => {
                    const match = clinicalStaff.find((s) => s.name.toUpperCase() === val.toUpperCase());
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
                  uppercase
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
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <label className="form-label mb-0">MRD File Number</label>
                  <span className="badge bg-light text-muted border" style={{ fontSize: '0.68rem' }}>
                    Auto: MRD-{'{IP No}'}
                  </span>
                </div>
                <input
                  type="text"
                  className="form-control"
                  value={data.mrd_no}
                  onChange={(e) => setData('mrd_no', e.target.value)}
                  placeholder={data.ip_no ? defaultMrdFromIp(data.ip_no) : 'e.g. MRD-{IP No}'}
                />
                <div className="form-text text-muted small">
                  Default format: <span className="font-monospace">MRD-{'{IP No}'}</span>
                </div>
              </div>
              <PaymentInsuranceFields
                data={data}
                errors={errors}
                onPatch={(patch) => setData((prev) => ({ ...prev, ...patch }))}
                syncFormat={true}
              />
            </div>
          </div>
        </div>

        {/* Section 3: Format & Diagnosis */}
        <div className="card mb-4 border-0 shadow-sm">
          <div className="card-header bg-white py-3 fw-bold text-dark">
            <span className="badge bg-primary me-2">1.3</span> Format &amp; Admission Diagnosis
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
              <span>Save &amp; Proceed to Stage 2: Clinical Assessment</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>
    </AppLayout>
  );
}
