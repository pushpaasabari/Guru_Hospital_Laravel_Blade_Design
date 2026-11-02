import React from 'react';
import { Link } from '@inertiajs/react';

export interface AdmissionStageNavProps {
  currentStage: 'admission' | 'surgery' | 'test-reports' | 'discharge';
  admission?: {
    id: number;
    patient_name: string;
    uhid: string;
    ip_no: string;
    age?: string | null;
    sex?: string | null;
    format?: string;
    surgery_type?: string;
    status?: string;
    procedure_name?: string | null;
    investigations?: string | null;
    discharge_at?: string | null;
  } | null;
}

export default function AdmissionStageNav({ currentStage, admission }: AdmissionStageNavProps) {
  const stages = [
    {
      id: 'admission',
      step: 1,
      name: 'Admission',
      subtitle: 'Demographics & Case',
      href: admission ? `/admissions/${admission.id}/edit` : undefined,
      isCompleted: Boolean(admission?.uhid && admission?.ip_no),
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      ),
    },
    {
      id: 'surgery',
      step: 2,
      name: 'Labour / LSCS / Surgery',
      subtitle: 'OT Team, Notes & Newborns',
      href: admission ? `/admissions/${admission.id}/surgery` : undefined,
      isCompleted: Boolean(admission?.procedure_name || admission?.surgery_type === 'delivery'),
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L9.88 9.88m4.241 4.241L9.88 9.88" />
        </svg>
      ),
    },
    {
      id: 'test-reports',
      step: 3,
      name: 'Test Reports',
      subtitle: 'Lab Tests & Diagnostics',
      href: admission ? `/admissions/${admission.id}/test-reports` : undefined,
      isCompleted: Boolean(admission?.investigations && admission.investigations.trim().length > 0),
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
      ),
    },
    {
      id: 'discharge',
      step: 4,
      name: 'Discharge',
      subtitle: 'Summary, Advice & MRD',
      href: admission ? `/admissions/${admission.id}/discharge` : undefined,
      isCompleted: Boolean(admission?.discharge_at),
      icon: (
        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
    },
  ];

  return (
    <div className="mb-6 space-y-3">
      {/* Patient Clinical Banner (When editing existing admission) */}
      {admission && (
        <div className="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
              {admission.patient_name ? admission.patient_name.charAt(0).toUpperCase() : 'P'}
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h2 className="text-lg font-bold text-slate-800">{admission.patient_name || 'Patient Record'}</h2>
                {admission.sex && (
                  <span className="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">
                    {admission.age ? `${admission.age} Yrs / ` : ''}{admission.sex}
                  </span>
                )}
                <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/60 uppercase">
                  {admission.status || 'Draft'}
                </span>
              </div>
              <div className="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1 font-mono">
                <span><strong className="text-slate-600 font-sans">UHID:</strong> {admission.uhid}</span>
                <span>&bull;</span>
                <span><strong className="text-slate-600 font-sans">IP No:</strong> {admission.ip_no}</span>
                <span>&bull;</span>
                <span className="uppercase font-sans font-medium px-2 py-0.5 bg-slate-100 rounded text-slate-700">
                  {admission.format || 'General'}
                </span>
                <span className="uppercase font-sans font-medium px-2 py-0.5 bg-sky-50 text-sky-800 rounded">
                  {admission.surgery_type === 'delivery' ? 'Delivery / Obstetric' : 'Other Surgery'}
                </span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Link
              href={`/admissions/${admission.id}/documents/preview`}
              className="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300/80 transition shadow-xs"
            >
              <svg className="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              View Document Pack
            </Link>
          </div>
        </div>
      )}

      {/* 4-Stage Navigation Bar */}
      <nav aria-label="Admission Stage Workflow" className="bg-white rounded-xl border border-slate-200/80 p-2 shadow-sm">
        <ol className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
          {stages.map((stage) => {
            const isActive = currentStage === stage.id;
            const isClickable = Boolean(stage.href);

            const content = (
              <div
                className={`flex items-center gap-3 p-3 rounded-lg border transition-all ${
                  isActive
                    ? 'bg-sky-50/80 border-sky-400 text-sky-950 shadow-xs ring-1 ring-sky-300'
                    : isClickable
                    ? 'bg-slate-50/50 border-slate-200/70 text-slate-600 hover:bg-slate-100/70 hover:border-slate-300'
                    : 'bg-slate-50/30 border-dashed border-slate-200 text-slate-400 cursor-not-allowed opacity-60'
                }`}
              >
                <div
                  className={`w-9 h-9 rounded-lg flex items-center justify-center shrink-0 font-bold text-sm ${
                    isActive
                      ? 'bg-sky-600 text-white shadow-sm'
                      : stage.isCompleted
                      ? 'bg-emerald-500 text-white'
                      : 'bg-slate-200 text-slate-600'
                  }`}
                >
                  {stage.isCompleted && !isActive ? (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                  ) : (
                    stage.step
                  )}
                </div>

                <div className="min-w-0 flex-1">
                  <div className="flex items-center justify-between gap-1">
                    <p className={`text-xs font-bold truncate ${isActive ? 'text-sky-900' : 'text-slate-800'}`}>
                      {stage.name}
                    </p>
                    {isActive && (
                      <span className="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-sky-200/70 text-sky-800">
                        Active
                      </span>
                    )}
                  </div>
                  <p className="text-[11px] text-slate-500 truncate">{stage.subtitle}</p>
                </div>
              </div>
            );

            return (
              <li key={stage.id} className="relative">
                {isClickable ? (
                  <Link href={stage.href!} className="block focus:outline-hidden">
                    {content}
                  </Link>
                ) : (
                  content
                )}
              </li>
            );
          })}
        </ol>
      </nav>
    </div>
  );
}
