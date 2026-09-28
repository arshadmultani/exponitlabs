/**
 * Reminder Card 2: Clinical Product Spotlight
 * Exponit Labs - Call Reminder Card System
 * 
 * DESIGN CONTROLS:
 * Edit the THEME object below to customize background colors, accents and fonts.
 */
import { roundedRect, drawCardHeader, drawProductCard } from './shared.js';

export const SPOTLIGHT_THEME = {
  headerBg: ['#0f766e', '#0F2A44'],
  bodyBg: '#f8fafc',
  primaryAccent: '#0d9488',
  secondaryAccent: '#2563eb',
  doctorNameColor: '#ffffff',
  doctorSpecialtyColor: '#a5f3fc'
};

export async function drawSpotlightCard(ctx, W, H, data) {
  // Split Background: Dark Medical Header (Top 220px) + Clean Slate Body
  ctx.fillStyle = SPOTLIGHT_THEME.bodyBg;
  ctx.fillRect(0, 0, W, H);

  const headerH = 220;
  const headerGrad = ctx.createLinearGradient(0, 0, W, headerH);
  headerGrad.addColorStop(0, SPOTLIGHT_THEME.headerBg[0]);
  headerGrad.addColorStop(1, SPOTLIGHT_THEME.headerBg[1]);
  ctx.fillStyle = headerGrad;
  ctx.fillRect(0, 0, W, headerH);

  // Top Branding
  drawCardHeader(ctx, W, {
    title: 'EXPONIT LABS',
    subtitle: 'CLINICAL FORMULATION SPOTLIGHT',
    badgeText: 'PRODUCT COMPLIANCE',
    badgeBg: 'rgba(255, 255, 255, 0.15)',
    badgeBorder: 'rgba(255, 255, 255, 0.3)',
    badgeColor: '#ffffff'
  });

  // Doctor Details inside Header
  ctx.save();
  ctx.textAlign = 'left';
  ctx.fillStyle = SPOTLIGHT_THEME.doctorNameColor;
  ctx.font = 'bold 24px Inter, sans-serif';
  ctx.fillText(`Prepared for ${data.docName}`, 64, 135);

  ctx.font = '500 15px Inter, sans-serif';
  ctx.fillStyle = SPOTLIGHT_THEME.doctorSpecialtyColor;
  ctx.fillText(data.docSpecialty, 64, 165);
  ctx.restore();

  // Hero Spotlight Products
  const prods = (data.products && data.products.length > 0)
    ? data.products.slice(0, 3)
    : [
        { name: 'CardioGuard 50', composition: 'Amlodipine 5mg + Atenolol 50mg', strength: '50mg', packaging: '10x10 Strips' },
        { name: 'GlycoNorm 500', composition: 'Metformin Hydrochloride Prolonged-Release', strength: '500mg', packaging: '15 Tablets' }
      ];

  const cardH = prods.length === 3 ? 165 : 200;
  const gap = 20;
  const startY = 240;

  prods.forEach((prod, idx) => {
    const cardY = startY + idx * (cardH + gap);
    drawProductCard(ctx, 48, cardY, W - 96, cardH, prod, idx === 0 ? SPOTLIGHT_THEME.primaryAccent : SPOTLIGHT_THEME.secondaryAccent);
  });

  // Bottom MR Signature & Date Bar
  const bottomY = H - 120;
  roundedRect(ctx, 48, bottomY, W - 96, 80, 16);
  ctx.fillStyle = '#0F2A44';
  ctx.fill();

  ctx.save();
  ctx.fillStyle = '#ffffff';
  ctx.font = 'bold 18px Inter, sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText(data.mrName, 76, bottomY + 34);

  ctx.font = '500 14px Inter, sans-serif';
  ctx.fillStyle = '#94a3b8';
  ctx.fillText(data.mrTitle, 76, bottomY + 58);

  ctx.textAlign = 'right';
  ctx.font = 'bold 16px Inter, sans-serif';
  if (data.formattedDate) {
    ctx.fillStyle = '#5eead4';
    ctx.fillText(`VISITED ON: ${data.formattedDate}`, W - 76, bottomY + 46);
  } else {
    ctx.fillStyle = '#94a3b8';
    ctx.fillText('EXPONIT LABS • THERAPEUTICS', W - 76, bottomY + 46);
  }
  ctx.restore();
}
