import { formatDate, formatDateTime } from './dateFormatter';

export interface FormattedTreatmentRow {
  medicine: string;
  dose: string;
}

export interface FormattedInvestigation {
  label: string;
  value: string;
  unit?: string;
  normal_range?: string;
  reference?: string;
}

export interface FormattedCondition {
  general: string;
  bp: string;
  pulse: string;
  pa: string;
}

export interface FormattedMedication {
  medicine: string;
  frequency: string;
  duration: string;
  timing: string;
}

export interface FormattedAdviceAndReview {
  advice_items: string[];
  review_text: string;
  review_date: string;
  tamil_note?: string;
}

export interface FormattedNewbornCard {
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
}

const INVESTIGATION_ORDER: Record<string, number> = {
  BP: 1,
  PULSE: 2,
  SPO2: 3,
  'BLOOD GROUP': 4,
  HB: 5,
  RBS: 6,
  UREA: 7,
  CREATININE: 8,
  'URIC ACID': 9,
  ECG: 10,
  HCV: 11,
  HIV: 12,
  VDRL: 13,
  HBSAG: 14,
  CBC: 20,
  ESR: 21,
  'BT, CT': 22,
  'PT - INR': 23,
  APTT: 24,
  'PERIPHERAL SMEAR': 25,
  'URINE ROUTINE': 26,
  RFT: 27,
  LFT: 28,
  'SR. BILIRUBIN': 29,
  'SERUM ELECTROLYTES': 30,
  'FASTING (FBS)': 31,
  PPBS: 32,
  HBA1C: 33,
  OGCT: 34,
  'SERUM CALCIUM': 35,
  'SERUM AMYLASE': 36,
  'SERUM LIPASE': 37,
  'TOTAL IRON': 38,
  TIBC: 39,
  'SERUM FERRITIN': 40,
  CHOLESTEROL: 41,
  TRIGLYCERIDES: 42,
  HDL: 43,
  LDL: 44,
  VLDL: 45,
  CRP: 46,
  WIDAL: 47,
  'RA FACTOR': 48,
  'ASO TITER': 49,
  'DENGUE SEROLOGY': 50,
  'SCRUB TYPHUS': 51,
  TSH: 52,
  TFT: 53,
  'TROPONIN-T': 54,
  'CK-MB': 55,
  'CHEST X-RAY (PA)': 56,
  'X-RAY KUB': 57,
  'URINE C/S': 58,
  'SPUTUM C/S': 59,
  'PUS C/S': 60,
  'BLOOD C/S': 61,
  'SPUTUM AFB': 62,
  'GEN-XPERT (MTB)': 63,
  'CA 125': 64,
  FPSA: 65,
  'S PSA': 66,
  TESTOSTERONE: 67,
  ESTRADIOL: 68,
  AMH: 69,
  ANA: 70,
  PROLACTIN: 71,
  'LH, FSH': 72,
  'D-DIMER': 73,
  'BETA HCG': 74,
  'SEMEN ANALYSIS': 75,
  'SEMEN C/S': 76,
  'SEMEN-XPERT': 77,
  'SPOT PCR': 78,
  'SPOT MICROALBUMIN': 79,
  'SPOT SODIUM': 80,
  'SMEAR FOR MP/MF': 81,
  'PACKAGE - 1': 82,
  'PACKAGE - 2': 83,
  'PACKAGE - 3': 84,
};

