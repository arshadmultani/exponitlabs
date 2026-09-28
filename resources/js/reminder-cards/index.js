/**
 * Reminder Cards Registry & Canvas Rendering Coordinator
 * Exponit Labs - Call Reminder Card System
 */
import { loadImage } from './shared.js';
import { formatOrdinalDate } from './text-engine.js';
import { drawExecutiveCard } from './executive.js';
import { drawSpotlightCard } from './spotlight.js';
import { drawMemoryCard } from './memory.js';
import { drawWishingCard } from './wishing.js';
import { drawCelebrationCard } from './celebration.js';

export const IMAGE_TEMPLATES = [
  {
    id: 'executive',
    name: 'Executive Dual-Profile',
    description: 'Doctor & MR photos with product cards and company branding',
    badge: 'Popular',
    icon: 'briefcase'
  },
  {
    id: 'spotlight',
    name: 'Clinical Product Spotlight',
    description: 'Prominent focus on 2-3 detailed products & compositions',
    badge: 'Detailed',
    icon: 'sparkles'
  },
  {
    id: 'memory',
    name: 'Meeting Memory & Calendar',
    description: 'Calendar date stamp or greeting badge with visit memory',
    badge: 'Visit Date',
    icon: 'calendar'
  },
  {
    id: 'wishing',
    name: 'Healthcare Wishes & Greeting',
    description: 'Elegant healthcare gradient with cordial wishing & products',
    badge: 'Cordial',
    icon: 'heart'
  },
  {
    id: 'celebration',
    name: 'Celebration & Festival Greeting',
    description: 'Festive gold & navy aesthetic for birthdays, national days & events',
    badge: 'Occasions',
    icon: 'gift'
  }
];

// Card rendering dispatch table
const CARD_RENDERERS = {
  executive: drawExecutiveCard,
  spotlight: drawSpotlightCard,
  memory: drawMemoryCard,
  wishing: drawWishingCard,
  celebration: drawCelebrationCard
};

/**
 * Render High-Res 1080x1080 Reminder Card to Canvas
 */
export async function renderReminderCanvas(canvas, options = {}) {
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const W = 1080;
  const H = 1080;
  canvas.width = W;
  canvas.height = H;

  const {
    templateId = 'executive',
    doctor = {},
    mr = {},
    products = [],
    visitDate = '',
    showDoctorPhoto = true,
    showMrPhoto = true
  } = options;

  // Asynchronously load available photos
  const [docImg, mrImg, productImages] = await Promise.all([
    showDoctorPhoto ? loadImage(doctor.profile_photo_url || doctor.photo) : null,
    showMrPhoto ? loadImage(mr.photo || mr.profile_photo_url) : null,
    Promise.all((products || []).slice(0, 3).map(p => loadImage(p.image_url || p.image_path)))
  ]);

  // Clean doctor name
  let docName = (doctor.name || 'Doctor').trim();
  if (!docName.toLowerCase().startsWith('dr.') && !docName.toLowerCase().startsWith('dr ')) {
    docName = 'Dr. ' + docName;
  }
  const docSpecialty = doctor.specialty ? `${doctor.specialty}${doctor.town ? ' • ' + doctor.town : ''}` : (doctor.clinic_name || 'Healthcare Specialist');

  const mrName = mr.name || 'Exponit Representative';
  const mrTitle = mr.title || 'Territory Manager • Exponit Labs';
  const formattedDate = visitDate ? formatOrdinalDate(visitDate) : '';

  // Clear canvas before painting
  ctx.clearRect(0, 0, W, H);

  // Render selected card layout
  const renderer = CARD_RENDERERS[templateId] || CARD_RENDERERS.executive;
  await renderer(ctx, W, H, {
    docName,
    docSpecialty,
    docImg,
    mrName,
    mrTitle,
    mrImg,
    products,
    productImages,
    formattedDate,
    rawDate: visitDate,
    clinic: doctor.clinic_name || doctor.town
  });
}

// Re-export all sub-modules for convenient access
export * from './shared.js';
export * from './text-engine.js';
export * from './dispatcher.js';
export { drawExecutiveCard } from './executive.js';
export { drawSpotlightCard } from './spotlight.js';
export { drawMemoryCard } from './memory.js';
export { drawWishingCard } from './wishing.js';
export { drawCelebrationCard } from './celebration.js';
