import { db, getMrProfile, setMrProfile, logReminderSent } from './offline-db.js';
import { runFullSync } from './sync-engine.js';
import { Telestrator } from './telestrator.js';
import {
  TEXT_TEMPLATES,
  IMAGE_TEMPLATES,
  interpolateText,
  renderReminderCanvas,
  normalizeWhatsAppNumber,
  formatPhoneForDisplay,
  buildWhatsAppUrl,
  dispatchWhatsAppDirect,
  shareViaNativeShareSheet,
  downloadCardPNG,
  copyImageToClipboard,
  formatOrdinalDate
} from './reminder-cards/index.js';

export function registerMrComponents(Alpine) {
  // Navigation & Sync Bar state
  Alpine.data('syncBarApp', () => ({
    isOnline: navigator.onLine,
    isSyncing: false,
    lastSyncTime: null,
    pendingDoctorsCount: 0,
    pendingDcrsCount: 0,
    canInstall: false,
    deferredPrompt: null,

    async init() {
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        this.deferredPrompt = e;
        this.canInstall = true;
      });

      window.addEventListener('appinstalled', () => {
        this.canInstall = false;
        this.deferredPrompt = null;
      });

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
    },

    async installApp() {
      if (!this.deferredPrompt) return;
      this.deferredPrompt.prompt();
      const choiceResult = await this.deferredPrompt.userChoice;
      if (choiceResult && choiceResult.outcome === 'accepted') {
        this.canInstall = false;
      }
      this.deferredPrompt = null;
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

      // Keep reference to saved data for Call Reminder Studio
      const savedDoc = doc || { name: this.selectedDoctorName, uuid: this.selectedDoctorUuid };
      const savedDate = this.date;
      const savedRemarks = this.remarks;
      const savedProdIds = this.selectedProducts.map(p => parseInt(p.product_id));

      // Reset form for next call
      this.remarks = '';
      this.selectedProducts = [];
      this.selectedInputs = [];
      this.clearDoctor();

      // Instantly open Call Reminder Studio on the client side
      window.dispatchEvent(new CustomEvent('open-call-reminder', {
        detail: {
          doctor: savedDoc,
          date: savedDate,
          remarks: savedRemarks,
          productIds: savedProdIds
        }
      }));

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
  Alpine.data('doctorShowApp', (doctorUuid, serverDoctor = null) => ({
    uuid: doctorUuid,
    doctor: serverDoctor,
    history: serverDoctor?.dcrs || [],
    pendingDcrs: [],

    async init() {
      if (!this.uuid) return;
      const localDoc = await db.doctors.where('uuid').equals(this.uuid).first();
      if (localDoc) {
        this.doctor = localDoc;
      }
      
      await this.reloadVisits();

      window.addEventListener('reminder-logged', async (e) => {
        if (e.detail?.doctorUuid === this.uuid) {
          await this.reloadVisits();
        }
      });
    },

    async reloadVisits() {
      const pastVisits = await db.visit_history.where('doctor_uuid').equals(this.uuid).toArray();
      const queuedDcrs = await db.dcr_outbox.where('doctor_uuid').equals(this.uuid).toArray();

      if (pastVisits && pastVisits.length > 0) {
        this.history = pastVisits;
      }
      this.pendingDcrs = queuedDcrs || [];
    },

    get latestReminderSentAt() {
      const all = [...this.pendingDcrs, ...this.history];
      const withRem = all.filter(v => v.reminder_sent_at).sort((a, b) => (b.reminder_sent_at > a.reminder_sent_at ? 1 : -1));
      return withRem.length > 0 ? withRem[0].reminder_sent_at : null;
    },

    formatDate(dateStr) {
      return formatOrdinalDate(dateStr);
    },

    formatSentTime(isoStr) {
      if (!isoStr) return '';
      try {
        const d = new Date(isoStr);
        const timePart = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        return formatOrdinalDate(d) + ' at ' + timePart;
      } catch (e) {
        return isoStr;
      }
    },

    openReminder() {
      if (!this.doctor) return;
      // If visits exist, pass the most recent visit date; otherwise open in general greetings mode
      const all = [...this.pendingDcrs, ...this.history].sort((a, b) => (b.date > a.date ? 1 : -1));
      const targetDate = all.length > 0 ? all[0].date : null;

      window.dispatchEvent(new CustomEvent('open-call-reminder', {
        detail: {
          doctor: this.doctor,
          date: targetDate,
          includeDate: Boolean(targetDate)
        }
      }));
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
        window.location.href = '/elos/dcr';
      }, 800);
    }
  }));

  // Offline DCR History Directory Component
  Alpine.data('dcrListApp', () => ({
    dcrs: [],
    search: '',
    dateFilter: new Date().toISOString().split('T')[0],
    reminderFilter: 'all', // 'all' | 'sent' | 'pending'
    availableDates: [],

    async init() {
      await this.loadDcrs();

      if (this.dcrs.length === 0 && navigator.onLine) {
        await runFullSync();
        await this.loadDcrs();
      }

      window.addEventListener('mr-sync-completed', async () => {
        await this.loadDcrs();
      });

      window.addEventListener('reminder-logged', (e) => {
        const detail = e.detail;
        if (!detail) return;
        const targetDcr = this.dcrs.find(d => 
          (detail.dcrUuid && (d.key === detail.dcrUuid || d.client_uuid === detail.dcrUuid)) ||
          (detail.doctorUuid && d.doctor_uuid === detail.doctorUuid && d.date === detail.visitDate)
        );
        if (targetDcr) {
          targetDcr.reminder_sent_at = detail.sentAt;
        }
      });
    },

    async loadDcrs() {
      const historyVisits = await db.visit_history.toArray();
      const outboxDcrs = await db.dcr_outbox.toArray();

      const combinedMap = new Map();

      // Load server visit history first
      historyVisits.forEach(v => {
        const key = v.uuid || (v.id ? 'id_' + v.id : null);
        if (key) {
          combinedMap.set(key, {
            key: key,
            date: v.date,
            doctor_name: v.doctor_name || 'Doctor',
            doctor_uuid: v.doctor_uuid || null,
            doctor_id: v.doctor_id || null,
            remarks: v.remarks || '',
            status: 'synced',
            reminder_sent_at: v.reminder_sent_at || null,
            products: v.products || [],
            products_count: (v.products ? v.products.length : 0),
            inputs_count: (v.inputs ? v.inputs.length : 0)
          });
        }
      });

      // Load local outbox DCRs (which override or add pending DCRs)
      outboxDcrs.forEach(o => {
        const key = o.client_uuid;
        combinedMap.set(key, {
          key: key,
          date: o.date,
          doctor_name: o.doctor_name || 'Doctor',
          doctor_uuid: o.doctor_uuid || null,
          doctor_id: o.doctor_id || null,
          remarks: o.remarks || '',
          status: o.status || 'pending',
          reminder_sent_at: o.reminder_sent_at || null,
          products: o.products || [],
          products_count: (o.products ? o.products.length : 0),
          inputs_count: (o.promotional_inputs ? o.promotional_inputs.length : 0)
        });
      });

      this.dcrs = Array.from(combinedMap.values()).sort((a, b) => (b.date > a.date ? 1 : -1));

      // Build date filter list (Today + recent backdates)
      const todayStr = new Date().toISOString().split('T')[0];
      const dateSet = new Set([todayStr]);

      for (let i = 1; i <= 14; i++) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        dateSet.add(d.toISOString().split('T')[0]);
      }

      this.dcrs.forEach(d => {
        if (d.date) dateSet.add(d.date);
      });

      this.availableDates = Array.from(dateSet).sort((a, b) => (b > a ? 1 : -1));
    },

    formatDate(dateStr) {
      return formatOrdinalDate(dateStr);
    },

    formatDateLabel(dateStr) {
      const todayStr = new Date().toISOString().split('T')[0];
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      const yesterdayStr = yesterday.toISOString().split('T')[0];

      if (dateStr === todayStr) return 'Today (' + formatOrdinalDate(dateStr) + ')';
      if (dateStr === yesterdayStr) return 'Yesterday (' + formatOrdinalDate(dateStr) + ')';
      return formatOrdinalDate(dateStr);
    },

    formatReminderSentTime(isoStr) {
      if (!isoStr) return '';
      try {
        const d = new Date(isoStr);
        const timePart = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        return formatOrdinalDate(d) + ' at ' + timePart;
      } catch (e) {
        return isoStr;
      }
    },

    get filteredDcrs() {
      const q = this.search.toLowerCase().trim();
      return this.dcrs.filter(dcr => {
        const matchesQuery = !q || 
          (dcr.doctor_name && dcr.doctor_name.toLowerCase().includes(q)) ||
          (dcr.remarks && dcr.remarks.toLowerCase().includes(q)) ||
          (dcr.date && dcr.date.includes(q));

        const matchesDate = !this.dateFilter || dcr.date === this.dateFilter;

        const matchesReminder = 
          this.reminderFilter === 'all' ||
          (this.reminderFilter === 'sent' && Boolean(dcr.reminder_sent_at)) ||
          (this.reminderFilter === 'pending' && !dcr.reminder_sent_at);

        return matchesQuery && matchesDate && matchesReminder;
      });
    },

    get sentRemindersCount() {
      return this.dcrs.filter(d => (!this.dateFilter || d.date === this.dateFilter) && d.reminder_sent_at).length;
    },

    get pendingRemindersCount() {
      return this.dcrs.filter(d => (!this.dateFilter || d.date === this.dateFilter) && !d.reminder_sent_at).length;
    },

    async openReminderForDcr(dcr) {
      let doc = null;
      if (dcr.doctor_uuid) {
        doc = await db.doctors.where('uuid').equals(dcr.doctor_uuid).first();
      } else if (dcr.doctor_id) {
        doc = await db.doctors.where('id').equals(dcr.doctor_id).first();
      }

      if (!doc) {
        doc = {
          name: dcr.doctor_name,
          uuid: dcr.doctor_uuid
        };
      }

      const prodIds = (dcr.products || []).map(p => parseInt(p.product_id)).filter(Boolean);

      window.dispatchEvent(new CustomEvent('open-call-reminder', {
        detail: {
          doctor: doc,
          date: dcr.date,
          remarks: dcr.remarks,
          productIds: prodIds
        }
      }));
    }
  }));

  // 16:9 Presentation Stage & Telestrator Component
  Alpine.data('presentationApp', (totalSlidesCount = 5) => ({
    currentSlide: 0,
    totalSlides: totalSlidesCount,
    isFullscreen: false,
    showControls: true,
    controlsTimeout: null,
    
    // Telestrator State
    telestrator: null,
    isTelestratorActive: false,
    activeTool: 'pen', // 'pen' | 'highlighter' | 'eraser'
    activeColor: '#1FB6AA',
    colors: ['#1FB6AA', '#EF4444', '#F59E0B', '#3B82F6', '#FFFFFF', '#0F2A44'],

    // Touch Swipe Gesture Tracking
    touchStartX: 0,
    touchEndX: 0,

    init() {
      // Initialize Telestrator after DOM mounts
      this.$nextTick(() => {
        const canvas = document.getElementById('telestrator-canvas');
        if (canvas) {
          this.telestrator = new Telestrator(canvas);
        }
      });

      // Keyboard navigation
      window.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
          this.nextSlide();
        } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
          this.prevSlide();
        } else if (e.key === 'f' || e.key === 'F') {
          this.toggleFullscreen();
        } else if (e.key === 't' || e.key === 'T') {
          this.toggleTelestrator();
        }
      });

      // Fullscreen change listener
      document.addEventListener('fullscreenchange', () => {
        this.isFullscreen = !!document.fullscreenElement;
        if (this.telestrator) {
          setTimeout(() => this.telestrator.resize(), 150);
        }
      });

      this.resetControlsTimer();
    },

    nextSlide() {
      if (this.currentSlide < this.totalSlides - 1) {
        this.goToSlide(this.currentSlide + 1);
      }
    },

    prevSlide() {
      if (this.currentSlide > 0) {
        this.goToSlide(this.currentSlide - 1);
      }
    },

    goToSlide(index) {
      if (index < 0 || index >= this.totalSlides) return;
      if (this.telestrator) {
        this.telestrator.saveForSlide(this.currentSlide);
      }
      this.currentSlide = index;
      if (this.telestrator) {
        this.telestrator.loadForSlide(index);
      }
      this.resetControlsTimer();
    },

    // Touch gesture swipe handlers
    handleTouchStart(e) {
      this.touchStartX = e.changedTouches[0].screenX;
    },

    handleTouchEnd(e) {
      if (this.isTelestratorActive) return; // Don't slide while drawing
      this.touchEndX = e.changedTouches[0].screenX;
      const diff = this.touchStartX - this.touchEndX;
      if (Math.abs(diff) > 45) {
        if (diff > 0) {
          this.nextSlide();
        } else {
          this.prevSlide();
        }
      }
    },

    toggleTelestrator() {
      if (!this.telestrator) return;
      this.isTelestratorActive = !this.isTelestratorActive;
      this.telestrator.toggle(this.isTelestratorActive);
      this.showControls = true;
    },

    setTool(tool) {
      this.activeTool = tool;
      if (this.telestrator) {
        this.telestrator.setTool(tool);
        if (!this.isTelestratorActive) {
          this.toggleTelestrator();
        }
      }
    },

    setColor(color) {
      this.activeColor = color;
      if (this.telestrator) {
        this.telestrator.setColor(color);
        if (this.activeTool === 'eraser') {
          this.setTool('pen');
        }
      }
    },

    clearDrawing() {
      if (this.telestrator) {
        this.telestrator.clear();
      }
    },

    undoDrawing() {
      if (this.telestrator) {
        this.telestrator.undo();
      }
    },

    toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen().catch(() => {});
        }
      }
    },

    resetControlsTimer() {
      this.showControls = true;
      clearTimeout(this.controlsTimeout);
      this.controlsTimeout = setTimeout(() => {
        if (!this.isTelestratorActive) {
          this.showControls = false;
        }
      }, 4000);
    }
  }));

  // 100% Client-Side Call Reminder Studio Component (Zero Server Round-trips, Full Offline)
  Alpine.data('callReminderApp', () => ({
    isOpen: false,
    doctor: null,
    phoneDigits: '',
    visitDate: new Date().toISOString().split('T')[0],
    includeVisitDate: true,
    doctorDcrs: [],
    availableDates: [],
    activeDcr: null,
    reminderSentForActiveDate: null,
    hasLoggedDcrs: false,
    remarks: '',
    allProducts: [],
    selectedProductIds: [],
    selectedImageTemplate: 'executive',
    selectedTextTemplateId: 'product_gratitude',
    messageText: '',
    textTemplates: TEXT_TEMPLATES,
    imageTemplates: IMAGE_TEMPLATES,
    showDoctorPhoto: true,
    showMrPhoto: true,
    includeImage: true,
    sendMode: 'both', // 'both' (Card + Caption) or 'text_only' (Direct Chat)
    mrProfile: {
      name: (window.currentMR && window.currentMR.name) || 'Field Representative',
      title: 'Territory Manager • Exponit Labs',
      phone: '',
      photo: null
    },
    isRendering: false,
    isDispatching: false,
    toastMessage: '',
    toastType: 'success',
    copiedText: false,
    copiedImage: false,
    cachedBlob: null,
    showLaptopPasteGuide: false,
    renderDebounceTimer: null,

    get isMobileDevice() {
      return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },

    get fullWhatsAppPhone() {
      if (!this.phoneDigits) return '';
      return '91' + this.phoneDigits;
    },

    get isValidPhone() {
      return Boolean(this.phoneDigits && this.phoneDigits.length === 10);
    },

    get normalizedPhoneDisplay() {
      if (!this.phoneDigits) return 'No number entered';
      if (this.phoneDigits.length === 10) {
        return `+91 ${this.phoneDigits.slice(0, 5)} ${this.phoneDigits.slice(5)} (91${this.phoneDigits})`;
      }
      const needed = 10 - this.phoneDigits.length;
      return `+91 ${this.phoneDigits} (${needed} more digit${needed === 1 ? '' : 's'} needed)`;
    },

    formatDateOptionLabel(dateStr) {
      if (!dateStr) return '';
      const todayStr = new Date().toISOString().split('T')[0];
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      const yesterdayStr = yesterday.toISOString().split('T')[0];

      if (dateStr === todayStr) return 'Today (' + formatOrdinalDate(dateStr) + ')';
      if (dateStr === yesterdayStr) return 'Yesterday (' + formatOrdinalDate(dateStr) + ')';
      return formatOrdinalDate(dateStr);
    },

    formatDateTime(isoStr) {
      if (!isoStr) return '';
      try {
        const d = new Date(isoStr);
        const timePart = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        return formatOrdinalDate(d) + ' at ' + timePart;
      } catch (e) {
        return isoStr;
      }
    },

    onPhoneInput(event) {
      let val = String(event.target.value || '').replace(/\D/g, '');
      if (val.startsWith('0091')) val = val.slice(4);
      else if (val.startsWith('091')) val = val.slice(3);
      else if (val.startsWith('0') && val.length === 11) val = val.slice(1);
      else if (val.startsWith('91') && val.length === 12) val = val.slice(2);

      this.phoneDigits = val.slice(0, 10);
      this.syncPhoneToDoctor();
    },

    async syncPhoneToDoctor() {
      if (!this.isValidPhone || !this.doctor) return;
      const clean10 = this.phoneDigits;
      const clean91 = '91' + clean10;
      this.doctor.phone = clean10;
      this.doctor.whatsapp_number = clean91;

      try {
        if (this.doctor.id) {
          await db.doctors.update(this.doctor.id, { phone: clean10, whatsapp_number: clean91 });
        } else if (this.doctor.uuid) {
          const doc = await db.doctors.where('uuid').equals(this.doctor.uuid).first();
          if (doc && doc.id) {
            await db.doctors.update(doc.id, { phone: clean10, whatsapp_number: clean91 });
          }
        }
      } catch (e) {
        // ignore
      }
    },

    async init() {
      // Load products from IndexedDB
      this.allProducts = await db.products.toArray();

      // Load cached MR profile from local IndexedDB
      const savedMr = await getMrProfile();
      if (savedMr) {
        this.mrProfile = { ...this.mrProfile, ...savedMr };
      } else if (window.currentMR && window.currentMR.name) {
        this.mrProfile.name = window.currentMR.name;
      }

      // Listen for global open event
      window.addEventListener('open-call-reminder', async (e) => {
        await this.openWithDetail(e.detail || {});
      });

      // Reload products if background sync updates
      window.addEventListener('mr-sync-completed', async () => {
        this.allProducts = await db.products.toArray();
      });
    },

    async openWithDetail(detail) {
      if (this.allProducts.length === 0) {
        this.allProducts = await db.products.toArray();
      }

      this.doctor = detail.doctor || null;
      const rawPhone = this.doctor ? (this.doctor.whatsapp_number || this.doctor.phone || '') : '';
      let digits = String(rawPhone || '').replace(/\D/g, '');
      if (digits.startsWith('0091')) digits = digits.slice(4);
      else if (digits.startsWith('091')) digits = digits.slice(3);
      else if (digits.startsWith('0') && digits.length === 11) digits = digits.slice(1);
      else if (digits.startsWith('91') && digits.length === 12) digits = digits.slice(2);
      this.phoneDigits = digits.slice(0, 10);

      this.sendMode = 'both';
      this.includeImage = true;
      this.remarks = detail.remarks || '';

      // Query logged DCRs for this doctor from IndexedDB outbox and visit_history
      const docUuid = this.doctor?.uuid;
      const docId = this.doctor?.id;

      let outboxDocs = [];
      if (docUuid) {
        outboxDocs = await db.dcr_outbox.where('doctor_uuid').equals(docUuid).toArray();
      } else if (docId) {
        outboxDocs = await db.dcr_outbox.filter(d => d.doctor_id == docId).toArray();
      }

      let historyDocs = [];
      if (docUuid) {
        historyDocs = await db.visit_history.where('doctor_uuid').equals(docUuid).toArray();
      } else if (docId) {
        historyDocs = await db.visit_history.filter(d => d.doctor_id == docId).toArray();
      }

      const dcrMap = new Map();
      outboxDocs.forEach(d => {
        const key = d.client_uuid || d.uuid || (d.date + '_' + (d.doctor_uuid || d.doctor_id));
        dcrMap.set(key, { ...d, source: 'outbox' });
      });
      historyDocs.forEach(d => {
        const key = d.uuid || (d.date + '_' + (d.doctor_uuid || d.doctor_id));
        if (!dcrMap.has(key)) {
          dcrMap.set(key, { ...d, source: 'history' });
        }
      });

      this.doctorDcrs = Array.from(dcrMap.values()).sort((a, b) => (b.date > a.date ? 1 : -1));

      // If detail.date is passed (e.g. freshly filled DCR) and not yet in doctorDcrs, unshift it
      if (detail.date && !this.doctorDcrs.some(d => d.date === detail.date)) {
        this.doctorDcrs.unshift({
          date: detail.date,
          products: (detail.productIds || []).map(id => ({ product_id: id })),
          remarks: detail.remarks || '',
          reminder_sent_at: null
        });
      }

      this.hasLoggedDcrs = this.doctorDcrs.length > 0;

      if (this.hasLoggedDcrs) {
        this.availableDates = this.doctorDcrs.map(d => {
          const dateLabel = this.formatDateOptionLabel(d.date);
          const prodsCount = (d.products ? d.products.length : 0);
          const prodSuffix = prodsCount > 0 ? ` (${prodsCount} Product${prodsCount > 1 ? 's' : ''})` : '';
          const sentSuffix = d.reminder_sent_at ? ' ✓ Sent' : '';
          return {
            date: d.date,
            label: `${dateLabel}${prodSuffix}${sentSuffix}`,
            dcr: d,
            isSent: Boolean(d.reminder_sent_at),
            sentAt: d.reminder_sent_at || null
          };
        });

        // Check if date inclusion was specified
        if (detail.includeDate !== undefined) {
          this.includeVisitDate = Boolean(detail.includeDate);
        } else {
          this.includeVisitDate = Boolean(detail.date);
        }

        if (this.includeVisitDate === false) {
          this.visitDate = '';
          this.activeDcr = null;
          this.reminderSentForActiveDate = null;
          if (detail.productIds && Array.isArray(detail.productIds) && detail.productIds.length > 0) {
            this.selectedProductIds = detail.productIds.slice(0, 3);
          } else if (this.allProducts.length > 0) {
            this.selectedProductIds = this.allProducts.slice(0, 2).map(p => p.id);
          }
        } else {
          // Match selected date
          const matched = detail.date ? this.availableDates.find(o => o.date === detail.date) : null;
          const chosenDate = matched ? matched.date : this.availableDates[0].date;
          this.onVisitDateSelect(chosenDate, detail);
        }
      } else {
        // Fallback when no DCRs exist for this doctor (e.g. general greetings/occasions)
        const fallbackDate = detail.date || '';
        this.availableDates = [];
        this.visitDate = fallbackDate;
        this.includeVisitDate = Boolean(fallbackDate && detail.includeDate !== false);
        this.activeDcr = null;
        this.reminderSentForActiveDate = null;

        if (detail.productIds && Array.isArray(detail.productIds) && detail.productIds.length > 0) {
          this.selectedProductIds = detail.productIds.slice(0, 3);
        } else if (this.allProducts.length > 0) {
          this.selectedProductIds = this.allProducts.slice(0, 2).map(p => p.id);
        } else {
          this.selectedProductIds = [];
        }
        this.resetTemplateText();
      }

      this.selectedTextTemplateId = 'product_gratitude';
      this.resetTemplateText();

      this.isOpen = true;
      this.toastMessage = '';
      this.copiedText = false;
      this.copiedImage = false;
      this.showLaptopPasteGuide = false;

      this.$nextTick(() => {
        this.triggerRender();
      });
    },

    onVisitDateSelect(newDate, detail = null) {
      if (!newDate) {
        this.visitDate = '';
        this.includeVisitDate = false;
        this.activeDcr = null;
        this.reminderSentForActiveDate = null;
        this.resetTemplateText();
        this.triggerRender();
        return;
      }

      this.visitDate = newDate;
      this.includeVisitDate = true;
      const dcr = this.doctorDcrs.find(d => d.date === newDate);
      this.activeDcr = dcr || null;
      this.reminderSentForActiveDate = dcr?.reminder_sent_at || null;

      if (dcr) {
        if (detail && detail.date === newDate && detail.productIds && detail.productIds.length > 0) {
          this.selectedProductIds = detail.productIds.slice(0, 3);
        } else if (dcr.products && dcr.products.length > 0) {
          this.selectedProductIds = dcr.products.map(p => parseInt(p.product_id)).filter(Boolean).slice(0, 3);
        } else if (this.allProducts.length > 0 && this.selectedProductIds.length === 0) {
          this.selectedProductIds = this.allProducts.slice(0, 2).map(p => p.id);
        }
        if (dcr.remarks && (!detail || detail.date !== newDate)) {
          this.remarks = dcr.remarks;
        }
      }

      this.resetTemplateText();
      this.triggerRender();
    },

    toggleIncludeDate() {
      if (this.includeVisitDate && !this.visitDate && this.availableDates.length > 0) {
        this.visitDate = this.availableDates[0].date;
      }
      this.resetTemplateText();
      this.triggerRender();
    },

    async recordReminderSent(mode) {
      const dcrUuid = (this.includeVisitDate && this.visitDate) ? (this.activeDcr?.client_uuid || this.activeDcr?.uuid || null) : null;
      try {
        const result = await logReminderSent({
          doctorUuid: this.doctor?.uuid,
          doctorId: this.doctor?.id,
          dcrClientUuid: dcrUuid,
          visitDate: (this.includeVisitDate && this.visitDate) ? this.visitDate : null,
          mode: mode,
          templateId: this.selectedImageTemplate
        });

        if (this.includeVisitDate && this.visitDate) {
          this.reminderSentForActiveDate = result.sentAt;
          if (this.activeDcr) {
            this.activeDcr.reminder_sent_at = result.sentAt;
          }

          const opt = this.availableDates.find(o => o.date === this.visitDate);
          if (opt) {
            opt.isSent = true;
            opt.sentAt = result.sentAt;
            if (!opt.label.includes('✓ Sent')) {
              opt.label += ' ✓ Sent';
            }
          }
        }

        window.dispatchEvent(new CustomEvent('reminder-logged', {
          detail: {
            doctorUuid: this.doctor?.uuid,
            doctorId: this.doctor?.id,
            dcrUuid: dcrUuid,
            visitDate: (this.includeVisitDate && this.visitDate) ? this.visitDate : null,
            sentAt: result.sentAt,
            mode: mode
          }
        }));

        if (navigator.onLine) {
          runFullSync();
        }
      } catch (err) {
        console.warn('[CallReminder] Could not record reminder log:', err);
      }
    },

    closeModal() {
      this.isOpen = false;
      this.showLaptopPasteGuide = false;
    },

    selectImageTemplate(tmplId) {
      this.selectedImageTemplate = tmplId;
      this.triggerRender();
    },

    selectTextTemplate(tmplId) {
      this.selectedTextTemplateId = tmplId;
      this.resetTemplateText();
    },

    resetTemplateText() {
      const tmpl = this.textTemplates.find(t => t.id === this.selectedTextTemplateId) || this.textTemplates[0];
      const prods = this.getSelectedProducts();
      this.messageText = interpolateText(tmpl.template, {
        doctor_name: this.doctor ? this.doctor.name : 'Doctor',
        mr_name: this.mrProfile.name,
        visit_date: this.includeVisitDate ? this.visitDate : '',
        products: prods,
        clinic_name: this.doctor ? (this.doctor.clinic_name || this.doctor.town) : ''
      });
    },

    toggleProduct(productId) {
      const idx = this.selectedProductIds.indexOf(productId);
      if (idx >= 0) {
        this.selectedProductIds.splice(idx, 1);
      } else {
        if (this.selectedProductIds.length >= 3) {
          this.selectedProductIds.shift();
        }
        this.selectedProductIds.push(productId);
      }
      this.resetTemplateText();
      this.triggerRender();
    },

    getSelectedProducts() {
      if (this.selectedProductIds.length === 0) return [];
      return this.allProducts.filter(p => this.selectedProductIds.includes(p.id));
    },

    onStateChange() {
      this.resetTemplateText();
      this.triggerRender();
    },

    triggerRender() {
      clearTimeout(this.renderDebounceTimer);
      this.renderDebounceTimer = setTimeout(async () => {
        const canvas = this.$refs.reminderCanvas;
        if (!canvas) return;

        this.isRendering = true;
        try {
          await renderReminderCanvas(canvas, {
            templateId: this.selectedImageTemplate,
            doctor: this.doctor || {},
            mr: this.mrProfile,
            products: this.getSelectedProducts(),
            visitDate: this.includeVisitDate ? this.visitDate : '',
            showDoctorPhoto: this.showDoctorPhoto,
            showMrPhoto: this.showMrPhoto
          });
          // Cache the rendered image blob immediately so it's ready with 0ms delay
          canvas.toBlob((blob) => {
            this.cachedBlob = blob;
          }, 'image/png', 0.95);
        } catch (err) {
          console.warn('[CallReminder] Canvas rendering error:', err);
        } finally {
          this.isRendering = false;
        }
      }, 60);
    },

    async onMrPhotoUpload(event) {
      const file = event.target.files?.[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = async (e) => {
        this.mrProfile.photo = e.target.result;
        await setMrProfile(this.mrProfile);
        this.triggerRender();
        this.showToast('MR Photo saved on device!', 'success');
      };
      reader.readAsDataURL(file);
    },

    async sendWhatsAppReminder(selectedMode = null) {
      const mode = selectedMode || this.sendMode;
      const isMobile = this.isMobileDevice;
      const cleanPhone = this.fullWhatsAppPhone;

      if (!this.isValidPhone) {
        this.showToast('Please enter a valid 10-digit mobile number for this doctor.', 'error');
        return;
      }

      // Record reminder sent locally and trigger sync
      await this.recordReminderSent(mode);

      const canvas = this.$refs.reminderCanvas;
      const doctorName = this.doctor ? this.doctor.name : 'Doctor';
      const waUrl = buildWhatsAppUrl(cleanPhone, this.messageText, isMobile);

      // --- MOBILE DEVICE ---
      if (isMobile) {
        // Mode 'both': Send both Image Card and Text Caption together into WhatsApp
        if (mode === 'both' && canvas) {
          this.isDispatching = true;
          try {
            const shared = await shareViaNativeShareSheet(canvas, {
              phone: cleanPhone,
              text: this.messageText,
              doctorName: doctorName
            });
            if (shared) {
              this.showToast('Select Dr. ' + doctorName + ' in WhatsApp to send Card + Caption together!', 'success');
              return;
            }
          } catch (err) {
            if (err.name === 'AbortError') {
              // User cancelled the OS share sheet, do not redirect
              return;
            }
            console.warn('[CallReminder] Native share unavailable, falling back to direct chat:', err);
          } finally {
            this.isDispatching = false;
          }
        }

        // Mode 'text_only' or fallback: Direct chat deep-link straight to doctor's chat!
        this.isDispatching = true;
        try {
          await dispatchWhatsAppDirect(canvas, {
            phone: cleanPhone,
            text: this.messageText,
            doctorName: doctorName,
            isMobile: true
          });
        } finally {
          this.isDispatching = false;
        }
        return;
      }

      // --- LAPTOP / DESKTOP ---
      // 1. If sending card, copy to clipboard BEFORE opening window so document focus is retained
      if (mode === 'both') {
        this.showLaptopPasteGuide = true;
        if (canvas) {
          downloadCardPNG(canvas, doctorName);
        }
        if (this.cachedBlob && navigator.clipboard && window.ClipboardItem) {
          try {
            await navigator.clipboard.write([
              new ClipboardItem({ 'image/png': this.cachedBlob })
            ]);
            this.copiedImage = true;
          } catch (clipErr) {
            console.warn('[CallReminder] Pre-window clipboard write warning:', clipErr);
          }
        }
      }

      // 2. Open WhatsApp Web window
      let waWindow = null;
      try {
        waWindow = window.open(waUrl, '_blank', 'noopener,noreferrer');
      } catch (e) {
        console.warn('[CallReminder] Could not open blank window:', e);
      }

      this.isDispatching = true;
      try {
        await dispatchWhatsAppDirect(mode === 'both' ? (this.cachedBlob || canvas) : null, {
          phone: cleanPhone,
          text: this.messageText,
          doctorName: doctorName,
          waWindow: waWindow,
          isMobile: false
        });

        if (mode === 'both') {
          this.showToast('Card copied & downloaded! Press Ctrl+V (or Cmd+V) in WhatsApp Web to paste.', 'success');
        } else {
          this.showToast('Opening Dr. ' + doctorName + '\'s chat directly in WhatsApp Web...', 'success');
        }
      } catch (err) {
        console.error('[CallReminder] Desktop dispatch failed:', err);
        if (waWindow && !waWindow.closed) waWindow.close();
        this.showToast(err.message || 'Could not launch WhatsApp', 'error');
      } finally {
        this.isDispatching = false;
      }
    },

    async openDirectChatTextOnly() {
      await this.sendWhatsAppReminder('text_only');
    },

    async shareViaShareSheet() {
      const target = this.cachedBlob || this.$refs.reminderCanvas;
      if (!target) return false;

      try {
        await shareViaNativeShareSheet(target, {
          phone: this.fullWhatsAppPhone,
          text: this.messageText,
          doctorName: this.doctor ? this.doctor.name : 'Doctor'
        });
        return true;
      } catch (err) {
        if (err.name !== 'AbortError') {
          console.warn('[CallReminder] Share sheet cancelled or failed:', err);
        }
        return false;
      }
    },

    async copyCardImage() {
      const target = this.cachedBlob || this.$refs.reminderCanvas;
      if (!target) return;

      const copied = await copyImageToClipboard(target);
      if (copied) {
        this.copiedImage = true;
        this.showToast('Card Image copied! Ready to paste (Ctrl+V / Cmd+V).', 'success');
        setTimeout(() => { this.copiedImage = false; }, 3000);
      } else {
        await downloadCardPNG(target, this.doctor?.name || 'Doctor');
        this.showToast('Card downloaded to your device!', 'success');
      }
    },

    async downloadCard() {
      const canvas = this.$refs.reminderCanvas;
      if (!canvas) return;

      await downloadCardPNG(canvas, this.doctor?.name || 'Doctor');
      this.showToast('Card downloaded & copied to clipboard!', 'success');
    },

    async copyTextMessage() {
      try {
        await navigator.clipboard.writeText(this.messageText);
        this.copiedText = true;
        this.showToast('Message text copied to clipboard!', 'success');
        setTimeout(() => { this.copiedText = false; }, 3000);
      } catch (err) {
        this.showToast('Failed to copy text', 'error');
      }
    },

    showToast(msg, type = 'success') {
      this.toastMessage = msg;
      this.toastType = type;
      setTimeout(() => {
        if (this.toastMessage === msg) {
          this.toastMessage = '';
        }
      }, 4500);
    }
  }));
}