export const INVESTIGATION_STANDARDS: Record<string, { unit: string; normal_range: string; reference: string }> = {
  HB: { unit: 'g/dL', normal_range: '12.0 - 16.0', reference: '12.0 - 16.0 g/dL' },
  'BLOOD GROUP': { unit: '', normal_range: 'Compatible', reference: 'ABO/Rh Compatible' },
  BP: { unit: 'mmHg', normal_range: '120/80', reference: '120/80 mmHg' },
  PULSE: { unit: 'bpm', normal_range: '60 - 100', reference: '60 - 100 bpm' },
  SPO2: { unit: '%', normal_range: '95 - 100', reference: '95 - 100 %' },
  HIV: { unit: '', normal_range: 'Non-Reactive', reference: 'Non-Reactive' },
  HCV: { unit: '', normal_range: 'Non-Reactive', reference: 'Non-Reactive' },
  VDRL: { unit: '', normal_range: 'Non-Reactive', reference: 'Non-Reactive' },
  HBSAG: { unit: '', normal_range: 'Non-Reactive', reference: 'Non-Reactive' },
  UREA: { unit: 'mg/dL', normal_range: '15 - 40', reference: '15 - 40 mg/dL' },
  CREATININE: { unit: 'mg/dL', normal_range: '0.6 - 1.2', reference: '0.6 - 1.2 mg/dL' },
  'URIC ACID': { unit: 'mg/dL', normal_range: '3.5 - 7.2', reference: '3.5 - 7.2 mg/dL' },
  RBS: { unit: 'mg/dL', normal_range: '70 - 140', reference: '70 - 140 mg/dL' },
  ECG: { unit: '', normal_range: 'Normal Sinus', reference: 'Normal Sinus Rhythm' },
  CBC: { unit: 'cells/cu.mm', normal_range: 'Normal Hemogram', reference: 'TC: 4000-11000 | Plt: 1.5-4.5L' },
  ESR: { unit: 'mm/hr', normal_range: '0 - 20', reference: '0 - 20 mm/hr' },
  'BT, CT': { unit: 'min', normal_range: 'BT: 2-6 | CT: 4-10', reference: 'BT: 2-6 min | CT: 4-10 min' },
  'SMEAR FOR MP/MF': { unit: '', normal_range: 'Negative', reference: 'Negative for MP & MF' },
  'PT - INR': { unit: 'INR', normal_range: '0.8 - 1.2', reference: '0.8 - 1.2 INR' },
  APTT: { unit: 'sec', normal_range: '25 - 35', reference: '25 - 35 sec' },
  'PERIPHERAL SMEAR': { unit: '', normal_range: 'Normocytic Normochromic', reference: 'Normocytic Normochromic' },
  'TOTAL IRON': { unit: 'mcg/dL', normal_range: '60 - 170', reference: '60 - 170 mcg/dL' },
  TIBC: { unit: 'mcg/dL', normal_range: '240 - 450', reference: '240 - 450 mcg/dL' },
  'SERUM FERRITIN': { unit: 'ng/mL', normal_range: '15 - 200', reference: '15 - 200 ng/mL' },
  'CHEST X-RAY (PA)': { unit: '', normal_range: 'Normal Chest', reference: 'Clear Bilateral Lung Fields' },
  'X-RAY KUB': { unit: '', normal_range: 'No Calculus', reference: 'No Radio-opaque Calculi' },
  'TROPONIN-T': { unit: 'ng/mL', normal_range: '< 0.01', reference: '< 0.01 ng/mL (Negative)' },
  'CK-MB': { unit: 'U/L', normal_range: '< 25', reference: '0 - 25 U/L' },
  'SPUTUM AFB': { unit: '', normal_range: 'Negative', reference: 'Negative for AFB' },
  'GEN-XPERT (MTB)': { unit: '', normal_range: 'Not Detected', reference: 'MTB Not Detected' },
  'SEMEN ANALYSIS': { unit: 'mil/mL', normal_range: '> 15', reference: 'Count > 15 mil/mL | Motility > 40%' },
  'SEMEN C/S': { unit: '', normal_range: 'Sterile', reference: 'No Pathogen Grown' },
  'SEMEN-XPERT': { unit: '', normal_range: 'Not Detected', reference: 'MTB Not Detected' },
  OGCT: { unit: 'mg/dL', normal_range: '< 140', reference: '< 140 mg/dL' },
  'FASTING (FBS)': { unit: 'mg/dL', normal_range: '70 - 100', reference: '70 - 100 mg/dL' },
  PPBS: { unit: 'mg/dL', normal_range: '< 140', reference: '< 140 mg/dL' },
  HBA1C: { unit: '%', normal_range: '< 5.7', reference: '< 5.7 %' },
  RFT: { unit: 'mg/dL', normal_range: 'Normal RFT', reference: 'Urea: 15-40 | Creat: 0.6-1.2' },
  LFT: { unit: '', normal_range: 'Normal LFT', reference: 'Normal Hepatic Profile' },
  'SR. BILIRUBIN': { unit: 'mg/dL', normal_range: '0.2 - 1.2', reference: '0.2 - 1.2 mg/dL' },
  'SERUM ELECTROLYTES': { unit: 'mmol/L', normal_range: 'Na: 135-145 | K: 3.5-5.0', reference: 'Na: 135-145 | K: 3.5-5.0 | Cl: 98-107' },
  'SERUM CALCIUM': { unit: 'mg/dL', normal_range: '8.5 - 10.5', reference: '8.5 - 10.5 mg/dL' },
  'SERUM AMYLASE': { unit: 'U/L', normal_range: '28 - 100', reference: '28 - 100 U/L' },
  'SERUM LIPASE': { unit: 'U/L', normal_range: '10 - 140', reference: '10 - 140 U/L' },
  'URINE ROUTINE': { unit: '', normal_range: 'Alb: Nil | Sug: Nil | Pus: 1-2', reference: 'Albumin: Nil | Sugar: Nil | Pus: 1-2' },
  'SPOT PCR': { unit: 'mg/g', normal_range: '< 150', reference: '< 150 mg/g' },
  'SPOT MICROALBUMIN': { unit: 'mg/L', normal_range: '< 20', reference: '< 20 mg/L' },
  'SPOT SODIUM': { unit: 'mmol/L', normal_range: '40 - 220', reference: '40 - 220 mmol/L' },
  CHOLESTEROL: { unit: 'mg/dL', normal_range: '< 200', reference: '< 200 mg/dL' },
  TRIGLYCERIDES: { unit: 'mg/dL', normal_range: '< 150', reference: '< 150 mg/dL' },
  HDL: { unit: 'mg/dL', normal_range: '> 40', reference: '> 40 mg/dL' },
  LDL: { unit: 'mg/dL', normal_range: '< 100', reference: '< 100 mg/dL' },
  VLDL: { unit: 'mg/dL', normal_range: '2 - 30', reference: '2 - 30 mg/dL' },
  'CA 125': { unit: 'U/mL', normal_range: '< 35', reference: '< 35 U/mL' },
  FPSA: { unit: 'ng/mL', normal_range: '> 0.25', reference: 'Ratio > 0.25' },
  'S PSA': { unit: 'ng/mL', normal_range: '< 4.0', reference: '< 4.0 ng/mL' },
  CRP: { unit: 'mg/L', normal_range: '< 6.0', reference: '< 6.0 mg/L' },
  WIDAL: { unit: '', normal_range: 'Negative', reference: 'Titers < 1:80' },
  'RA FACTOR': { unit: 'IU/mL', normal_range: '< 14', reference: '< 14 IU/mL' },
  'ASO TITER': { unit: 'IU/mL', normal_range: '< 200', reference: '< 200 IU/mL' },
  'DENGUE SEROLOGY': { unit: '', normal_range: 'Negative', reference: 'NS1, IgM, IgG: Negative' },
  'SCRUB TYPHUS': { unit: '', normal_range: 'Negative', reference: 'Negative' },
  'URINE C/S': { unit: '', normal_range: 'Sterile', reference: 'No Growth After 48 hrs' },
  'SPUTUM C/S': { unit: '', normal_range: 'Normal Flora', reference: 'Normal Commensals Grown' },
  'PUS C/S': { unit: '', normal_range: 'Sterile', reference: 'No Growth After 48 hrs' },
  'BLOOD C/S': { unit: '', normal_range: 'Sterile', reference: 'Sterile After 5 Days' },
  TSH: { unit: 'mIU/L', normal_range: '0.4 - 4.2', reference: '0.4 - 4.2 mIU/L' },
  TFT: { unit: '', normal_range: 'Euthyroid', reference: 'T3, T4, TSH WNL' },
  TESTOSTERONE: { unit: 'ng/dL', normal_range: '280 - 1100 (M)', reference: 'Male: 280-1100 | Fem: 15-70' },
  ESTRADIOL: { unit: 'pg/mL', normal_range: '30 - 400', reference: '30 - 400 pg/mL' },
  AMH: { unit: 'ng/mL', normal_range: '1.5 - 4.0', reference: '1.5 - 4.0 ng/mL' },
  ANA: { unit: '', normal_range: 'Negative', reference: 'Negative (< 1:80)' },
  PROLACTIN: { unit: 'ng/mL', normal_range: '4.8 - 23.3', reference: '4.8 - 23.3 ng/mL' },
  'LH, FSH': { unit: 'mIU/mL', normal_range: 'LH: 2-15 | FSH: 3-12', reference: 'LH: 2-15 | FSH: 3-12 mIU/mL' },
  'D-DIMER': { unit: 'mcg/mL FEU', normal_range: '< 0.5', reference: '< 0.5 mcg/mL FEU' },
  'BETA HCG': { unit: 'mIU/mL', normal_range: '< 5.0 (Non-Preg)', reference: '< 5.0 mIU/mL' },
  'PACKAGE - 1': { unit: '', normal_range: 'Pre-op Clearance', reference: 'Pre-Operative Profile Normal' },
  'PACKAGE - 2': { unit: '', normal_range: 'Normal Profile', reference: 'Master Health Profile Normal' },
  'PACKAGE - 3': { unit: '', normal_range: 'Normal Profile', reference: 'Executive Cardiac Profile Normal' },
};

