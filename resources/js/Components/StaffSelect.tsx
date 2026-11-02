import { useState } from 'react';

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
}

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

  // Filter staff by the requested role
  const filtered = staffList.filter(
    (s) => s.category === role || (s.roles && s.roles.includes(role))
  );

  // Check if current value matches one in the filtered list
  const hasMatch = filtered.some((s) => s.name === value);

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
            value={value}
            onChange={(e) => onChange(e.target.value)}
            placeholder={`Enter custom ${label}`}
            required={required}
          />
          <button
            type="button"
            className="btn btn-outline-secondary"
            onClick={() => setCustomMode(false)}
            title="Pick from staff list"
          >
            List
          </button>
        </div>
      ) : (
        <select
          className="form-select"
          value={value}
          onChange={(e) => {
            if (e.target.value === '__custom__') {
              setCustomMode(true);
            } else {
              onChange(e.target.value);
            }
          }}
          required={required}
        >
          <option value="">{placeholder}</option>
          {value && !hasMatch && (
            <option value={value}>{value} (Current)</option>
          )}
          {filtered.map((s) => (
            <option key={s.id} value={s.name}>
              {s.name} {s.qualification ? `(${s.qualification})` : s.department ? `(${s.department})` : ''}
            </option>
          ))}
          <option value="__custom__">+ Enter custom name...</option>
        </select>
      )}
    </div>
  );
}
