import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const pages = import.meta.glob('./Pages/**/*.tsx');

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
  },
  progress: {
    color: '#080808',
  },
});
