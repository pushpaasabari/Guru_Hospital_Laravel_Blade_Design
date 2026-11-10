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