export function normalizeInvestigationKey(rawLabel: string): string {
  const key = rawLabel.toLowerCase().replace(/[^a-z0-9]/g, '');
  switch (key) {
    case 'hb':
    case 'haemoglobin':
      return 'HB';
    case 'bg':
    case 'bloodgroup':
    case 'bloodgrouping':
    case 'rh':
      return 'BLOOD GROUP';
    case 'bp':
    case 'bloodpressure':
      return 'BP';
    case 'pulse':
    case 'pulserate':
    case 'heartrate':
    case 'hr':
      return 'PULSE';
    case 'spo2':
    case 'oxygen':
    case 'oxygensaturation':
    case 'sats':
      return 'SPO2';
    case 'hiv':
      return 'HIV';
    case 'hcv':
      return 'HCV';
    case 'vdrl':
      return 'VDRL';
    case 'hbsag':
    case 'hbs':
      return 'HBSAG';
    case 'urea':
    case 'bloodurea':
      return 'UREA';
    case 'creatinine':
    case 'creatine':
    case 'serumcreatinine':
      return 'CREATININE';
    case 'uricacid':
    case 'serumuricacid':
      return 'URIC ACID';
    case 'rbs':
    case 'bloodsugar':
    case 'randombloodsugar':
      return 'RBS';
    case 'ecg':
    case 'ekg':
    case 'electrocardiogram':
      return 'ECG';
    case 'cbc':
    case 'completebloodcount':
    case 'hemogram':
      return 'CBC';
    case 'esr':
    case 'erythrocytesedimentationrate':
      return 'ESR';
    case 'btct':
    case 'bt':
    case 'ct':
    case 'bleedingtimeclottingtime':
      return 'BT, CT';
    case 'smearformpmf':
    case 'smearformp':
    case 'mpmf':
    case 'smearmpmf':
      return 'SMEAR FOR MP/MF';
    case 'ptinr':
    case 'pt':
    case 'inr':
    case 'prothrombintime':
      return 'PT - INR';
    case 'aptt':
      return 'APTT';
    case 'peripheralsmear':
    case 'ps':
    case 'peripheralsmearstudy':
      return 'PERIPHERAL SMEAR';
    case 'totaliron':
    case 'serumiron':
    case 'iron':
      return 'TOTAL IRON';
    case 'tibc':
      return 'TIBC';
    case 'ferritin':
    case 'serumferritin':
      return 'SERUM FERRITIN';
    case 'xraychest':
    case 'chestxray':
    case 'chestpa':
    case 'xraychestpa':
    case 'xraychestpaview':
    case 'chestpaview':
    case 'chest':
      return 'CHEST X-RAY (PA)';
    case 'xraykub':
    case 'kub':
      return 'X-RAY KUB';
    case 'troponint':
    case 'tropt':
    case 'trop':
      return 'TROPONIN-T';
    case 'ckmb':
      return 'CK-MB';
    case 'sputumafb':
    case 'afb':
      return 'SPUTUM AFB';
    case 'genxpert':
    case 'genxpertmtbrif':
    case 'genxpertmtb':
    case 'xpert':
      return 'GEN-XPERT (MTB)';
    case 'semenanalysis':
    case 'semencount':
      return 'SEMEN ANALYSIS';
    case 'semencs':
      return 'SEMEN C/S';
    case 'semenxpert':
      return 'SEMEN-XPERT';
    case 'ogct':
      return 'OGCT';
    case 'fasting':
    case 'fbs':
    case 'fastingbloodsugar':
      return 'FASTING (FBS)';
    case 'postprandial':
    case 'ppbs':
    case 'postprandialbloodsugar':
      return 'PPBS';
    case 'hba1c':
    case 'glycatedhemoglobin':
      return 'HBA1C';
    case 'rft':
    case 'renalfunctiontest':
    case 'renalfunction':
      return 'RFT';
    case 'lft':
    case 'liverfunctiontest':
    case 'liverfunction':
      return 'LFT';
    case 'srbilirubin':
    case 'bilirubin':
    case 'totalbilirubin':
    case 'serumbilirubin':
      return 'SR. BILIRUBIN';
    case 'electrolytes':
    case 'serumelectrolytes':
    case 'electrolyte':
      return 'SERUM ELECTROLYTES';
    case 'calcium':
    case 'serumcalcium':
      return 'SERUM CALCIUM';
    case 'amylase':
    case 'serumamylase':
      return 'SERUM AMYLASE';
    case 'lipase':
    case 'serumlipase':
      return 'SERUM LIPASE';
    case 'urineroutine':
    case 'urinalysis':
    case 'urineanalysis':
    case 'urinecomplete':
    case 'urine':
      return 'URINE ROUTINE';
    case 'spotpcr':
      return 'SPOT PCR';
    case 'spotmicroalbumin':
      return 'SPOT MICROALBUMIN';
    case 'spotsodium':
      return 'SPOT SODIUM';
    case 'cholestrol':
    case 'cholesterol':
    case 'totalcholesterol':
      return 'CHOLESTEROL';
    case 'triglycerides':
    case 'tg':
    case 'tgl':
      return 'TRIGLYCERIDES';
    case 'hdl':
    case 'hdlcholesterol':
      return 'HDL';
    case 'ldl':
    case 'ldlcholesterol':
      return 'LDL';
    case 'vldl':
    case 'vldlcholesterol':
      return 'VLDL';
    case 'ca125':
      return 'CA 125';
    case 'fpsa':
    case 'freepsa':
      return 'FPSA';
    case 'spsa':
    case 'psa':
    case 'serumpsa':
      return 'S PSA';
    case 'crp':
    case 'creactiveprotein':
      return 'CRP';
    case 'widal':
    case 'widaltest':
      return 'WIDAL';
    case 'rafactor':
    case 'rheumatoidfactor':
      return 'RA FACTOR';
    case 'asotiter':
    case 'aso':
      return 'ASO TITER';
    case 'dengue':
    case 'dengueserology':
    case 'denguens1':
    case 'denguepanel':
      return 'DENGUE SEROLOGY';
    case 'scrubtyphus':
      return 'SCRUB TYPHUS';
    case 'urinecs':
      return 'URINE C/S';
    case 'sputumcs':
      return 'SPUTUM C/S';
    case 'puscs':
      return 'PUS C/S';
    case 'bloodcs':
      return 'BLOOD C/S';
    case 'tsh':
    case 'thyroidstimulatinghormone':
      return 'TSH';
    case 'tft':
    case 'thyroidfunctiontest':
      return 'TFT';
    case 'testosterone':
    case 'serumtestosterone':
      return 'TESTOSTERONE';
    case 'estradiol':
    case 'e2':
    case 'serumestradiol':
      return 'ESTRADIOL';
    case 'amh':
      return 'AMH';
    case 'ana':
    case 'antinuclearantibodies':
      return 'ANA';
    case 'prolactin':
    case 'serumprolactin':
      return 'PROLACTIN';
    case 'lhfsh':
    case 'lh':
    case 'fsh':
      return 'LH, FSH';
    case 'ddimer':
      return 'D-DIMER';
    case 'betahcg':
    case 'hcg':
      return 'BETA HCG';
    case 'package1':
    case 'packagei':
      return 'PACKAGE - 1';
    case 'package2':
    case 'packageii':
      return 'PACKAGE - 2';
    case 'package3':
    case 'packageiii':
      return 'PACKAGE - 3';
    default:
      return rawLabel.toUpperCase();
  }
}

