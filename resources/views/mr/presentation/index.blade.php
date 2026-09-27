<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Visual Aid e-Detailing - Exponit Labs</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F2A44">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 text-slate-900 min-h-screen flex flex-col justify-between items-center overflow-hidden font-sans select-none"
      x-data="presentationApp(5)"
      @mousemove="resetControlsTimer()"
      @touchstart="resetControlsTimer()">

    <!-- TOP FLOATING HUD BAR -->
    <header class="fixed top-0 left-0 right-0 z-40 transition-opacity duration-300 px-4 py-3 bg-gradient-to-b from-slate-950/90 to-transparent"
            :class="showControls ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left: Back & Doctor Detailing Indicator -->
            <div class="flex items-center space-x-3">
                <a href="{{ $doctor ? route('elos.doctors.show', ['uuid' => $doctor->uuid]) : route('elos.dcr') }}"
                   class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-700/60 text-xs font-semibold backdrop-blur transition-all">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Exit Stage</span>
                </a>

                @if ($doctor)
                    <div class="hidden sm:flex items-center space-x-1.5 px-3 py-1 rounded-full bg-teal-950/80 border border-teal-500/40 text-teal-300 text-xs font-semibold backdrop-blur">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                        <span>Detailing: {{ $doctor->name }} ({{ $doctor->specialty ?? 'Physician' }})</span>
                    </div>
                @endif
            </div>

            <!-- Right: Stage Actions (Telestrator Toggle, Fullscreen, Log DCR) -->
            <div class="flex items-center space-x-2">
                <!-- Telestrator Mode Toggle -->
                <button type="button" @click="toggleTelestrator()"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm border"
                        :class="isTelestratorActive 
                            ? 'bg-teal-500 text-white border-teal-400 shadow-teal-500/30 ring-2 ring-teal-400/50' 
                            : 'bg-slate-900/80 text-slate-300 border-slate-700/60 hover:bg-slate-800'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span x-text="isTelestratorActive ? 'Drawing ON' : 'Telestrator'"></span>
                </button>

                <!-- Fullscreen Toggle -->
                <button type="button" @click="toggleFullscreen()"
                        class="p-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-800 text-slate-300 border border-slate-700/60 text-xs backdrop-blur transition-all"
                        title="Toggle Fullscreen">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isFullscreen">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isFullscreen" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Log to DCR Bridge -->
                <a href="{{ $doctor ? route('elos.dcr', ['doctor_uuid' => $doctor->uuid]) : route('elos.dcr') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center space-x-1">
                    <span>Log to DCR</span>
                    <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- 16:9 PRESENTATION STAGE CONTAINER -->
    <main class="w-full flex-1 flex items-center justify-center p-2 sm:p-6 md:p-10">
        <div class="relative w-full max-w-6xl aspect-[16/9] bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-800/80 flex flex-col"
             @touchstart="handleTouchStart($event)"
             @touchend="handleTouchEnd($event)">

            <!-- SLIDE DECK: PURE HTML / TAILWIND SLIDES -->
            <div class="relative w-full h-full overflow-hidden text-slate-900 select-text">

                <!-- SLIDE 1: PRODUCT HERO & CLINICAL INDICATION -->
                <div x-show="currentSlide === 0"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="w-full h-full p-8 md:p-12 flex flex-col justify-between bg-gradient-to-br from-slate-50 via-white to-teal-50/40">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800">
                                Exponit Labs • Cardiovascular Division
                            </span>
                            <span class="text-xs font-bold text-slate-400">CardioGuard Duo™</span>
                        </div>

                        <div class="mt-6 sm:mt-8 space-y-3">
                            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                                CardioGuard <span class="text-teal-600">Duo</span>
                            </h1>
                            <p class="text-base sm:text-xl font-bold text-slate-600">
                                Telmisartan 40mg + Amlodipine 5mg Tablets
                            </p>
                            <p class="max-w-2xl text-xs sm:text-sm text-slate-500 leading-relaxed">
                                Synergistic 24-hour hemodynamic stabilization engineered for stage-2 hypertensive patients with metabolic and vascular risks.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 sm:gap-6 mt-6">
                        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-2xl sm:text-3xl font-black text-teal-600">-28.4 <span class="text-sm font-semibold">mmHg</span></span>
                            <p class="text-[11px] sm:text-xs font-bold text-slate-700 mt-1">Systolic Reduction</p>
                            <p class="text-[10px] sm:text-[11px] text-slate-400">Sustained 24h trough-to-peak ratio</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-2xl sm:text-3xl font-black text-blue-600">92.4%</span>
                            <p class="text-[11px] sm:text-xs font-bold text-slate-700 mt-1">BP Goal Attainment</p>
                            <p class="text-[10px] sm:text-[11px] text-slate-400">Within 6 weeks of initiation</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-2xl sm:text-3xl font-black text-emerald-600">&lt; 1.2%</span>
                            <p class="text-[11px] sm:text-xs font-bold text-slate-700 mt-1">Peripheral Edema</p>
                            <p class="text-[10px] sm:text-[11px] text-slate-400">Superior venodilatory counteraction</p>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: MECHANISM OF ACTION (MOA) INTERACTIVE CASCADE -->
                <div x-show="currentSlide === 1"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     style="display: none;"
                     class="w-full h-full p-8 md:p-12 flex flex-col justify-between bg-white"
                     x-data="{ activeStep: 1 }">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800">
                                Mechanism of Action
                            </span>
                            <span class="text-xs font-bold text-slate-400">Dual Pharmacological Synergy</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-3">
                            Complementary Vascular Protection
                        </h2>
                    </div>

                    <!-- Step Cards -->
                    <div class="grid grid-cols-3 gap-4 my-auto">
                        <div @click="activeStep = 1"
                             class="p-5 rounded-2xl border-2 cursor-pointer transition-all"
                             :class="activeStep === 1 ? 'border-teal-500 bg-teal-50/50 shadow-md scale-[1.02]' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                            <span class="w-7 h-7 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-black mb-3">1</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Selective AT₁ Blockade</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-2 leading-relaxed">
                                High receptor affinity prevents Angiotensin II binding, promoting systemic vasodilation and reducing aldosterone excretion.
                            </p>
                        </div>

                        <div @click="activeStep = 2"
                             class="p-5 rounded-2xl border-2 cursor-pointer transition-all"
                             :class="activeStep === 2 ? 'border-teal-500 bg-teal-50/50 shadow-md scale-[1.02]' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-black mb-3">2</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">L-Type Calcium Influx Inhibition</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-2 leading-relaxed">
                                Directly relaxes arterial smooth muscle cells, decreasing total peripheral resistance without reflex tachycardia.
                            </p>
                        </div>

                        <div @click="activeStep = 3"
                             class="p-5 rounded-2xl border-2 cursor-pointer transition-all"
                             :class="activeStep === 3 ? 'border-teal-500 bg-teal-50/50 shadow-md scale-[1.02]' : 'border-slate-200 bg-slate-50 hover:border-slate-300'">
                            <span class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-black mb-3">3</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Postcapillary Venodilation</h3>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-2 leading-relaxed">
                                Telmisartan dilates postcapillary venules, neutralizing the hydrostatic pressure that causes CCB ankle edema.
                            </p>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-100 rounded-xl flex items-center justify-between text-xs text-slate-600">
                        <span class="font-bold text-slate-800">Key takeaway for Doctor:</span>
                        <span>Zero edema compromise with maximal vascular relaxation.</span>
                    </div>
                </div>

                <!-- SLIDE 3: CLINICAL STUDY & COMPARATIVE EFFICACY -->
                <div x-show="currentSlide === 2"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     style="display: none;"
                     class="w-full h-full p-8 md:p-12 flex flex-col justify-between bg-white">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Multicentric Clinical Trial
                            </span>
                            <span class="text-xs font-bold text-slate-400">Double-Blind RCT (n = 3,420)</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-3">
                            Mean Blood Pressure Reductions at Week 8
                        </h2>
                    </div>

                    <!-- Visual Chart Bars -->
                    <div class="space-y-4 my-auto">
                        <div>
                            <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                <span>CardioGuard Duo (Telmisartan 40 + Amlodipine 5)</span>
                                <span class="text-teal-600 font-extrabold">-28.4 mmHg SBP / -18.2 mmHg DBP</span>
                            </div>
                            <div class="w-full bg-slate-100 h-6 rounded-full overflow-hidden p-0.5">
                                <div class="bg-gradient-to-r from-teal-500 to-teal-600 h-full rounded-full transition-all duration-1000" style="width: 88%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                <span>Amlodipine 10mg Monotherapy</span>
                                <span class="text-blue-600 font-extrabold">-17.8 mmHg SBP / -11.4 mmHg DBP</span>
                            </div>
                            <div class="w-full bg-slate-100 h-6 rounded-full overflow-hidden p-0.5">
                                <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-full rounded-full transition-all duration-1000" style="width: 58%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                <span>Telmisartan 80mg Monotherapy</span>
                                <span class="text-slate-600 font-extrabold">-16.5 mmHg SBP / -10.9 mmHg DBP</span>
                            </div>
                            <div class="w-full bg-slate-100 h-6 rounded-full overflow-hidden p-0.5">
                                <div class="bg-slate-400 h-full rounded-full transition-all duration-1000" style="width: 52%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-xs text-slate-400">
                        <span>p &lt; 0.0001 vs Monotherapies • Journal of Hypertension Study</span>
                        <span class="font-bold text-teal-600">Statistically Superior BP Control</span>
                    </div>
                </div>

                <!-- SLIDE 4: SAFETY PROFILE & RENAL PROTECTION -->
                <div x-show="currentSlide === 3"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     style="display: none;"
                     class="w-full h-full p-8 md:p-12 flex flex-col justify-between bg-white">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-purple-100 text-purple-800">
                                Safety & Organ Protection
                            </span>
                            <span class="text-xs font-bold text-slate-400">Renal & Metabolic Benefits</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-3">
                            High Tolerability Beyond Blood Pressure
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 gap-6 my-auto">
                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                Renal Filtration & Proteinuria
                            </h3>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                Significantly reduces urinary albumin excretion rate in hypertensive patients with type 2 diabetes mellitus, preserving eGFR over long-term therapy.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                                PPAR-γ Metabolic Activity
                            </h3>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                                Telmisartan uniquely acts as a partial agonist of PPAR-γ, enhancing insulin sensitivity, improving glucose metabolism, and supporting favorable lipid markers.
                            </p>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-semibold flex items-center justify-between">
                        <span>Safe in comorbid Diabetic and Hypertensive patients.</span>
                        <span>No dosage adjustment needed in mild-to-moderate renal impairment.</span>
                    </div>
                </div>

                <!-- SLIDE 5: DOSING, PACKAGING & CALL TO ACTION -->
                <div x-show="currentSlide === 4"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-x-8"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     style="display: none;"
                     class="w-full h-full p-8 md:p-12 flex flex-col justify-between bg-gradient-to-br from-white via-slate-50 to-teal-50/50">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800">
                                Prescribing Information
                            </span>
                            <span class="text-xs font-bold text-slate-400">Exponit Labs Portfolio</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 mt-3">
                            Once-Daily Precision Dosing
                        </h2>
                    </div>

                    <div class="grid grid-cols-3 gap-5 my-auto">
                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-xs font-extrabold text-teal-600 uppercase">Recommended Dose</span>
                            <h3 class="text-xl font-black text-slate-900 mt-1">1 Tablet OD</h3>
                            <p class="text-xs text-slate-500 mt-2">Morning administration with or without food. Peak action within 1-2 hours.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-xs font-extrabold text-blue-600 uppercase">Packaging</span>
                            <h3 class="text-xl font-black text-slate-900 mt-1">10 × 10 Alu-Alu</h3>
                            <p class="text-xs text-slate-500 mt-2">Moisture-proof blister protection ensuring 100% active compound stability.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                            <span class="text-xs font-extrabold text-purple-600 uppercase">Patient Support</span>
                            <h3 class="text-xl font-black text-slate-900 mt-1">Affordable MRP</h3>
                            <p class="text-xs text-slate-500 mt-2">Cost-effective chronic therapy designed for maximum long-term adherence.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Dr. Recommendation Prompt:</p>
                            <p class="text-xs text-slate-500">"Doctor, for your stage-2 hypertensive patients with metabolic risks, initiate CardioGuard Duo today."</p>
                        </div>

                        <a href="{{ $doctor ? route('elos.dcr', ['doctor_uuid' => $doctor->uuid]) : route('elos.dcr') }}"
                           class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-lg shadow-teal-600/30 transition-all flex items-center space-x-1.5">
                            <span>Conclude & Log to DCR</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- TELESTRATOR DRAWING CANVAS OVERLAY -->
            <canvas id="telestrator-canvas"
                    class="absolute inset-0 z-20 w-full h-full"
                    :style="isTelestratorActive ? 'pointer-events: auto; cursor: crosshair;' : 'pointer-events: none;'"></canvas>
        </div>
    </main>

    <!-- BOTTOM FLOATING HUD CONTROLS & TELESTRATOR TOOLBAR -->
    <footer class="fixed bottom-0 left-0 right-0 z-40 transition-opacity duration-300 pb-4 px-4 bg-gradient-to-t from-slate-950/90 to-transparent"
            :class="showControls || isTelestratorActive ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'">
        <div class="max-w-7xl mx-auto flex flex-col items-center gap-3">

            <!-- Telestrator Tools Floating Palette (Shown when Telestrator is ON) -->
            <div x-show="isTelestratorActive"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-4 py-2 rounded-2xl bg-slate-900/95 border border-slate-700/80 shadow-2xl backdrop-blur flex items-center space-x-3">
                
                <!-- Tools: Pen, Highlighter, Eraser -->
                <div class="flex items-center space-x-1 pr-3 border-r border-slate-700">
                    <button type="button" @click="setTool('pen')"
                            class="p-2 rounded-xl transition-all"
                            :class="activeTool === 'pen' ? 'bg-teal-500 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                            title="Pen Tool">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>

                    <button type="button" @click="setTool('highlighter')"
                            class="p-2 rounded-xl transition-all"
                            :class="activeTool === 'highlighter' ? 'bg-amber-400 text-slate-950 shadow-sm font-bold' : 'text-slate-400 hover:text-white'"
                            title="Highlighter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </button>

                    <button type="button" @click="setTool('eraser')"
                            class="p-2 rounded-xl transition-all"
                            :class="activeTool === 'eraser' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                            title="Eraser">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <!-- Color Palette -->
                <div class="flex items-center space-x-1.5 pr-3 border-r border-slate-700">
                    <template x-for="c in colors" :key="c">
                        <button type="button" @click="setColor(c)"
                                class="w-5 h-5 rounded-full transition-transform"
                                :style="'background-color: ' + c"
                                :class="activeColor === c ? 'scale-125 ring-2 ring-white' : 'opacity-70 hover:opacity-100'">
                        </button>
                    </template>
                </div>

                <!-- Undo & Clear Actions -->
                <div class="flex items-center space-x-1">
                    <button type="button" @click="undoDrawing()"
                            class="p-2 text-slate-400 hover:text-white rounded-xl transition-colors"
                            title="Undo Stroke">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2a5 5 0 01-5 5H6m-3-12l4-4m-4 4l4 4"/>
                        </svg>
                    </button>

                    <button type="button" @click="clearDrawing()"
                            class="px-2.5 py-1 text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 rounded-lg transition-colors">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Slide Navigation Bar -->
            <div class="px-5 py-2 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl backdrop-blur flex items-center space-x-4">
                <button type="button" @click="prevSlide()" :disabled="currentSlide === 0"
                        class="p-2 rounded-xl text-slate-400 hover:text-white disabled:opacity-30 disabled:hover:text-slate-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Slide Dots Indicator -->
                <div class="flex items-center space-x-2">
                    <template x-for="i in totalSlides" :key="i">
                        <button type="button" @click="goToSlide(i - 1)"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="currentSlide === (i - 1) ? 'w-6 bg-teal-400' : 'w-2 bg-slate-700 hover:bg-slate-500'">
                        </button>
                    </template>
                </div>

                <span class="text-xs font-extrabold text-slate-400 min-w-[70px] text-center"
                      x-text="'Slide ' + (currentSlide + 1) + ' / ' + totalSlides"></span>

                <button type="button" @click="nextSlide()" :disabled="currentSlide === totalSlides - 1"
                        class="p-2 rounded-xl text-slate-400 hover:text-white disabled:opacity-30 disabled:hover:text-slate-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </div>
    </footer>

</body>
</html>
