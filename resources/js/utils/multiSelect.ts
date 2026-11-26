/**
 * Helpers for serializing and parsing multiple-choice clinical team and anaesthesia values
 * while preserving commas inside medical degrees (e.g. "DR. KARTHIK RAJAN MS, MCH").
 */

const DEGREE_SUFFIX_LOOKAHEAD = /^\s*(?:M\.?S\.?|M\.?D\.?|M\.?Ch\.?|M\.?B\.?B\.?S|D\.?G\.?O|D\.?N\.?B|D\.?A|FRCS|MRCOG|DM|FCPS|DCH|B\.?Sc|GNM)\b/i;

export function normalizeSelectionTokens(
  items: string[],
  normalizeFn: (val: string) => string = (v) => v.trim()
): string[] {
  const seen = new Set<string>();
  const result: string[] = [];

  for (const raw of items) {
    const cleaned = normalizeFn(String(raw || '').trim()).trim();
    if (!cleaned) continue;
    const dedupeKey = cleaned.toLowerCase();
    if (seen.has(dedupeKey)) continue;
    seen.add(dedupeKey);
    result.push(cleaned);
  }

  return result;
}

export function joinMultiValues(
  items: string[],
  normalizeFn: (val: string) => string = (v) => v.trim()
): string {
  return normalizeSelectionTokens(items, normalizeFn).join('; ');
}

export function parseMultiValues(
  raw: string | string[] | null | undefined,
  knownOptions: string[] = [],
  normalizeFn: (val: string) => string = (v) => v.trim()
): string[] {
  if (Array.isArray(raw)) {
    return normalizeSelectionTokens(raw, normalizeFn);
  }

  const text = String(raw || '').trim();
  if (!text) return [];

  const normalizedKnown = new Set(knownOptions.map((o) => normalizeFn(o).toLowerCase()));

  // Split primarily on semicolons, pipes, or newlines (preserving commas inside degree names)
  const primaryChunks = text
    .split(/[;\r\n|]+/)
    .map((c) => c.trim())
    .filter(Boolean);

  const tokens: string[] = [];
  for (const chunk of primaryChunks) {
    const normalizedChunk = normalizeFn(chunk);
    if (normalizedKnown.has(normalizedChunk.toLowerCase()) || !chunk.includes(',')) {
      tokens.push(normalizedChunk);
      continue;
    }

    // Only split on commas that do NOT introduce a medical qualification suffix
    const commaParts = chunk.split(',');
    let current = commaParts[0] || '';
    for (let i = 1; i < commaParts.length; i++) {
      const part = commaParts[i];
      if (DEGREE_SUFFIX_LOOKAHEAD.test(part)) {
        current += `,${part}`;
      } else {
        if (current.trim()) tokens.push(normalizeFn(current));
        current = part;
      }
    }
    if (current.trim()) tokens.push(normalizeFn(current));
  }

  return normalizeSelectionTokens(tokens, normalizeFn);
}
