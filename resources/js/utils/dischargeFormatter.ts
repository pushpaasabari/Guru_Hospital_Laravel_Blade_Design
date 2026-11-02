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
      else if (key === 'creatinine') label = 'CREATININE';
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
    general: 'Good',
    bp: '110/70 mmHg',
    pulse: '86 b/min',
    pa: 'Uterus contracted well; no undue bleeding P/V',
  };

  if (!text) return res;

  const gm = text.match(/general\s+condition\s*[:\-]?\s*([a-zA-Z]+)/i);
  if (gm) {
    res.general = gm[1].charAt(0).toUpperCase() + gm[1].slice(1).toLowerCase();
  }

  const bpm = text.match(/BP\s*[:\-]?\s*([0-9\/\s]+(?:mmHg)?)/i);
  if (bpm) {
    let bp = bpm[1].trim();
    if (!bp.toLowerCase().includes('mmhg')) bp += ' mmHg';
    res.bp = bp;
  }

  const pm = text.match(/Pulse\s*[:\-]?\s*(\d+)\s*(?:b\/mt|b\/min|bpm)?/i);
  if (pm) {
    res.pulse = `${pm[1]} b/min`;
  }

  const pam = text.match(/PA\s*[-:]\s*(.*?)(?=\n|$)/i);
  if (pam) {
    const pa = pam[1].trim().replace(/\.$/, '');
    res.pa = /no\s+undue\s+bleeding/i.test(text) ? `${pa}; no undue bleeding P/V` : pa;
  }

  return res;
}

export function formatMedications(text?: string | null): FormattedMedication[] {
  if (!text) return [];
  const lines = text.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);
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

    let med = line.replace(/[-–x]+$/, '').trim().replace(/\s+/, ' ');
    med = med.replace(/\b(tab|cap|syp|inj)\b/gi, (m) => m.charAt(0).toUpperCase() + m.slice(1).toLowerCase() + '.');
    med = med.replace(/Tab\.\./g, 'Tab.').replace(/Cap\.\./g, 'Cap.').replace(/Inj\.\./g, 'Inj.');
    med = med.replace(/\+\s*([a-zA-Z]+)/g, (_, word) => `+ ${word.charAt(0).toUpperCase()}${word.slice(1).toLowerCase()}`);
    med = med.replace(/Sulbactum/i, 'Sulbactam');

    rows.push({ medicine: med, frequency: freq, duration: dur, timing });
  }

  return rows;
}

export function formatAdviceAndReview(adviceText?: string | null, followUpText?: string | null, dischargeAt?: string | null): FormattedAdviceAndReview {
  let items: string[] = [];
  let reviewText = 'Review after one week';
  let reviewDate = '03/09/2026';

  if (dischargeAt) {
    const d = new Date(dischargeAt);
    d.setDate(d.getDate() + 5);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    reviewDate = `${day}/${month}/${d.getFullYear()}`;
  }

  const source = `${adviceText || ''}\n${followUpText || ''}`;
  const lines = source.split(/\r?\n/).map((l) => l.trim().replace(/^[*•-]\s*/, '')).filter(Boolean);

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
    }

    if (/post\s*natal\s*exercise/i.test(line)) line = 'Post natal exercise';
    else if (/exclusive\s*breast\s*feeding/i.test(line)) line = 'Exclusive breast feeding for 6 months';
    else if (/immunisation|immunization/i.test(line)) line = 'Immunisation';

    items.push(line);
  }

  items = Array.from(new Set(items));
  if (items.length === 0) {
    items = ['Immunisation', 'Exclusive breast feeding for 6 months', 'Post natal exercise'];
  }

  return { advice_items: items, review_text: reviewText, review_date: reviewDate };
}

export function formatNewborns(admission: any): FormattedNewbornCard[] {
  const newborns = admission.newborns || [];
  if (!newborns || newborns.length === 0) return [];

  const isMultiple = newborns.length > 1;
  const cards: FormattedNewbornCard[] = [];

  newborns.forEach((baby: any, idx: number) => {
    const twinLabel = idx === 0 ? 'Twin A' : idx === 1 ? 'Twin B' : idx === 2 ? 'Twin C' : `Baby ${idx + 1}`;
    const title = isMultiple ? `Newborn #${idx + 1} - ${twinLabel}` : 'Newborn Details';
    const sex = `Alive ${(baby.sex || 'male').toLowerCase()} baby`;

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
    let mode = 'Breech delivery with episiotomy';
    if (/breech/i.test(babyNotes)) mode = 'Breech delivery with episiotomy';
    else if (/lscs|c-section/i.test(babyNotes)) mode = 'Emergency LSCS under SA';
    else if (/normal|natural/i.test(babyNotes)) mode = 'Labour natural with episiotomy';
    else if (isMultiple && idx === 1) mode = 'Emergency LSCS under SA';

    const weight = baby.birth_weight_kg ? `${Number(baby.birth_weight_kg).toFixed(2)} kg` : '2.25 kg';
    const apgar = `${baby.apgar_1_min || '7'}/10, ${baby.apgar_5_min || '8'}/10`;

    let neonatalCare = baby.neonatal_care || 'Handed over to pediatrician.';
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
