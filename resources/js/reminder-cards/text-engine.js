/**
 * Reminder Text Templates, Ordinal Date Formatting & Interpolation Engine
 * Exponit Labs - Call Reminder Card System
 */

export const TEXT_TEMPLATES = [
  {
    id: 'product_gratitude',
    category: 'post_visit',
    name: 'Post-Visit Gratitude & Products',
    icon: 'sparkles',
    template: 'Respected Dr. {doctor_name},\n\nThank you for your valuable time today ({visit_date}). It was a pleasure discussing {product_list}.\n\nLooking forward to your kind support and prescription patronage.\n\nWarm regards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'new_introduction',
    category: 'post_visit',
    name: 'New Product Introduction',
    icon: 'rocket',
    template: 'Dear Dr. {doctor_name},\n\nThank you for giving us the opportunity to introduce our latest formulation {product_list}. We are confident in its clinical efficacy and superior patient outcomes.\n\nLooking forward to your valuable prescriptions.\n\nBest regards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'visit_memory',
    category: 'post_visit',
    name: 'Visit Memory & Date Confirmation',
    icon: 'calendar',
    template: 'Greetings Dr. {doctor_name},\n\nIt was an honor meeting you at {clinic_name}{on_visit_date}. Thank you for your continued encouragement and time.\n\nWishing you a great week ahead!\n\nCordially,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'sample_delivery',
    category: 'clinical',
    name: 'Sample Delivery & Follow-up',
    icon: 'gift',
    template: 'Respected Dr. {doctor_name},\n\nI have provided clinical samples of {product_list} during my visit today ({visit_date}). Kindly evaluate them for suitable clinical cases in your practice.\n\nPlease let me know if any literature or further samples are needed.\n\nRegards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'clinical_feedback',
    category: 'clinical',
    name: 'Clinical Discussion & Feedback',
    icon: 'chat',
    template: 'Dear Dr. {doctor_name},\n\nThank you for the insightful clinical discussion regarding patient compliance and therapy with {product_list}. Your medical insights are deeply valued.\n\nSincerely,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'birthday_wishes',
    category: 'occasions',
    name: 'Happy Birthday Wishes',
    icon: 'cake',
    template: 'Respected Dr. {doctor_name},\n\nWishing you a very Happy Birthday! 🎂 May this year bring you boundless joy, vibrant health, and continued success in your noble healing profession.\n\nWarm regards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'independence_day',
    category: 'occasions',
    name: 'Independence Day Greetings',
    icon: 'flag',
    template: 'Respected Dr. {doctor_name},\n\nWarm greetings to you and your family on the occasion of Independence Day! 🇮🇳 Saluting your selfless dedication and tireless service towards building a healthier nation.\n\nHappy Independence Day!\n{mr_name}\nExponit Labs'
  },
  {
    id: 'festive_greetings',
    category: 'occasions',
    name: 'Festive & Celebration Wishes',
    icon: 'sparkles',
    template: 'Respected Dr. {doctor_name},\n\nWishing you and your loved ones a joyous, blessed, and prosperous festive season! ✨ May peace and good health always illuminate your path.\n\nWarm regards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'warm_wishing',
    category: 'general',
    name: 'Warm Professional Wishing',
    icon: 'heart',
    template: 'Warm greetings Dr. {doctor_name}!\n\nJust following up with cordial regards. Wishing you and your team continued success in healing and patient care.\n\nWarm regards,\n{mr_name}\nExponit Labs'
  },
  {
    id: 'general_regards',
    category: 'general',
    name: 'General Professional Regards',
    icon: 'hand',
    template: 'Greetings Dr. {doctor_name},\n\nJust following up to extend my heartfelt regards and thank you for your continuous guidance and trust in Exponit Labs.\n\nWishing you a wonderful week ahead!\n\nWarm regards,\n{mr_name}\nExponit Labs'
  }
];

/**
 * Safely parse date parts (year, monthIdx 0-11, day) from YYYY-MM-DD, ordinal strings, or Date objects
 */