export function formatTreatmentCourse(text?: string | null): FormattedTreatmentRow[] {
  if (!text) return [];
  const lines = text.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);
  const rows: FormattedTreatmentRow[] = [];

  for (const line of lines) {
    const treatedWithMatch = line.match(/^Treated with\s*(.*)$/i);
    if (treatedWithMatch) {
      rows.push({
        medicine: treatedWithMatch[1].trim() || 'IV Fluids',
        dose: '—',
      });
      continue;
    }

    const normalized = line.replace(/^(Inj|Tab|Cap|Syp)-/i, '$1. ');
    const match = normalized.match(/^(Inj\.\s+[A-Za-z0-9_\-+]+|Tab\.\s+[A-Za-z0-9_\-+\s]+|Cap\.\s+[A-Za-z0-9_\-+\s]+|[A-Za-z0-9_\-+]+)\s+(.+)$/i);
    if (match) {
      const med = match[1].trim();
      let dose = match[2].trim().replace(/(\d+)\s*(mg|g|mcg|ml)/i, '$1 $2');
      dose = dose.replace(/\b(iv|im|sc|po|od|bd|tds|qid|stat|prn)\b/gi, (m) => m.toUpperCase());
      rows.push({ medicine: med, dose });
    } else {
      rows.push({ medicine: normalized, dose: '—' });
    }
  }

  return rows;
}

export function formatInvestigations(
  text?: string | null,
  admission?: {
    vital_bp?: string | null;
    vital_pulse?: string | null;
    vital_spo2?: string | null;
    discharge_bp?: string | null;
    discharge_pulse?: string | null;
    discharge_spo2?: string | null;
  } | null
): FormattedInvestigation[] {
  const items: FormattedInvestigation[] = [];
  if (text) {
    const lines = text.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);

    for (const line of lines) {
      const match = line.match(/^([^:=]+)[:=](.+)$/);
      if (match) {
        const rawLabel = match[1].trim();
        const rawVal = match[2].trim();
        const label = normalizeInvestigationKey(rawLabel);

        const std = INVESTIGATION_STANDARDS[label] || { unit: '', normal_range: '', reference: '' };
        items.push({
          label,
          value: rawVal,
          unit: std.unit,
          normal_range: std.normal_range,
          reference: std.reference,
        });
      } else {
        items.push({ label: 'NOTE', value: line, unit: '', normal_range: '', reference: '' });
      }
    }
  }

  if (admission) {
    const existingLabels = new Set(items.map((i) => i.label));

    const bpVal = (admission.vital_bp || admission.discharge_bp || '').trim();
    if (bpVal && bpVal !== '—' && !existingLabels.has('BP')) {
      items.push({
        label: 'BP',
        value: bpVal,
        unit: 'mmHg',
        normal_range: '120/80',
        reference: '120/80 mmHg',
      });
    }

    const pulseVal = (admission.vital_pulse || admission.discharge_pulse || '').trim();
    if (pulseVal && pulseVal !== '—' && !existingLabels.has('PULSE')) {
      items.push({
        label: 'PULSE',
        value: pulseVal,
        unit: 'bpm',
        normal_range: '60 - 100',
        reference: '60 - 100 bpm',
      });
    }

    const spo2Val = (admission.vital_spo2 || admission.discharge_spo2 || '').trim();
    if (spo2Val && spo2Val !== '—' && !existingLabels.has('SPO2')) {
      items.push({
        label: 'SPO2',
        value: spo2Val,
        unit: '%',
        normal_range: '95 - 100',
        reference: '95 - 100 %',
      });
    }
  }

  items.sort((a, b) => {
    const orderA = INVESTIGATION_ORDER[a.label] ?? 99;
    const orderB = INVESTIGATION_ORDER[b.label] ?? 99;
    return orderA - orderB;
  });

  return items;
}

