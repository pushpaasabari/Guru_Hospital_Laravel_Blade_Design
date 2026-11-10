export interface UiActivityEvent {
  action: 'ui.click' | 'ui.change';
  description: string;
  path: string;
  element: string;
  label: string;
  href?: string;
  metadata?: Record<string, string | number | boolean | null>;
}

const ENDPOINT = '/audit-logs/events';
const FLUSH_DELAY_MS = 250;
const MAX_BATCH_SIZE = 15;

let queue: UiActivityEvent[] = [];
let flushTimer: number | null = null;
let initialized = false;

function readXsrfToken(): string | null {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  if (match && match[1]) {
    try {
      return decodeURIComponent(match[1]);
    } catch {
      return match[1];
    }
  }
  return null;
}

function readMetaCsrfToken(): string | null {
  if (typeof document === 'undefined') return null;
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') || null;
}

function resolveClickTarget(rawTarget: EventTarget | null): HTMLElement | null {
  if (!(rawTarget instanceof Element)) return null;
  const htmlEl = rawTarget as HTMLElement;
  if (htmlEl.tagName === 'HTML' || htmlEl.tagName === 'BODY') {
    return null;
  }

  const interactive = htmlEl.closest(
    'button, a, [role="button"], [role="menuitem"], input, select, textarea, label, .fg-stage-card, [data-audit-label]'
  );

  return (interactive as HTMLElement) || htmlEl;
}

function extractElementLabel(el: HTMLElement): string {
  if (el instanceof HTMLInputElement && el.type === 'password') {
    return 'Password field';
  }

  const explicit =
    el.dataset?.auditLabel ||
    el.getAttribute('aria-label') ||
    el.getAttribute('title');

  if (explicit && explicit.trim()) {
    return explicit.trim().replace(/\s+/g, ' ').slice(0, 120);
  }

  if (el instanceof HTMLInputElement) {
    if (el.type === 'submit' || el.type === 'button') {
      return (el.value || el.name || el.id || 'button').trim().slice(0, 120);
    }
    return (el.placeholder || el.name || el.id || el.type || 'input').trim().slice(0, 120);
  }

  if (el instanceof HTMLSelectElement || el instanceof HTMLTextAreaElement) {
    return (el.name || el.id || el.getAttribute('placeholder') || el.tagName.toLowerCase()).trim().slice(0, 120);
  }

  const text = (el.innerText || el.textContent || '').trim().replace(/\s+/g, ' ');
  if (text) {
    return text.slice(0, 120);
  }

  return (el.id || el.className?.toString().split(' ').slice(0, 2).join('.') || el.tagName.toLowerCase()).slice(0, 120);
}

function buildClickEvent(rawTarget: EventTarget | null, event: MouseEvent): UiActivityEvent | null {
  const el = resolveClickTarget(rawTarget);
  if (!el) return null;

  const path = window.location.pathname || '/';
  const tag = el.tagName.toLowerCase();
  const label = extractElementLabel(el);
  const href = el instanceof HTMLAnchorElement ? el.getAttribute('href') || undefined : undefined;
  const role = el.getAttribute('role') || undefined;
  const elementType = el instanceof HTMLInputElement ? `input[${el.type}]` : role || tag;

  const description = href
    ? `Clicked ${elementType} '${label}' -> ${href} on ${path}`
    : `Clicked ${elementType} '${label}' on ${path}`;

  return {
    action: 'ui.click',
    description,
    path,
    element: elementType.slice(0, 60),
    label,
    href: href?.slice(0, 255),
    metadata: {
      id: el.id || null,
      x: Math.round(event.clientX),
      y: Math.round(event.clientY),
    },
  };
}

function buildChangeEvent(rawTarget: EventTarget | null): UiActivityEvent | null {
  if (!(rawTarget instanceof HTMLElement)) return null;
  if (rawTarget instanceof HTMLInputElement && rawTarget.type === 'password') return null;

  const path = window.location.pathname || '/';

  if (rawTarget instanceof HTMLSelectElement) {
    const label = extractElementLabel(rawTarget);
    const selectedText = rawTarget.options[rawTarget.selectedIndex]?.text || rawTarget.value || '';
    const valPreview = selectedText.trim().slice(0, 80);
    return {
      action: 'ui.change',
      description: `Selected '${valPreview}' in '${label}' on ${path}`,
      path,
      element: 'select',
      label,
      metadata: {
        id: rawTarget.id || null,
        value: valPreview,
      },
    };
  }

  if (rawTarget instanceof HTMLInputElement && (rawTarget.type === 'checkbox' || rawTarget.type === 'radio')) {
    const label = extractElementLabel(rawTarget);
    return {
      action: 'ui.change',
      description: `Toggled ${rawTarget.type} '${label}' (${rawTarget.checked ? 'checked' : 'unchecked'}) on ${path}`,
      path,
      element: `input[${rawTarget.type}]`,
      label,
      metadata: {
        id: rawTarget.id || null,
        checked: rawTarget.checked,
      },
    };
  }

  return null;
}

export function flushActivityLogs(): void {
  if (flushTimer !== null) {
    window.clearTimeout(flushTimer);
    flushTimer = null;
  }

  if (queue.length === 0) return;

  const batch = queue.splice(0, queue.length);
  const xsrf = readXsrfToken();
  const metaToken = readMetaCsrfToken();
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };

  if (xsrf) {
    headers['X-XSRF-TOKEN'] = xsrf;
  } else if (metaToken) {
    headers['X-CSRF-TOKEN'] = metaToken;
  }

  try {
    fetch(ENDPOINT, {
      method: 'POST',
      headers,
      body: JSON.stringify({ events: batch }),
      credentials: 'same-origin',
      keepalive: true,
    }).catch(() => {});
  } catch {
    // Non-blocking telemetry must never impact UX.
  }
}

function enqueueEvent(evt: UiActivityEvent, flushImmediately = false): void {
  queue.push(evt);

  if (flushImmediately || queue.length >= MAX_BATCH_SIZE) {
    flushActivityLogs();
    return;
  }

  if (flushTimer === null) {
    flushTimer = window.setTimeout(flushActivityLogs, FLUSH_DELAY_MS);
  }
}

export function initActivityLogger(): void {
  if (typeof window === 'undefined' || initialized) return;
  initialized = true;

  document.addEventListener(
    'click',
    (event: MouseEvent) => {
      if (!event.isTrusted) return;
      const evt = buildClickEvent(event.target, event);
      if (!evt) return;

      const el = event.target instanceof Element ? event.target : null;
      const triggersNavOrAuth = Boolean(
        el?.closest('button[type="submit"], form button:not([type="button"]), a[target="_blank"], a[download], a[href="/logout"]')
      );
      enqueueEvent(evt, triggersNavOrAuth);
    },
    { capture: true, passive: true }
  );

  document.addEventListener(
    'change',
    (event: Event) => {
      if (!event.isTrusted) return;
      const evt = buildChangeEvent(event.target);
      if (evt) {
        enqueueEvent(evt, false);
      }
    },
    { capture: true, passive: true }
  );

  window.addEventListener('pagehide', flushActivityLogs);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      flushActivityLogs();
    }
  });
}

