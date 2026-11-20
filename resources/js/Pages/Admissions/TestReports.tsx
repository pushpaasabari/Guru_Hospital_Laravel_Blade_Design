import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdmissionStageNav from '../../Components/AdmissionStageNav';
import {
  LAB_CATALOG,
  ALL_LAB_TESTS,
  findLabTest,
  FREQUENT_LAB_TESTS,
  LabCatalogItem,
} from '../../constants/labCatalog';
import { formatInvestigations } from '../../utils/dischargeFormatter';

interface AdmissionData {
  id: number;
  patient_name: string;
  uhid: string;
  ip_no: string;
  age?: string | null;
  sex?: string | null;
  format: string;
  surgery_type: string;
  diagnosis: string;
  investigations?: string | null;
  status: string;
}

interface Props {
  admission: AdmissionData;
}

interface LabValues {
  hb: string;
  bg: string;
  hcv: string;
  hiv: string;
  vdrl: string;
  hbsag: string;
  urea: string;
  creatinine: string;
  uric_acid: string;
  rbs: string;
  ecg: string;
}

interface ExtraTest {
  name: string;
  value: string;
}

const parseInvestigations = (raw: string): { core: LabValues; extra: ExtraTest[] } => {
  const core: LabValues = {
    hb: '',
    bg: '',
    hcv: '',
    hiv: '',
    vdrl: '',
    hbsag: '',
    urea: '',
    creatinine: '',
    uric_acid: '',
    rbs: '',
    ecg: '',
  };
  const extra: ExtraTest[] = [];

  if (!raw || !raw.trim()) {
    return { core, extra };
  }

  const cleanRaw = raw.replace(/\t+/g, '\n').replace(/\|/g, '\n');
  const lines = cleanRaw.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);

  for (const line of lines) {
    const m = line.match(/^([^:=]+)[:=](.+)$/);
    if (m) {
      const rawK = m[1].trim();
      const val = m[2].trim();
      const k = rawK.toLowerCase().replace(/[^a-z0-9]/g, '');

      if (k === 'hb' || k === 'haemoglobin') core.hb = val;
      else if (k === 'bg' || k === 'bloodgroup' || k === 'bloodgrouping' || k === 'rh') core.bg = val;
      else if (k === 'hcv') core.hcv = val;
      else if (k === 'hiv') core.hiv = val;
      else if (k === 'vdrl') core.vdrl = val;
      else if (k === 'hbsag' || k === 'hbs') core.hbsag = val;
      else if (k === 'urea' || k === 'bloodurea') core.urea = val;
      else if (k === 'creatine' || k === 'creatinine' || k === 'serumcreatinine') core.creatinine = val;
      else if (k === 'uricacid' || k === 'serumuricacid') core.uric_acid = val;
      else if (k === 'rbs' || k === 'bloodsugar' || k === 'randombloodsugar') core.rbs = val;
      else if (k === 'ecg' || k === 'ekg' || k === 'electrocardiogram') core.ecg = val;
      else {
        extra.push({ name: rawK, value: val });
      }
    } else {
      if (!line.toLowerCase().startsWith('investigations')) {
        extra.push({ name: 'Note', value: line });
      }
    }
  }

  return { core, extra };
};

const serializeInvestigations = (core: LabValues, extra: ExtraTest[]): string => {
  const lines: string[] = [];
  if (core.hb.trim()) lines.push(`Hb : ${core.hb.trim()}`);
  if (core.bg.trim()) lines.push(`BG : ${core.bg.trim()}`);
  if (core.hcv.trim()) lines.push(`HCV : ${core.hcv.trim()}`);
  if (core.hiv.trim()) lines.push(`HIV : ${core.hiv.trim()}`);
  if (core.vdrl.trim()) lines.push(`VDRL : ${core.vdrl.trim()}`);
  if (core.hbsag.trim()) lines.push(`HBsAG : ${core.hbsag.trim()}`);
  if (core.urea.trim()) lines.push(`UREA : ${core.urea.trim()}`);
  if (core.creatinine.trim()) lines.push(`CREATININE : ${core.creatinine.trim()}`);
  if (core.uric_acid.trim()) lines.push(`URIC ACID : ${core.uric_acid.trim()}`);
  if (core.rbs.trim()) lines.push(`RBS : ${core.rbs.trim()}`);
  if (core.ecg && core.ecg.trim()) lines.push(`ECG : ${core.ecg.trim()}`);

  for (const item of extra) {
    if (item.value.trim()) {
      if (item.name === 'Note' || !item.name.trim()) {
        lines.push(item.value.trim());
      } else {
        lines.push(`${item.name.trim()} : ${item.value.trim()}`);
      }
    }
  }

  return lines.join('\n');
};