export function groupInvestigationsForGrid(investigations: FormattedInvestigation[]): FormattedInvestigation[][] {
  const valid = investigations.filter((inv) => {
    const val = String(inv.value ?? '').trim();
    return val !== '' && val !== '—' && val !== '-' && !/^(nil|none)$/i.test(val);
  });

  if (valid.length === 0) return [];

  const row1Labels = new Set(['BP', 'PULSE', 'SPO2', 'BLOOD GROUP', 'HB']);
  const row2Labels = new Set(['RBS', 'UREA', 'CREATININE', 'URIC ACID', 'ECG']);
  const row3Labels = new Set(['HCV', 'HIV', 'VDRL', 'HBSAG']);

  const row1 = valid.filter((i) => row1Labels.has(i.label));
  const row2 = valid.filter((i) => row2Labels.has(i.label));
  const row3 = valid.filter((i) => row3Labels.has(i.label));
  const extra = valid.filter((i) => !row1Labels.has(i.label) && !row2Labels.has(i.label) && !row3Labels.has(i.label));

  const rows: FormattedInvestigation[][] = [];
  if (row1.length > 0) rows.push(row1);
  if (row2.length > 0) rows.push(row2);
  if (row3.length > 0) rows.push(row3);
  for (let i = 0; i < extra.length; i += 5) {
    rows.push(extra.slice(i, i + 5));
  }

  return rows;
}

export function formatConditionOnDischarge(text?: string | null): FormattedCondition {
  const res: FormattedCondition = {
    general: '—',
    bp: '—',
    pulse: '—',
    pa: '—',
  };

  if (!text || !text.trim()) return res;

  let matchedAny = false;

  const gm = text.match(/general\s+condition\s*[:\-]?\s*([a-zA-Z]+)/i);
  if (gm) {
    res.general = gm[1].charAt(0).toUpperCase() + gm[1].slice(1).toLowerCase();
    matchedAny = true;
  }

  const bpm = text.match(/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg)?)/i);
  if (bpm) {
    let bp = bpm[1].trim();
    if (!bp.toLowerCase().includes('mmhg')) bp += ' mmHg';
    res.bp = bp;
    matchedAny = true;
  }

  const pm = text.match(/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i);
  if (pm) {
    res.pulse = `${pm[1]} b/min`;
    matchedAny = true;
  }

  const pam = text.match(/\bP\/?A\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (pam) {
    const pa = pam[1].trim().replace(/\.$/, '');
    res.pa =
      /no\s+undue\s+bleeding/i.test(text) && !/no\s+undue\s+bleeding/i.test(pa)
        ? `${pa}; no undue bleeding P/V`
        : pa;
    matchedAny = true;
  }

  if (!matchedAny) {
    res.general = text.trim().replace(/\s+/g, ' ');
  }

  return res;
}

export function formatMedications(text?: string | any[] | null): FormattedMedication[] {
  if (!text) return [];

  if (Array.isArray(text)) {
    return text.map((item) => ({
      medicine: item.medicine || item.name || '—',
      frequency: item.frequency || item.freq || '—',
      duration: item.duration || item.dur || '—',
      timing: item.timing || '—',
    }));
  }

  if (typeof text === 'string' && text.trim().startsWith('[') && text.trim().endsWith(']')) {
    try {
      const parsed = JSON.parse(text);
      if (Array.isArray(parsed)) return formatMedications(parsed);
    } catch (e) {
      // fallback
    }
  }

  const lines = (typeof text === 'string' ? text : '').split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);
  const rows: FormattedMedication[] = [];

  for (let line of lines) {
    let freq = '—';
    let dur = '—';
    let timing = '—';

    const fm = line.match(/\(([\d\-]+)\)/) || line.match(/\b(\d-\d-\d)\b/);
    if (fm) {
      freq = fm[1];
      line = line.replace(fm[0], '');
    }

    const tm = line.match(/(After\s+food|Before\s+food|With\s+food|Bedtime)/i);
    if (tm) {
      timing = tm[1].charAt(0).toUpperCase() + tm[1].slice(1).toLowerCase();
      line = line.replace(tm[0], '');
    }

    const dm = line.match(/(?:x\s*|for\s*)?(\d+\s*(?:days|weeks|months|day))/i);
    if (dm) {
      dur = dm[1].toLowerCase().trim();
      line = line.replace(dm[0], '');
    }

    let med = line
      .replace(/\s+/g, ' ')
      .trim()
      .replace(/(?:\s*[-–:]+\s*)+$/u, '')
      .replace(/\s+x$/i, '')
      .replace(/(?:\s*[-–:]+\s*)+$/u, '')
      .trim();
    med = med.replace(/\b(tab|cap|syp|inj)\b/gi, (m) => m.charAt(0).toUpperCase() + m.slice(1).toLowerCase() + '.');
    med = med.replace(/Tab\.\./g, 'Tab.').replace(/Cap\.\./g, 'Cap.').replace(/Inj\.\./g, 'Inj.');
    med = med.replace(/\+\s*([a-zA-Z]+)/g, (_, word) => `+ ${word.charAt(0).toUpperCase()}${word.slice(1).toLowerCase()}`);
    med = med.replace(/Sulbactum/i, 'Sulbactam');

    rows.push({ medicine: med, frequency: freq, duration: dur, timing });
  }

  return rows;
}

