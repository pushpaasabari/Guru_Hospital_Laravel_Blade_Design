import { useState } from 'react';
import MultiSelectField, { MultiSelectOption } from './MultiSelectField';

export interface AnesthesiaTypeItem {
  id: number;
  name: string;
  code?: string;
  description?: string;
}

interface AnesthesiaSelectProps {
  label?: string;
  value: string;
  onChange: (value: string) => void;
  types?: AnesthesiaTypeItem[];
  anesthesiaList?: AnesthesiaTypeItem[];
  placeholder?: string;
  required?: boolean;
  multiple?: boolean;
}

export default function AnesthesiaSelect({
  label = 'Anaesthesia Type',
  value,
  onChange,
  types,
  anesthesiaList,
  placeholder = '-- Select Anaesthesia Type --',
  required = false,
  multiple = false,
}: AnesthesiaSelectProps) {
  const [customMode, setCustomMode] = useState(false);
  const typeList = types ?? anesthesiaList ?? [];

  if (multiple) {
    const multiOptions: MultiSelectOption[] = typeList.map((t) => ({
      key: t.id,
      value: t.name,
      label: t.name,
      matchKey: t.code || undefined,
    }));

    return (
      <MultiSelectField
        label={label}
        value={value}
        onChange={onChange}
        options={multiOptions}
        placeholder={placeholder}
        customPlaceholder="Add custom anaesthesia type (e.g. Block)..."
        required={required}
      />
    );
  }

  // Match if current value matches either the name or code
  const hasMatch = typeList.some((t) => t.name === value || (t.code && t.code === value));

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-1">
        <label className={`form-label mb-0 ${required ? 'required' : ''}`}>
          {label}
        </label>
        <button
          type="button"
          className="btn btn-link p-0 text-decoration-none small text-primary"
          style={{ fontSize: '0.75rem', fontWeight: 500 }}
          onClick={() => setCustomMode(!customMode)}
        >
          {customMode ? '← Pick from list' : '+ Custom type'}
        </button>
      </div>

      {customMode ? (
        <input
          type="text"
          className="form-control"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder="e.g. SA, GA, Spinal, Local, Epidural"
          required={required}
        />
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
          {typeList.map((t) => (
            <option key={t.id} value={t.name}>
              {t.name}
            </option>
          ))}
          <option value="__custom__">+ Enter custom type...</option>
        </select>
      )}
    </div>
  );
}

