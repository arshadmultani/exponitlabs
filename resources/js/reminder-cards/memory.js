/**
 * Reminder Card 3: Meeting Memory & Calendar Card
 * Exponit Labs - Call Reminder Card System
 * 
 * DESIGN CONTROLS:
 * Edit the THEME object below to customize background colors, accents and fonts.
 */
import { roundedRect, drawAvatar } from './shared.js';
import { parseDateParts } from './text-engine.js';

export const MEMORY_THEME = {
  bg: '#fafaf9',
  outerBorderColor: '#e2e8f0',
  innerBorderColor: '#0d9488',
  calendarHeaderBg: '#e11d48',
  greetingHeaderBg: '#0d9488',
  headlineColor: '#0F2A44',
  quoteColor: '#475569'
};

export async function drawMemoryCard(ctx, W, H, data) {
  // Warm, premium stationery aesthetic
  ctx.fillStyle = MEMORY_THEME.bg;
  ctx.fillRect(0, 0, W, H);

  // Outer border frame
  ctx.lineWidth = 12;
  ctx.strokeStyle = MEMORY_THEME.outerBorderColor;
  ctx.strokeRect(32, 32, W - 64, H - 64);

  ctx.lineWidth = 2;
  ctx.strokeStyle = MEMORY_THEME.innerBorderColor;
  ctx.strokeRect(48, 48, W - 96, H - 96);

  // Calendar Icon or Greeting Widget
  const calX = 80;
  const calY = 80;
  const calW = 120;
  const calH = 130;

  roundedRect(ctx, calX, calY, calW, calH, 14);
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0,0,0,0.12)';
  ctx.shadowBlur = 10;
  ctx.fill();
  ctx.stroke();

  if (data.formattedDate) {
    // Red Calendar Header
    roundedRect(ctx, calX, calY, calW, 36, [14, 14, 0, 0]);
    ctx.fillStyle = MEMORY_THEME.calendarHeaderBg;
    ctx.fill();

    const dateParts = parseDateParts(data.rawDate || data.formattedDate);
    const shortMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    const monthStr = shortMonths[dateParts.monthIdx] || 'VISIT';
    const dayStr = String(dateParts.day || '20');

    ctx.save();
    ctx.textAlign = 'center';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 15px Inter, sans-serif';
    ctx.fillText(monthStr, calX + calW / 2, calY + 24);

    ctx.fillStyle = '#0F2A44';
    ctx.font = '900 48px Inter, sans-serif';
    ctx.fillText(dayStr, calX + calW / 2, calY + 98);
    ctx.restore();
  } else {
    // Teal Greeting Badge when no visit date is specified
    roundedRect(ctx, calX, calY, calW, 36, [14, 14, 0, 0]);
    ctx.fillStyle = MEMORY_THEME.greetingHeaderBg;
    ctx.fill();

    ctx.save();
    ctx.textAlign = 'center';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 14px Inter, sans-serif';
    ctx.fillText('CONNECT', calX + calW / 2, calY + 24);

    ctx.fillStyle = '#0d9488';
    ctx.font = '900 44px Inter, sans-serif';
    ctx.fillText('Rx', calX + calW / 2, calY + 96);
    ctx.restore();
  }

  // Headline
  ctx.save();
  ctx.textAlign = 'left';
  ctx.fillStyle = MEMORY_THEME.headlineColor;
  ctx.font = '900 36px Inter, sans-serif';
  ctx.fillText(data.formattedDate ? 'Pleasure Meeting You!' : 'Warm Professional Greetings!', 230, 115);

  ctx.font = 'bold 22px Inter, sans-serif';
  ctx.fillStyle = '#0d9488';
  ctx.fillText(data.docName, 230, 155);

  ctx.font = '500 16px Inter, sans-serif';
  ctx.fillStyle = '#64748b';
  ctx.fillText(`${data.clinic ? data.clinic + ' • ' : ''}${data.docSpecialty}`, 230, 190);
  ctx.restore();

  // Doctor & MR side-by-side photo badges
  drawAvatar(ctx, data.docImg, 180, 260, 150, data.docName, 'Respected Doctor', '#0d9488');
  drawAvatar(ctx, data.mrImg, W - 180 - 150, 260, 150, data.mrName, 'Medical Representative', '#0284c7');

  // Key Products Discussed Box
  const pBoxY = 520;
  roundedRect(ctx, 80, pBoxY, W - 160, 340, 20);
  ctx.fillStyle = '#ffffff';
  ctx.fill();
  ctx.lineWidth = 1;
  ctx.strokeStyle = '#e2e8f0';
  ctx.stroke();

  ctx.save();
  ctx.font = 'bold 20px Inter, sans-serif';
  ctx.fillStyle = '#0F2A44';
  ctx.textAlign = 'left';
  ctx.fillText('FORMULATIONS & SAMPLE HIGHLIGHTS', 110, pBoxY + 45);

  const prods = (data.products && data.products.length > 0)
    ? data.products.slice(0, 3)
    : [
        { name: 'CardioGuard 50', composition: 'Amlodipine 5mg + Atenolol 50mg' },
        { name: 'GlycoNorm 500', composition: 'Metformin Hydrochloride Prolonged-Release' }
      ];

  prods.forEach((p, i) => {
    const itemY = pBoxY + 80 + i * 75;
    ctx.beginPath();
    ctx.arc(120, itemY + 20, 6, 0, Math.PI * 2);
    ctx.fillStyle = '#0d9488';
    ctx.fill();

    ctx.font = 'bold 18px Inter, sans-serif';
    ctx.fillStyle = '#1e293b';
    ctx.fillText(p.name, 140, itemY + 24);

    if (p.composition) {
      ctx.font = '500 13px Inter, sans-serif';
      ctx.fillStyle = '#64748b';
      ctx.fillText(p.composition, 140, itemY + 46);
    }
  });
  ctx.restore();

  // Signature note
  ctx.save();
  ctx.textAlign = 'center';
  ctx.font = 'italic 18px Georgia, serif';
  ctx.fillStyle = MEMORY_THEME.quoteColor;
  ctx.fillText(`“Looking forward to our continued partnership in patient health.” — ${data.mrName}`, W / 2, H - 110);

  ctx.font = 'bold 14px Inter, sans-serif';
  ctx.fillStyle = '#0d9488';
  ctx.fillText('EXPONIT LABS PVT. LTD.', W / 2, H - 75);
  ctx.restore();
}