export function formatAdviceAndReview(adviceText?: string | null, followUpText?: string | null, dischargeAt?: string | null, extraContext?: string | null): FormattedAdviceAndReview {
  let items: string[] = [];
  let reviewText = '';
  let reviewDate = '';

  const source = `${adviceText || ''}\n${followUpText || ''}\n${extraContext || ''}`;
  const lines = source.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);

  let tamil_note: string | undefined = undefined;

  for (let line of lines) {
    const rm = line.match(/review\s+after\s+([^\(\)]+)(?:\((.*?)\))?/i);
    if (rm) {
      reviewText = `Review after ${rm[1].trim()}`;
      if (rm[2]?.trim()) reviewDate = rm[2].trim();
      continue;
    }

    const dm = line.match(/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/);
    if (dm) {
      reviewDate = dm[1].replace(/[\/\-]/g, '.');
      if (!reviewText && /review/i.test(line)) {
        reviewText = line.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, '$1.$2.$3');
        continue;
      }
    }

    if (/[\u0B80-\u0BFF]/.test(line)) {
      tamil_note = line;
      continue;
    }

    if (/post\s*natal\s*exercise/i.test(line)) line = 'Post natal exercise';
    else if (/exclusive\s*breast\s*feeding/i.test(line)) line = 'Exclusive breast feeding for 6 months';
    else if (/immunisation|immunization/i.test(line)) line = 'Immunisation';

    items.push(line);
  }

  if (!reviewText && followUpText && followUpText.trim()) {
    const cleanFollow = followUpText.replace(/[\u0B80-\u0BFF].*/, '').trim();
    if (cleanFollow && !/^\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4}$/.test(cleanFollow)) {
      reviewText = cleanFollow;
    } else if (reviewDate) {
      reviewText = 'Review Date';
    }
  } else if (!reviewText && reviewDate) {
    reviewText = 'Review Date';
  }

  if (!tamil_note && /stent/i.test(source)) {
    tamil_note = `வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு ${reviewDate || '(Date)'} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.`;
  }

  items = Array.from(new Set(items));

  return { advice_items: items, review_text: reviewText, review_date: reviewDate, tamil_note };
}

const CANONICAL_BIRTH_VACCINES = ['Vitamin K', 'BCG', 'OPV', 'HEP-B1'] as const;

function formatDotDate(raw?: string | null): string {
  if (!raw) return '';
  const trimmed = String(raw).trim();
  if (!trimmed) return '';
  return formatDate(trimmed);
}

export function formatBirthVaccinesText(
  vaccines?: string[] | null,
  vaccineDates?: Record<string, string> | null,
  fallbackDate?: string | null
): string {
  const inputSet = new Set(Array.isArray(vaccines) ? vaccines.filter(Boolean) : []);
  const selected = CANONICAL_BIRTH_VACCINES.filter((v) => inputSet.has(v));
  const fallbackFormatted = formatDotDate(fallbackDate);

  if (selected.length === 0) {
    return fallbackFormatted ? `Given on ${fallbackFormatted}` : '';
  }

  const grouped = new Map<string, string[]>();
  const distinctDates = new Set<string>();

  for (const vaccine of selected) {
    const rawDate = vaccineDates?.[vaccine];
    const formattedDate = formatDotDate(rawDate) || fallbackFormatted;
    if (formattedDate) {
      distinctDates.add(formattedDate);
    }
    const existing = grouped.get(formattedDate) || [];
    existing.push(vaccine);
    grouped.set(formattedDate, existing);
  }

  if (distinctDates.size === 1 && !grouped.has('')) {
    const onlyDate = Array.from(distinctDates)[0];
    return `${selected.join(', ')} (Given on ${onlyDate})`;
  }

  if (distinctDates.size === 0) {
    return selected.join(', ');
  }

  const parts: string[] = [];
  grouped.forEach((names, dateLabel) => {
    parts.push(dateLabel ? `${names.join(', ')} (${dateLabel})` : names.join(', '));
  });

  return parts.join('; ');
}

export function formatNewborns(admission: any): FormattedNewbornCard[] {
  const newborns = admission.newborns || [];
  if (!newborns || newborns.length === 0) return [];

  const isMultiple = newborns.length > 1;
  const cards: FormattedNewbornCard[] = [];

  newborns.forEach((baby: any, idx: number) => {
    const twinLabel = idx === 0 ? 'Twin A' : idx === 1 ? 'Twin B' : idx === 2 ? 'Twin C' : `Baby ${idx + 1}`;
    const title = isMultiple ? `Newborn #${idx + 1} - ${twinLabel}` : 'Newborn Details';
    const sex = baby.sex ? `Alive ${baby.sex.toUpperCase()} baby` : '—';

    const rawBornAt = baby.birth_at || admission.procedure_at;
    const bornTime = rawBornAt ? formatDateTime(rawBornAt, ' at ') : '—';

    const babyNotes = `${baby.notes || ''} ${baby.neonatal_care || ''}`;
    let mode = admission.procedure_done || admission.procedure_name || '—';
    if (/breech/i.test(babyNotes)) mode = 'Breech delivery with episiotomy';
    else if (/lscs|c-section/i.test(babyNotes)) mode = 'Emergency LSCS under SA';
    else if (/normal|natural/i.test(babyNotes)) mode = 'Labour natural with episiotomy';

    const weight = baby.birth_weight_kg ? `${Number(baby.birth_weight_kg).toFixed(2)} kg` : '—';
    const dischargeWeight = baby.discharge_weight_kg ? `${Number(baby.discharge_weight_kg).toFixed(2)} kg` : null;
    const apgar = (baby.apgar_1_min !== null && baby.apgar_1_min !== undefined) || (baby.apgar_5_min !== null && baby.apgar_5_min !== undefined)
      ? `${baby.apgar_1_min ?? '—'}/10, ${baby.apgar_5_min ?? '—'}/10`
      : '—';

    let neonatalCare = baby.neonatal_care || '—';
    const notes = String(baby.notes || '').trim();
    const birthVaccinesText = formatBirthVaccinesText(
      baby.birth_vaccines,
      baby.vaccine_dates,
      baby.vaccine_date
    );

    cards.push({
      title,
      sex,
      born: bornTime,
      mode,
      weight,
      discharge_weight: dischargeWeight,
      apgar,
      neonatal_care: neonatalCare,
      notes,
      birth_vaccines_text: birthVaccinesText,
    });
  });

  return cards;
}

