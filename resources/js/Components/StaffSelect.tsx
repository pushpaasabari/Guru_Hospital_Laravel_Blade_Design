import { useState } from 'react';
import MultiSelectField, { MultiSelectOption } from './MultiSelectField';
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
  multiple?: boolean;
}

const ROLE_ALIASES: Record<string, string[]> = {
  surgeon: ['surgeon'],
  assisted_surgeon: ['assisted_surgeon', 'surgeon'],
  doctor: ['consultant_doctor', 'surgeon', 'assisted_surgeon', 'anaesthetist', 'consultant_pediatrician'],
  consultant_doctor: ['consultant_doctor', 'surgeon', 'consultant_pediatrician'],
  anaesthetist: ['anaesthetist'],
  assisted_anaesthetist: ['assisted_anaesthetist', 'anaesthetist'],
  staff_nurse: ['staff_nurse', 'nurse'],
  nurse: ['staff_nurse'],
  ot_technician: ['ot_technician', 'ot_tech'],
  ot_tech: ['ot_technician', 'ot_tech'],
  consultant_pediatrician: ['consultant_pediatrician', 'pediatrician'],
  pediatrician: ['consultant_pediatrician', 'pediatrician'],
  all: [],
  other: [],
};

function getBaseStaffName(name: string): string {
  if (!name) return '';
  return name
    .replace(/(?:,\s*|\s+)(?:M\.?S\.?|M\.?D\.?|M\.?B\.?B\.?S|M\.?Ch\.?|D\.?G\.?O|D\.?N\.?B|D\.?A|FRCS|MRCOG|DM|FCPS|DCH|B\.?Sc|GNM)(?:\s*\([^)]*\))?/gi, '')
    .trim();
}

function formatStaffOptionLabel(staff: ClinicalStaffMember): string {
  const name = staff.name ? staff.name.trim() : '';
  const qual = staff.qualification ? staff.qualification.trim() : '';
  const dept = staff.department ? staff.department.trim() : '';

  if (qual) {
    const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');
    const cleanQual = qual.toLowerCase().replace(/[^a-z0-9]/g, '');
    if (cleanQual && cleanName.includes(cleanQual)) {
      return name;
    }
    return `${name} (${qual})`;
  }

  if (dept) {
    const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');
    const cleanDept = dept.toLowerCase().replace(/[^a-z0-9]/g, '');
    if (cleanDept && cleanName.includes(cleanDept)) {
      return name;
    }
    return `${name} (${dept})`;
  }

  return name;
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
  uppercase = false,
  multiple = false,
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

  // Display only filtered staff; do NOT fall back to unrelated staff members
  const displayList = filtered;

  // Deduplicate by base normalized name so entries with/without degree suffixes merge cleanly
  const deduplicatedMap = new Map<string, ClinicalStaffMember>();
  displayList.forEach((item) => {
    const baseKey = normalize(getBaseStaffName(item.name));
    const existing = deduplicatedMap.get(baseKey);
    if (!existing) {
      deduplicatedMap.set(baseKey, item);
    } else {
      const existingHasDegree = /(?:M\.?S|M\.?D|M\.?Ch|MBBS)/i.test(existing.name);
      const currentHasDegree = /(?:M\.?S|M\.?D|M\.?Ch|MBBS)/i.test(item.name);
      if (existingHasDegree && !currentHasDegree) {
        deduplicatedMap.set(baseKey, item);
      } else if (!existing.qualification && item.qualification) {
        deduplicatedMap.set(baseKey, item);
      }
    }
  });
  const uniqueList = Array.from(deduplicatedMap.values());

  if (multiple) {
    const multiOptions: MultiSelectOption[] = uniqueList.map((s) => ({
      key: s.id,
      value: normalize(s.name),
      label: formatStaffOptionLabel(s),
      matchKey: normalize(getBaseStaffName(s.name)),
    }));

    return (
      <MultiSelectField
        label={label}
        value={currentValue}
        onChange={onChange}
        options={multiOptions}
        placeholder={placeholder}
        customPlaceholder={`Add custom ${label.toLowerCase()}...`}
        required={required}
        uppercase={uppercase}
      />
    );
  }

  // Check if current value matches one in the list
  const hasMatch = uniqueList.some(
    (s) =>
      normalize(s.name) === currentValue ||
      normalize(getBaseStaffName(s.name)) === normalize(getBaseStaffName(currentValue))
  );

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
                {formatStaffOptionLabel(s)}
              </option>
            );
          })}
          <option value="__custom__">+ Enter custom name...</option>
        </select>
      )}
    </div>
  );
}
