import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import '../css/guru-theme.css';

const pages = import.meta.glob('./Pages/**/*.tsx');

const CORE_PRELOAD_PAGES = [
  './Pages/Dashboard.tsx',
  './Pages/Admissions/Index.tsx',
  './Pages/Admissions/Create.tsx',
  './Pages/Admissions/Edit.tsx',
  './Pages/Admissions/Surgery.tsx',
  './Pages/Admissions/TestReports.tsx',
  './Pages/Admissions/Discharge.tsx',
  './Pages/Documents/Pack.tsx',
];

function scheduleCoreChunkPreload(): void {
  if (typeof window === 'undefined') return;
  const runPreload = () => {
    for (const key of CORE_PRELOAD_PAGES) {
      const loader = pages[key];
      if (loader) {
        loader().catch(() => {});
      }
    }
  };

  const w = window as Window & {
    requestIdleCallback?: (cb: () => void, opts?: { timeout: number }) => number;
  };
  if (typeof w.requestIdleCallback === 'function') {
    w.requestIdleCallback(runPreload, { timeout: 1500 });
  } else {
    window.setTimeout(runPreload, 400);
  }
}

createInertiaApp({
  title: (title) => (title ? `${title} — Guru Hospital` : 'Guru Hospital — Clinical Document Workflow'),
  resolve: async (name) => {
    const loader = pages[`./Pages/${name}.tsx`];
    if (!loader) {
      throw new Error(`Inertia page not found: ./Pages/${name}.tsx`);
    }
    return await loader();
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
    scheduleCoreChunkPreload();
  },
  progress: {
    color: '#080808',
    delay: 120,
    showSpinner: false,
  },
});
