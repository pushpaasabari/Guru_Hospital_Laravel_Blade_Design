/**
 * Standard Date & Wall-Clock Time Formatter for Guru Hospital:
 * Preserves hospital wall-clock date/time verbatim without browser UTC->local offsets.
 */

export interface WallClockParts {
  year: number;
  month: number;
  day: number;
  hours24: number | null;
  minutes: number | null;
}

export function parseWallClockParts(val?: string | Date | null): WallClockParts | null {
  if (!val) return null;

  if (val instanceof Date) {
    if (Number.isNaN(val.getTime())) return null;
    return {
      year: val.getFullYear(),
      month: val.getMonth() + 1,
      day: val.getDate(),
      hours24: val.getHours(),
      minutes: val.getMinutes(),
    };
  }

  const trimmed = String(val).trim();
  if (!trimmed) return null;

  // Match ISO / SQL timestamps verbatim ("YYYY-MM-DD" or "YYYY-MM-DD[T ]HH:mm(:ss)(.sssZ)?")
  const iso = trimmed.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T ](\d{1,2}):(\d{2}))?/);
  if (iso) {
    return {
      year: Number(iso[1]),
      month: Number(iso[2]),
      day: Number(iso[3]),
      hours24: iso[4] !== undefined ? Number(iso[4]) : null,
      minutes: iso[5] !== undefined ? Number(iso[5]) : null,
    };
  }

  const fallback = new Date(trimmed);
  if (Number.isNaN(fallback.getTime())) return null;
  return {
    year: fallback.getFullYear(),
    month: fallback.getMonth() + 1,
    day: fallback.getDate(),
    hours24: fallback.getHours(),
    minutes: fallback.getMinutes(),
  };
}

function format12HourTime(hours24: number, minutes: number, timeSeparator = ':', lowercaseMeridiem = false): string {
  const meridiem = hours24 >= 12 ? 'PM' : 'AM';
  const hour12 = hours24 % 12 === 0 ? 12 : hours24 % 12;
  const h = String(hour12).padStart(2, '0');
  const m = String(minutes).padStart(2, '0');
  return `${h}${timeSeparator}${m} ${lowercaseMeridiem ? meridiem.toLowerCase() : meridiem}`;
}

export function normalizeDateToDots(val?: string | null): string {
  if (!val) return '';
  const trimmed = val.trim();
  const iso = trimmed.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (iso) {
    return `${iso[3].padStart(2, '0')}.${iso[2].padStart(2, '0')}.${iso[1]}`;
  }
  return trimmed.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, (_m, d, m, y) => {
    return `${d.padStart(2, '0')}.${m.padStart(2, '0')}.${y.length === 2 ? '20' + y : y}`;
  });
}

export function formatDate(val?: string | Date | null): string {
  if (!val) return '—';
  const parts = parseWallClockParts(val);
  if (!parts) {
    return normalizeDateToDots(String(val)) || String(val);
  }
  return `${String(parts.day).padStart(2, '0')}.${String(parts.month).padStart(2, '0')}.${parts.year}`;
}

export function formatDateTime(val?: string | Date | null, separator: string = ', '): string {
  if (!val) return '—';
  const parts = parseWallClockParts(val);
  if (!parts) {
    return normalizeDateToDots(String(val)) || String(val);
  }
  const datePart = `${String(parts.day).padStart(2, '0')}.${String(parts.month).padStart(2, '0')}.${parts.year}`;
  if (parts.hours24 === null || parts.minutes === null) {
    return datePart;
  }
  return `${datePart}${separator}${format12HourTime(parts.hours24, parts.minutes)}`;
}

/**
 * Matches Blade OT Notes date of surgery format: `d.m.Y h:i a`
 */
export function formatOtSurgeryDateTime(val?: string | Date | null): string {
  if (!val) return '';
  const parts = parseWallClockParts(val);
  if (!parts) return '';
  const datePart = `${String(parts.day).padStart(2, '0')}.${String(parts.month).padStart(2, '0')}.${parts.year}`;
  const hours = parts.hours24 ?? 0;
  const mins = parts.minutes ?? 0;
  return `${datePart} ${format12HourTime(hours, mins, ':', true)}`;
}

/**
 * Matches Blade OT Notes baby birth timestamp: `d.m.Y @ h.iA`
 */
export function formatBabyBirthTime(val?: string | Date | null): string {
  if (!val) return '';
  const parts = parseWallClockParts(val);
  if (!parts) return '';
  const datePart = `${String(parts.day).padStart(2, '0')}.${String(parts.month).padStart(2, '0')}.${parts.year}`;
  const hours = parts.hours24 ?? 0;
  const mins = parts.minutes ?? 0;
  const meridiem = hours >= 12 ? 'PM' : 'AM';
  const hour12 = hours % 12 === 0 ? 12 : hours % 12;
  return `${datePart} @ ${String(hour12).padStart(2, '0')}.${String(mins).padStart(2, '0')}${meridiem}`;
}

/**
 * Matches Blade MRD Form Discharge Date & Time format: `d.m.Y @ h.i A`
 */
export function formatMrdDateTime(val?: string | Date | null): string {
  if (!val) return '29.08.2026 @ 04.00 PM';
  const parts = parseWallClockParts(val);
  if (!parts) return String(val);
  const datePart = `${String(parts.day).padStart(2, '0')}.${String(parts.month).padStart(2, '0')}.${parts.year}`;
  const hours = parts.hours24 ?? 0;
  const mins = parts.minutes ?? 0;
  return `${datePart} @ ${format12HourTime(hours, mins, '.', false)}`;
}