export function parseDateParts(dateInput) {
  if (!dateInput) {
    const d = new Date();
    return { year: d.getFullYear(), monthIdx: d.getMonth(), day: d.getDate() };
  }
  if (typeof dateInput === 'string') {
    if (/^\d{4}-\d{2}-\d{2}/.test(dateInput)) {
      const parts = dateInput.slice(0, 10).split('-');
      return {
        year: parseInt(parts[0], 10),
        monthIdx: parseInt(parts[1], 10) - 1,
        day: parseInt(parts[2], 10)
      };
    }
    // Handle ordinal strings like "20th September 2026"
    const ordinalMatch = dateInput.match(/^(\d{1,2})(?:st|nd|rd|th)?\s+([A-Za-z]+)\s+(\d{4})/);
    if (ordinalMatch) {
      const day = parseInt(ordinalMatch[1], 10);
      const months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
      const mIdx = months.indexOf(ordinalMatch[2].toLowerCase());
      const year = parseInt(ordinalMatch[3], 10);
      return { year, monthIdx: mIdx >= 0 ? mIdx : 0, day };
    }
  }
  const d = dateInput instanceof Date ? dateInput : new Date(dateInput);
  if (!isNaN(d.getTime())) {
    return { year: d.getFullYear(), monthIdx: d.getMonth(), day: d.getDate() };
  }
  const now = new Date();
  return { year: now.getFullYear(), monthIdx: now.getMonth(), day: now.getDate() };
}

/**
 * Format date strictly as ordinal day, full month, and year (e.g. "20th September 2026")
 */
export function formatOrdinalDate(dateInput) {
  if (!dateInput) return '';
  try {
    const { year, monthIdx, day } = parseDateParts(dateInput);

    const months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const month = months[monthIdx] || '';

    const rem100 = day % 100;
    let suffix = 'th';
    if (rem100 < 11 || rem100 > 13) {
      const rem10 = day % 10;
      if (rem10 === 1) suffix = 'st';
      else if (rem10 === 2) suffix = 'nd';
      else if (rem10 === 3) suffix = 'rd';
    }

    return `${day}${suffix} ${month} ${year}`;
  } catch (e) {
    return String(dateInput);
  }
}

/**
 * Interpolate text template with dynamic doctor, representative, product, and visit date parameters
 */
export function interpolateText(templateStr, data = {}) {
  if (!templateStr) return '';
  const rawName = (data.doctor_name || 'Doctor').trim();
  const cleanName = rawName.replace(/^dr\.?\s+/i, '');

  const mrName = (data.mr_name || 'Exponit Representative').trim();
  const hasDate = Boolean(data.visit_date);
  const formattedDate = hasDate ? formatOrdinalDate(data.visit_date) : '';
  const clinic = data.clinic_name || data.town || 'your clinic';

  let productList = 'our healthcare products';
  if (data.products && data.products.length > 0) {
    const names = data.products.map(p => p.name || p.title || 'Product');
    if (names.length === 1) {
      productList = names[0];
    } else if (names.length === 2) {
      productList = `${names[0]} & ${names[1]}`;
    } else {
      productList = `${names.slice(0, -1).join(', ')} & ${names[names.length - 1]}`;
    }
  }

  let text = templateStr
    .replace(/Dr\.\s*\{doctor_name\}/gi, `Dr. ${cleanName}`)
    .replace(/\{doctor_name\}/g, cleanName)
    .replace(/\{mr_name\}/g, mrName)
    .replace(/\{product_list\}/g, productList)
    .replace(/\{clinic_name\}/g, clinic)
    .replace(/\{company_name\}/g, 'Exponit Labs');

  if (hasDate) {
    text = text
      .replace(/\{visit_date\}/g, formattedDate)
      .replace(/\{on_visit_date\}/g, ` on ${formattedDate}`);
  } else {
    // When date is optional / omitted, cleanly remove date phrasing
    text = text
      .replace(/\s*today\s*\(\{visit_date\}\)/gi, '')
      .replace(/\s*today\s*\(today\)/gi, '')
      .replace(/\s*on\s*\{visit_date\}/gi, '')
      .replace(/\{visit_date\}/g, '')
      .replace(/\{on_visit_date\}/g, '')
      .replace(/\s*during my visit today\b/gi, ' during my visit')
      .replace(/\s*after our meeting today\b/gi, ' after our meeting');
  }

  return text;
}
