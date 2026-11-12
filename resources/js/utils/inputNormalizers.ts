import React from 'react';

/**
 * Convert text input value to uppercase for stored CAPS fields.
 */
export function toUpperCaseValue(value: string): string {
  return value.toUpperCase();
}

/**
 * Strip all non-numeric characters for digits-only phone / contact fields.
 */
export function digitsOnlyValue(value: string): string {
  return value.replace(/\D+/g, '');
}

/**
 * Normalize a controlled React input value while preserving cursor position when characters are transformed or stripped.
 */
export function handleNormalizedInputChange(
  e: React.ChangeEvent<HTMLInputElement>,
  transform: (value: string) => string,
  apply: (nextValue: string) => void
): void {
  const input = e.target;
  const start = input.selectionStart;
  const end = input.selectionEnd;
  const raw = input.value;
  const next = transform(raw);

  apply(next);

  if (start !== null && end !== null && typeof window !== 'undefined') {
    const diff = raw.length - next.length;
    const nextStart = Math.max(0, start - diff);
    const nextEnd = Math.max(0, end - diff);
    window.requestAnimationFrame(() => {
      if (document.activeElement === input) {
        try {
          input.setSelectionRange(nextStart, nextEnd);
        } catch {
          // Ignore selection errors on unsupported input types
        }
      }
    });
  }
}

/**
 * Format default MRD file number from an IP No (`MRD-{IP No}`).
 */
export function defaultMrdFromIp(ipNo?: string | null): string {
  const trimmed = (ipNo || '').trim();
  if (!trimmed) {
    return '';
  }

  return trimmed.startsWith('MRD-') ? trimmed : `MRD-${trimmed}`;
}

/**
 * Resolve an explicit MRD number or fall back to `MRD-{IP No}`.
 */
export function resolveMrdNo(mrdNo?: string | null, ipNo?: string | null): string {
  const explicit = (mrdNo || '').trim();
  return explicit || defaultMrdFromIp(ipNo);
}

export const PATIENT_NAME_PREFIXES = [
  { value: 'MR.', label: 'Mr.' },
  { value: 'MRS.', label: 'Mrs.' },
  { value: 'MISS', label: 'Miss' },
  { value: 'MS.', label: 'Ms.' },
  { value: 'MASTER', label: 'Master' },
  { value: 'BABY OF', label: 'Baby of' },
  { value: 'BABY', label: 'Baby' },
  { value: 'DR.', label: 'Dr.' },
] as const;

const PREFIX_CANONICAL_MAP: Array<{ pattern: RegExp; canonical: string }> = [
  { pattern: /^BABY\s+OF(?:\.|\s+)(.*)$/i, canonical: 'BABY OF' },
  { pattern: /^MRS(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'MRS.' },
  { pattern: /^MR(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'MR.' },
  { pattern: /^MISS(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'MISS' },
  { pattern: /^MS(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'MS.' },
  { pattern: /^MASTER(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'MASTER' },
  { pattern: /^BABY(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'BABY' },
  { pattern: /^DR(?:\.(?=\s*\S)|\s+)(.*)$/i, canonical: 'DR.' },
];

/**
 * Split a full patient name into a known salutation prefix and the remaining name body.
 */
export function splitPatientNamePrefix(fullName?: string | null): { prefix: string; name: string } {
  const trimmed = (fullName || '').trimStart();
  if (!trimmed) {
    return { prefix: '', name: '' };
  }

  for (const { pattern, canonical } of PREFIX_CANONICAL_MAP) {
    const match = trimmed.match(pattern);
    if (match) {
      return {
        prefix: canonical,
        name: match[1].trimStart(),
      };
    }
  }

  return { prefix: '', name: trimmed };
}

/**
 * Combine a patient salutation prefix and name body into a single uppercase-ready string.
 */
export function combinePatientNameWithPrefix(prefix: string, name: string): string {
  const cleanName = name.trimStart();
  if (!cleanName.trim()) {
    return '';
  }

  const cleanPrefix = prefix.trim();
  return cleanPrefix ? `${cleanPrefix} ${cleanName}` : cleanName;
}


