import React, { useEffect, useState } from 'react';
import {
  PATIENT_NAME_PREFIXES,
  combinePatientNameWithPrefix,
  handleNormalizedInputChange,
  splitPatientNamePrefix,
  toUpperCaseValue,
} from '../utils/inputNormalizers';

interface PatientNameInputProps {
  value: string;
  onChange: (fullName: string) => void;
  error?: string;
  required?: boolean;
  placeholder?: string;
}

const DEFAULT_PATIENT_PREFIX = 'MR.';

/**
 * Renders a salutation prefix selector paired inline before the uppercase Patient Full Name input.
 */
export default function PatientNameInput({
  value,
  onChange,
  error,
  required = false,
  placeholder = 'e.g. PATIENT NAME',
}: PatientNameInputProps) {
  const initial = splitPatientNamePrefix(value);
  const [prefix, setPrefix] = useState<string>(initial.prefix || DEFAULT_PATIENT_PREFIX);
  const [nameBody, setNameBody] = useState<string>(initial.name);

  // Sync only when parent prop changes externally (avoid resetting selected prefix while name is empty)
  useEffect(() => {
    const combined = combinePatientNameWithPrefix(prefix, nameBody);
    if ((value || '') !== combined) {
      const parsed = splitPatientNamePrefix(value);
      setPrefix(parsed.prefix || DEFAULT_PATIENT_PREFIX);
      setNameBody(parsed.name);
    }
  }, [value]);

  const handlePrefixChange = (nextPrefix: string) => {
    setPrefix(nextPrefix);
    onChange(combinePatientNameWithPrefix(nextPrefix, nameBody));
  };

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    handleNormalizedInputChange(e, toUpperCaseValue, (upperName) => {
      setNameBody(upperName);
      onChange(combinePatientNameWithPrefix(prefix, upperName));
    });
  };

  return (
    <>
      <div className="d-flex gap-2">
        <select
          className="form-select fg-prefix-select"
          aria-label="Patient Prefix"
          value={prefix}
          onChange={(e) => handlePrefixChange(e.target.value)}
        >
          {PATIENT_NAME_PREFIXES.map((item) => (
            <option key={item.value} value={item.value}>
              {item.label}
            </option>
          ))}
        </select>
        <input
          type="text"
          className={`form-control flex-grow-1 ${error ? 'is-invalid' : ''}`}
          style={{ textTransform: 'uppercase', minWidth: 0 }}
          aria-label="Patient Full Name"
          value={nameBody}
          onChange={handleNameChange}
          placeholder={placeholder}
          required={required}
        />
      </div>
      {error && <div className="invalid-feedback d-block">{error}</div>}
    </>
  );
}