export function formatSurgeryPerformed(admission: any): string {
  const done = (admission.procedure_done || '').trim();
  const name = (admission.procedure_name || '').trim();

  if (done) {
    return done;
  }
  if (name) {
    return name;
  }
  return '—';
}

/* =========================================================================
 * SURGICAL / NON-OBSTETRIC FORMATTERS (Mr. Gopalakrishnan Hospital Standard)
 * ========================================================================= */

export interface FormattedSurgicalPastHistory {
  dm: string;
  ht: string;
  asthma: string;
  tb: string;
  other: string;
  past_surgical: string;
  allergies: string;
  medications: string;
  pulse: string;
  bp: string;
}

export function formatSurgicalPastHistory(admission: any): FormattedSurgicalPastHistory {
  const text = `${admission.clinical_history || ''}\n${admission.examination || ''}`;
  const res: FormattedSurgicalPastHistory = {
    dm: 'Nil',
    ht: 'Nil',
    asthma: 'Nil',
    tb: 'Nil',
    other: 'Nil',
    past_surgical: 'Nil',
    allergies: 'Nil',
    medications: 'Nil',
    pulse: '—',
    bp: '—',
  };

  const dmM = text.match(/DM\s*[:\-]\s*([^\s\|]+)/i);
  if (dmM) res.dm = dmM[1].trim();
  const htM = text.match(/HT\s*[:\-]\s*([^\s\|]+)/i);
  if (htM) res.ht = htM[1].trim();
  const asthM = text.match(/Asthma\s*[:\-]\s*([^\s\|]+)/i);
  if (asthM) res.asthma = asthM[1].trim();
  const tbM = text.match(/(?:TB|Tuberculosis)\s*[:\-]\s*([^\s\|]+)/i);
  if (tbM) res.tb = tbM[1].trim();
  const othM = text.match(/Other\s*[:\-]\s*([^\s\|]+)/i);
  if (othM) res.other = othM[1].trim();
  const surgM = text.match(/Past\s*Surgical\s*History\s*[:\-]\s*([^\n\|]+)/i);
  if (surgM) res.past_surgical = surgM[1].trim();
  const allM = text.match(/Allergies\s*[:\-]\s*([^\n\|]+)/i);
  if (allM) res.allergies = allM[1].trim();
  const medM = text.match(/Medications\s*[:\-]\s*([^\n\|]+)/i);
  if (medM) res.medications = medM[1].trim();

  const pulseM = text.match(/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i);
  if (pulseM) res.pulse = `${pulseM[1]} b/min`;

  const bpM = text.match(/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg|mmhg)?)/i);
  if (bpM) {
    let bp = bpM[1].trim();
    if (!/mmhg/i.test(bp)) bp += 'mmhg';
    res.bp = bp;
  }

  return res;
}

export interface FormattedSurgicalInvestigations {
  group1: Record<string, string>;
  group2: Record<string, string>;
  imaging: Record<string, string>;
  units?: Record<string, string>;
  ranges?: Record<string, string>;
}

export function formatSurgicalInvestigations(admission: any): FormattedSurgicalInvestigations {
  const text = admission.investigations || '';
  const lines = text.split(/\r?\n/).map((l: string) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);

  const group1: Record<string, string> = {
    Hb: '—',
    Urea: '—',
    Creatinine: '—',
    'Uric Acid': '—',
    RBS: '—',
  };

  const group2: Record<string, string> = {
    'HBs Ag': '—',
    HIV: '—',
    HCV: '—',
    BG: '—',
    ECG: '—',
  };

  const units: Record<string, string> = {
    Hb: 'g/dL',
    Urea: 'mg/dL',
    Creatinine: 'mg/dL',
    'Uric Acid': 'mg/dL',
    RBS: 'mg/dL',
    'HBs Ag': '',
    HIV: '',
    HCV: '',
    BG: '',
    ECG: '',
  };

  const ranges: Record<string, string> = {
    Hb: '12.0-16.0',
    Urea: '15-40',
    Creatinine: '0.6-1.2',
    'Uric Acid': '3.5-7.2',
    RBS: '70-140',
    'HBs Ag': 'Non-React',
    HIV: 'Non-React',
    HCV: 'Non-React',
    BG: 'Compatible',
    ECG: 'Normal Sinus',
  };

  const imaging: Record<string, string> = {};

  for (const line of lines) {
    const m = line.match(/^([^:=]+)[:=](.+)$/);
    if (m) {
      const rawK = m[1].trim();
      const v = m[2].trim();
      const cleanK = rawK.toLowerCase().replace(/[^a-z0-9]/g, '');

      if (cleanK === 'hb' || cleanK === 'haemoglobin') group1['Hb'] = v;
      else if (cleanK === 'urea') group1['Urea'] = v;
      else if (cleanK === 'creatinine' || cleanK === 'creatine') group1['Creatinine'] = v;
      else if (cleanK === 'uricacid') group1['Uric Acid'] = v;
      else if (cleanK === 'rbs') group1['RBS'] = v;
      else if (cleanK === 'hbsag' || cleanK === 'hbs') group2['HBs Ag'] = v.replace(/negative/i, 'Neg');
      else if (cleanK === 'hiv') group2['HIV'] = v.replace(/negative/i, 'Neg');
      else if (cleanK === 'hcv') group2['HCV'] = v.replace(/negative/i, 'Neg');
      else if (cleanK === 'bg' || cleanK === 'bloodgroup') group2['BG'] = v.replace(/\s+/g, '');
      else if (cleanK === 'ecg' || cleanK === 'ekg' || cleanK === 'electrocardiogram') group2['ECG'] = v;
      else if (/usg|x-?ray|ct|mri|biopsy|histopath/i.test(rawK)) {
        imaging[rawK.toUpperCase()] = v;
      }
    }
  }

  return { group1, group2, imaging, units, ranges };
}

export interface FormattedSurgicalAdvice {
  implant: string;
  diet: string;
  exercise: string;
  emergency_contact: string;
  when_to_contact: string;
  review: string;
  tamil_note: string;
}

