export default function OrbitalBackground() {
  return (
    <div className="fg-orbital-rings-bg" aria-hidden="true">
      {/* Concentric faint orbital rings matching FinGM reference */}
      <div className="fg-orbit-ring ring-1" style={{ width: '380px', height: '380px', borderColor: '#e2e8f0' }} />
      <div className="fg-orbit-ring ring-2" style={{ width: '560px', height: '560px', borderColor: 'rgba(226, 232, 240, 0.7)' }} />
      <div className="fg-orbit-ring ring-3" style={{ width: '740px', height: '740px', borderColor: 'rgba(226, 232, 240, 0.45)' }} />

      {/* Floating circular chips positioned on the orbital lines as in FinGM */}
      {/* Top Left: Purple Users chip */}
      <div
        className="fg-orbit-badge"
        style={{ top: '18%', left: '22%', color: '#7c3aed' }}
        title="Staff & Patients"
      >
        <i className="bi bi-people-fill"></i>
      </div>

      {/* Mid Left: Blue Database / Storage chip */}
      <div
        className="fg-orbit-badge"
        style={{ top: '48%', left: '12%', transform: 'translateY(-50%)', color: '#2563eb' }}
        title="Clinical Database"
      >
        <i className="bi bi-database-fill"></i>
      </div>

      {/* Top Right: Orange Currency / Vitals chip */}
      <div
        className="fg-orbit-badge"
        style={{ top: '18%', right: '22%', color: '#f59e0b', fontWeight: 'bold' }}
        title="Vitals & Billing"
      >
        <span style={{ fontSize: '15px' }}>₹</span>
      </div>

      {/* Mid Right: Red Document / Summary chip */}
      <div
        className="fg-orbit-badge"
        style={{ top: '48%', right: '12%', transform: 'translateY(-50%)', color: '#ef4444' }}
        title="Discharge Summaries"
      >
        <i className="bi bi-file-earmark-medical-fill"></i>
      </div>

      {/* Bottom Right: Blue Bar Chart chip */}
      <div
        className="fg-orbit-badge"
        style={{ bottom: '16%', right: '20%', color: '#3b82f6' }}
        title="Hospital Analytics"
      >
        <i className="bi bi-bar-chart-fill"></i>
      </div>
    </div>
  );
}
