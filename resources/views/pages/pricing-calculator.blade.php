<x-layouts.public title="Pricing Calculator — Exponit Labs"
    description="Internal trade-pricing calculator: PTR, PTS, scheme-adjusted price, GST invoice breakdown and margins."
    robots="noindex, nofollow">

    <section class="py-12 lg:py-16" x-data="pharmaCalc()">
        <div class="mx-auto max-w-6xl px-6 lg:px-12">
            <x-site.section-heading eyebrow="PTR/PTS Tool" title="Pharma pricing calculator"
                subtitle="PTR / PTS, scheme-adjusted price, GST invoice preview and margins. Calculates live as you type." />

            <div class="mt-10 grid gap-8 lg:grid-cols-2">

                {{-- ------------------------------ INPUTS ------------------------------ --}}
                <form class="rounded-3xl border border-line bg-surface p-6 sm:p-8 space-y-5" @submit.prevent>
                    <div>
                        <label class="block text-sm font-medium text-ink">MRP per unit (₹) <span
                                class="text-brand">*</span></label>
                        <input type="number" min="0" step="0.01" x-model.number="mrp" placeholder="e.g. 120"
                            class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink">Retailer margin %</label>
                            <input type="number" min="0" max="100" step="0.1"
                                x-model.number="retailer_margin_percent"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink">Stockist margin %</label>
                            <input type="number" min="0" max="100" step="0.1"
                                x-model.number="stockist_margin_percent"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink">Scheme paid units</label>
                            <input type="number" min="1" step="1" x-model.number="scheme_paid_units"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink">Scheme free units</label>
                            <input type="number" min="0" step="1" x-model.number="scheme_free_units"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-ink">GST %</label>
                            <input type="number" min="0" max="100" step="0.1"
                                x-model.number="gst_percent"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                            <p class="mt-1 text-xs text-muted">Set per SKU (commonly 5% or 12%).</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink">Supply type</label>
                            <select x-model="supply_type"
                                class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                                <option value="intra_state">Intra-state (CGST + SGST)</option>
                                <option value="inter_state">Inter-state (IGST)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-ink">Company unit cost (₹) <span
                                class="text-muted">— optional</span></label>
                        <input type="number" min="0" step="0.01" x-model.number="unit_cost"
                            placeholder="incl. GST; leave blank to skip profit"
                            class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none focus:border-brand focus:bg-surface">
                    </div>

                    {{-- Validation errors --}}
                    <template x-if="errors.length">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <template x-for="err in errors" :key="err">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </form>

                {{-- ------------------------------ RESULTS ------------------------------ --}}
                <div class="space-y-4">
                    <template x-if="! errors.length">
                        <div class="space-y-4">

                            {{-- Trade prices --}}
                            <div class="rounded-3xl border border-line bg-surface p-6 sm:p-8">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-brand">Trade prices</h3>
                                <dl class="mt-4 space-y-3">
                                    <div class="flex items-baseline justify-between gap-4">
                                        <dt class="text-muted">PTR <span class="text-xs">(Price to Retailer)</span></dt>
                                        <dd class="text-lg font-semibold text-ink" x-text="money(r.ptr)"></dd>
                                    </div>
                                    <div class="flex items-baseline justify-between gap-4">
                                        <dt class="text-muted">PTS <span class="text-xs">(Price to Stockist —
                                                billed)</span></dt>
                                        <dd class="text-lg font-semibold text-ink" x-text="money(r.pts)"></dd>
                                    </div>
                                    <template x-if="scheme_free_units > 0">
                                        <div
                                            class="flex items-baseline justify-between gap-4 border-t border-line pt-3">
                                            <dt class="text-muted">Effective PTS <span
                                                    class="text-xs">(post-scheme)</span></dt>
                                            <dd class="text-lg font-semibold text-brand" x-text="money(r.effPts)"></dd>
                                        </div>
                                    </template>
                                </dl>
                            </div>

                            {{-- Margins --}}
                            <div class="rounded-3xl border border-line bg-surface p-6 sm:p-8">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-brand">Margins</h3>
                                <dl class="mt-4 space-y-3">
                                    <div class="flex items-baseline justify-between gap-4">
                                        <dt class="text-muted">Retailer margin</dt>
                                        <dd class="text-ink"><span class="font-semibold"
                                                x-text="money(r.retailerMarginPerUnit)"></span>
                                            <span class="text-muted text-sm"
                                                x-text="'/unit · ' + pct(retailer_margin_percent)"></span>
                                        </dd>
                                    </div>
                                    <div class="flex items-baseline justify-between gap-4">
                                        <dt class="text-muted">Stockist margin</dt>
                                        <dd class="text-ink"><span class="font-semibold"
                                                x-text="money(r.stockistMarginPerUnit)"></span>
                                            <span class="text-muted text-sm"
                                                x-text="'/unit · nominal ' + pct(stockist_margin_percent) + ' · actual ' + pct(r.stockistMarginPctActual)"></span>
                                        </dd>
                                    </div>
                                    <div class="border-t border-line pt-3 text-sm text-muted" x-text="schemeSummary">
                                    </div>
                                </dl>
                            </div>

                            {{-- Profit (only if unit_cost) --}}
                            <template x-if="r.cost !== null">
                                <div class="rounded-3xl border border-line bg-surface p-6 sm:p-8">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-brand">Profitability
                                    </h3>
                                    <dl class="mt-4 space-y-3">
                                        <div class="flex items-baseline justify-between gap-4">
                                            <dt class="text-muted">Profit per unit <span class="text-xs">(vs effective
                                                    PTS)</span></dt>
                                            <dd class="text-lg font-semibold"
                                                :class="r.profit >= 0 ? 'text-ink' : 'text-red-600'"
                                                x-text="money(r.profit)"></dd>
                                        </div>
                                        <div class="flex items-baseline justify-between gap-4">
                                            <dt class="text-muted">Profit margin %</dt>
                                            <dd class="font-semibold text-ink" x-text="pct(r.profitMarginPct)"></dd>
                                        </div>
                                        <div class="flex items-baseline justify-between gap-4">
                                            <dt class="text-muted">Markup %</dt>
                                            <dd class="font-semibold text-ink" x-text="pct(r.markupPct)"></dd>
                                        </div>
                                        <div
                                            class="flex items-baseline justify-between gap-4 border-t border-line pt-3">
                                            <dt class="text-muted">MRP-to-cost ratio</dt>
                                            <dd class="text-right">
                                                <span class="font-semibold text-ink"
                                                    x-text="'1 : ' + r.ratio.toFixed(1)"></span>
                                                <span class="block text-xs"
                                                    :class="r.ratio >= 5 ? 'text-brand' : 'text-amber-600'"
                                                    x-text="r.ratio >= 5 ? 'within/above typical 1:5 industry benchmark' : 'below typical 1:5 industry benchmark'"></span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </template>

                            {{-- GST invoice preview --}}
                            <div class="rounded-3xl border border-line bg-ink text-white p-6 sm:p-8">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-brand-light">GST invoice
                                    breakdown (per unit)</h3>
                                <dl class="mt-4 space-y-3">
                                    <div class="flex items-baseline justify-between gap-4">
                                        <dt class="text-white/70">PTS base (taxable) value</dt>
                                        <dd class="font-semibold" x-text="money(r.taxablePTS)"></dd>
                                    </div>
                                    <template x-if="supply_type === 'intra_state'">
                                        <div class="space-y-3">
                                            <div class="flex items-baseline justify-between gap-4">
                                                <dt class="text-white/70"
                                                    x-text="'CGST (' + pct(gst_percent/2) + ')'"></dt>
                                                <dd class="font-semibold" x-text="money(r.gstAmtPTS/2)"></dd>
                                            </div>
                                            <div class="flex items-baseline justify-between gap-4">
                                                <dt class="text-white/70"
                                                    x-text="'SGST (' + pct(gst_percent/2) + ')'"></dt>
                                                <dd class="font-semibold" x-text="money(r.gstAmtPTS/2)"></dd>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="supply_type === 'inter_state'">
                                        <div class="flex items-baseline justify-between gap-4">
                                            <dt class="text-white/70" x-text="'IGST (' + pct(gst_percent) + ')'"></dt>
                                            <dd class="font-semibold" x-text="money(r.gstAmtPTS)"></dd>
                                        </div>
                                    </template>
                                    <div
                                        class="flex items-baseline justify-between gap-4 border-t border-white/15 pt-3">
                                        <dt class="text-white/70">Total per unit <span class="text-xs">(= PTS
                                                ✓)</span></dt>
                                        <dd class="text-lg font-semibold text-brand-light"
                                            x-text="money(r.invoiceUnitTotal)"></dd>
                                    </div>

                                    <template x-if="scheme_free_units > 0">
                                        <div class="border-t border-white/15 pt-3 space-y-1">
                                            <div class="flex items-baseline justify-between gap-4">
                                                <dt class="text-white/70"
                                                    x-text="'Invoice for ' + scheme_paid_units + ' billed units'"></dt>
                                                <dd class="font-semibold" x-text="money(r.invoiceTotal)"></dd>
                                            </div>
                                            <p class="text-xs text-white/60"
                                                x-text="'+ ' + scheme_free_units + ' free units shipped at nil taxable value'">
                                            </p>
                                        </div>
                                    </template>
                                </dl>
                            </div>

                            {{-- <p class="text-xs text-muted">
                                Per-unit invoice preview only — not a filing-ready document. PTR/PTS are treated as
                                GST-inclusive (derived from MRP).
                            </p> --}}
                        </div>
                    </template>

                    <template x-if="errors.length">
                        <div class="rounded-3xl border border-dashed border-line p-8 text-center text-muted">
                            Enter valid inputs to see the calculation.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pharmaCalc', () => ({
                mrp: '',
                retailer_margin_percent: 20,
                stockist_margin_percent: 10,
                scheme_free_units: 0,
                scheme_paid_units: 10,
                unit_cost: '',
                gst_percent: 5,
                supply_type: 'intra_state',

                inr: new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }),

                money(v) {
                    if (v === null || v === undefined || isNaN(v)) return '—';
                    return this.inr.format(v);
                },

                pct(v) {
                    if (v === null || v === undefined || isNaN(v)) return '—';
                    return Number(v).toFixed(1) + '%';
                },

                num(v) {
                    return v === '' || v === null || v === undefined ? NaN : Number(v);
                },

                get errors() {
                    const e = [];
                    const mrp = this.num(this.mrp);
                    const rm = this.num(this.retailer_margin_percent);
                    const sm = this.num(this.stockist_margin_percent);
                    const paid = this.num(this.scheme_paid_units);
                    const free = this.num(this.scheme_free_units);
                    const gst = this.num(this.gst_percent);

                    if (isNaN(mrp) || mrp <= 0) e.push('MRP must be greater than 0.');
                    if (isNaN(rm) || rm < 0 || rm > 100) e.push(
                        'Retailer margin must be between 0 and 100.');
                    if (isNaN(sm) || sm < 0 || sm > 100) e.push(
                        'Stockist margin must be between 0 and 100.');
                    if (isNaN(paid) || paid <= 0) e.push(
                        'Scheme paid units must be greater than 0.');
                    if (isNaN(free) || free < 0) e.push('Scheme free units cannot be negative.');
                    if (isNaN(gst) || gst < 0) e.push('GST % cannot be negative.');

                    if (!e.length) {
                        const ptr = mrp * (1 - rm / 100);
                        const pts = ptr * (1 - sm / 100);
                        if (ptr < 0 || pts < 0) e.push('These margins make PTR or PTS negative.');
                    }
                    return e;
                },

                get r() {
                    const mrp = this.num(this.mrp);
                    const rm = this.num(this.retailer_margin_percent);
                    const sm = this.num(this.stockist_margin_percent);
                    const paid = this.num(this.scheme_paid_units);
                    const free = this.num(this.scheme_free_units);
                    const gst = this.num(this.gst_percent);
                    const cost = (this.unit_cost === '' || this.unit_cost === null || this.num(this
                            .unit_cost) <= 0) ?
                        null : this.num(this.unit_cost);

                    const ptr = mrp * (1 - rm / 100);
                    const pts = ptr * (1 - sm / 100);
                    const total = paid + free;
                    const effPts = total > 0 ? (paid * pts) / total : pts;

                    const stockistMarginPerUnit = ptr - effPts;
                    const stockistMarginPctActual = ptr ? (stockistMarginPerUnit / ptr) * 100 : 0;
                    const retailerMarginPerUnit = mrp - ptr;

                    const taxablePTS = pts / (1 + gst / 100);
                    const gstAmtPTS = pts - taxablePTS;

                    const invoiceTaxable = paid * taxablePTS;
                    const invoiceGst = paid * gstAmtPTS;
                    const invoiceTotal = invoiceTaxable + invoiceGst;

                    let profit = null,
                        profitMarginPct = null,
                        markupPct = null,
                        ratio = null;
                    if (cost !== null) {
                        profit = effPts - cost;
                        profitMarginPct = effPts ? (profit / effPts) * 100 : 0;
                        markupPct = (profit / cost) * 100;
                        ratio = mrp / cost;
                    }

                    return {
                        ptr,
                        pts,
                        effPts,
                        stockistMarginPerUnit,
                        stockistMarginPctActual,
                        retailerMarginPerUnit,
                        taxablePTS,
                        gstAmtPTS,
                        invoiceTaxable,
                        invoiceGst,
                        invoiceTotal,
                        invoiceUnitTotal: taxablePTS + gstAmtPTS,
                        cost,
                        profit,
                        profitMarginPct,
                        markupPct,
                        ratio,
                    };
                },

                get schemeSummary() {
                    const paid = this.num(this.scheme_paid_units);
                    const free = this.num(this.scheme_free_units);
                    if (!free) return 'No scheme — ' + paid + ' units billed and shipped.';
                    return paid + '+' + free + ' scheme → ' + (paid + free) +
                        ' units shipped per ' + paid + ' billed.';
                },
            }));
        });
    </script>

</x-layouts.public>
