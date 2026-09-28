/**
 * Reminder Card 1: Executive Dual-Profile
 * Exponit Labs - Call Reminder Card System
 * 
 * DESIGN CONTROLS:
 * Edit the THEME object below to customize background colors, accents and fonts.
 */
import { roundedRect, drawAvatar, drawCardHeader, drawProductCard } from './shared.js';

export const EXECUTIVE_THEME = {
  bgGradient: ['#0F2A44', '#133555', '#f8fafc', '#edf2f7'],
  headerAccent: '#5eead4',
  doctorAccent: '#0d9488',
  mrAccent: '#0284c7',
  badgeBg: 'rgba(255, 255, 255, 0.12)',
  badgeBorder: 'rgba(255, 255, 255, 0.25)',
  badgeColor: '#ffffff'
};

export async function drawExecutiveCard(ctx, W, H, data) {
  // Background gradient: Navy to modern Medical Slate
  const bgGrad = ctx.createLinearGradient(0, 0, 0, H);
  bgGrad.addColorStop(0, EXECUTIVE_THEME.bgGradient[0]);
  bgGrad.addColorStop(0.35, EXECUTIVE_THEME.bgGradient[1]);
  bgGrad.addColorStop(0.35, EXECUTIVE_THEME.bgGradient[2]);
  bgGrad.addColorStop(1, EXECUTIVE_THEME.bgGradient[3]);
  ctx.fillStyle = bgGrad;
  ctx.fillRect(0, 0, W, H);

  // Decorative Top Accents
  ctx.fillStyle = 'rgba(13, 148, 136, 0.15)';
  ctx.beginPath();
  ctx.arc(W - 100, 80, 220, 0, Math.PI * 2);
  ctx.fill();

  // Top Header Banner
  const badgeText = data.formattedDate ? `VISIT: ${data.formattedDate.toUpperCase()}` : 'DOCTOR CONNECT';
  drawCardHeader(ctx, W, {
    title: 'EXPONIT LABS',
    subtitle: 'EXCELLENCE IN HEALTHCARE & THERAPEUTICS',
    badgeText: badgeText,
    badgeBg: data.formattedDate ? EXECUTIVE_THEME.badgeBg : 'rgba(13, 148, 136, 0.25)',
    badgeBorder: data.formattedDate ? EXECUTIVE_THEME.badgeBorder : '#5eead4',
    badgeColor: data.formattedDate ? EXECUTIVE_THEME.badgeColor : '#5eead4'
  });

  // Dual Profile Section (Doctor & MR)
  const avatarSize = 140;
  const avatarY = 160;

  // Doctor (Left)
  drawAvatar(ctx, data.docImg, 150, avatarY, avatarSize, data.docName, data.docSpecialty, EXECUTIVE_THEME.doctorAccent);

  // MR (Right)
  drawAvatar(ctx, data.mrImg, W - 150 - avatarSize, avatarY, avatarSize, data.mrName, data.mrTitle, EXECUTIVE_THEME.mrAccent);

  // Center Connection Ribbon
  ctx.save();
  const ribbonW = 200;
  const ribbonX = (W - ribbonW) / 2;
  const ribbonY = avatarY + 45;
  roundedRect(ctx, ribbonX, ribbonY, ribbonW, 44, 22);
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0,0,0,0.1)';
  ctx.shadowBlur = 8;
  ctx.fill();
  ctx.lineWidth = 1.5;
  ctx.strokeStyle = '#0d9488';
  ctx.stroke();

  ctx.fillStyle = '#0d9488';
  ctx.font = 'bold 14px Inter, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText('HEALTHCARE CONNECT', W / 2, ribbonY + 22);
  ctx.restore();

  // Product Showcase Section
  const prods = (data.products && data.products.length > 0)
    ? data.products.slice(0, 3)
    : [
        { name: 'CardioGuard 50', composition: 'Amlodipine 5mg + Atenolol 50mg', strength: '50mg', packaging: '10x10 Strips' },
        { name: 'GlycoNorm 500', composition: 'Metformin Hydrochloride Prolonged-Release', strength: '500mg', packaging: '15 Tablets' }
      ];

  const pSectionY = 380;
  ctx.save();
  ctx.fillStyle = '#0F2A44';
  ctx.font = 'bold 20px Inter, sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText('DISCUSSED FORMULATIONS', 64, pSectionY);

  ctx.fillStyle = '#64748b';
  ctx.font = '500 13px Inter, sans-serif';
  ctx.fillText('Premium Quality Therapeutics by Exponit Labs', 64, pSectionY + 24);
  ctx.restore();

  // Product Cards Grid
  const cardH = prods.length === 3 ? 120 : 140;
  const gap = 16;
  const startY = pSectionY + 45;

  prods.forEach((prod, idx) => {
    const cardY = startY + idx * (cardH + gap);
    drawProductCard(ctx, 64, cardY, W - 128, cardH, prod, idx === 0 ? '#0d9488' : '#2563eb');
  });

  // Bottom Footer Bar
  const footerY = H - 90;
  roundedRect(ctx, 48, footerY, W - 96, 60, 14);
  ctx.fillStyle = '#0F2A44';
  ctx.fill();

  ctx.save();
  ctx.fillStyle = '#ffffff';
  ctx.font = 'bold 15px Inter, sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText(`Presented by: ${data.mrName} • Exponit Labs`, 72, footerY + 36);

  ctx.textAlign = 'right';
  ctx.fillStyle = '#5eead4';
  ctx.font = 'bold 13px Inter, sans-serif';
  ctx.fillText('PATIENT WELLNESS FIRST', W - 72, footerY + 36);
  ctx.restore();
}
