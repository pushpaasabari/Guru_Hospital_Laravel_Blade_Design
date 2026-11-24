/**
 * Discharge Intimation WhatsApp/SMS Message Generator
 * Format strictly matches the official Guru Hospital TN VHN/PHC discharge intimation structure:
 *
 * *DISCHARGE INTIMATION*
 *
 * *Mrs. Name*, Age | Gender
 * W/O Name
 * Address 1
 * Address 2
 * *Ph.no:* 9999999999
 * *RCH ID:* 123123123123
 * *VHN Name:* Name
 * *PHC:* Village Name
 *
 * *Diagnosis / Indication:*
 * *P1L1A1* | *IUGR with Oligohydramnios* | *GDM* | *Mobile head at term* | *Emergency LSCS*
 *
 * *Patient discharged with vitals stable.*
 *
 * Kindly *VHN follow up* with her.
 *
 * Thank you.
 */

export interface IntimationData {
  patient_name: string;
  age?: string | number | null;
  sex?: string | null;
  attender_name?: string | null;
  attender_relation?: string | null;
  address_line1?: string | null;
  address_line2?: string | null;
  address_line3?: string | null;
  address?: string | null;
  contact?: string | null;
  picme_no?: string | null;
  vhn_name?: string | null;
  phc?: string | null;
  diagnosis?: string | null;
  findings?: string | null;
  procedure_done?: string | null;
  procedure_name?: string | null;
  discharge_condition?: string | null;
  include_link?: boolean;
  link_url?: string | null;
}

export function formatAttenderRelationLine(
  attenderName?: string | null,
  relation?: string | null,
  sex?: string | null
): string | null {
  if (!attenderName || !attenderName.trim()) return null;
  const name = attenderName.trim();
  const rel = (relation || 'husband').toLowerCase().trim();
  const isFemale = (sex || '').toLowerCase().startsWith('f');

  if (rel.includes('husband')) {
    return `W/O ${name}`;
  }
  if (rel.includes('father')) {
    return isFemale ? `D/O ${name}` : `S/O ${name}`;
  }
  if (rel.includes('wife')) {
    return `H/O ${name}`;
  }
  if (rel.includes('mother')) {
    return isFemale ? `D/O ${name}` : `S/O ${name}`;
  }
  if (rel.includes('care') || rel.includes('c/o') || rel.includes('guardian')) {
    return `C/O ${name}`;
  }
  return `C/O ${name} (${relation})`;
}

export function formatPatientHeader(
  name?: string | null,
  age?: string | number | null,
  sex?: string | null
): string {
  if (!name || !name.trim()) return '*Patient*';
  let formattedName = name.trim();
  const isFemale = (sex || '').toLowerCase().startsWith('f');

  // Check if name already begins with a title
  const hasTitle = /^(mr\.|mrs\.|ms\.|dr\.|master|smt\.|baby)\b/i.test(formattedName);
  if (!hasTitle) {
    formattedName = isFemale ? `Mrs. ${formattedName}` : `Mr. ${formattedName}`;
  }

  const parts: string[] = [];
  if (age && String(age).trim()) {
    parts.push(`${String(age).trim()} Yrs`);
  }
  if (sex && sex.trim()) {
    parts.push(sex.trim());
  }

  const suffix = parts.length > 0 ? `, ${parts.join(' | ')}` : '';
  return `*${formattedName}*${suffix}`;
}

export function formatDiagnosisAndIndications(
  diagnosis?: string | null,
  procedureDone?: string | null,
  findings?: string | null
): string[] {
  const items: string[] = [];

  // Parse diagnosis items (split by comma, semicolon, pipe, or newline)
  if (diagnosis && diagnosis.trim()) {
    const rawParts = diagnosis.split(/[,;\n|]+/);
    for (const p of rawParts) {
      const trimmed = p.trim().replace(/^[*_\s]+|[*_\s]+$/g, '');
      if (trimmed && !items.some((i) => i.toLowerCase() === trimmed.toLowerCase())) {
        items.push(trimmed);
      }
    }
  }

  // Findings if distinct
  if (findings && findings.trim()) {
    const rawFindings = findings.split(/[,;\n|]+/);
    for (const f of rawFindings) {
      const trimmed = f.trim().replace(/^[*_\s]+|[*_\s]+$/g, '');
      if (trimmed && trimmed.length < 50 && !items.some((i) => i.toLowerCase() === trimmed.toLowerCase())) {
        items.push(trimmed);
      }
    }
  }

  // Procedure done if distinct
  const proc = (procedureDone || '').trim().replace(/^[*_\s]+|[*_\s]+$/g, '');
  if (proc && !items.some((i) => i.toLowerCase() === proc.toLowerCase())) {
    items.push(proc);
  }

  return items;
}

export function buildDischargeIntimationMessage(data: IntimationData): string {
  const lines: string[] = [];

  // 1. Header
  lines.push('*DISCHARGE INTIMATION*');
  lines.push('');

  // 2. Patient Name, Age | Gender
  lines.push(formatPatientHeader(data.patient_name, data.age, data.sex));

  // 3. W/O Attender Name
  const attenderLine = formatAttenderRelationLine(data.attender_name, data.attender_relation, data.sex);
  if (attenderLine) {
    lines.push(attenderLine);
  }

  // 4. Address Lines (omit any empty lines)
  if (data.address_line1 && data.address_line1.trim()) {
    lines.push(data.address_line1.trim());
  } else if (data.address && data.address.trim()) {
    lines.push(data.address.trim());
  }

  if (data.address_line2 && data.address_line2.trim()) {
    lines.push(data.address_line2.trim());
  }

  if (data.address_line3 && data.address_line3.trim()) {
    lines.push(data.address_line3.trim());
  }

  // 5. Phone Number
  if (data.contact && data.contact.trim()) {
    lines.push(`*Ph.no:* ${data.contact.trim()}`);
  }

  // 6. RCH ID
  if (data.picme_no && data.picme_no.trim()) {
    lines.push(`*RCH ID:* ${data.picme_no.trim()}`);
  }

  // 7. VHN Name
  if (data.vhn_name && data.vhn_name.trim()) {
    lines.push(`*VHN Name:* ${data.vhn_name.trim()}`);
  }

  // 8. PHC
  if (data.phc && data.phc.trim()) {
    lines.push(`*PHC:* ${data.phc.trim()}`);
  }

  lines.push('');

  // 9. Diagnosis / Indication
  lines.push('*Diagnosis / Indication:*');
  const diagItems = formatDiagnosisAndIndications(data.diagnosis, data.procedure_done || data.procedure_name, data.findings);
  if (diagItems.length > 0) {
    lines.push(diagItems.map((item) => `*${item}*`).join(' | '));
  } else {
    lines.push('*Clinical Case Evaluation*');
  }

  lines.push('');

  // 10. Discharge condition
  lines.push('*Patient discharged with vitals stable.*');
  lines.push('');

  // 11. Follow up instruction
  const pronoun = (data.sex || '').toLowerCase().startsWith('m') ? 'him' : 'her';
  lines.push(`Kindly *VHN follow up* with ${pronoun}.`);
  lines.push('');

  // 12. Thank you
  lines.push('Thank you.');

  // 13. Optional Digital Link
  if (data.include_link && data.link_url) {
    lines.push('');
    lines.push(`*Discharge Document:* ${data.link_url}`);
  }

  return lines.join('\n');
}
