/**
 * Shared Canvas Primitives & Reusable Component Helpers
 * Exponit Labs - Call Reminder Card System
 */

/**
 * Asynchronously load an image URL safely with CORS support
 */
export function loadImage(src) {
  return new Promise((resolve) => {
    if (!src) return resolve(null);
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => resolve(img);
    img.onerror = () => resolve(null);
    img.src = src;
  });
}

/**
 * Draw a rectangle with rounded corners
 */
export function roundedRect(ctx, x, y, width, height, radius) {
  ctx.beginPath();
  if (typeof radius === 'number') {
    radius = { tl: radius, tr: radius, br: radius, bl: radius };
  } else if (Array.isArray(radius)) {
    radius = { tl: radius[0], tr: radius[1], br: radius[2], bl: radius[3] };
  } else {
    radius = { tl: 0, tr: 0, br: 0, bl: 0, ...radius };
  }

  ctx.moveTo(x + radius.tl, y);
  ctx.lineTo(x + width - radius.tr, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
  ctx.lineTo(x + width, y + height - radius.br);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
  ctx.lineTo(x + radius.bl, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
  ctx.lineTo(x, y + radius.tl);
  ctx.quadraticCurveTo(x, y, x + radius.tl, y);
  ctx.closePath();
}

/**
 * Draw circular avatar with border, fallback initials monogram, and optional title
 */
export function drawAvatar(ctx, img, x, y, size, name = null, subtitle = null, accentColor = '#0d9488') {
  const r = size / 2;
  const cx = x + r;
  const cy = y + r;

  ctx.save();

  // Outer border & shadow
  ctx.beginPath();
  ctx.arc(cx, cy, r + 4, 0, Math.PI * 2);
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0, 0, 0, 0.15)';
  ctx.shadowBlur = 12;
  ctx.fill();

  ctx.beginPath();
  ctx.arc(cx, cy, r + 2, 0, Math.PI * 2);
  ctx.lineWidth = 3;
  ctx.strokeStyle = accentColor;
  ctx.stroke();

  // Clip circular photo area
  ctx.beginPath();
  ctx.arc(cx, cy, r, 0, Math.PI * 2);
  ctx.clip();

  if (img) {
    ctx.drawImage(img, x, y, size, size);
  } else {
    // Elegant fallback monogram
    ctx.fillStyle = '#0F2A44';
    ctx.fillRect(x, y, size, size);

    const initial = (name || 'D').replace(/^Dr\.?\s*/i, '').trim().charAt(0).toUpperCase() || 'D';
    ctx.fillStyle = '#5eead4';
    ctx.font = `bold ${Math.round(size * 0.42)}px Inter, sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(initial, cx, cy);
  }

  ctx.restore();

  // Subtitle / Label below avatar if provided
  if (name && subtitle) {
    ctx.save();
    ctx.textAlign = 'center';
    ctx.fillStyle = '#0F2A44';
    ctx.font = 'bold 18px Inter, sans-serif';
    ctx.fillText(name, cx, y + size + 24);

    ctx.fillStyle = '#64748b';
    ctx.font = '500 13px Inter, sans-serif';
    ctx.fillText(subtitle, cx, y + size + 44);
    ctx.restore();
  }
}

/**
 * Draw consistent card top branding banner
 */
export function drawCardHeader(ctx, W, options = {}) {
  const {
    title = 'EXPONIT LABS',
    subtitle = 'EXCELLENCE IN HEALTHCARE & THERAPEUTICS',
    badgeText = null,
    badgeBg = 'rgba(255, 255, 255, 0.12)',
    badgeBorder = 'rgba(255, 255, 255, 0.25)',
    badgeColor = '#ffffff'
  } = options;

  ctx.save();
  ctx.textAlign = 'left';
  ctx.textBaseline = 'top';

  ctx.fillStyle = '#ffffff';
  ctx.font = '900 32px Inter, system-ui, sans-serif';
  ctx.fillText(title, 64, 48);

  ctx.font = '600 13px Inter, system-ui, sans-serif';
  ctx.fillStyle = '#5eead4';
  ctx.letterSpacing = '2px';
  ctx.fillText(subtitle, 64, 88);

  if (badgeText) {
    ctx.font = 'bold 13px Inter, sans-serif';
    const badgeW = ctx.measureText(badgeText).width + 30;
    roundedRect(ctx, W - 64 - badgeW, 52, badgeW, 36, 18);
    ctx.fillStyle = badgeBg;
    ctx.fill();
    ctx.lineWidth = 1;
    ctx.strokeStyle = badgeBorder;
    ctx.stroke();
    ctx.fillStyle = badgeColor;
    ctx.textAlign = 'center';
    ctx.fillText(badgeText, W - 64 - (badgeW / 2), 64);
  }

  ctx.restore();
}

/**
 * Draw product card box with composition, strength, and packaging
 */
export function drawProductCard(ctx, x, y, w, h, product, accentColor = '#0d9488') {
  ctx.save();

  // White Card Container
  roundedRect(ctx, x, y, w, h, 16);
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0, 0, 0, 0.06)';
  ctx.shadowBlur = 10;
  ctx.shadowOffsetY = 4;
  ctx.fill();
  ctx.lineWidth = 1;
  ctx.strokeStyle = '#e2e8f0';
  ctx.stroke();

  // Left Accent Bar
  roundedRect(ctx, x, y, 8, h, [16, 0, 0, 16]);
  ctx.fillStyle = accentColor;
  ctx.fill();

  // Product Name
  ctx.textAlign = 'left';
  ctx.textBaseline = 'alphabetic';
  ctx.fillStyle = '#0F2A44';
  ctx.font = 'bold 22px Inter, sans-serif';
  ctx.fillText(product.name || 'Therapeutic Formulation', x + 28, y + 36);

  // Composition
  ctx.font = '500 14px Inter, sans-serif';
  ctx.fillStyle = '#475569';
  const comp = product.composition || 'Active Pharmaceutical Formulation';
  ctx.fillText(comp.length > 55 ? comp.slice(0, 52) + '...' : comp, x + 28, y + 64);

  // Strength / Packaging Badges
  let badgeX = x + 28;
  if (product.strength) {
    const sText = product.strength;
    ctx.font = 'bold 12px Inter, sans-serif';
    const sW = ctx.measureText(sText).width + 18;
    roundedRect(ctx, badgeX, y + 80, sW, 26, 6);
    ctx.fillStyle = '#f1f5f9';
    ctx.fill();
    ctx.fillStyle = '#334155';
    ctx.fillText(sText, badgeX + 9, y + 98);
    badgeX += sW + 10;
  }

  if (product.packaging) {
    const pText = product.packaging;
    ctx.font = 'bold 12px Inter, sans-serif';
    const pW = ctx.measureText(pText).width + 18;
    roundedRect(ctx, badgeX, y + 80, pW, 26, 6);
    ctx.fillStyle = '#f0fdfa';
    ctx.fill();
    ctx.fillStyle = '#0f766e';
    ctx.fillText(pText, badgeX + 9, y + 98);
  }

  ctx.restore();
}

/**
 * Draw bottom signature bar
 */
export function drawCardFooter(ctx, W, H, options = {}) {
  const {
    mrName = 'Exponit Representative',
    mrTitle = 'Territory Manager • Exponit Labs',
    rightText = 'EXPONIT LABS',
    rightColor = '#5eead4',
    bgColor = '#0F2A44',
    y = H - 120
  } = options;

  roundedRect(ctx, 48, y, W - 96, 80, 16);
  ctx.fillStyle = bgColor;
  ctx.fill();

  ctx.save();
  ctx.fillStyle = '#ffffff';
  ctx.font = 'bold 18px Inter, sans-serif';
  ctx.textAlign = 'left';
  ctx.fillText(mrName, 76, y + 34);

  ctx.font = '500 14px Inter, sans-serif';
  ctx.fillStyle = '#94a3b8';
  ctx.fillText(mrTitle, 76, y + 58);

  ctx.textAlign = 'right';
  ctx.font = 'bold 16px Inter, sans-serif';
  ctx.fillStyle = rightColor;
  ctx.fillText(rightText, W - 76, y + 46);
  ctx.restore();
}
