/**
 * Reminder Card 4: Healthcare Wishes & Greetings
 * Exponit Labs - Call Reminder Card System
 * 
 * DESIGN CONTROLS:
 * Edit the THEME object below to customize background colors, accents and fonts.
 */
import { roundedRect, drawAvatar } from './shared.js';

export const WISHING_THEME = {
  bgGradient: ['#064e3b', '#0f766e', '#0F2A44'],
  doctorAccent: '#0d9488',
  quoteColor: '#334155',
  cardPlaqueBg: '#ffffff',
  ribbonColor: '#0d9488'
};

export async function drawWishingCard(ctx, W, H, data) {
  // Deep emerald/teal gradient
  const bgGrad = ctx.createLinearGradient(0, 0, W, H);
  bgGrad.addColorStop(0, WISHING_THEME.bgGradient[0]);
  bgGrad.addColorStop(0.6, WISHING_THEME.bgGradient[1]);
  bgGrad.addColorStop(1, WISHING_THEME.bgGradient[2]);
  ctx.fillStyle = bgGrad;
  ctx.fillRect(0, 0, W, H);

  // Decorative Top Accents
  ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
  ctx.beginPath();
  ctx.arc(W / 2, -100, 450, 0, Math.PI * 2);
  ctx.fill();

  // White Center Card Plaque
  const cardW = W - 140;
  const cardH = H - 240;
  roundedRect(ctx, 70, 120, cardW, cardH, 28);
  ctx.fillStyle = WISHING_THEME.cardPlaqueBg;
  ctx.shadowColor = 'rgba(0,0,0,0.2)';
  ctx.shadowBlur = 24;
  ctx.fill();

  ctx.save();
  ctx.textAlign = 'center';

  // Header inside card
  ctx.font = 'bold 15px Inter, sans-serif';
  ctx.fillStyle = '#0d9488';
  ctx.letterSpacing = '3px';
  ctx.fillText('EXPONIT LABS • HEALTHCARE GREETINGS', W / 2, 175);

  ctx.font = '900 32px Inter, sans-serif';
  ctx.fillStyle = '#0F2A44';
  ctx.fillText(data.docName, W / 2, 230);

  ctx.font = '500 16px Inter, sans-serif';
  ctx.fillStyle = '#64748b';
  ctx.fillText(data.docSpecialty, W / 2, 265);

  // Cordial Quote
  ctx.font = 'italic 20px Georgia, serif';
  ctx.fillStyle = WISHING_THEME.quoteColor;
  ctx.fillText('“Wishing you continued success, strength, and clinical excellence', W / 2, 335);
  ctx.fillText('as you touch and heal countless lives every day.”', W / 2, 368);

  // Doctor Avatar Display
  drawAvatar(ctx, data.docImg, (W - 140) / 2, 420, 140, null, null, WISHING_THEME.doctorAccent);

  // Featured Formulations Ribbon
  const ribbonY = 640;
  ctx.font = 'bold 18px Inter, sans-serif';
  ctx.fillStyle = '#0F2A44';
  ctx.fillText('COMMITTED TO PATIENT WELLNESS WITH', W / 2, ribbonY);

  const prods = (data.products && data.products.length > 0)
    ? data.products.slice(0, 3)
    : [
        { name: 'CardioGuard 50' },
        { name: 'GlycoNorm 500' }
      ];

  const prodNames = prods.map(p => p.name).join('   •   ');
  ctx.font = 'bold 22px Inter, sans-serif';
  ctx.fillStyle = WISHING_THEME.ribbonColor;
  ctx.fillText(prodNames, W / 2, ribbonY + 40);

  // MR Footer
  ctx.font = 'bold 16px Inter, sans-serif';
  ctx.fillStyle = '#64748b';
  if (data.formattedDate) {
    ctx.fillText(`With Warm Regards: ${data.mrName} • ${data.formattedDate}`, W / 2, H - 120);
  } else {
    ctx.fillText(`With Warm Regards: ${data.mrName} • Exponit Labs`, W / 2, H - 120);
  }
  ctx.restore();
}
