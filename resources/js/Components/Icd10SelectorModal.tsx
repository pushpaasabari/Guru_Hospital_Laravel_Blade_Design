import React, { useState, useMemo } from 'react';
import { ICD10_CATALOG, Icd10Entry } from '../constants/icd10Catalog';

interface Icd10SelectorModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSelect: (code: string, entry?: Icd10Entry) => void;
  currentCode?: string;
}

const CATEGORIES = [
  'All',
  'Maternity diagnoses',
  'Adult diagnoses',
  'Diagnoses for females only',
  'Diagnoses for males only',
  'Newborn diagnoses',
  'Pediatric diagnoses',
  'Manifestation diagnoses',
  'Mental health diagnoses',
] as const;

export default function Icd10SelectorModal({
  isOpen,
  onClose,
  onSelect,
  currentCode = '',
}: Icd10SelectorModalProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [activeCategory, setActiveCategory] = useState<string>('All');
  const [displayLimit, setDisplayLimit] = useState(50);

  const filteredCodes = useMemo(() => {
    const q = searchQuery.trim().toLowerCase();
    return ICD10_CATALOG.filter((item) => {
      const matchesCategory =
        activeCategory === 'All' || item.category === activeCategory;
      if (!matchesCategory) return false;
      if (!q) return true;
      return (
        item.code.toLowerCase().includes(q) ||
        item.description.toLowerCase().includes(q)
      );
    });
  }, [searchQuery, activeCategory]);

  if (!isOpen) return null;

  const visibleList = filteredCodes.slice(0, displayLimit);

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      role="dialog"
      style={{ backgroundColor: 'rgba(15, 23, 42, 0.65)', zIndex: 1060 }}
      onClick={(e) => {
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div className="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div className="modal-content shadow-lg border-0 rounded-3">
          <div className="modal-header bg-light py-3 border-bottom">
            <div className="d-flex align-items-center gap-2">
              <div
                className="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                style={{ width: '32px', height: '32px', fontSize: '14px' }}
              >
                <i className="bi bi-journal-medical"></i>
              </div>
              <div>
                <h5 className="modal-title fs-6 fw-bold mb-0">Official ICD-10 Diagnosis Code Directory</h5>
                <small className="text-muted" style={{ fontSize: '11px' }}>
                  Integrated Outpatient Code Editor (IOCE) • 5,974 Standard Codes
                </small>
              </div>
            </div>
            <button
              type="button"
              className="btn-close"
              aria-label="Close"
              onClick={onClose}
            ></button>
          </div>

          <div className="modal-body p-3">
            {/* Search Input */}
            <div className="position-relative mb-3">
              <i
                className="bi bi-search position-absolute text-muted"
                style={{ left: '14px', top: '50%', transform: 'translateY(-50%)' }}
              ></i>
              <input
                type="text"
                className="form-control form-control-lg ps-5 pe-5 fs-6"
                placeholder="Search by code (e.g. O82, N20.1) or clinical keyword (e.g. cesarean, calculus, hernia)..."
                value={searchQuery}
                onChange={(e) => {
                  setSearchQuery(e.target.value);
                  setDisplayLimit(50);
                }}
                autoFocus
              />
              {searchQuery && (
                <button
                  type="button"
                  className="btn btn-sm btn-link position-absolute text-muted p-0 text-decoration-none"
                  style={{ right: '14px', top: '50%', transform: 'translateY(-50%)' }}
                  onClick={() => setSearchQuery('')}
                >
                  <i className="bi bi-x-circle-fill"></i>
                </button>
              )}
            </div>

            {/* Category Filter Pills */}
            <div className="d-flex flex-wrap gap-1 mb-3 pb-2 border-bottom">
              {CATEGORIES.map((cat) => {
                const label =
                  cat === 'All'
                    ? 'All Codes'
                    : cat.replace(' diagnoses', '').replace('Diagnoses for ', '');
                const isActive = activeCategory === cat;
                return (
                  <button
                    key={cat}
                    type="button"
                    className={`btn btn-sm rounded-pill py-1 px-2 ${
                      isActive ? 'btn-primary' : 'btn-outline-secondary'
                    }`}
                    style={{ fontSize: '11.5px' }}
                    onClick={() => {
                      setActiveCategory(cat);
                      setDisplayLimit(50);
                    }}
                  >
                    {label}
                  </button>
                );
              })}
            </div>

            {/* Results Header */}
            <div className="d-flex justify-content-between align-items-center mb-2 px-1">
              <span className="text-muted" style={{ fontSize: '12px' }}>
                Showing <strong>{visibleList.length}</strong> of <strong>{filteredCodes.length}</strong> matching codes
              </span>
              {currentCode && (
                <span className="badge bg-light text-dark border" style={{ fontSize: '11px' }}>
                  Current: <strong className="text-primary">{currentCode}</strong>
                </span>
              )}
            </div>

            {/* Code List Table */}
            <div className="table-responsive border rounded" style={{ maxHeight: '380px' }}>
              <table className="table table-hover align-middle mb-0" style={{ fontSize: '12.5px' }}>
                <thead className="table-light sticky-top">
                  <tr>
                    <th style={{ width: '110px' }}>ICD-10 Code</th>
                    <th>Clinical Description</th>
                    <th style={{ width: '160px' }}>Category</th>
                    <th style={{ width: '80px' }} className="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  {visibleList.length === 0 ? (
                    <tr>
                      <td colSpan={4} className="text-center py-4 text-muted">
                        <i className="bi bi-inbox fs-4 d-block mb-1"></i>
                        No ICD-10 diagnosis codes found matching "{searchQuery}".
                      </td>
                    </tr>
                  ) : (
                    visibleList.map((item) => {
                      const isCurrent = currentCode.toUpperCase() === item.code.toUpperCase();
                      return (
                        <tr
                          key={item.code}
                          className={isCurrent ? 'table-primary' : ''}
                          style={{ cursor: 'pointer' }}
                          onClick={() => {
                            onSelect(item.code, item);
                            onClose();
                          }}
                        >
                          <td>
                            <span className="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-1">
                              {item.code}
                            </span>
                          </td>
                          <td className="fw-medium text-dark">{item.description}</td>
                          <td>
                            <span
                              className="text-muted"
                              style={{ fontSize: '11px' }}
                            >
                              {item.category.replace(' diagnoses', '')}
                            </span>
                          </td>
                          <td className="text-end">
                            <button
                              type="button"
                              className="btn btn-sm btn-outline-primary py-0 px-2"
                              style={{ fontSize: '11.5px' }}
                              onClick={(e) => {
                                e.stopPropagation();
                                onSelect(item.code, item);
                                onClose();
                              }}
                            >
                              Select
                            </button>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>

            {/* Load More Button */}
            {filteredCodes.length > visibleList.length && (
              <div className="text-center mt-2">
                <button
                  type="button"
                  className="btn btn-sm btn-link text-decoration-none"
                  style={{ fontSize: '12px' }}
                  onClick={() => setDisplayLimit((prev) => prev + 50)}
                >
                  Load next 50 codes ({filteredCodes.length - visibleList.length} remaining)...
                </button>
              </div>
            )}
          </div>

          <div className="modal-footer bg-light py-2 border-top d-flex justify-content-between">
            <small className="text-muted" style={{ fontSize: '11px' }}>
              Press <strong>Esc</strong> or click outside to dismiss
            </small>
            <button type="button" className="btn btn-sm btn-secondary" onClick={onClose}>
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

