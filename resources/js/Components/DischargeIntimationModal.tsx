import React, { useState, useEffect } from 'react';
import { buildDischargeIntimationMessage, IntimationData } from '../utils/dischargeIntimation';

interface DischargeIntimationModalProps {
  show: boolean;
  onClose: () => void;
  admission: {
    id: number;
    patient_name: string;
    uhid: string;
    ip_no: string;
    age?: string | number | null;
    sex?: string | null;
    attender_name?: string | null;
    attender_relation?: string | null;
    address_line1?: string | null;
    address_line2?: string | null;
    address_line3?: string | null;
    address?: string | null;
    contact?: string | null;
    picme_no?: string | null;
    vhn_name?: string | null;
    phc?: string | null;
    diagnosis?: string | null;
    findings?: string | null;
    procedure_done?: string | null;
    procedure_name?: string | null;
    discharge_condition?: string | null;
  };
}

export default function DischargeIntimationModal({
  show,
  onClose,
  admission,
}: DischargeIntimationModalProps) {
  const [vhnName, setVhnName] = useState<string>(admission.vhn_name || '');
  const [phcName, setPhcName] = useState<string>(admission.phc || admission.address_line2 || '');
  const [includeLink, setIncludeLink] = useState<boolean>(false);
  const [copied, setCopied] = useState<boolean>(false);
  const [linkCopied, setLinkCopied] = useState<boolean>(false);

  useEffect(() => {
    setVhnName(admission.vhn_name || '');
    setPhcName(admission.phc || admission.address_line2 || '');
    setCopied(false);
    setLinkCopied(false);
  }, [admission]);

  if (!show) return null;

  const docUrl = typeof window !== 'undefined'
    ? `${window.location.origin}/admissions/${admission.id}/documents/pdf/discharge-summary`
    : `/admissions/${admission.id}/documents/pdf/discharge-summary`;

  const intimationData: IntimationData = {
    patient_name: admission.patient_name,
    age: admission.age,
    sex: admission.sex,
    attender_name: admission.attender_name,
    attender_relation: admission.attender_relation,
    address_line1: admission.address_line1,
    address_line2: admission.address_line2,
    address_line3: admission.address_line3,
    address: admission.address,
    contact: admission.contact,
    picme_no: admission.picme_no,
    vhn_name: vhnName,
    phc: phcName,
    diagnosis: admission.diagnosis,
    findings: admission.findings,
    procedure_done: admission.procedure_done,
    procedure_name: admission.procedure_name,
    discharge_condition: admission.discharge_condition,
    include_link: includeLink,
    link_url: docUrl,
  };

  const messageText = buildDischargeIntimationMessage(intimationData);

  const handleCopyMessage = async () => {
    try {
      await navigator.clipboard.writeText(messageText);
      setCopied(true);
      setTimeout(() => setCopied(false), 3000);
    } catch {
      // Fallback
      const textArea = document.createElement('textarea');
      textArea.value = messageText;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      setCopied(true);
      setTimeout(() => setCopied(false), 3000);
    }
  };

  const handleCopyLinkOnly = async () => {
    try {
      await navigator.clipboard.writeText(docUrl);
      setLinkCopied(true);
      setTimeout(() => setLinkCopied(false), 3000);
    } catch {
      setLinkCopied(true);
      setTimeout(() => setLinkCopied(false), 3000);
    }
  };

  const handleOpenWhatsApp = () => {
    const encoded = encodeURIComponent(messageText);
    window.open(`https://wa.me/?text=${encoded}`, '_blank');
  };

  return (
    <>
      <div
        className="modal fade show d-block"
        tabIndex={-1}
        style={{ backgroundColor: 'rgba(15, 23, 42, 0.65)', backdropFilter: 'blur(4px)', zIndex: 1070 }}
        role="dialog"
      >
        <div className="modal-dialog modal-dialog-centered modal-lg">
          <div className="modal-content border-0 shadow-lg" style={{ borderRadius: '20px' }}>
            {/* Modal Header */}
            <div className="modal-header border-0 pb-0 pt-4 px-4 align-items-center justify-content-between">
              <div className="d-flex align-items-center gap-2">
                <span className="p-2 rounded-circle" style={{ backgroundColor: '#dcfce7', color: '#16a34a' }}>
                  <i className="bi bi-whatsapp fs-5"></i>
                </span>
                <div>
                  <h5 className="modal-title fw-bold text-dark mb-0" style={{ letterSpacing: '-0.01em' }}>
                    Discharge Intimation Message
                  </h5>
                  <div className="text-muted small" style={{ fontSize: '12px' }}>
                    Formatted official intimation for VHN &amp; PHC WhatsApp communication
                  </div>
                </div>
              </div>
              <button
                type="button"
                className="btn-close"
                onClick={onClose}
                aria-label="Close"
              ></button>
            </div>

            <div className="modal-body p-4">
              {/* Optional Quick Fields for VHN & PHC */}
              <div className="p-3 mb-3 bg-light rounded-3 border">
                <div className="row g-2 align-items-center">
                  <div className="col-12 col-sm-6">
                    <label className="form-label small text-muted mb-1 fw-semibold">
                      VHN Name (Village Health Nurse)
                    </label>
                    <input
                      type="text"
                      className="form-control form-control-sm"
                      placeholder="e.g. S. LAKSHMI"
                      value={vhnName}
                      onChange={(e) => setVhnName(e.target.value)}
                    />
                  </div>
                  <div className="col-12 col-sm-6">
                    <label className="form-label small text-muted mb-1 fw-semibold">
                      PHC / Primary Health Centre
                    </label>
                    <input
                      type="text"
                      className="form-control form-control-sm"
                      placeholder="e.g. PENNAGARAM PHC"
                      value={phcName}
                      onChange={(e) => setPhcName(e.target.value)}
                    />
                  </div>
                </div>
                <div className="form-check mt-2 pt-1">
                  <input
                    type="checkbox"
                    className="form-check-input"
                    id="includeLinkCheckbox"
                    checked={includeLink}
                    onChange={(e) => setIncludeLink(e.target.checked)}
                  />
                  <label className="form-check-label small text-muted" htmlFor="includeLinkCheckbox">
                    Include direct Discharge Summary PDF link in message
                  </label>
                </div>
              </div>

              {/* WhatsApp Message Preview Box */}
              <div
                className="position-relative p-3 p-md-4 rounded-3 border"
                style={{
                  backgroundColor: '#efeae2',
                  backgroundImage:
                    'radial-gradient(#d4cdc3 1px, transparent 1px), radial-gradient(#d4cdc3 1px, #efeae2 1px)',
                  backgroundSize: '24px 24px',
                  backgroundPosition: '0 0, 12px 12px',
                }}
              >
                <div
                  className="bg-white p-3 shadow-sm rounded-3 mx-auto"
                  style={{
                    maxWidth: '520px',
                    borderRadius: '12px',
                    borderTopLeftRadius: '2px',
                    whiteSpace: 'pre-wrap',
                    fontFamily: 'monospace',
                    fontSize: '13px',
                    lineHeight: '1.5',
                    color: '#111827',
                  }}
                >
                  {messageText}
                </div>
              </div>

              {/* Feedback toast */}
              {copied && (
                <div className="alert alert-success mt-3 mb-0 py-2 d-flex align-items-center gap-2">
                  <i className="bi bi-check-circle-fill text-success fs-5"></i>
                  <div>
                    <strong>Copied to clipboard!</strong> Message is ready to paste into WhatsApp.
                  </div>
                </div>
              )}

              {linkCopied && (
                <div className="alert alert-info mt-3 mb-0 py-2 d-flex align-items-center gap-2">
                  <i className="bi bi-link-45deg fs-5"></i>
                  <div>
                    <strong>Document Link copied to clipboard!</strong>
                  </div>
                </div>
              )}
            </div>

            {/* Modal Actions */}
            <div className="modal-footer border-0 pt-0 pb-4 px-4 d-flex flex-wrap justify-content-between gap-2">
              <button
                type="button"
                onClick={handleCopyLinkOnly}
                className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                style={{ borderRadius: '8px' }}
                title="Copy public link to patient's discharge summary"
              >
                <i className="bi bi-link-45deg"></i> Copy Link Only
              </button>

              <div className="d-flex align-items-center gap-2">
                <button
                  type="button"
                  onClick={handleOpenWhatsApp}
                  className="btn btn-sm btn-outline-success d-flex align-items-center gap-1"
                  style={{ borderRadius: '8px', fontWeight: 600 }}
                  title="Open WhatsApp Web / App with message"
                >
                  <i className="bi bi-whatsapp"></i> Share on WhatsApp
                </button>

                <button
                  type="button"
                  onClick={handleCopyMessage}
                  className="btn btn-sm btn-success d-flex align-items-center gap-1 shadow-sm px-3"
                  style={{ borderRadius: '8px', fontWeight: 600 }}
                  title="Copy formatted discharge intimation message"
                >
                  <i className={`bi ${copied ? 'bi-check2' : 'bi-clipboard-check'}`}></i>
                  <span>{copied ? 'Copied Message!' : 'Copy Message'}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
