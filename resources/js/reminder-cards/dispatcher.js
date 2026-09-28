/**
 * WhatsApp Dispatcher, Share Sheet, Clipboard & Download Engines
 * Exponit Labs - Call Reminder Card System
 */

/**
 * Clean & normalize phone number for WhatsApp
 * Guarantees Indian mobile numbers have international prefix "91" (e.g. 9890312002 -> 919890312002)
 * Handles spaces, dashes, parentheses, +91, 0091, leading 0, etc.
 */
export function normalizeWhatsAppNumber(rawPhone) {
  if (!rawPhone) return '';
  let digits = String(rawPhone).replace(/\D/g, '');
  if (!digits) return '';

  if (digits.startsWith('0091')) {
    digits = digits.slice(4);
  } else if (digits.startsWith('091')) {
    digits = digits.slice(3);
  } else if (digits.startsWith('0') && digits.length === 11) {
    digits = digits.slice(1);
  } else if (digits.startsWith('91') && digits.length === 12) {
    digits = digits.slice(2);
  }

  // If 10-digit Indian standard, prepend 91
  if (digits.length === 10) {
    return '91' + digits;
  }

  return digits;
}

/**
 * Pretty format phone number for UI display
 */
export function formatPhoneForDisplay(rawPhone) {
  const norm = normalizeWhatsAppNumber(rawPhone);
  if (norm.startsWith('91') && norm.length === 12) {
    const local = norm.slice(2);
    return `+91 ${local.slice(0, 5)} ${local.slice(5)}`;
  }
  return rawPhone || 'No Phone';
}

/**
 * Build direct WhatsApp chat URL with pre-filled text
 */
export function buildWhatsAppUrl(phone, text, isMobile = false) {
  const cleanPhone = normalizeWhatsAppNumber(phone);
  const encodedText = encodeURIComponent(text || '');
  if (isMobile) {
    return cleanPhone
      ? `whatsapp://send?phone=${cleanPhone}&text=${encodedText}`
      : `whatsapp://send?text=${encodedText}`;
  }
  return cleanPhone
    ? `https://web.whatsapp.com/send?phone=${cleanPhone}&text=${encodedText}`
    : `https://web.whatsapp.com/send?text=${encodedText}`;
}

/**
 * Download canvas or blob directly as a PNG file
 */
export async function downloadCardPNG(canvasOrBlob, doctorName = 'Doctor') {
  let blob = canvasOrBlob;
  if (canvasOrBlob instanceof HTMLCanvasElement) {
    blob = await new Promise((resolve) => canvasOrBlob.toBlob(resolve, 'image/png', 0.95));
  }
  if (!blob) return false;

  const cleanName = (doctorName || 'Doctor').replace(/[^a-zA-Z0-9]/g, '_');
  const filename = `Exponit_Reminder_${cleanName}_${new Date().toISOString().slice(0, 10)}.png`;

  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  setTimeout(() => URL.revokeObjectURL(url), 2000);
  return true;
}

/**
 * Copy image directly to operating system clipboard
 */
export async function copyImageToClipboard(canvasOrBlob) {
  if (!navigator.clipboard || !window.ClipboardItem) return false;
  try {
    let blob = canvasOrBlob;
    if (canvasOrBlob instanceof HTMLCanvasElement) {
      blob = await new Promise((resolve) => canvasOrBlob.toBlob(resolve, 'image/png', 0.95));
    }
    if (!blob) return false;

    await navigator.clipboard.write([
      new ClipboardItem({ 'image/png': blob })
    ]);
    return true;
  } catch (err) {
    console.warn('[CallReminder] Clipboard copy warning:', err);
    return false;
  }
}

/**
 * Share image card + text caption natively via Web Share API
 */
export async function shareViaNativeShareSheet(canvasOrBlob, options = {}) {
  const { text = '', doctorName = 'Doctor' } = options;
  if (!navigator.share) return false;

  let blob = canvasOrBlob;
  if (canvasOrBlob instanceof HTMLCanvasElement) {
    blob = await new Promise((resolve) => canvasOrBlob.toBlob(resolve, 'image/png', 0.95));
  }
  if (!blob) return false;

  const cleanName = (doctorName || 'Doctor').replace(/[^a-zA-Z0-9]/g, '_');
  const file = new File([blob], `Exponit_Reminder_${cleanName}.png`, { type: 'image/png' });

  if (navigator.canShare && !navigator.canShare({ files: [file] })) {
    return false;
  }

  await navigator.share({
    title: `Exponit Reminder - Dr. ${doctorName}`,
    text: text,
    files: [file]
  });
  return true;
}

/**
 * Dispatch Call Reminder via WhatsApp
 */
export async function dispatchWhatsAppDirect(canvasOrBlob, options = {}) {
  const { phone, text, isMobile = false, waWindow = null } = options;
  const cleanPhone = normalizeWhatsAppNumber(phone);
  const waUrl = buildWhatsAppUrl(cleanPhone, text, isMobile);

  if (isMobile) {
    window.location.href = waUrl;
    return true;
  }

  if (waWindow && !waWindow.closed) {
    try {
      waWindow.location.href = waUrl;
      waWindow.focus();
      return true;
    } catch (e) {
      // Fallback
    }
  }

  window.open(waUrl, '_blank', 'noopener,noreferrer');
  return true;
}
