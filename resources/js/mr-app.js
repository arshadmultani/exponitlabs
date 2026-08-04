import { db } from './offline-db.js';
import { runFullSync } from './sync-engine.js';

export function registerMrComponents(Alpine) {
  // Navigation & Sync Bar state
  Alpine.data('syncBarApp', () => ({
    isOnline: navigator.onLine,
    isSyncing: false,
    lastSyncTime: null,
    pendingDoctorsCount: 0,
    pendingDcrsCount: 0,

    async init() {
      window.addEventListener('online', () => {
        this.isOnline = true;
        this.autoSync();
      });
      window.addEventListener('offline', () => {
        this.isOnline = false;
      });

      await this.refreshCounts();

      if (this.isOnline) {
        await this.autoSync();
      }

      setInterval(() => this.refreshCounts(), 3000);
    },

    async refreshCounts() {
      try {
        this.pendingDoctorsCount = await db.doctor_outbox.where('sync_status').equals('pending').count();
        this.pendingDcrsCount = await db.dcr_outbox.where('status').equals('pending').count();
      } catch (e) {
        // quiet fallback
      }
    },

    async autoSync() {
      if (this.isSyncing || !this.isOnline) return;
      this.isSyncing = true;
      const res = await runFullSync();
      this.isSyncing = false;
      if (res.success) {
        this.lastSyncTime = res.time || new Date().toLocaleTimeString();
        await this.refreshCounts();
      }
    }
  }));

  // Offline DCR Form Component
  Alpine.data('dcrApp', () => ({
    doctors: [],
    products: [],
    inputs: [],
    selectedDoctorUuid: '',
    selectedDoctorName: '',
    doctorQuery: '',
    showDoctorDropdown: false,
    date: new Date().toISOString().split('T')[0],
    remarks: '',
    selectedProducts: [],
    selectedInputs: [],
    toastMessage: '',
    toastType: 'success',

    async init() {
      await this.loadMasterData();

      // If local database has no doctors yet, auto-trigger server sync
      if (this.doctors.length === 0 && navigator.onLine) {
        await runFullSync();
        await this.loadMasterData();
      }

      // Re-load master data whenever background sync finishes
      window.addEventListener('mr-sync-completed', async () => {
        await this.loadMasterData();
      });
    },

    async loadMasterData() {
      this.doctors = await db.doctors.toArray();
      this.products = await db.products.toArray();
      this.inputs = await db.promotional_inputs.toArray();
    },

    get filteredDoctorsList() {
      const q = this.doctorQuery.toLowerCase().trim();
      if (!q) return this.doctors.slice(0, 30);
      return this.doctors.filter(d => 
        (d.name && d.name.toLowerCase().includes(q)) ||
        (d.specialty && d.specialty.toLowerCase().includes(q)) ||
        (d.town && d.town.toLowerCase().includes(q)) ||
        (d.clinic_name && d.clinic_name.toLowerCase().includes(q))
      ).slice(0, 30);
    },

    selectDoctor(doc) {
      this.selectedDoctorUuid = doc.uuid;
      this.selectedDoctorName = doc.name;
      this.doctorQuery = doc.name;
      this.showDoctorDropdown = false;
    },

    clearDoctor() {
      this.selectedDoctorUuid = '';
      this.selectedDoctorName = '';
      this.doctorQuery = '';
      this.showDoctorDropdown = true;
    },

    addProductRow() {
      if (this.products.length === 0) return;
      this.selectedProducts.push({ product_id: this.products[0].id, quantity: 1 });
    },

    removeProductRow(index) {
      this.selectedProducts.splice(index, 1);
    },

    addInputRow() {
      if (this.inputs.length === 0) return;
      this.selectedInputs.push({ promotional_input_id: this.inputs[0].id, quantity: 1 });
    },

    removeInputRow(index) {
      this.selectedInputs.splice(index, 1);
    },

    async saveDcr() {
      if (!this.selectedDoctorUuid) {
        this.showToast('Please select a doctor from the list.', 'error');
        return;
      }

      const doc = this.doctors.find(d => d.uuid === this.selectedDoctorUuid);
      const clientUuid = crypto.randomUUID();

      const dcrRecord = {
        client_uuid: clientUuid,
        date: this.date,
        doctor_uuid: this.selectedDoctorUuid,
        doctor_id: doc ? doc.id : null,
        doctor_name: doc ? doc.name : (this.selectedDoctorName || 'Doctor'),
        remarks: this.remarks,
        products: this.selectedProducts.map(p => ({
          product_id: parseInt(p.product_id),
          quantity: parseInt(p.quantity)
        })),
        promotional_inputs: this.selectedInputs.map(i => ({
          promotional_input_id: parseInt(i.promotional_input_id),
          quantity: parseInt(i.quantity)
        })),
        status: 'pending',
        created_at_client: new Date().toISOString()
      };

      await db.dcr_outbox.add(dcrRecord);

      this.showToast('DCR Saved Locally (0ms delay)!', 'success');

      // Reset form
      this.remarks = '';
      this.selectedProducts = [];
      this.selectedInputs = [];
      this.clearDoctor();

      // Trigger background sync if online
      if (navigator.onLine) {
        runFullSync();
      }
    },

    showToast(msg, type = 'success') {
      this.toastMessage = msg;
      this.toastType = type;
      setTimeout(() => {
        this.toastMessage = '';
      }, 4000);
    }
  }));

  // Offline Doctor Directory Component
  Alpine.data('doctorListApp', () => ({
    doctors: [],
    areas: [],
    headquarters: [],
    search: '',
    hqFilter: '',
    areaFilter: '',
    areaNames: [],
    hqNames: [],

    async init() {
      await this.loadDoctors();

      if (this.doctors.length === 0 && navigator.onLine) {
        await runFullSync();
        await this.loadDoctors();
      }

      window.addEventListener('mr-sync-completed', async () => {
        await this.loadDoctors();
      });
    },

    async loadDoctors() {
      const rawDocs = await db.doctors.toArray();
      const areasList = await db.areas.toArray();
      const hqList = await db.headquarters.toArray();

      this.areas = areasList;
      this.headquarters = hqList;

      const areaMap = new Map(areasList.map(a => [a.id, a]));
      const hqMap = new Map(hqList.map(h => [h.id, h.name]));

      const areaSet = new Set();
      const docMap = new Map();

      rawDocs.forEach(d => {
        const key = d.uuid || (d.id ? 'id_' + d.id : null) || d.name;
        if (!key || docMap.has(key)) return;

        const areaObj = areaMap.get(d.area_id);
        const areaName = areaObj ? areaObj.name : (d.town || '');
        const hqId = areaObj ? areaObj.headquarter_id : null;
        const hqName = hqId ? (hqMap.get(hqId) || '') : '';

        if (areaName) areaSet.add(areaName);

        docMap.set(key, {
          ...d,
          area_name: areaName,
          hq_name: hqName
        });
      });

      this.doctors = Array.from(docMap.values());
      this.areaNames = Array.from(areaSet).sort();
      this.hqNames = hqList.map(h => h.name).sort();
    },

    get visibleAreaNames() {
      if (!this.hqFilter) {
        return this.areaNames;
      }
      const hqObj = this.headquarters.find(h => h.name === this.hqFilter);
      if (!hqObj) return this.areaNames;

      const validAreaNames = new Set(
        this.areas
          .filter(a => a.headquarter_id === hqObj.id)
          .map(a => a.name)
      );

      return this.areaNames.filter(name => validAreaNames.has(name));
    },

    setHqFilter(hq) {
      this.hqFilter = hq;
      this.areaFilter = '';
    },

    get filteredDoctors() {
      const q = this.search.toLowerCase().trim();
      return this.doctors.filter(doc => {
        const matchesQuery = !q || 
          (doc.name && doc.name.toLowerCase().includes(q)) ||
          (doc.area_name && doc.area_name.toLowerCase().includes(q)) ||
          (doc.hq_name && doc.hq_name.toLowerCase().includes(q)) ||
          (doc.specialty && doc.specialty.toLowerCase().includes(q)) ||
          (doc.town && doc.town.toLowerCase().includes(q)) ||
          (doc.address && doc.address.toLowerCase().includes(q)) ||
          (doc.phone && doc.phone.includes(q));

        const matchesHq = !this.hqFilter || doc.hq_name === this.hqFilter;
        const matchesArea = !this.areaFilter || doc.area_name === this.areaFilter;

        return matchesQuery && matchesHq && matchesArea;
      });
    }
  }));

  // Offline Doctor Detail Component
  Alpine.data('doctorShowApp', (doctorUuid) => ({
    uuid: doctorUuid,
    doctor: null,
    history: [],
    pendingDcrs: [],

    async init() {
      if (!this.uuid) return;
      this.doctor = await db.doctors.where('uuid').equals(this.uuid).first();
      
      const pastVisits = await db.visit_history.where('doctor_uuid').equals(this.uuid).toArray();
      const queuedDcrs = await db.dcr_outbox.where('doctor_uuid').equals(this.uuid).toArray();

      this.history = pastVisits || [];
      this.pendingDcrs = queuedDcrs || [];
    }
  }));

  // Offline Doctor Creation Component with Searchable Area Combobox & Leaflet Map
  Alpine.data('doctorCreateApp', () => ({
    areas: [],
    areaQuery: '',
    showAreaDropdown: false,
    selectedAreaName: '',
    isGettingLocation: false,
    map: null,
    marker: null,
    form: {
      name: '',
      specialty: '',
      qualification: '',
      phone: '',
      email: '',
      town: '',
      area_id: '',
      clinic_name: '',
      address: '',
      latitude: '',
      longitude: ''
    },
    toastMessage: '',

    async init() {
      this.areas = await db.areas.toArray();

      if (this.areas.length === 0 && navigator.onLine) {
        await runFullSync();
        this.areas = await db.areas.toArray();
      }

      window.addEventListener('mr-sync-completed', async () => {
        this.areas = await db.areas.toArray();
      });
    },

    get filteredAreasList() {
      const q = this.areaQuery.toLowerCase().trim();
      if (!q) return this.areas.slice(0, 30);
      return this.areas.filter(a => a.name && a.name.toLowerCase().includes(q)).slice(0, 30);
    },

    selectArea(area) {
      this.form.area_id = area.id;
      this.selectedAreaName = area.name;
      this.areaQuery = area.name;
      this.showAreaDropdown = false;
    },

    clearArea() {
      this.form.area_id = '';
      this.selectedAreaName = '';
      this.areaQuery = '';
      this.showAreaDropdown = true;
    },

    initMap(mapElement) {
      if (!mapElement || typeof L === 'undefined') return;

      const defaultLat = this.form.latitude ? parseFloat(this.form.latitude) : 19.4023;
      const defaultLng = this.form.longitude ? parseFloat(this.form.longitude) : 72.8328;

      if (this.map) {
        this.map.remove();
        this.map = null;
      }

      this.map = L.map(mapElement).setView([defaultLat, defaultLng], 14);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(this.map);

      this.marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(this.map);

      this.marker.on('dragend', (e) => {
        const latlng = e.target.getLatLng();
        this.form.latitude = latlng.lat.toFixed(7);
        this.form.longitude = latlng.lng.toFixed(7);
      });

      this.map.on('click', (e) => {
        if (this.marker) {
          this.marker.setLatLng(e.latlng);
        }
        this.form.latitude = e.latlng.lat.toFixed(7);
        this.form.longitude = e.latlng.lng.toFixed(7);
      });

      setTimeout(() => {
        if (this.map) {
          this.map.invalidateSize();
        }
      }, 250);
    },

    getCurrentLocation() {
      if (!navigator.geolocation) {
        this.toastMessage = 'Geolocation is not supported by your browser.';
        return;
      }
      this.isGettingLocation = true;
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          this.form.latitude = lat.toFixed(7);
          this.form.longitude = lng.toFixed(7);
          this.isGettingLocation = false;
          this.toastMessage = 'GPS Location captured!';

          if (this.map && this.marker) {
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], 16);
          }

          setTimeout(() => this.toastMessage = '', 3000);
        },
        (err) => {
          this.isGettingLocation = false;
          this.toastMessage = 'Unable to fetch location: ' + err.message;
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    },

    async saveDoctor() {
      if (!this.form.name.trim()) {
        this.toastMessage = 'Doctor name is required.';
        return;
      }

      if (!this.form.area_id) {
        this.toastMessage = 'Please select an Area from the list.';
        return;
      }

      const latNum = this.form.latitude ? parseFloat(this.form.latitude) : null;
      const lngNum = this.form.longitude ? parseFloat(this.form.longitude) : null;

      const locationGeoJson = (latNum !== null && lngNum !== null) ? {
        type: 'FeatureCollection',
        features: [{
          type: 'Feature',
          properties: {},
          geometry: {
            type: 'Point',
            coordinates: [lngNum, latNum]
          }
        }]
      } : null;

      const uuid = crypto.randomUUID();
      const doctorData = {
        uuid: uuid,
        id: null,
        name: this.form.name.trim(),
        specialty: this.form.specialty.trim(),
        qualification: this.form.qualification.trim(),
        phone: this.form.phone.trim(),
        email: this.form.email.trim(),
        town: this.form.town.trim(),
        area_id: parseInt(this.form.area_id),
        clinic_name: this.form.clinic_name.trim(),
        address: this.form.address.trim(),
        latitude: latNum,
        longitude: lngNum,
        location: locationGeoJson,
        sync_status: 'pending',
        created_at_client: new Date().toISOString()
      };

      await db.doctors.add(doctorData);
      await db.doctor_outbox.add(doctorData);

      this.toastMessage = 'Doctor created locally!';

      if (navigator.onLine) {
        runFullSync();
      }

      setTimeout(() => {
        window.location.href = '/mr/dcr';
      }, 800);
    }
  }));
}
