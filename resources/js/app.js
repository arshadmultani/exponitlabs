import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

// Public site interactions (mobile nav, scroll-reveal). Filament ships its own
// Alpine scoped to /console, so the public pages bundle their own here.
Alpine.plugin(intersect);
window.Alpine = Alpine;
Alpine.start();
