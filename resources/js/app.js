import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import { registerMrComponents } from './mr-app.js';

// Public site interactions (mobile nav, scroll-reveal). Filament ships its own
// Alpine scoped to /console, so the public pages bundle their own here.
Alpine.plugin(intersect);

registerMrComponents(Alpine);

window.Alpine = Alpine;
Alpine.start();

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(err => {
      console.warn('ServiceWorker registration failed: ', err);
    });
  });
}
