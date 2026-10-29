export default function OrbitalBackground() {
  return (
    <div className="fg-orbital-rings-bg" aria-hidden="true">
      <div className="fg-ambient-glow" />
      <div className="fg-orbit-ring ring-1" />
      <div className="fg-orbit-ring ring-2" />
      <div className="fg-orbit-ring ring-3" />
      <div className="fg-orbit-ring ring-4" />

      <svg className="fg-orbit-arcs-svg" viewBox="0 0 1000 1000" fill="none">
        <circle cx="500" cy="500" r="280" stroke="#245dff" strokeWidth="1.5" strokeDasharray="30 450" strokeLinecap="round" opacity="0.6" />
        <circle cx="500" cy="500" r="390" stroke="#38bdf8" strokeWidth="1.5" strokeDasharray="45 600" strokeLinecap="round" opacity="0.7" />
        <circle cx="500" cy="500" r="480" stroke="#245dff" strokeWidth="1.5" strokeDasharray="60 800" strokeLinecap="round" opacity="0.5" />
      </svg>

      {/* Floating Healthcare & Governance Badges */}
      <div className="fg-orbit-badge badge-top-left" style={{ color: '#0d9488' }} title="Clinical Diagnosis">
        <i className="bi bi-heart-pulse-fill"></i>
      </div>
      <div className="fg-orbit-badge badge-top-right" style={{ color: '#2563eb' }} title="Operation Theatre">
        <i className="bi bi-hospital"></i>
      </div>
      <div className="fg-orbit-badge badge-mid-left" style={{ color: '#16a34a' }} title="Discharge Summary">
        <i className="bi bi-file-earmark-medical"></i>
      </div>
      <div className="fg-orbit-badge badge-mid-right" style={{ color: '#7c3aed' }} title="MRD Records">
        <i className="bi bi-folder-check"></i>
      </div>
      <div className="fg-orbit-badge badge-bottom-left" style={{ color: '#e11d48' }} title="Tamper-Resistant Audit">
        <i className="bi bi-shield-check"></i>
      </div>
      <div className="fg-orbit-badge badge-bottom-right" style={{ color: '#f59e0b' }} title="Bilingual Formats">
        <i className="bi bi-translate"></i>
      </div>
    </div>
  );
}

