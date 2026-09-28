import Dexie from 'dexie';

export const db = new Dexie('ExponitMRFieldDatabase');

db.version(4).stores({
  doctors: 'uuid, id, name, specialty, town, area_id, sync_status',
  areas: 'id, name, slug, headquarter_id',
  headquarters: 'id, name',
  products: 'id, name',
  promotional_inputs: 'id, name',
  doctor_outbox: 'uuid, name, sync_status, created_at_client',
  dcr_outbox: 'client_uuid, doctor_uuid, date, status, created_at_client',
  visit_history: 'uuid, doctor_uuid, date',
  sync_meta: 'key, value'
}).upgrade(tx => {
  // Clear legacy stores on version upgrade to eliminate duplicate primary key entries
  return tx.doctors.clear();
});

db.version(5).stores({
  reminder_logs: '++id, doctor_uuid, doctor_id, dcr_client_uuid, visit_date, sent_at, mode, sync_status',
  dcr_outbox: 'client_uuid, doctor_uuid, date, status, reminder_sent_at, created_at_client',
  visit_history: 'uuid, doctor_uuid, date, reminder_sent_at'
});

export async function getLastSyncedAt() {
  const meta = await db.sync_meta.get('last_synced_at');
  return meta ? meta.value : null;
}

export async function setLastSyncedAt(timestamp) {
  await db.sync_meta.put({ key: 'last_synced_at', value: timestamp });
}

export async function getMrProfile() {
  try {
    const meta = await db.sync_meta.get('mr_profile');
    return meta ? meta.value : null;
  } catch (e) {
    return null;
  }
}

export async function setMrProfile(profile) {
  try {
    await db.sync_meta.put({ key: 'mr_profile', value: profile });
  } catch (e) {
    console.warn('[OfflineDB] Could not save MR profile:', e);
  }
}

export async function logReminderSent({ doctorUuid, doctorId, dcrClientUuid, visitDate, mode, templateId }) {
  const sentAt = new Date().toISOString();
  const entry = {
    doctor_uuid: doctorUuid || null,
    doctor_id: doctorId || null,
    dcr_client_uuid: dcrClientUuid || null,
    visit_date: visitDate || null,
    sent_at: sentAt,
    mode: mode || 'both',
    template_id: templateId || null,
    sync_status: 'pending'
  };

  const id = await db.reminder_logs.add(entry);

  // Update corresponding DCR in outbox if client_uuid matches
  if (dcrClientUuid) {
    try {
      await db.dcr_outbox.where('client_uuid').equals(dcrClientUuid).modify({ reminder_sent_at: sentAt });
      await db.visit_history.where('uuid').equals(dcrClientUuid).modify({ reminder_sent_at: sentAt });
    } catch (e) {
      // ignore
    }
  }

  // Also update matching visits by doctor and date
  if (doctorUuid && visitDate) {
    try {
      await db.dcr_outbox.where('doctor_uuid').equals(doctorUuid).and(d => d.date === visitDate).modify({ reminder_sent_at: sentAt });
      await db.visit_history.where('doctor_uuid').equals(doctorUuid).and(d => d.date === visitDate).modify({ reminder_sent_at: sentAt });
    } catch (e) {
      // ignore
    }
  }

  return { id, sentAt };
}

