<x-layouts.public title="Market Insights — Exponit Labs" description="Internal pharma sales-audit dashboard."
    robots="noindex, nofollow">

    <section class="overflow-x-hidden py-10 lg:py-14" x-data="insightsDashboard()" x-init="load()">
        <div class="mx-auto max-w-7xl px-6 lg:px-12">

            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <x-site.section-heading eyebrow="Sales Audit" title="Market insights"
                        subtitle="Pack-level secondary-sales audit. Slice by therapy, molecule, brand or company." />
                </div>
                <div class="shrink-0 text-right text-xs text-muted">
                    <div>MAT period: <span class="font-semibold text-ink">{{ $matLabel }}</span></div>
                </div>
            </div>

            {{-- Filters --}}
            <div
                class="mt-8 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-medium text-muted">Acute / Chronic</label>
                    <select x-model="filters.acute_chronic" @change="load()"
                        class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All</option>
                        @foreach ($acuteChronic as $ac)
                            <option value="{{ $ac }}">{{ $ac }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-muted">Therapy (supergroup)</label>
                    <select x-model="filters.supergroup" @change="filters.group=''; load()"
                        class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All</option>
                        @foreach ($supergroups as $sg)
                            <option value="{{ $sg }}">{{ $sg }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-muted">Group</label>
                    <select x-model="filters.group" @change="load()" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All</option>
                        <template x-for="g in groupsForSupergroup()" :key="g">
                            <option :value="g" x-text="g"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-muted">Origin</label>
                    <select x-model="filters.indian_mnc" @change="load()"
                        class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All</option>
                        @foreach ($origins as $o)
                            <option value="{{ $o }}">{{ $o }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-muted">Search molecule / brand / pack /
                        company</label>
                    <div class="flex gap-2">
                        <input x-model="filters.q" @keydown.enter="load()" type="search" placeholder="e.g. PARACETAMOL"
                            class="w-full min-w-0 rounded-lg border-slate-300 text-sm">
                        <button @click="load()"
                            class="shrink-0 rounded-lg bg-ink px-3 text-sm font-medium text-white">Go</button>
                        <button @click="reset()"
                            class="shrink-0 rounded-lg border border-slate-300 px-3 text-sm text-muted">Reset</button>
                    </div>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" :class="loading && 'opacity-50'">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-muted">MAT Value</div>
                    <div class="mt-1 text-2xl font-bold text-ink" x-text="fmt(data.kpi.mat)"></div>
                    <div class="text-xs text-muted">{{ $matLabel }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-muted">YoY Growth</div>
                    <div class="mt-1 text-2xl font-bold" :class="growthClass(data.kpi.growth)"
                        x-text="data.kpi.growth===null ? '—' : (data.kpi.growth>0?'+':'')+data.kpi.growth+'%'"></div>
                    <div class="text-xs text-muted">vs prior MAT</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-muted">Market Share</div>
                    <div class="mt-1 text-2xl font-bold text-brand" x-text="data.kpi.share+'%'"></div>
                    <div class="text-xs text-muted">of total audited value</div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-xs font-medium uppercase tracking-wide text-muted">Packs</div>
                    <div class="mt-1 text-2xl font-bold text-ink"
                        x-text="Number(data.kpi.packs).toLocaleString('en-IN')"></div>
                    <div class="text-xs text-muted">matching SKUs</div>
                </div>
            </div>

            {{-- Trend chart --}}
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-ink">Monthly value trend</h3>
                    <span class="text-xs text-muted" x-text="trendRange()"></span>
                </div>
                <div class="relative h-44 touch-pan-y" @mousemove="onHover($event)" @mouseleave="hoverIndex=null"
                    @touchstart.passive="onHover($event)" @touchmove.passive="onHover($event)"
                    @touchend="hoverIndex=null">
                    <svg viewBox="0 0 100 34" preserveAspectRatio="none" class="h-full w-full overflow-visible">
                        <polyline :points="areaPath()" fill="url(#grad)" stroke="none" />
                        <polyline :points="linePath()" fill="none" stroke="#1FB6AA" stroke-width="0.4"
                            vector-effect="non-scaling-stroke" />
                        <defs>
                            <linearGradient id="grad" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#1FB6AA" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#1FB6AA" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                    </svg>

                    {{-- Hover/tap readout: guide line + dot + tooltip --}}
                    <template x-if="hoverIndex!==null && data.trend.length">
                        <div class="pointer-events-none absolute inset-0">
                            <div class="absolute bottom-0 top-0 w-px bg-ink/15" :style="`left:${dotLeft()}%`"></div>
                            <div class="absolute h-2.5 w-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-brand bg-white"
                                :style="`left:${dotLeft()}%; top:${dotTop()}%`"></div>
                            <div class="absolute z-10 -translate-x-1/2 -translate-y-full whitespace-nowrap rounded-lg bg-ink px-2 py-1 text-center text-[11px] leading-tight text-white shadow-lg"
                                :style="`left:${tipLeft()}%; top:${Math.max(dotTop()-3,2)}%`">
                                <div class="font-semibold" x-text="data.trend[hoverIndex].label"></div>
                                <div class="text-brand-light" x-text="fmt(data.trend[hoverIndex].value)"></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-1 flex justify-between text-[10px] text-muted">
                    <span x-text="data.trend.length ? data.trend[0].label : ''"></span>
                    <span x-text="data.trend.length ? data.trend[data.trend.length-1].label : ''"></span>
                </div>
            </div>

            {{-- Top-N tables --}}
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <template x-for="block in topBlocks" :key="block.key">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-3 text-sm font-semibold text-ink" x-text="block.title"></h3>
                        <div class="overflow-x-auto">
                            <div class="w-max min-w-full space-y-1.5">
                                <template x-for="(row,i) in data.top[block.key]" :key="i">
                                    <div class="relative min-w-full rounded-md px-2 py-1.5">
                                        <div class="absolute inset-y-0 left-0 rounded-md bg-brand/10"
                                            :style="`width:${barWidth(row.mat, block.key)}%`"></div>
                                        <div class="relative flex items-center justify-between gap-6 text-sm">
                                            <div class="whitespace-nowrap">
                                                <div class="font-medium text-ink" x-text="row.label"></div>
                                                <div class="text-[11px] text-muted" x-text="row.sub"></div>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-3 whitespace-nowrap">
                                                <span class="font-semibold text-ink" x-text="fmt(row.mat)"></span>
                                                <span class="w-12 text-right text-xs" :class="growthClass(row.growth)"
                                                    x-text="row.growth===null?'—':(row.growth>0?'+':'')+row.growth+'%'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="!data.top[block.key]?.length" class="py-4 text-center text-sm text-muted">No
                                    data.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Pack drill-down --}}
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-ink">Top packs <span class="font-normal text-muted">(by MAT
                        value)</span></h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs uppercase text-muted">
                                <th class="py-2 pr-3">Pack</th>
                                <th class="py-2 pr-3">Molecule</th>
                                <th class="py-2 pr-3">Brand</th>
                                <th class="py-2 pr-3">Company</th>
                                <th class="py-2 pr-3 text-right">MAT Value</th>
                                <th class="py-2 text-right">YoY</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(p,i) in data.packs" :key="i">
                                <tr class="border-b border-slate-100">
                                    <td class="py-1.5 pr-3" x-text="p.pack_desc"></td>
                                    <td class="py-1.5 pr-3 text-muted" x-text="p.molecule_desc"></td>
                                    <td class="py-1.5 pr-3" x-text="p.brands"></td>
                                    <td class="py-1.5 pr-3 text-muted" x-text="p.company"></td>
                                    <td class="py-1.5 pr-3 text-right font-medium" x-text="fmt(p.mat_value)"></td>
                                    <td class="py-1.5 text-right" :class="growthClass(p.growth)"
                                        x-text="p.growth===null?'—':(p.growth>0?'+':'')+p.growth+'%'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p x-show="!data.packs.length" class="py-6 text-center text-sm text-muted">No packs match.</p>
                </div>
            </div>

        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('insightsDashboard', () => ({
                dataUrl: @json(route('insights.data')),
                allGroups: @json($groups),
                loading: false,
                filters: {
                    supergroup: '',
                    group: '',
                    acute_chronic: '',
                    indian_mnc: '',
                    q: ''
                },
                data: {
                    kpi: {
                        mat: 0,
                        prev: 0,
                        growth: null,
                        share: 0,
                        packs: 0
                    },
                    trend: [],
                    top: {
                        molecule: [],
                        brand: [],
                        company: [],
                        group: []
                    },
                    packs: []
                },
                topBlocks: [{
                        key: 'molecule',
                        title: 'Top molecules'
                    },
                    {
                        key: 'brand',
                        title: 'Top brands'
                    },
                    {
                        key: 'company',
                        title: 'Top companies'
                    },
                    {
                        key: 'group',
                        title: 'Top therapy groups'
                    },
                ],
                hoverIndex: null,

                groupsForSupergroup() {
                    const sg = this.filters.supergroup;
                    return this.allGroups
                        .filter(g => !sg || g.supergroup === sg)
                        .map(g => g.group_desc)
                        .filter((v, i, a) => v && a.indexOf(v) === i);
                },

                async load() {
                    this.loading = true;
                    const params = new URLSearchParams();
                    for (const [k, v] of Object.entries(this.filters))
                        if (v) params.set(k, v);
                    try {
                        const res = await fetch(this.dataUrl + '?' + params.toString());
                        this.data = await res.json();
                    } finally {
                        this.loading = false;
                    }
                },

                reset() {
                    this.filters = {
                        supergroup: '',
                        group: '',
                        acute_chronic: '',
                        indian_mnc: '',
                        q: ''
                    };
                    this.load();
                },

                // --- formatting helpers ---
                fmt(n) {
                    n = Number(n) || 0;
                    if (Math.abs(n) >= 1000) return n.toLocaleString('en-IN', {
                        maximumFractionDigits: 0
                    });
                    return n.toLocaleString('en-IN', {
                        maximumFractionDigits: 2
                    });
                },
                growthClass(g) {
                    if (g === null || g === undefined) return 'text-muted';
                    return g > 0 ? 'text-emerald-600' : (g < 0 ? 'text-rose-600' : 'text-muted');
                },
                barWidth(v, key) {
                    const rows = this.data.top[key] || [];
                    const max = Math.max(...rows.map(r => r.mat), 0.0001);
                    return Math.max(2, (v / max) * 100);
                },
                trendRange() {
                    const t = this.data.trend;
                    if (!t.length) return '';
                    return `${t.length} months`;
                },
                _pts() {
                    const t = this.data.trend;
                    if (!t.length) return [];
                    const max = Math.max(...t.map(p => p.value), 0.0001);
                    const n = t.length;
                    return t.map((p, i) => [(i / (n - 1)) * 100, 34 - (p.value / max) * 32]);
                },
                linePath() {
                    return this._pts().map(p => p.join(',')).join(' ');
                },
                areaPath() {
                    const pts = this._pts();
                    if (!pts.length) return '';
                    return `0,34 ${pts.map(p => p.join(',')).join(' ')} 100,34`;
                },

                // --- trend hover/tap readout ---
                onHover(e) {
                    const n = this.data.trend.length;
                    if (!n) return;
                    const point = e.touches ? e.touches[0] : e;
                    const rect = e.currentTarget.getBoundingClientRect();
                    let x = (point.clientX - rect.left) / rect.width;
                    x = Math.max(0, Math.min(1, x));
                    this.hoverIndex = Math.round(x * (n - 1));
                },
                dotLeft() {
                    const n = this.data.trend.length;
                    return n > 1 ? (this.hoverIndex / (n - 1)) * 100 : 0;
                },
                dotTop() {
                    const p = this._pts()[this.hoverIndex];
                    return p ? (p[1] / 34) * 100 : 0; // viewBox height is 34
                },
                tipLeft() {
                    // keep the tooltip from spilling past the chart edges
                    return Math.min(90, Math.max(10, this.dotLeft()));
                },
            }));
        });
    </script>

</x-layouts.public>
