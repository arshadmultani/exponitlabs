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

export async function getLastSyncedAt() {
  const meta = await db.sync_meta.get('last_synced_at');
  return meta ? meta.value : null;
}

export async function setLastSyncedAt(timestamp) {
  await db.sync_meta.put({ key: 'last_synced_at', value: timestamp });
}