export function formatSurgicalAdvice(admission: any): FormattedSurgicalAdvice {
  const text = `${admission.discharge_advice || ''}\n${admission.follow_up || ''}`;
  const fullContext = `${text} ${admission.procedure_name || ''} ${admission.procedure_done || ''} ${admission.procedure_note || ''} ${admission.findings || ''} ${admission.drain || ''}`;
  const hasStent = /\bstent\b/i.test(fullContext);

  const res: FormattedSurgicalAdvice = {
    implant: hasStent ? 'DJ Stent' : 'Nil',
    diet: '—',
    exercise: '—',
    emergency_contact: 'Reception: 04342-262266, 7418084266',
    when_to_contact: 'Report immediately if bleeding, fever, or severe pain',
    review: '',
    tamil_note: '',
  };

  const impM = text.match(/Implant\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (impM) res.implant = impM[1].trim();
  const dietM = text.match(/Diet\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (dietM) res.diet = dietM[1].trim();
  const exM = text.match(/Exercise\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (exM) res.exercise = exM[1].trim();
  const ecM = text.match(/Emergency\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (ecM) res.emergency_contact = ecM[1].trim();
  const wtcM = text.match(/When\s*to\s*Contact\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (wtcM) res.when_to_contact = wtcM[1].trim();
  const revM = text.match(/Review\s*[:\-]\s*(.*?)(?=\n|$)/i);
  if (revM) res.review = revM[1].trim();

  const tamilM = text.match(/([\u0B80-\u0BFF].*)/);
  if (tamilM) {
    res.tamil_note = tamilM[1].trim();
  } else if (hasStent) {
    const dm = (res.review || admission.follow_up || '').match(/(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4})/);
    const d = dm ? dm[1].replace(/[\/\-]/g, '.') : '(Date)';
    res.tamil_note = `வயிற்றுக்குள் வைக்கப்பட்டிருக்கும் ஸ்டெண்ட் என்ற டியூபை எடுப்பதற்கு ${d} அன்று வர வேண்டும். ஒரு நாள் முன்னர் மருத்துவமனையை தொடர்பு கொண்டு நேரம் அறிந்து கொள்ள வேண்டும்.`;
  }

  return res;
}

export interface FormattedSurgicalDrug {
  drug: string;
  dosage: string;
  no: string;
}

export function formatSurgicalDrugs(text?: string | null): FormattedSurgicalDrug[] {
  if (!text || !text.trim()) {
    return [];
  }

  const lines = text.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);
  const rows: FormattedSurgicalDrug[] = [];

  for (let line of lines) {
    const revM = line.match(/review\s+after\s*([^\(\)]+)(?:\((.*?)\))?/i);
    if (revM) {
      rows.push({
        drug: `Review after ${revM[1].trim()}`,
        dosage: revM[2]?.trim() || '—',
        no: '',
      });
      continue;
    }

    let no = '—';
    const nm = line.match(/(\d+)\s*(?:nos|no|tablets|tabs|caps)\b/i);
    if (nm) {
      no = nm[1];
      line = line.replace(nm[0], '');
    }

    let dosage = '—';
    const dm = line.match(/\(([\d\-]+)\)/) || line.match(/\b(\d-\d-\d)\b/);
    if (dm) {
      dosage = dm[1];
      line = line.replace(dm[0], '');
    }

    line = line.replace(/[-–]?\s*(After\s+food|Before\s+food|With\s+food)/i, '');
    const drug = line.replace(/[-–x\s]+$/, '').replace(/\s+/g, ' ').trim();

    rows.push({ drug, dosage, no });
  }

  return rows;
}

export interface InsuranceDetailItem {
  label: string;
  value: string;
}

export function isInsuranceAdmission(admission: any): boolean {
  return String(admission?.payment_mode || '').trim().toLowerCase() === 'insurance';
}

export function getInsuranceDetailsList(admission: any): InsuranceDetailItem[] {
  if (!admission) return [];
  if (!isInsuranceAdmission(admission) && !admission.insurance_company && !admission.policy_number) {
    return [];
  }

  const items: InsuranceDetailItem[] = [];
  if (admission.insurance_type) {
    items.push({ label: 'Scheme / Category', value: admission.insurance_type });
  }
  if (admission.insurance_company) {
    items.push({ label: 'Insurer / TPA', value: admission.insurance_company });
  }
  if (admission.policy_number) {
    items.push({ label: 'Policy / URN No.', value: admission.policy_number });
  }
  if (admission.pre_auth_no) {
    items.push({ label: 'Pre-Auth / Claim No.', value: admission.pre_auth_no });
  }
  if (admission.pre_auth_status) {
    items.push({ label: 'Pre-Auth Status', value: admission.pre_auth_status });
  }
  if (admission.approved_amount !== null && admission.approved_amount !== undefined && admission.approved_amount !== '') {
    const amt = Number(admission.approved_amount);
    items.push({
      label: 'Approved Amount',
      value: Number.isFinite(amt)
        ? `Rs. ${amt.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
        : String(admission.approved_amount),
    });
  }
  if (admission.policy_holder_name) {
    const holder = admission.policy_holder_relation
      ? `${admission.policy_holder_name} (${admission.policy_holder_relation})`
      : admission.policy_holder_name;
    items.push({ label: 'Policy Holder', value: holder });
  }
  if (admission.policy_validity) {
    items.push({ label: 'Valid Till', value: formatDate(admission.policy_validity) });
  }

  return items;
}

export function formatPaymentSummary(admission: any): string {
  if (!admission?.payment_mode && !admission?.insurance_company && !admission?.policy_number) {
    return '—';
  }
  if (!isInsuranceAdmission(admission) && !admission?.insurance_company && !admission?.policy_number) {
    return admission?.payment_mode || '—';
  }

  const parts = [
    admission.insurance_type || null,
    admission.insurance_company || null,
    admission.policy_number ? `Policy/URN: ${admission.policy_number}` : null,
    admission.pre_auth_no ? `Pre-Auth: ${admission.pre_auth_no}` : null,
    admission.pre_auth_status ? `Status: ${admission.pre_auth_status}` : null,
  ].filter(Boolean);

  return parts.length > 0 ? `Insurance — ${parts.join(' | ')}` : 'Insurance';
}

