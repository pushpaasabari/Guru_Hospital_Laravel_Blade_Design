export type ExamPresetGroupId = 'general' | 'systemic' | 'abdomen' | 'obstetric';

export interface ExamPresetItem {
  id: string;
  group: ExamPresetGroupId;
  label: string;
  text: string;
}

export interface ExamPresetGroup {
  id: ExamPresetGroupId;
  title: string;
  icon: string;
  items: ExamPresetItem[];
}

export const CLINICAL_EXAM_PRESET_GROUPS: ExamPresetGroup[] = [
  {
    id: 'general',
    title: 'General Physical (O/E)',
    icon: 'bi-person-check',
    items: [
      { id: 'conscious', group: 'general', label: 'Conscious & oriented', text: 'Conscious & oriented' },
      { id: 'afebrile', group: 'general', label: 'Afebrile', text: 'Afebrile' },
      { id: 'vitals_stable', group: 'general', label: 'Vitals stable', text: 'Vitals stable' },
      { id: 'no_pallor', group: 'general', label: 'No pallor', text: 'No pallor' },
      { id: 'no_icterus', group: 'general', label: 'No icterus', text: 'No icterus' },
      { id: 'no_cyanosis', group: 'general', label: 'No cyanosis', text: 'No cyanosis' },
      { id: 'no_lymphadenopathy', group: 'general', label: 'No lymphadenopathy', text: 'No lymphadenopathy' },
      { id: 'no_pedal_edema', group: 'general', label: 'No pedal edema', text: 'No pedal edema' },
    ],
  },
  {
    id: 'systemic',
    title: 'Systemic (CVS / RS / CNS)',
    icon: 'bi-heart-pulse',
    items: [
      { id: 'cvs_s1s2', group: 'systemic', label: 'CVS: S1 S2 heard (no murmur)', text: 'CVS: S1 S2 heard (no murmur)' },
      { id: 'cvs_rs_nad', group: 'systemic', label: 'CVS & RS: NAD', text: 'CVS & RS: NAD' },
      { id: 'rs_clear', group: 'systemic', label: 'RS: Clear (B/L air entry equal)', text: 'RS: Clear (bilateral air entry equal)' },
      { id: 'cns_intact', group: 'systemic', label: 'CNS: No focal deficit', text: 'CNS: No focal neurological deficit' },
    ],
  },
  {
    id: 'abdomen',
    title: 'Per Abdomen & Local Exam',
    icon: 'bi-clipboard2-pulse',
    items: [
      { id: 'pa_soft', group: 'abdomen', label: 'P/A: Soft & non-tender', text: 'P/A: Soft & non-tender' },
      { id: 'no_organomegaly', group: 'abdomen', label: 'No organomegaly', text: 'No organomegaly' },
      { id: 'no_free_fluid', group: 'abdomen', label: 'No free fluid', text: 'No free fluid' },
      { id: 'bowel_sounds', group: 'abdomen', label: 'Bowel sounds present', text: 'Bowel sounds present' },
      { id: 'renal_angle', group: 'abdomen', label: 'Renal angle tenderness (+)', text: 'Renal angle tenderness (+)' },
      { id: 'local_normal', group: 'abdomen', label: 'Local examination normal', text: 'Local examination normal' },
    ],
  },
  {
    id: 'obstetric',
    title: 'Obstetric / Antenatal Exam',
    icon: 'bi-hospital',
    items: [
      { id: 'uterus_term_relaxed', group: 'obstetric', label: 'Uterus term size & relaxed', text: 'P/A: Uterus term size & relaxed' },
      { id: 'uterus_acting', group: 'obstetric', label: 'Uterus acting', text: 'Uterus acting' },
      { id: 'cephalic', group: 'obstetric', label: 'Cephalic presentation', text: 'Cephalic presentation' },
      { id: 'fhs_good', group: 'obstetric', label: 'FHS good & regular', text: 'FHS good & regular' },
      { id: 'spt_scar', group: 'obstetric', label: 'SPT scar (+)', text: 'SPT scar (+)' },
      { id: 'membranes_intact', group: 'obstetric', label: 'Membranes intact', text: 'Membranes intact' },
      { id: 'no_bleeding_pv', group: 'obstetric', label: 'No bleeding / leaking P/V', text: 'No bleeding / leaking P/V' },
    ],
  },
];

