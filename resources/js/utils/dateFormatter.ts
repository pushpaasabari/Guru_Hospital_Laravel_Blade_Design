/**
 * Standard Date Formatter for Guru Hospital: dd.mm.yyyy
 */

export function normalizeDateToDots(val?: string | null): string {
  if (!val) return '';
  const trimmed = val.trim();
  // Convert YYYY-MM-DD (e.g. 2026-11-23) to DD.MM.YYYY
  const iso = trimmed.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (iso) {
    return `${iso[3].padStart(2, '0')}.${iso[2].padStart(2, '0')}.${iso[1]}`;
  }
  // Convert DD/MM/YYYY or DD-MM-YYYY to DD.MM.YYYY
  return trimmed.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g, (_m, d, m, y) => {
    return `${d.padStart(2, '0')}.${m.padStart(2, '0')}.${y.length === 2 ? '20' + y : y}`;
  });
}

export function formatDate(val?: string | Date | null): string {
  if (!val) return '—';
  try {
    if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}/.test(val)) {
      const parts = val.slice(0, 10).split('-');
      return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    const d = typeof val === 'string' ? new Date(val) : val;
    if (isNaN(d.getTime())) {
      return normalizeDateToDots(String(val)) || String(val);
    }
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}.${month}.${year}`;
  } catch {
    return String(val);
  }
}

export function formatDateTime(val?: string | Date | null, separator: string = ', '): string {
  if (!val) return '—';
  try {
    const d = typeof val === 'string' ? new Date(val) : val;
    if (isNaN(d.getTime())) {
      return normalizeDateToDots(String(val)) || String(val);
    }
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const time = d.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    });
    return `${day}.${month}.${year}${separator}${time}`;
  } catch {
    return String(val);
  }
}