export default function AdmissionsTestReports({ admission }: Props) {
  const initial = parseInvestigations(admission.investigations || '');
  const [coreValues, setCoreValues] = useState<LabValues>(initial.core);
  const [extraTests, setExtraTests] = useState<ExtraTest[]>(initial.extra);
  const [showRawEditor, setShowRawEditor] = useState<boolean>(false);
  const [showCatalogModal, setShowCatalogModal] = useState<boolean>(false);
  const [catalogSearch, setCatalogSearch] = useState<string>('');
  const [selectedCategoryTab, setSelectedCategoryTab] = useState<string>('All');

  const isTestAdded = (item: LabCatalogItem): boolean => {
    if (item.id === 'bg') return Boolean(coreValues.bg);
    if (item.id === 'rbs') return Boolean(coreValues.rbs);
    if (item.id === 'hiv') return Boolean(coreValues.hiv);
    if (item.id === 'hcv') return Boolean(coreValues.hcv);
    if (item.id === 'vdrl') return Boolean(coreValues.vdrl);
    if (item.id === 'hbsag') return Boolean(coreValues.hbsag);
    if (item.id === 'urea') return Boolean(coreValues.urea);
    if (item.id === 'creatinine') return Boolean(coreValues.creatinine);
    if (item.id === 'uric_acid') return Boolean(coreValues.uric_acid);
    if (item.id === 'ecg') return Boolean(coreValues.ecg);
    return extraTests.some(
      (t) =>
        t.name.trim().toLowerCase() === item.name.trim().toLowerCase() ||
        t.name.trim().toLowerCase() === item.id.toLowerCase()
    );
  };

  const toggleCatalogTest = (item: LabCatalogItem) => {
    if (item.id === 'bg') {
      if (!coreValues.bg) updateCore('bg', 'O positive');
      return;
    }
    if (item.id === 'rbs') {
      if (!coreValues.rbs) updateCore('rbs', '100 mg/dL');
      return;
    }
    if (item.id === 'hiv') {
      if (!coreValues.hiv) updateCore('hiv', 'Negative');
      return;
    }
    if (item.id === 'hcv') {
      if (!coreValues.hcv) updateCore('hcv', 'Negative');
      return;
    }
    if (item.id === 'vdrl') {
      if (!coreValues.vdrl) updateCore('vdrl', 'Negative');
      return;
    }
    if (item.id === 'hbsag') {
      if (!coreValues.hbsag) updateCore('hbsag', 'Negative');
      return;
    }
    if (item.id === 'urea') {
      if (!coreValues.urea) updateCore('urea', '20 mg/dL');
      return;
    }
    if (item.id === 'creatinine') {
      if (!coreValues.creatinine) updateCore('creatinine', '0.8 mg/dL');
      return;
    }
    if (item.id === 'uric_acid') {
      if (!coreValues.uric_acid) updateCore('uric_acid', '4.2 mg/dL');
      return;
    }
    if (item.id === 'ecg') {
      if (!coreValues.ecg) updateCore('ecg', 'Normal Sinus Rhythm');
      return;
    }

    const idx = extraTests.findIndex(
      (t) =>
        t.name.trim().toLowerCase() === item.name.trim().toLowerCase() ||
        t.name.trim().toLowerCase() === item.id.toLowerCase()
    );

    if (idx >= 0) {
      removeExtraTest(idx);
    } else {
      const defaultVal = item.quickOptions && item.quickOptions.length > 0 ? item.quickOptions[0] : '';
      addExtraTest(item.name, defaultVal);
    }
  };

  const addFromCatalog = (item: LabCatalogItem) => {
    if (!isTestAdded(item)) {
      toggleCatalogTest(item);
    }
  };

  const filteredCatalog = LAB_CATALOG.filter((cat) => {
    if (selectedCategoryTab !== 'All' && cat.name !== selectedCategoryTab) {
      return false;
    }
    if (!catalogSearch.trim()) return true;
    const q = catalogSearch.toLowerCase().trim();
    return (
      cat.name.toLowerCase().includes(q) ||
      cat.tests.some((t) => t.name.toLowerCase().includes(q) || t.id.toLowerCase().includes(q))
    );
  }).map((cat) => {
    if (!catalogSearch.trim()) return cat;
    const q = catalogSearch.toLowerCase().trim();
    return {
      ...cat,
      tests: cat.tests.filter(
        (t) =>
          t.name.toLowerCase().includes(q) ||
          t.id.toLowerCase().includes(q) ||
          cat.name.toLowerCase().includes(q)
      ),
    };
  }).filter((cat) => cat.tests.length > 0);

  const previewInvestigations = formatInvestigations(
    showRawEditor ? data.investigations : serializeInvestigations(coreValues, extraTests)
  );

  const { data, setData, put, transform, processing, errors } = useForm({
    investigations: admission.investigations || serializeInvestigations(initial.core, initial.extra),
    next_step: 'surgery',
  });

  const updateCore = (key: keyof LabValues, val: string) => {
    setCoreValues((prev) => ({ ...prev, [key]: val }));
  };

  const setAllViralNegative = () => {
    setCoreValues((prev) => ({
      ...prev,
      hiv: 'Negative',
      hcv: 'Negative',
      vdrl: 'Negative',
      hbsag: 'Negative',
    }));
  };

  const addExtraTest = (name = '', value = '') => {
    setExtraTests((prev) => [...prev, { name, value }]);
  };

  const updateExtraTest = (idx: number, field: 'name' | 'value', val: string) => {
    setExtraTests((prev) => {
      const updated = [...prev];
      updated[idx] = { ...updated[idx], [field]: val };
      return updated;
    });
  };

  const removeExtraTest = (idx: number) => {
    setExtraTests((prev) => prev.filter((_, i) => i !== idx));
  };

  const toggleRawEditor = () => {
    if (!showRawEditor) {
      setData('investigations', serializeInvestigations(coreValues, extraTests));
    }
    setShowRawEditor((prev) => !prev);
  };

  const handleRawChange = (raw: string) => {
    setData('investigations', raw);
    const parsed = parseInvestigations(raw);
    setCoreValues(parsed.core);
    setExtraTests(parsed.extra);
  };

  const submitStage = (step: 'stay' | 'surgery', e: React.FormEvent) => {
    e.preventDefault();
    transform((current) => ({
      ...current,
      investigations: showRawEditor
        ? current.investigations
        : serializeInvestigations(coreValues, extraTests),
      next_step: step,
    }));
    put(`/admissions/${admission.id}/test-reports`);
  };

  const handleSaveOnly = (e: React.FormEvent) => submitStage('stay', e);

  const handleSaveAndNext = (e: React.FormEvent) => submitStage('surgery', e);

  return (
    <AppLayout>
      <Head title={`Stage 2: Lab Results — ${admission.patient_name}`} />

      {/* 4-Stage Navigation Header */}
      <AdmissionStageNav currentStage="test-reports" admission={admission} />

      <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h1 className="h3 fw-bold mb-1 text-dark" style={{ letterSpacing: '-0.02em' }}>
            Stage 2: Laboratory &amp; Diagnostic Test Reports
          </h1>
          <p className="text-muted mb-0">
            Standard laboratory investigations are predefined below. Simply enter the patient&rsquo;s test values.
          </p>
        </div>
        <div className="d-flex gap-2">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>
          <button
            type="button"
            onClick={toggleRawEditor}
            className="btn btn-outline-secondary"
          >
            <i className={`bi bi-${showRawEditor ? 'table' : 'code-slash'} me-1`}></i>
            {showRawEditor ? 'Show Values Form' : 'Show Raw Text View'}
          </button>
        </div>
      </div>

      <form id="testReportsForm">
        {/* Core Hospital Lab Results Form (Values Only) */}
        {!showRawEditor ? (
          <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div className="d-flex align-items-center gap-2">
                <span className="badge bg-primary">2.1</span>
                <span>Hospital Core Lab Investigations (Enter Values Only)</span>
              </div>
              <button
                type="button"
                onClick={setAllViralNegative}
                className="btn btn-sm btn-outline-success fw-semibold shadow-xs"
              >
                <i className="bi bi-shield-check me-1"></i> Set All Viral Markers (HIV, HCV, VDRL, HBsAg) Negative
              </button>
            </div>
            <div className="card-body">
              {/* Row 1: Hematology & Blood Group */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-droplet-half text-danger"></i>
                    <span>Hematology &amp; Blood Grouping</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Standard Pre-Op Workup
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Haemoglobin (Hb)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.72rem' }}>
                        Unit: <strong>g/dL</strong> &bull; Ref: <strong>12.0 – 16.0 g/dL</strong>
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        Hb :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hb}
                        onChange={(e) => updateCore('hb', e.target.value)}
                        placeholder="Enter value (e.g. 12.9 g/dL)"
                      />
                    </div>
                  </div>

                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Blood Group &amp; Rh (BG)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.72rem' }}>
                        ABO &amp; Rh Compatibility
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        BG :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.bg}
                        onChange={(e) => updateCore('bg', e.target.value)}
                        placeholder="Enter value or select (e.g. O positive)"
                        list="bgOptions"
                      />
                      <datalist id="bgOptions">
                        <option value="O positive" />
                        <option value="A positive" />
                        <option value="B positive" />
                        <option value="AB positive" />
                        <option value="O negative" />
                        <option value="A negative" />
                        <option value="B negative" />
                        <option value="AB negative" />
                      </datalist>
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 2: Viral Serology Screen */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-virus text-primary"></i>
                    <span>Viral &amp; Infectious Disease Screening</span>
                  </h6>
                  <div className="d-flex align-items-center gap-2">
                    <span className="badge bg-white text-muted border small d-none d-md-inline">
                      Ref: Non-Reactive / Negative
                    </span>
                    <button
                      type="button"
                      onClick={setAllViralNegative}
                      className="btn btn-xs btn-outline-success fw-semibold"
                      style={{ fontSize: '0.78rem' }}
                    >
                      <i className="bi bi-check2-all me-1"></i> Quick: All Negative
                    </button>
                  </div>
                </div>
                <div className="row g-3">
                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HCV</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HCV :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hcv}
                        onChange={(e) => updateCore('hcv', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HIV</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HIV :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hiv}
                        onChange={(e) => updateCore('hiv', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">VDRL</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        VDRL :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.vdrl}
                        onChange={(e) => updateCore('vdrl', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <label className="form-label fw-semibold text-secondary small text-uppercase">HBsAg</label>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '70px' }}>
                        HBsAg :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.hbsag}
                        onChange={(e) => updateCore('hbsag', e.target.value)}
                        placeholder="Negative"
                        list="viralOptions"
                      />
                    </div>
                  </div>

                  <datalist id="viralOptions">
                    <option value="Negative" />
                    <option value="Non-Reactive" />
                    <option value="Positive" />
                  </datalist>
                </div>
              </div>

              {/* Row 3: Biochemistry & Renal Function */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-activity text-success"></i>
                    <span>Biochemistry, Renal Function &amp; Blood Sugar</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Standard Units: mg/dL
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Blood Urea
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 15–40
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Urea :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.urea}
                        onChange={(e) => updateCore('urea', e.target.value)}
                        placeholder="e.g. 20 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Serum Creatinine
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 0.6–1.2
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Creat :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.creatinine}
                        onChange={(e) => updateCore('creatinine', e.target.value)}
                        placeholder="e.g. 0.86 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        Serum Uric Acid
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 3.5–7.2
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        Uric :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.uric_acid}
                        onChange={(e) => updateCore('uric_acid', e.target.value)}
                        placeholder="e.g. 4.3 mg/dL"
                      />
                    </div>
                  </div>

                  <div className="col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        RBS (Random Sugar)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        Ref: 70–140
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '75px' }}>
                        RBS :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.rbs}
                        onChange={(e) => updateCore('rbs', e.target.value)}
                        placeholder="e.g. 100.8 mg/dL"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 4: Cardiac & Pre-Operative Diagnostic Evaluation (ECG) */}
              <div className="p-3 bg-light rounded-3 mb-3 border">
                <div className="d-flex justify-content-between align-items-center mb-3">
                  <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i className="bi bi-heart-pulse text-danger"></i>
                    <span>Cardiac &amp; Pre-Operative Diagnostic Evaluation (ECG)</span>
                  </h6>
                  <span className="badge bg-white text-secondary border small fw-medium">
                    Ref: Normal Sinus Rhythm (NSR)
                  </span>
                </div>
                <div className="row g-3">
                  <div className="col-md-6">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                      <label className="form-label fw-semibold text-secondary small text-uppercase mb-0">
                        12-Lead Electrocardiogram (ECG)
                      </label>
                      <span className="badge bg-white text-muted border" style={{ fontSize: '0.68rem' }}>
                        12-Lead Standard Tracing
                      </span>
                    </div>
                    <div className="input-group">
                      <span className="input-group-text bg-white fw-bold text-dark" style={{ width: '90px' }}>
                        ECG :
                      </span>
                      <input
                        type="text"
                        className="form-control"
                        value={coreValues.ecg}
                        onChange={(e) => updateCore('ecg', e.target.value)}
                        placeholder="e.g. Normal Sinus Rhythm or WNL"
                        list="ecgOptions"
                      />
                      <datalist id="ecgOptions">
                        <option value="Normal Sinus Rhythm" />
                        <option value="Within Normal Limits (WNL)" />
                        <option value="Normal ECG" />
                        <option value="Sinus Bradycardia" />
                        <option value="Sinus Tachycardia" />
                        <option value="Incomplete RBBB" />
                        <option value="Non-specific ST-T changes" />
                      </datalist>
                    </div>
                  </div>

                  <div className="col-md-6 d-flex flex-column justify-content-end">
                    <div className="small text-muted mb-1.5 fw-semibold">Quick Select ECG Presets:</div>
                    <div className="d-flex flex-wrap gap-2">
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Normal Sinus Rhythm')}
                        className={`btn btn-xs ${coreValues.ecg === 'Normal Sinus Rhythm' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        <i className="bi bi-check2 me-1"></i> Normal Sinus Rhythm
                      </button>
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Within Normal Limits (WNL)')}
                        className={`btn btn-xs ${coreValues.ecg === 'Within Normal Limits (WNL)' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        WNL
                      </button>
                      <button
                        type="button"
                        onClick={() => updateCore('ecg', 'Normal ECG')}
                        className={`btn btn-xs ${coreValues.ecg === 'Normal ECG' ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                        style={{ fontSize: '0.78rem' }}
                      >
                        Normal ECG
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* Row 5: Additional / Custom Tests & Imaging */}
              <div className="p-3 bg-light rounded-3 border">
                <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                  <div>
                    <h6 className="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                      <i className="bi bi-file-earmark-medical text-danger"></i>
                      <span>Additional Hospital Laboratory &amp; Diagnostic Investigations</span>
                    </h6>
                    <small className="text-muted">
                      Digitized from Guru Hospital&rsquo;s official printed requisition slip with clinical units and normal reference ranges.
                    </small>
                  </div>
                  <div className="d-flex gap-2">
                    <button
                      type="button"
                      onClick={() => setShowCatalogModal(true)}
                      className="btn btn-sm btn-danger fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs"
                    >
                      <i className="bi bi-card-checklist"></i>
                      <span>Browse Lab Requisition Slip (60+ Tests)</span>
                    </button>
                    <button
                      type="button"
                      onClick={() => addExtraTest()}
                      className="btn btn-sm btn-outline-primary"
                    >
                      <i className="bi bi-plus-circle me-1"></i> Add Custom Test
                    </button>
                  </div>
                </div>

                {/* Quick Add Popular Hospital Tests */}
                <div className="mb-3 p-2 bg-white rounded border">
                  <div className="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i className="bi bi-lightning-charge-fill text-warning"></i>
                    <span>Quick-Add Common Hospital Tests:</span>
                  </div>
                  <div className="d-flex flex-wrap gap-1.5">
                    {FREQUENT_LAB_TESTS.map((t) => {
                      const catalogItem = findLabTest(t.name);
                      const isAdded = catalogItem ? isTestAdded(catalogItem) : extraTests.some((e) => e.name.toLowerCase().includes(t.name.toLowerCase()));
                      return (
                        <button
                          key={t.name}
                          type="button"
                          onClick={() => {
                            if (catalogItem) addFromCatalog(catalogItem);
                            else addExtraTest(t.name, '');
                          }}
                          className={`btn btn-xs ${isAdded ? 'btn-success text-white' : 'btn-outline-secondary bg-white'}`}
                          style={{ fontSize: '0.78rem' }}
                          title={`Add ${t.name} (${t.category})`}
                        >
                          <i className={`bi bi-${isAdded ? 'check2' : 'plus'} me-0.5`}></i>
                          {t.name}
                        </button>
                      );
                    })}
                  </div>
                </div>

                {/* Extra tests list */}
                {extraTests.length === 0 ? (
                  <div className="text-muted small py-3 text-center bg-white rounded border border-dashed">
                    <i className="bi bi-info-circle me-1 text-primary"></i>
                    No additional test reports added yet. Standard pre-op panel (including ECG) above is active. Click <strong>Browse Lab Requisition Slip</strong> or any quick-add button above to include Urine Routine, ESR, CBC, Imaging, etc.
                  </div>
                ) : (
                  extraTests.map((t, idx) => {
                    const catalogItem = findLabTest(t.name);
                    return (
                      <div key={idx} className="p-2.5 bg-white rounded border mb-2 shadow-xs">
                        <div className="row g-2 align-items-center">
                          <div className="col-md-5">
                            <div className="input-group input-group-sm">
                              <span className="input-group-text bg-light text-muted small fw-bold">#{idx + 1}</span>
                              <input
                                type="text"
                                className="form-control fw-semibold"
                                value={t.name}
                                onChange={(e) => updateExtraTest(idx, 'name', e.target.value)}
                                placeholder="Test Name (e.g. Urine Routine, ESR, TSH)"
                                list="labCatalogDatalist"
                              />
                            </div>
                            {catalogItem && (
                              <div className="mt-1 d-flex flex-wrap align-items-center gap-1.5" style={{ fontSize: '0.72rem' }}>
                                <span className="badge bg-secondary-subtle text-secondary border">
                                  {catalogItem.category}
                                </span>
                                {catalogItem.unit && (
                                  <span className="text-muted">Unit: <strong>{catalogItem.unit}</strong></span>
                                )}
                                {catalogItem.normalRange && (
                                  <span className="text-muted">&bull; Ref: <strong>{catalogItem.normalRange}</strong></span>
                                )}
                              </div>
                            )}
                          </div>
                          <div className="col-md-6">
                            <input
                              type="text"
                              className="form-control form-control-sm"
                              value={t.value}
                              onChange={(e) => updateExtraTest(idx, 'value', e.target.value)}
                              placeholder={catalogItem?.placeholder || 'Result value (e.g. Albumin Nil, Sugar Nil, Pus 1-2)'}
                            />
                            {catalogItem?.quickOptions && catalogItem.quickOptions.length > 0 && (
                              <div className="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                <span className="text-muted" style={{ fontSize: '0.70rem' }}>Presets:</span>
                                {catalogItem.quickOptions.map((opt, oIdx) => (
                                  <button
                                    key={oIdx}
                                    type="button"
                                    onClick={() => updateExtraTest(idx, 'value', opt)}
                                    className={`btn btn-xs py-0 px-1.5 ${t.value === opt ? 'btn-success text-white' : 'btn-outline-secondary'}`}
                                    style={{ fontSize: '0.70rem' }}
                                  >
                                    {opt}
                                  </button>
                                ))}
                              </div>
                            )}
                          </div>
                          <div className="col-md-1 text-end">
                            <button
                              type="button"
                              onClick={() => removeExtraTest(idx)}
                              className="btn btn-sm btn-outline-danger"
                              title="Remove Test"
                            >
                              <i className="bi bi-trash"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    );
                  })
                )}

                <datalist id="labCatalogDatalist">
                  {ALL_LAB_TESTS.map((test) => (
                    <option key={test.id} value={test.name}>
                      {test.category} {test.normalRange ? `(Ref: ${test.normalRange})` : ''}
                    </option>
                  ))}
                </datalist>
              </div>
            </div>
          </div>
        ) : (
          /* Raw Text Area (Advanced Mode) */
          <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header bg-white py-3 fw-bold text-dark d-flex align-items-center justify-content-between">
              <div>
                <span className="badge bg-secondary me-2">RAW</span>
                <span>Direct Raw Text Editor</span>
              </div>
              <button
                type="button"
                onClick={() => setShowRawEditor(false)}
                className="btn btn-sm btn-primary"
              >
                Switch Back to Values Form
              </button>
            </div>
            <div className="card-body">
              <textarea
                className="form-control font-monospace"
                rows={12}
                value={data.investigations}
                onChange={(e) => handleRawChange(e.target.value)}
                style={{ fontSize: '0.92rem', lineHeight: '1.6' }}
              />
            </div>
          </div>
        )}

        {/* Live Document Print Preview */}
        <div className="card mb-4 border-0 shadow-sm border-start border-4 border-primary">
          <div className="card-header bg-white py-3 fw-bold text-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span className="small text-uppercase tracking-wide text-secondary">
              <i className="bi bi-printer me-1"></i> Discharge Summary Print Preview (INVESTIGATIONS Section)
            </span>
            <span className="badge bg-light text-dark border">
              Strict 5 or 6 Column Clinical Grid &bull; Units &amp; Normal Reference Ranges
            </span>
          </div>
          <div className="card-body bg-white p-3">
            {previewInvestigations.length === 0 ? (
              <div className="text-muted text-center py-3">No investigations recorded.</div>
            ) : (
              <div
                className="d-grid gap-2"
                style={{
                  gridTemplateColumns: `repeat(${previewInvestigations.length > 10 ? 6 : 5}, minmax(0, 1fr))`,
                }}
              >
                {previewInvestigations.map((inv, idx) => (
                  <div
                    key={idx}
                    className="p-2 border rounded bg-white text-center d-flex flex-column justify-content-between shadow-xs"
                    style={{ minHeight: '62px' }}
                  >
                    <div
                      className="small fw-bold text-secondary text-uppercase text-truncate"
                      style={{ fontSize: '0.68rem', letterSpacing: '0.02em' }}
                      title={inv.label}
                    >
                      {inv.label}
                      {inv.unit && (
                        <span className="fw-normal text-muted ms-1" style={{ fontSize: '0.62rem', textTransform: 'none' }}>
                          ({inv.unit})
                        </span>
                      )}
                    </div>
                    <div className="fw-bold text-dark my-0.5 text-truncate" style={{ fontSize: '0.85rem' }} title={inv.value}>
                      {inv.value || '—'}
                    </div>
                    {inv.normal_range ? (
                      <div className="text-muted text-truncate" style={{ fontSize: '0.62rem', fontWeight: 500 }} title={`Ref: ${inv.normal_range}`}>
                        Ref: {inv.normal_range}
                      </div>
                    ) : (
                      <div style={{ fontSize: '0.62rem' }}>&nbsp;</div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Action Bar */}
        <div className="d-flex justify-content-between align-items-center pb-5">
          <Link href={`/admissions/${admission.id}/edit`} className="btn btn-outline-secondary">
            <i className="bi bi-arrow-left me-1"></i> Back to Stage 1: Admission
          </Link>
          <div className="d-flex gap-2">
            <button
              type="button"
              onClick={handleSaveOnly}
              disabled={processing}
              className="btn btn-outline-primary px-3 py-2 fw-semibold"
            >
              Save Stage 2 Changes
            </button>
            <button
              type="button"
              onClick={handleSaveAndNext}
              disabled={processing}
              className="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
            >
              <span>Save &amp; Next: Stage 3 (Labour / Surgery)</span>
              <i className="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </form>

      {/* Official Lab Requisition Slip Catalog Modal */}
      {showCatalogModal && (
        <div
          className="modal fade show d-block"
          tabIndex={-1}
          style={{ backgroundColor: 'rgba(15, 23, 42, 0.65)', backdropFilter: 'blur(2px)', zIndex: 1055 }}
        >
          <div className="modal-dialog modal-xl modal-dialog-scrollable">
            <div className="modal-content border-0 shadow-lg">
              {/* Slip Header with red accent */}
              <div className="modal-header-lab-slip d-flex justify-content-between align-items-center">
                <div>
                  <div className="d-flex align-items-center gap-2">
                    <span className="badge bg-danger text-white fw-bold text-uppercase px-2 py-1" style={{ fontSize: '0.72rem' }}>
                      Guru Hospital
                    </span>
                    <h5 className="modal-title fw-bold mb-0 text-dark" style={{ letterSpacing: '-0.01em', fontSize: '1.25rem' }}>
                      Official Diagnostic &amp; Laboratory Test Requisition Slip
                    </h5>
                  </div>
                  <p className="small mb-0 text-muted mt-1">
                    Complete catalog from the printed hospital requisition slip with checkboxes, standard clinical units, and normal reference ranges.
                  </p>
                </div>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setShowCatalogModal(false)}
                />
              </div>

              {/* Search & Filter Toolbar */}
              <div className="bg-light p-3 border-bottom">
                <div className="row g-2 align-items-center">
                  <div className="col-md-5">
                    <div className="input-group">
                      <span className="input-group-text bg-white"><i className="bi bi-search"></i></span>
                      <input
                        type="text"
                        className="form-control"
                        placeholder="Search tests (e.g. CBC, ESR, Urine Routine, TSH, Bilirubin, X-Ray)..."
                        value={catalogSearch}
                        onChange={(e) => setCatalogSearch(e.target.value)}
                        autoFocus
                      />
                      {catalogSearch && (
                        <button
                          type="button"
                          className="btn btn-outline-secondary"
                          onClick={() => setCatalogSearch('')}
                        >
                          <i className="bi bi-x"></i>
                        </button>
                      )}
                    </div>
                  </div>
                  <div className="col-md-7">
                    <div className="d-flex flex-wrap gap-1 align-items-center justify-content-md-end">
                      <span className="small text-muted me-1">Category:</span>
                      <button
                        type="button"
                        onClick={() => setSelectedCategoryTab('All')}
                        className={`btn btn-xs ${selectedCategoryTab === 'All' ? 'btn-danger' : 'btn-outline-secondary bg-white'}`}
                        style={{ fontSize: '0.74rem' }}
                      >
                        All (15)
                      </button>
                      {LAB_CATALOG.map((cat) => (
                        <button
                          key={cat.name}
                          type="button"
                          onClick={() => setSelectedCategoryTab(cat.name)}
                          className={`btn btn-xs ${selectedCategoryTab === cat.name ? 'btn-danger' : 'btn-outline-secondary bg-white'}`}
                          style={{ fontSize: '0.74rem' }}
                        >
                          {cat.name}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>
              </div>

              {/* Modal Body: Categorized Grid matching physical slip */}
              <div className="modal-body p-4 bg-light">
                <div className="row g-3">
                  {filteredCatalog.map((cat) => (
                    <div key={cat.name} className="col-md-6 col-lg-4">
                      <div className="lab-slip-category-card h-100">
                        <div
                          className="lab-slip-category-header"
                          style={{ backgroundColor: cat.badgeColor || '#b91c1c' }}
                        >
                          <span>{cat.name}</span>
                          <span className="badge">{cat.tests.length}</span>
                        </div>
                        <div className="p-2 bg-white">
                          <div className="d-flex flex-column gap-1.5">
                            {cat.tests.map((test) => {
                              const added = isTestAdded(test);
                              return (
                                <div
                                  key={test.id}
                                  data-testid={`catalog-test-${test.id}`}
                                  className={`p-2 rounded border transition-all ${
                                    added
                                      ? 'border-success bg-success-subtle'
                                      : 'bg-white hover-bg-light'
                                  }`}
                                  style={{ cursor: 'pointer' }}
                                  onClick={() => toggleCatalogTest(test)}
                                >
                                  <div className="d-flex justify-content-between align-items-start">
                                    <div className="form-check mb-0">
                                      <input
                                        type="checkbox"
                                        className="form-check-input"
                                        checked={added}
                                        onChange={() => {}} // handled by parent div click
                                      />
                                      <label
                                        className="form-check-label fw-semibold text-dark small ms-1"
                                        style={{ cursor: 'pointer' }}
                                      >
                                        {test.name}
                                      </label>
                                    </div>
                                    {added && (
                                      <span className="badge bg-success small text-uppercase" style={{ fontSize: '0.62rem' }}>
                                        Added
                                      </span>
                                    )}
                                  </div>
                                  {(test.unit || test.normalRange) && (
                                    <div className="mt-1 ps-4 text-muted" style={{ fontSize: '0.68rem' }}>
                                      {test.unit && <span>Unit: <strong>{test.unit}</strong> &bull; </span>}
                                      {test.normalRange && <span>Ref: <strong>{test.normalRange}</strong></span>}
                                    </div>
                                  )}
                                </div>
                              );
                            })}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Modal Footer */}
              <div className="modal-footer bg-white px-4 py-3 justify-content-between">
                <div className="small text-muted">
                  <span className="fw-bold text-dark">{extraTests.length}</span> additional investigation(s) in patient record.
                </div>
                <div className="d-flex gap-2">
                  <button
                    type="button"
                    className="btn btn-outline-secondary"
                    onClick={() => setShowCatalogModal(false)}
                  >
                    Close &amp; Review Stage 2
                  </button>
                  <button
                    type="button"
                    className="btn btn-danger"
                    onClick={() => setShowCatalogModal(false)}
                  >
                    <i className="bi bi-check2-circle me-1"></i> Apply &amp; Return to Form
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
