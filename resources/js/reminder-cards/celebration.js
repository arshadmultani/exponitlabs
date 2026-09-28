/**
 * Reminder Card 5: Celebration & Festival Greeting
 * Exponit Labs - Call Reminder Card System
 * 
 * DESIGN CONTROLS:
 * Edit the THEME object below to customize background colors, accents and fonts.
 */
import { roundedRect, drawAvatar } from './shared.js';

export const CELEBRATION_THEME = {
  bgGradient: ['#0a192f', '#0F2A44', '#064e3b'],
  goldAccent: '#f59e0b',
  goldText: '#fbbf24',
  quoteColor: '#f8fafc',
  avatarGlow: '#f59e0b',
  plaqueBorder: 'rgba(245, 158, 11, 0.35)',
  productColor: '#5eead4'
};

export async function drawCelebrationCard(ctx, W, H, data) {
  // Deep royal celebratory gradient: Midnight Navy with rich Imperial Blue
  const bgGrad = ctx.createLinearGradient(0, 0, W, H);
  bgGrad.addColorStop(0, CELEBRATION_THEME.bgGradient[0]);
  bgGrad.addColorStop(0.4, CELEBRATION_THEME.bgGradient[1]);
  bgGrad.addColorStop(1, CELEBRATION_THEME.bgGradient[2]);
  ctx.fillStyle = bgGrad;
  ctx.fillRect(0, 0, W, H);

  // Decorative Golden Sparkle Accents
  ctx.save();
  const sparkles = [
    { x: 120, y: 140, r: 4 }, { x: 260, y: 80, r: 6 },
    { x: W - 140, y: 160, r: 5 }, { x: W - 280, y: 90, r: 4 },
    { x: 100, y: 450, r: 5 }, { x: W - 100, y: 480, r: 6 },
    { x: 180, y: 720, r: 4 }, { x: W - 180, y: 740, r: 5 }
  ];
  sparkles.forEach(s => {
    ctx.beginPath();
    ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(245, 158, 11, 0.45)';
    ctx.shadowColor = CELEBRATION_THEME.goldAccent;
    ctx.shadowBlur = 12;
    ctx.fill();
  });
  ctx.restore();

  // Top Header Banner
  ctx.save();
  ctx.textAlign = 'left';
  ctx.textBaseline = 'top';
  ctx.fillStyle = '#ffffff';
  ctx.font = '900 32px Inter, system-ui, sans-serif';
  ctx.fillText('EXPONIT LABS', 64, 48);

  ctx.font = '600 13px Inter, system-ui, sans-serif';
  ctx.fillStyle = CELEBRATION_THEME.goldText;
  ctx.letterSpacing = '2px';
  ctx.fillText('HEALTHCARE EXCELLENCE & HONORED PARTNERSHIP', 64, 88);

  // Top-Right Badge
  const badgeText = data.formattedDate ? `DATE: ${data.formattedDate.toUpperCase()}` : 'CELEBRATION GREETINGS';
  ctx.font = 'bold 13px Inter, sans-serif';
  const badgeW = ctx.measureText(badgeText).width + 30;
  roundedRect(ctx, W - 64 - badgeW, 52, badgeW, 36, 18);
  ctx.fillStyle = 'rgba(245, 158, 11, 0.15)';
  ctx.fill();
  ctx.lineWidth = 1.5;
  ctx.strokeStyle = CELEBRATION_THEME.goldAccent;
  ctx.stroke();
  ctx.fillStyle = CELEBRATION_THEME.goldText;
  ctx.textAlign = 'center';
  ctx.fillText(badgeText, W - 64 - (badgeW / 2), 64);
  ctx.restore();

  // Centered Doctor Avatar with Glowing Gold Ring
  const avatarSize = 170;
  const avatarX = (W - avatarSize) / 2;
  const avatarY = 160;

  // Glow ring
  ctx.save();
  ctx.beginPath();
  ctx.arc(avatarX + avatarSize / 2, avatarY + avatarSize / 2, (avatarSize / 2) + 8, 0, Math.PI * 2);
  ctx.lineWidth = 4;
  ctx.strokeStyle = CELEBRATION_THEME.avatarGlow;
  ctx.shadowColor = CELEBRATION_THEME.avatarGlow;
  ctx.shadowBlur = 20;
  ctx.stroke();
  ctx.restore();

  drawAvatar(ctx, data.docImg, avatarX, avatarY, avatarSize, null, null, CELEBRATION_THEME.goldAccent);

  // Doctor Name & Specialty
  ctx.save();
  ctx.textAlign = 'center';
  ctx.fillStyle = '#ffffff';
  ctx.font = '900 32px Inter, sans-serif';
  ctx.fillText(data.docName, W / 2, 385);

  ctx.font = '500 18px Inter, sans-serif';
  ctx.fillStyle = '#93c5fd';
  ctx.fillText(data.docSpecialty, W / 2, 418);
  ctx.restore();

  // Celebratory Plaque
  const plaqueY = 470;
  const plaqueW = W - 140;
  const plaqueH = 340;
  roundedRect(ctx, 70, plaqueY, plaqueW, plaqueH, 24);
  ctx.fillStyle = 'rgba(255, 255, 255, 0.06)';
  ctx.fill();
  ctx.lineWidth = 1;
  ctx.strokeStyle = CELEBRATION_THEME.plaqueBorder;
  ctx.stroke();

  ctx.save();
  ctx.textAlign = 'center';

  // Golden Banner Text
  ctx.font = '900 24px Inter, sans-serif';
  ctx.fillStyle = CELEBRATION_THEME.goldText;
  ctx.fillText('WARMEST WISHES & SINCERE REGARDS', W / 2, plaqueY + 50);

  // Quote
  ctx.font = 'italic 20px Georgia, serif';
  ctx.fillStyle = CELEBRATION_THEME.quoteColor;
  ctx.fillText('“Wishing you boundless health, joy, and continued distinction', W / 2, plaqueY + 105);
  ctx.fillText('in your tireless and noble dedication to healing lives.”', W / 2, plaqueY + 140);

  // Divider
  ctx.beginPath();
  ctx.moveTo(W / 2 - 120, plaqueY + 175);
  ctx.lineTo(W / 2 + 120, plaqueY + 175);
  ctx.strokeStyle = 'rgba(245, 158, 11, 0.4)';
  ctx.lineWidth = 1;
  ctx.stroke();

  // Products or Brand Statement
  if (data.products && data.products.length > 0) {
    ctx.font = 'bold 15px Inter, sans-serif';
    ctx.fillStyle = '#cbd5e1';
    ctx.fillText('EXPONIT HEALTHCARE FORMULATIONS', W / 2, plaqueY + 220);

    const pNames = data.products.slice(0, 3).map(p => p.name).join('   •   ');
    ctx.font = 'bold 20px Inter, sans-serif';
    ctx.fillStyle = CELEBRATION_THEME.productColor;
    ctx.fillText(pNames, W / 2, plaqueY + 260);
  } else {
    ctx.font = 'bold 18px Inter, sans-serif';
    ctx.fillStyle = CELEBRATION_THEME.productColor;
    ctx.fillText('Committed to Excellence in Patient Wellness', W / 2, plaqueY + 235);

    ctx.font = '500 15px Inter, sans-serif';
    ctx.fillStyle = '#94a3b8';
    ctx.fillText('Exponit Labs Pvt. Ltd. • Delivering Superior Therapeutics', W / 2, plaqueY + 270);
  }
  ctx.restore();

  // Bottom MR Signature
  const bottomY = H - 120;
  roundedRect(ctx, 70, bottomY, W - 140, 72, 16);
  ctx.fillStyle = 'rgba(15, 23, 42, 0.75)';
  ctx.fill();
  ctx.lineWidth = 1;
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
  ctx.stroke();

  ctx.save();
  ctx.fillStyle = '#ffffff';
  ctx.font = 'bold 17px Inter, sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText(data.mrName, 100, bottomY + 32);

  ctx.font = '500 13px Inter, sans-serif';
  ctx.fillStyle = '#94a3b8';
  ctx.fillText(data.mrTitle, 100, bottomY + 54);

  ctx.textAlign = 'right';
  ctx.font = 'bold 15px Inter, sans-serif';
  ctx.fillStyle = CELEBRATION_THEME.goldText;
  ctx.fillText('EXPONIT LABS', W - 100, bottomY + 44);
  ctx.restore();
}
