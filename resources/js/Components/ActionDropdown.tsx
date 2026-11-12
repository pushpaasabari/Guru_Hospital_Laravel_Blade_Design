import React, { useCallback, useEffect, useLayoutEffect, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';

export type ActionDropdownItem =
  | { type: 'header'; label: string }
  | { type: 'divider' }
  | {
      type: 'inertia';
      label: string;
      href: string;
      iconClass?: string;
      className?: string;
      prefetch?: boolean;
    }
  | {
      type: 'external';
      label: string;
      href: string;
      iconClass?: string;
      className?: string;
      target?: string;
    }
  | {
      type: 'button';
      label: string;
      onClick: () => void;
      iconClass?: string;
      className?: string;
      disabled?: boolean;
    };

export interface ActionDropdownProps {
  primaryAction: React.ReactNode;
  items: ActionDropdownItem[];
  toggleClassName?: string;
  groupClassName?: string;
  toggleTitle?: string;
  align?: 'start' | 'end';
  minMenuWidth?: number;
  fontSize?: string;
  disabled?: boolean;
}

/**
 * Self-contained React split dropdown rendered with viewport coordinates so it never
 * clips inside overflow-hidden cards or horizontally scrollable tables and needs no Bootstrap JS.
 */
export default function ActionDropdown({
  primaryAction,
  items,
  toggleClassName = 'btn btn-outline-secondary dropdown-toggle dropdown-toggle-split',
  groupClassName = 'btn-group btn-group-sm',
  toggleTitle = 'More options',
  align = 'end',
  minMenuWidth = 220,
  fontSize = '12.5px',
  disabled = false,
}: ActionDropdownProps) {
  const [open, setOpen] = useState(false);
  const [coords, setCoords] = useState<{ top: number; left?: number; right?: number; openUp: boolean }>({
    top: 0,
    right: 0,
    openUp: false,
  });
  const wrapRef = useRef<HTMLDivElement>(null);
  const menuRef = useRef<HTMLUListElement>(null);

  const updatePosition = useCallback(() => {
    const el = wrapRef.current;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const openUp = spaceBelow < 240 && rect.top > 240;

    setCoords({
      top: openUp ? rect.top - 4 : rect.bottom + 4,
      left: align === 'start' ? Math.max(8, rect.left) : undefined,
      right: align === 'end' ? Math.max(8, window.innerWidth - rect.right) : undefined,
      openUp,
    });
  }, [align]);

  useLayoutEffect(() => {
    if (!open) return;
    updatePosition();
  }, [open, updatePosition]);

  useEffect(() => {
    if (!open) return;

    const handlePointerDown = (e: MouseEvent) => {
      const target = e.target as Node;
      if (wrapRef.current?.contains(target) || menuRef.current?.contains(target)) {
        return;
      }
      setOpen(false);
    };

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        setOpen(false);
      }
    };

    const handleCloseOnViewportChange = () => setOpen(false);

    document.addEventListener('mousedown', handlePointerDown);
    document.addEventListener('keydown', handleKeyDown);
    window.addEventListener('scroll', handleCloseOnViewportChange, true);
    window.addEventListener('resize', handleCloseOnViewportChange);

    return () => {
      document.removeEventListener('mousedown', handlePointerDown);
      document.removeEventListener('keydown', handleKeyDown);
      window.removeEventListener('scroll', handleCloseOnViewportChange, true);
      window.removeEventListener('resize', handleCloseOnViewportChange);
    };
  }, [open]);

  return (
    <div ref={wrapRef} className={groupClassName}>
      {primaryAction}
      <button
        type="button"
        className={toggleClassName}
        aria-expanded={open}
        aria-haspopup="menu"
        title={toggleTitle}
        disabled={disabled}
        onClick={() => setOpen((prev) => !prev)}
      >
        <span className="visually-hidden">{toggleTitle}</span>
      </button>

      {open && (
        <ul
          ref={menuRef}
          role="menu"
          className="dropdown-menu show shadow border-0"
          style={{
            position: 'fixed',
            top: coords.openUp ? 'auto' : `${coords.top}px`,
            bottom: coords.openUp ? `${window.innerHeight - coords.top}px` : 'auto',
            left: coords.left !== undefined ? `${coords.left}px` : 'auto',
            right: coords.right !== undefined ? `${coords.right}px` : 'auto',
            minWidth: `${minMenuWidth}px`,
            zIndex: 1085,
            fontSize,
            borderRadius: '12px',
          }}
        >
          {items.map((item, idx) => {
            if (item.type === 'divider') {
              return (
                <li key={idx}>
                  <hr className="dropdown-divider my-1" />
                </li>
              );
            }
            if (item.type === 'header') {
              return (
                <li key={idx}>
                  <h6 className="dropdown-header text-uppercase fw-bold" style={{ fontSize: '10px' }}>
                    {item.label}
                  </h6>
                </li>
              );
            }
            if (item.type === 'inertia') {
              return (
                <li key={idx}>
                  <Link
                    href={item.href}
                    prefetch={item.prefetch ?? true}
                    className={`dropdown-item py-1.5 ${item.className || ''}`}
                    onClick={() => setOpen(false)}
                  >
                    {item.iconClass && <i className={`${item.iconClass} me-2`}></i>}
                    {item.label}
                  </Link>
                </li>
              );
            }
            if (item.type === 'external') {
              return (
                <li key={idx}>
                  <a
                    href={item.href}
                    target={item.target || '_blank'}
                    rel="noreferrer"
                    className={`dropdown-item py-1.5 ${item.className || ''}`}
                    onClick={() => setOpen(false)}
                  >
                    {item.iconClass && <i className={`${item.iconClass} me-2`}></i>}
                    {item.label}
                  </a>
                </li>
              );
            }
            return (
              <li key={idx}>
                <button
                  type="button"
                  disabled={item.disabled}
                  className={`dropdown-item py-1.5 ${item.className || ''}`}
                  onClick={() => {
                    setOpen(false);
                    item.onClick();
                  }}
                >
                  {item.iconClass && <i className={`${item.iconClass} me-2`}></i>}
                  {item.label}
                </button>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}

export function buildDocumentPreviewMenuItems(admissionId: number): ActionDropdownItem[] {
  return [
    { type: 'header', label: 'Separate Document Previews' },
    {
      type: 'inertia',
      label: '1. Discharge Summary',
      href: `/admissions/${admissionId}/documents/preview?doc=discharge_summary`,
      iconClass: 'bi bi-file-earmark-text text-primary',
    },
    {
      type: 'inertia',
      label: '2. Operation Theatre Notes',
      href: `/admissions/${admissionId}/documents/preview?doc=operation_notes`,
      iconClass: 'bi bi-journal-medical text-success',
    },
    {
      type: 'inertia',
      label: '3. MRD Inpatient Form',
      href: `/admissions/${admissionId}/documents/preview?doc=mrd_form`,
      iconClass: 'bi bi-file-earmark-check text-warning',
    },
    { type: 'divider' },
    {
      type: 'inertia',
      label: 'All Documents (Pack)',
      href: `/admissions/${admissionId}/documents/preview?doc=all`,
      iconClass: 'bi bi-collection text-dark',
      className: 'fw-semibold',
    },
    {
      type: 'inertia',
      label: 'Playwright PDF Preview',
      href: `/admissions/${admissionId}/documents/preview?mode=pdf`,
      iconClass: 'bi bi-file-earmark-pdf-fill text-danger',
      className: 'text-danger fw-semibold',
    },
  ];
}

export function buildEditStageMenuItems(admissionId: number): ActionDropdownItem[] {
  return [
    {
      type: 'inertia',
      label: '1. Admission Details',
      href: `/admissions/${admissionId}/edit`,
      iconClass: 'bi bi-person text-primary',
    },
    {
      type: 'inertia',
      label: '2. Labour / Surgery',
      href: `/admissions/${admissionId}/surgery`,
      iconClass: 'bi bi-heart-pulse text-danger',
    },
    {
      type: 'inertia',
      label: '3. Test Reports',
      href: `/admissions/${admissionId}/test-reports`,
      iconClass: 'bi bi-file-earmark-medical text-info',
    },
    {
      type: 'inertia',
      label: '4. Discharge Summary',
      href: `/admissions/${admissionId}/discharge`,
      iconClass: 'bi bi-clipboard2-check text-success',
    },
  ];
}

export function buildPdfDownloadMenuItems(admissionId: number, headerLabel?: string): ActionDropdownItem[] {
  const items: ActionDropdownItem[] = [];
  if (headerLabel) {
    items.push({ type: 'header', label: headerLabel });
  }
  items.push(
    {
      type: 'external',
      label: '1. Discharge Summary',
      href: `/admissions/${admissionId}/documents/pdf/discharge-summary`,
      iconClass: 'bi bi-file-earmark-text text-primary',
    },
    {
      type: 'external',
      label: '1B. Digital Discharge Summary',
      href: `/admissions/${admissionId}/documents/pdf/discharge-summary-a4`,
      iconClass: 'bi bi-file-earmark-richtext text-info',
    },
    {
      type: 'external',
      label: '2. Operation Theatre Notes',
      href: `/admissions/${admissionId}/documents/pdf/operation-notes`,
      iconClass: 'bi bi-journal-medical text-success',
    },
    {
      type: 'external',
      label: '3. MRD Inpatient Form',
      href: `/admissions/${admissionId}/documents/pdf/mrd-form`,
      iconClass: 'bi bi-file-earmark-check text-warning',
    },
    { type: 'divider' },
    {
      type: 'external',
      label: 'Complete 3-in-1 Pack',
      href: `/admissions/${admissionId}/documents/pdf/pack`,
      iconClass: 'bi bi-collection text-dark',
      className: 'fw-semibold',
    }
  );
  return items;
}

