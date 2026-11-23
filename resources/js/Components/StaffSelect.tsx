import { useState } from 'react';
import { handleNormalizedInputChange, toUpperCaseValue } from '../utils/inputNormalizers';

export interface ClinicalStaffMember {
  id: number;
  name: string;
  category: string;
  roles?: string[];
  department?: string;
  qualification?: string;
}

interface StaffSelectProps {
  label: string;
  role: string;
  value: string;
  onChange: (value: string) => void;
  staffList?: ClinicalStaffMember[];
  placeholder?: string;
  required?: boolean;
  showCustomToggle?: boolean;
  isCustom?: boolean;
  onCustomToggle?: (isCustom: boolean) => void;
  uppercase?: boolean;
}

const ROLE_ALIASES: Record<string, string[]> = {
  surgeon: ['surgeon', 'consultant_doctor'],
  assisted_surgeon: ['assisted_surgeon', 'surgeon', 'consultant_doctor', 'staff_nurse'],
  doctor: ['consultant_doctor', 'surgeon', 'assisted_surgeon', 'anaesthetist', 'consultant_pediatrician'],
  consultant_doctor: ['consultant_doctor', 'surgeon'],
  anaesthetist: ['anaesthetist'],
  assisted_anaesthetist: ['assisted_anaesthetist', 'anaesthetist', 'staff_nurse'],
  staff_nurse: ['staff_nurse'],
  nurse: ['staff_nurse'],
  ot_technician: ['ot_technician'],
  ot_tech: ['ot_technician'],
  consultant_pediatrician: ['consultant_pediatrician', 'consultant_doctor'],
  pediatrician: ['consultant_pediatrician', 'consultant_doctor'],
  all: [],
  other: [],
};

export default function StaffSelect({
  label,
  role,
  value,
  onChange,
  staffList = [],
  placeholder = '-- Select --',
  required = false,
  showCustomToggle = false,
  isCustom,
  onCustomToggle,
  uppercase = false,
}: StaffSelectProps) {
  const [internalCustom, setInternalCustom] = useState(false);

  // Controlled or uncontrolled custom mode
  const customMode = isCustom !== undefined ? isCustom : internalCustom;

  const setCustomMode = (val: boolean) => {
    if (onCustomToggle) {
      onCustomToggle(val);
    }
    setInternalCustom(val);
  };

  const normalize = (val: string) => (uppercase ? toUpperCaseValue(val) : val);
  const currentValue = normalize(value || '');

  const targetRoles = ROLE_ALIASES[role] ?? (role ? [role] : []);

  // Filter staff by the requested role or aliases
  const filtered = staffList.filter((s) => {
    if (!role || role === 'all' || role === 'other') return true;
    if (s.category === role) return true;
    if (s.roles && s.roles.includes(role)) return true;
    if (targetRoles.length > 0) {
      if (targetRoles.includes(s.category)) return true;
      if (s.roles && s.roles.some((r) => targetRoles.includes(r))) return true;
    }
    return false;
  });

  // Fallback to all staff if role filter returned empty so dropdown is never broken
  const displayList = filtered.length > 0 ? filtered : staffList;

  // Deduplicate by normalized name
  const uniqueList = Array.from(
    new Map(displayList.map((item) => [normalize(item.name), item])).values()
  );

  // Check if current value matches one in the list
  const hasMatch = uniqueList.some((s) => normalize(s.name) === currentValue);

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-1">
        <label className={`form-label mb-0 ${required ? 'required' : ''}`}>
          {label}
        </label>
        {showCustomToggle && (
          <button
            type="button"
            className="btn btn-link p-0 text-decoration-none small text-primary"
            style={{ fontSize: '0.75rem', fontWeight: 500 }}
            onClick={() => setCustomMode(!customMode)}
          >
            {customMode ? '← Pick from list' : '+ Custom name'}
          </button>
        )}
      </div>

      {customMode ? (
        <div className="input-group">
          <input
            type="text"
            className="form-control"
            style={uppercase ? { textTransform: 'uppercase' } : undefined}
            value={currentValue}
            onChange={(e) =>
              uppercase
                ? handleNormalizedInputChange(e, toUpperCaseValue, onChange)
                : onChange(e.target.value)
            }
            placeholder={`Enter custom ${label}`}
            required={required}
          />
          {showCustomToggle && (
            <button
              type="button"
              className="btn btn-outline-secondary"
              onClick={() => setCustomMode(false)}
              title="Pick from staff list"
            >
              List
            </button>
          )}
        </div>
      ) : (
        <select
          className="form-select"
          value={currentValue}
          onChange={(e) => {
            if (e.target.value === '__custom__') {
              setCustomMode(true);
            } else {
              onChange(normalize(e.target.value));
            }
          }}
          required={required}
        >
          <option value="">{placeholder}</option>
          {currentValue && !hasMatch && (
            <option value={currentValue}>{currentValue} (Current)</option>
          )}
          {uniqueList.map((s) => {
            const optionValue = normalize(s.name);
            return (
              <option key={s.id} value={optionValue}>
                {optionValue} {s.qualification ? `(${s.qualification})` : s.department ? `(${s.department})` : ''}
              </option>
            );
          })}
          <option value="__custom__">+ Enter custom name...</option>
        </select>
      )}
    </div>
  );
}
