import { useState } from 'react';
import { joinMultiValues, parseMultiValues } from '../utils/multiSelect';
import { handleNormalizedInputChange, toUpperCaseValue } from '../utils/inputNormalizers';

export interface MultiSelectOption {
  key: string | number;
  value: string;
  label: string;
  matchKey?: string;
}

interface MultiSelectFieldProps {
  label: string;
  value: string;
  onChange: (value: string) => void;
  options: MultiSelectOption[];
  placeholder?: string;
  customPlaceholder?: string;
  required?: boolean;
  uppercase?: boolean;
  onRequestAddNew?: () => void;
  addNewLabel?: string;
}

export default function MultiSelectField({
  label,
  value,
  onChange,
  options,
  placeholder = '-- Select one or more --',
  customPlaceholder = 'Add custom entry...',
  required = false,
  uppercase = false,
  onRequestAddNew,
  addNewLabel = 'Add New',
}: MultiSelectFieldProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [customDraft, setCustomDraft] = useState('');

  const normalize = (v: string) => (uppercase ? toUpperCaseValue(v) : v.trim());
  const optionValues = options.map((o) => o.value);
  const selectedItems = parseMultiValues(value, optionValues, normalize);

  const isOptionSelected = (opt: MultiSelectOption) => {
    const normVal = normalize(opt.value).toLowerCase();
    const normMatch = opt.matchKey ? normalize(opt.matchKey).toLowerCase() : null;
    return selectedItems.some((item) => {
      const lower = item.toLowerCase();
      return lower === normVal || (normMatch !== null && lower === normMatch);
    });
  };

  const toggleValue = (rawVal: string, matchKey?: string) => {
    const clean = normalize(rawVal);
    if (!clean) return;
    const normLower = clean.toLowerCase();
    const matchLower = matchKey ? normalize(matchKey).toLowerCase() : null;

    const exists = selectedItems.some((item) => {
      const lower = item.toLowerCase();
      return lower === normLower || (matchLower !== null && lower === matchLower);
    });

    const next = exists
      ? selectedItems.filter((item) => {
          const lower = item.toLowerCase();
          return lower !== normLower && !(matchLower !== null && lower === matchLower);
        })
      : [...selectedItems, clean];

    onChange(joinMultiValues(next, normalize));
  };

  const removeChip = (targetItem: string) => {
    const next = selectedItems.filter((item) => item !== targetItem);
    onChange(joinMultiValues(next, normalize));
  };

  const handleAddCustom = () => {
    const clean = normalize(customDraft);
    if (!clean) return;
    if (!selectedItems.some((item) => item.toLowerCase() === clean.toLowerCase())) {
      onChange(joinMultiValues([...selectedItems, clean], normalize));
    }
    setCustomDraft('');
  };

  const q = searchTerm.trim().toLowerCase();
  const visibleOptions = q
    ? options.filter(
        (opt) =>
          opt.label.toLowerCase().includes(q) ||
          opt.value.toLowerCase().includes(q)
      )
    : options;

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-1 gap-2">
        <label className={`form-label mb-0 ${required ? 'required' : ''}`}>
          {label}
          {selectedItems.length > 0 && (
            <span className="badge bg-primary-subtle text-primary border border-primary-subtle ms-2" style={{ fontSize: '0.7rem' }}>
              {selectedItems.length} selected
            </span>
          )}
        </label>
        <div className="d-flex align-items-center gap-2">
          {selectedItems.length > 0 && (
            <button
              type="button"
              className="btn btn-link p-0 text-decoration-none small text-danger"
              style={{ fontSize: '0.74rem' }}
              onClick={() => onChange('')}
            >
              Clear
            </button>
          )}
          {onRequestAddNew && (
            <button
              type="button"
              className="btn btn-link p-0 text-decoration-none small text-primary fw-semibold"
              style={{ fontSize: '0.75rem' }}
              onClick={onRequestAddNew}
            >
              + {addNewLabel}
            </button>
          )}
        </div>
      </div>

      {/* Selected Value Chips */}
      {selectedItems.length > 0 && (
        <div className="d-flex flex-wrap gap-1 mb-1">
          {selectedItems.map((item) => (
            <span
              key={item}
              className="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2"
              style={{ fontSize: '0.75rem', fontWeight: 600 }}
            >
              <span>{item}</span>
              <button
                type="button"
                aria-label={`Remove ${item}`}
                className="btn-close"
                style={{ fontSize: '0.55rem' }}
                onClick={() => removeChip(item)}
              />
            </span>
          ))}
        </div>
      )}

      {/* Trigger Bar */}
      <button
        type="button"
        className="form-select text-start d-flex justify-content-between align-items-center"
        aria-expanded={isOpen}
        onClick={() => setIsOpen((prev) => !prev)}
      >
        <span className={selectedItems.length === 0 ? 'text-muted' : 'text-dark text-truncate'}>
          {selectedItems.length === 0
            ? placeholder
            : isOpen
            ? 'Hide multiple-choice options'
            : `${selectedItems.length} selected — click to add/edit`}
        </span>
      </button>

      {/* Inline Multiple Choice Checkbox Panel */}
      {isOpen && (
        <div className="border rounded bg-white shadow-sm p-2 mt-1">
          {options.length > 5 && (
            <input
              type="text"
              className="form-control form-control-sm mb-2"
              placeholder={`Search ${label.toLowerCase()}...`}
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          )}

          <div style={{ maxHeight: '150px', overflowY: 'auto' }}>
            {visibleOptions.length === 0 ? (
              <div className="text-muted small py-1 px-2">No matching options</div>
            ) : (
              visibleOptions.map((opt) => {
                const checked = isOptionSelected(opt);
                return (
                  <label
                    key={opt.key}
                    className={`d-flex align-items-start gap-2 rounded px-2 py-1 mb-0 small ${
                      checked ? 'bg-primary-subtle text-dark fw-semibold' : 'text-dark'
                    }`}
                    style={{ cursor: 'pointer', fontSize: '0.82rem' }}
                  >
                    <input
                      type="checkbox"
                      className="form-check-input mt-1 flex-shrink-0"
                      checked={checked}
                      onChange={() => toggleValue(opt.value, opt.matchKey)}
                    />
                    <span>{opt.label}</span>
                  </label>
                );
              })
            )}
          </div>

          {onRequestAddNew ? (
            <div className="d-flex justify-content-between align-items-center gap-2 mt-2 pt-2 border-top">
              <button
                type="button"
                className="btn btn-sm btn-outline-primary"
                onClick={onRequestAddNew}
              >
                <i className="bi bi-plus-circle me-1"></i>
                {addNewLabel} {label}
              </button>
              <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                onClick={() => setIsOpen(false)}
              >
                Done
              </button>
            </div>
          ) : (
            <div className="input-group input-group-sm mt-2 pt-2 border-top">
              <input
                type="text"
                className="form-control"
                style={uppercase ? { textTransform: 'uppercase' } : undefined}
                placeholder={customPlaceholder}
                value={customDraft}
                onChange={(e) =>
                  uppercase
                    ? handleNormalizedInputChange(e, toUpperCaseValue, setCustomDraft)
                    : setCustomDraft(e.target.value)
                }
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    handleAddCustom();
                  }
                }}
              />
              <button
                type="button"
                className="btn btn-outline-primary"
                onClick={handleAddCustom}
              >
                + Add
              </button>
              <button
                type="button"
                className="btn btn-outline-secondary"
                onClick={() => setIsOpen(false)}
              >
                Done
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
