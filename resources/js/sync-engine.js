import { db, getLastSyncedAt, setLastSyncedAt } from './offline-db.js';

export async function syncMasterData() {
  try {
    const doctorCount = await db.doctors.count();
    // If local DB is empty, force full download without ?since timestamp filter
    const lastSync = (doctorCount > 0) ? await getLastSyncedAt() : null;

    const url = lastSync ? `/api/v1/sync/master-data?since=${encodeURIComponent(lastSync)}` : '/api/v1/sync/master-data';
    const response = await fetch(url);
    
    if (!response.ok) return false;
    
    const data = await response.json();
    
    if (data.doctors && data.doctors.length > 0) {
      const docsToPut = data.doctors.map(doc => ({
        ...doc,
        sync_status: 'synced'
      }));
      await db.doctors.bulkPut(docsToPut);
    }

    if (data.areas && data.areas.length > 0) {
      await db.areas.bulkPut(data.areas);
    }
    
    if (data.products && data.products.length > 0) {
      await db.products.bulkPut(data.products);
    }
    
    if (data.promotional_inputs && data.promotional_inputs.length > 0) {
      await db.promotional_inputs.bulkPut(data.promotional_inputs);
    }
    
    if (data.visit_history && data.visit_history.length > 0) {
      await db.visit_history.bulkPut(data.visit_history);
    }
    
    if (data.server_time) {
      await setLastSyncedAt(data.server_time);
    }

    // Dispatch global event so Alpine components automatically reload state
    window.dispatchEvent(new CustomEvent('mr-sync-completed'));
    
    return true;
  } catch (err) {
    console.warn('[SyncEngine] Master data sync skipped or offline:', err);
    return false;
  }
}

export async function syncPendingDoctors() {
  try {
    const pending = await db.doctor_outbox.where('sync_status').equals('pending').toArray();
    if (!pending || pending.length === 0) return true;

    const response = await fetch('/api/v1/sync/doctors-batch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify({ doctors: pending })
    });

    if (!response.ok) return false;

    const resData = await response.json();
    if (resData.success && resData.synced) {
      for (const item of resData.synced) {
        await db.doctor_outbox.where('uuid').equals(item.uuid).modify({ sync_status: 'synced' });
        await db.doctors.where('uuid').equals(item.uuid).modify({ id: item.server_id, sync_status: 'synced' });
      }
    }
    return true;
  } catch (err) {
    console.warn('[SyncEngine] Doctor outbox sync failed:', err);
    return false;
  }
}

export async function syncPendingDcrs() {
  try {
    const pending = await db.dcr_outbox.where('status').equals('pending').toArray();
    if (!pending || pending.length === 0) return true;

    const response = await fetch('/api/v1/sync/dcr-batch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify({ dcrs: pending })
    });

    if (!response.ok) return false;

    const resData = await response.json();
    if (resData.success && resData.synced_uuids) {
      for (const uuid of resData.synced_uuids) {
        await db.dcr_outbox.where('client_uuid').equals(uuid).modify({ status: 'synced' });
      }
    }
    return true;
  } catch (err) {
    console.warn('[SyncEngine] DCR outbox sync failed:', err);
    return false;
  }
}

export async function runFullSync() {
  if (!navigator.onLine) return { success: false, reason: 'offline' };
  
  const docSuccess = await syncPendingDoctors();
  const dcrSuccess = await syncPendingDcrs();
  const masterSuccess = await syncMasterData();

  return {
    success: docSuccess && dcrSuccess && masterSuccess,
    time: new Date().toLocaleTimeString()
  };
}