function escapeRegExp(str: string): string {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export function isExamPresetSelected(currentText: string, preset: ExamPresetItem): boolean {
  if (!currentText) return false;
  return currentText.toLowerCase().includes(preset.text.toLowerCase());
}

function appendToLineGroup(
  lines: string[],
  matcher: (line: string) => boolean,
  addition: string,
  separator: string,
  newLineValue: string
): string[] {
  const idx = lines.findIndex((line) => matcher(line.trim()));
  if (idx >= 0) {
    const updated = [...lines];
    const existing = updated[idx].trim();
    const needsSep = !/[,\|;:]$/.test(existing) && !/O\/E\s*[:–-]?\s*$/i.test(existing);
    updated[idx] = needsSep ? `${existing}${separator}${addition}` : `${existing} ${addition}`;
    return updated;
  }
  return [...lines, newLineValue];
}

export function toggleExamPreset(currentText: string, preset: ExamPresetItem): string {
  const normalizedInput = currentText || '';
  const selected = isExamPresetSelected(normalizedInput, preset);
  const lines = normalizedInput
    .split(/\r?\n/)
    .map((l) => l.trimEnd())
    .filter((l) => l.trim().length > 0);

  if (selected) {
    const escaped = escapeRegExp(preset.text);
    const cleanedLines = lines
      .map((line) => {
        let next = line;
        next = next.replace(new RegExp(`,\\s*${escaped}(?=\\s*,)`, 'gi'), '');
        next = next.replace(new RegExp(`\\s*\\|\\s*${escaped}(?=\\s*\\|)`, 'gi'), '');
        next = next.replace(new RegExp(`(?:,\\s*|\\s*\\|\\s*|;\\s*)${escaped}`, 'gi'), '');
        next = next.replace(new RegExp(`${escaped}(?:\\s*,\\s*|\\s*\\|\\s*|\\s*;\\s*)`, 'gi'), '');
        next = next.replace(new RegExp(escaped, 'gi'), '');
        next = next.replace(/^\s*(O\/E\s*[:–-]?)\s*,\s*/i, '$1 ').trim();
        next = next.replace(/^\s*(?:\||,|;)\s*/, '').replace(/\s*(?:\||,|;)\s*$/, '').trim();
        if (/^O\/E\s*[:–-]?\s*$/i.test(next)) {
          return '';
        }
        return next;
      })
      .filter((line) => line.length > 0);

    return cleanedLines.join('\n');
  }

  let nextLines: string[];

  if (preset.group === 'general') {
    const lowerPhrase = preset.text.charAt(0).toLowerCase() + preset.text.slice(1);
    const hasOeLine = lines.some((l) => /^O\/E\b/i.test(l.trim()));
    nextLines = hasOeLine
      ? appendToLineGroup(lines, (l) => /^O\/E\b/i.test(l), lowerPhrase, ', ', `O/E: ${preset.text}`)
      : [`O/E: ${preset.text}`, ...lines];
  } else if (preset.group === 'systemic') {
    nextLines = appendToLineGroup(
      lines,
      (l) => /\b(CVS|RS|CNS)\b\s*(?:&|:|–|-)/i.test(l),
      preset.text,
      ' | ',
      preset.text
    );
  } else if (preset.group === 'abdomen') {
    nextLines = appendToLineGroup(
      lines,
      (l) => /(?:P\/A\s*:|organomegaly|free fluid|bowel sounds|renal angle|local examination)/i.test(l) && !/uterus|fhs/i.test(l),
      preset.text,
      ' | ',
      preset.text
    );
  } else {
    nextLines = appendToLineGroup(
      lines,
      (l) => /(?:uterus|cephalic|fhs|spt scar|membranes|bleeding\s*\/?\s*leaking|p\/v)/i.test(l),
      preset.text,
      ' | ',
      preset.text
    );
  }

  return nextLines.join('\n');
}

export function applyNormalExamBundle(currentText: string, isDelivery: boolean): string {
  const defaultIds = isDelivery
    ? ['conscious', 'afebrile', 'vitals_stable', 'no_pallor', 'no_pedal_edema', 'cvs_rs_nad', 'uterus_term_relaxed', 'cephalic', 'fhs_good']
    : ['conscious', 'afebrile', 'vitals_stable', 'no_pallor', 'no_pedal_edema', 'cvs_s1s2', 'rs_clear', 'pa_soft', 'bowel_sounds'];

  const allItems = CLINICAL_EXAM_PRESET_GROUPS.flatMap((g) => g.items);
  let result = currentText || '';

  for (const id of defaultIds) {
    const preset = allItems.find((item) => item.id === id);
    if (preset && !isExamPresetSelected(result, preset)) {
      result = toggleExamPreset(result, preset);
    }
  }

  return result;
}
