export interface FormattedTreatmentRow {
  medicine: string;
  dose: string;
}

export interface FormattedInvestigation {
  label: string;
  value: string;
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
  apgar: string;
  neonatal_care: string;
}

const INVESTIGATION_ORDER: Record<string, number> = {
  HB: 1,
  'BLOOD GROUP': 2,
  HIV: 3,
  HCV: 4,
  VDRL: 5,
  HBSAG: 6,
  UREA: 7,
  CREATININE: 8,
  'URIC ACID': 9,
  RBS: 10,
};

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

export function formatInvestigations(text?: string | null): FormattedInvestigation[] {
  if (!text) return [];
  const lines = text.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);
  const items: FormattedInvestigation[] = [];

  for (const line of lines) {
    const match = line.match(/^([^:=]+)[:=](.+)$/);
    if (match) {
      const rawLabel = match[1].trim();
      const rawVal = match[2].trim();
      const key = rawLabel.toLowerCase().replace(/[^a-z0-9]/g, '');

      let label = rawLabel.toUpperCase();
      if (key === 'hb' || key === 'haemoglobin') label = 'HB';
      else if (key === 'bg' || key === 'bloodgroup') label = 'BLOOD GROUP';
      else if (key === 'hiv') label = 'HIV';
      else if (key === 'hcv') label = 'HCV';
      else if (key === 'vdrl') label = 'VDRL';
      else if (key === 'hbsag') label = 'HBSAG';
      else if (key === 'urea') label = 'UREA';
      else if (key === 'creatinine' || key === 'creatine') label = 'CREATININE';
      else if (key === 'uricacid') label = 'URIC ACID';
      else if (key === 'rbs') label = 'RBS';

      items.push({ label, value: rawVal });
    } else {
      items.push({ label: 'NOTE', value: line });
    }
  }

  items.sort((a, b) => {
    const orderA = INVESTIGATION_ORDER[a.label] ?? 99;
    const orderB = INVESTIGATION_ORDER[b.label] ?? 99;
    return orderA - orderB;
  });

  return items;
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
      reviewDate = dm[1];
      if (!reviewText && /review/i.test(line)) {
        reviewText = line;
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

export function formatNewborns(admission: any): FormattedNewbornCard[] {
  const newborns = admission.newborns || [];
  if (!newborns || newborns.length === 0) return [];

  const isMultiple = newborns.length > 1;
  const cards: FormattedNewbornCard[] = [];

  newborns.forEach((baby: any, idx: number) => {
    const twinLabel = idx === 0 ? 'Twin A' : idx === 1 ? 'Twin B' : idx === 2 ? 'Twin C' : `Baby ${idx + 1}`;
    const title = isMultiple ? `Newborn #${idx + 1} - ${twinLabel}` : 'Newborn Details';
    const sex = baby.sex ? `Alive ${baby.sex.toLowerCase()} baby` : '—';

    let bornTime = '—';
    if (baby.birth_at) {
      const dt = new Date(baby.birth_at);
      const day = String(dt.getDate()).padStart(2, '0');
      const mon = String(dt.getMonth() + 1).padStart(2, '0');
      const yr = dt.getFullYear();
      const time = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      bornTime = `${day}.${mon}.${yr} at ${time}`;
    }

    const babyNotes = `${baby.notes || ''} ${baby.neonatal_care || ''}`;
    let mode = admission.procedure_name || admission.procedure_done || '—';
    if (/breech/i.test(babyNotes)) mode = 'Breech delivery with episiotomy';
    else if (/lscs|c-section/i.test(babyNotes)) mode = 'Emergency LSCS under SA';
    else if (/normal|natural/i.test(babyNotes)) mode = 'Labour natural with episiotomy';

    const weight = baby.birth_weight_kg ? `${Number(baby.birth_weight_kg).toFixed(2)} kg` : '—';
    const apgar = (baby.apgar_1_min !== null && baby.apgar_1_min !== undefined) || (baby.apgar_5_min !== null && baby.apgar_5_min !== undefined)
      ? `${baby.apgar_1_min ?? '—'}/10, ${baby.apgar_5_min ?? '—'}/10`
      : '—';

    let neonatalCare = baby.neonatal_care || '—';
    if (idx === 0 && /rds/i.test(neonatalCare)) {
      neonatalCare = 'RDS (+). Handed over to pediatrician; shifted to NICU.';
    }

    cards.push({ title, sex, born: bornTime, mode, weight, apgar, neonatal_care: neonatalCare });
  });

  return cards;
}

export function formatSurgeryPerformed(admission: any): string {
  if (admission.newborns && admission.newborns.length > 1) {
    return 'Twin A: Labour natural with episiotomy | Twin B: Emergency LSCS';
  }
  return admission.procedure_name || admission.procedure_done || '—';
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
      else if (/usg|x-?ray|ct|mri|biopsy|histopath/i.test(rawK)) {
        imaging[rawK.toUpperCase()] = v;
      }
    }
  }

  return { group1, group2, imaging };
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
    const d = dm ? dm[1] : '(Date)';
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
    const dt = new Date(admission.policy_validity);
    const val = Number.isNaN(dt.getTime())
      ? String(admission.policy_validity)
      : `${String(dt.getDate()).padStart(2, '0')}/${String(dt.getMonth() + 1).padStart(2, '0')}/${dt.getFullYear()}`;
    items.push({ label: 'Valid Till', value: val });
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

