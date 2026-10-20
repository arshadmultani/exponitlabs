<?php

namespace App\Support;

use App\Models\Product;
use App\Models\TherapeuticArea;

class MarkdownTwin
{
    /**
     * Generate the markdown twin for a single product.
     */
    public static function product(Product $product): string
    {
        $product->loadMissing('therapeuticArea');
        $mrpFormatted = $product->mrp !== null ? number_format((float) $product->mrp, 2) : null;

        $yaml = [];
        $yaml[] = '---';
        $yaml[] = 'type: pharmaceutical_product';
        $yaml[] = 'slug: '.$product->slug;
        $yaml[] = 'name: "'.addslashes($product->name).'"';
        $yaml[] = 'brand: "Exponit Labs"';
        if ($product->therapeuticArea) {
            $yaml[] = 'therapeutic_area: "'.addslashes($product->therapeuticArea->name).'"';
        }
        if ($product->category) {
            $yaml[] = 'dosage_form: "'.addslashes($product->category).'"';
        }
        if ($product->composition) {
            $yaml[] = 'composition: "'.addslashes($product->composition).'"';
        }
        if ($product->strength) {
            $yaml[] = 'strength: "'.addslashes($product->strength).'"';
        }
        if ($product->packaging) {
            $yaml[] = 'packaging: "'.addslashes($product->packaging).'"';
        }
        if ($mrpFormatted !== null) {
            $yaml[] = 'mrp: '.$mrpFormatted;
            $yaml[] = 'currency: INR';
            $yaml[] = 'tax_basis: inclusive_mrp';
        }
        $yaml[] = 'country: IN';
        $yaml[] = 'prescription_required: true';
        $yaml[] = 'url: '.route('products.show', $product);
        $yaml[] = '---';

        $body = [];
        $body[] = '# '.$product->name;
        $body[] = '';
        $body[] = '> '.$product->name.' is a prescription formulation by Exponit Labs (Mumbai, India) indicated for use under medical supervision.';
        $body[] = '';
        $body[] = '## Technical & Product Specifications';
        $body[] = '';
        $body[] = '| Specification | Detail |';
        $body[] = '| :--- | :--- |';
        $body[] = '| Product Name | '.$product->name.' |';
        $body[] = '| Brand / Marketer | Exponit Labs |';
        $body[] = '| Therapeutic Area | '.($product->therapeuticArea?->name ?? 'General Medicine').' |';
        $body[] = '| Dosage Form / Category | '.($product->category ?: 'Solid / Liquid Formulation').' |';
        $body[] = '| Active Ingredients (Composition) | '.($product->composition ?: 'Proprietary formulation').' |';
        $body[] = '| Strength | '.($product->strength ?: 'As labeled').' |';
        $body[] = '| Packaging Type | '.($product->packaging ?: 'Standard Strip / Bottle').' |';
        if ($mrpFormatted !== null) {
            $body[] = '| Maximum Retail Price (MRP) | ₹'.$mrpFormatted.' (inclusive of all taxes) |';
        }
        $body[] = '| Country of Origin | India |';
        $body[] = '| Prescription Classification | Schedule Prescription Drug (Rx) |';
        $body[] = '';

        if ($product->description) {
            $body[] = '## Clinical Overview & Indications';
            $body[] = '';
            $body[] = trim($product->description);
            $body[] = '';
        }

        $body[] = '## Commercial & Trade Reference';
        $body[] = '';
        if ($mrpFormatted !== null) {
            $body[] = "- **MRP**: ₹{$mrpFormatted} per pack (inclusive of GST).";
        }
        $body[] = '- **Trade Pricing Tool (PTR & PTS)**: Calculate exact Price to Retailer (PTR), Price to Stockist (PTS), and promotional scheme pricing at ['.route('pricing-calculator').']('.route('pricing-calculator.md').').';
        $body[] = '- **Distribution & Stockist Inquiries**: For PCD franchise, hospital supply, or stockist partnership, contact Exponit Labs via ['.route('contact').']('.route('contact').').';
        $body[] = '';
        $body[] = '## Important Safety & Regulatory Notice';
        $body[] = '';
        $body[] = 'This product is a prescription drug to be used strictly under registered medical practitioner supervision. Store in a cool, dry place away from direct sunlight. Keep out of reach of children.';
        $body[] = '';

        return implode("\n", array_merge($yaml, [''], $body));
    }

    /**
     * Generate the markdown twin for the products directory (with optional therapeutic area filter).
     */
    public static function products(?string $areaSlug = null): string
    {
        $query = Product::active()->with('therapeuticArea')->orderBy('therapeutic_area_id')->orderBy('sort_order');
        $currentArea = null;

        if ($areaSlug) {
            $currentArea = TherapeuticArea::where('slug', $areaSlug)->first();
            if ($currentArea) {
                $query->where('therapeutic_area_id', $currentArea->id);
            }
        }

        $products = $query->get();

        $yaml = [];
        $yaml[] = '---';
        $yaml[] = 'type: product_catalog';
        $yaml[] = 'brand: "Exponit Labs"';
        $yaml[] = 'total_products: '.$products->count();
        if ($currentArea) {
            $yaml[] = 'therapeutic_area: "'.addslashes($currentArea->name).'"';
        }
        $yaml[] = 'country: IN';
        $yaml[] = 'currency: INR';
        $yaml[] = 'url: '.route('products.index', $currentArea ? ['area' => $currentArea->slug] : []);
        $yaml[] = '---';

        $body = [];
        $body[] = '# Exponit Labs — Prescription Product Directory';
        $body[] = '';
        if ($currentArea) {
            $body[] = "> Showing prescription formulations in **{$currentArea->name}**.";
        } else {
            $body[] = '> Complete directory of prescription pharmaceutical formulations manufactured through WHO-GMP compliant partnerships by Exponit Labs (Mumbai, India).';
        }
        $body[] = '';
        $body[] = '## Formulations';
        $body[] = '';
        $body[] = '| Formulation | Generic Composition | Strength | Packaging | MRP (INR) | Markdown Twin |';
        $body[] = '| :--- | :--- | :--- | :--- | :--- | :--- |';

        foreach ($products as $p) {
            $mrp = $p->mrp !== null ? '₹'.number_format((float) $p->mrp, 2) : 'Trade';
            $comp = $p->composition ?: '-';
            $strength = $p->strength ?: '-';
            $pack = $p->packaging ?: '-';
            $mdUrl = route('products.show.md', $p);
            $body[] = "| [{$p->name}](".route('products.show', $p).") | {$comp} | {$strength} | {$pack} | {$mrp} | [Markdown]({$mdUrl}) |";
        }
        $body[] = '';
        $body[] = '## Core Therapeutic Areas';
        $body[] = '';
        $areas = TherapeuticArea::active()->get();
        foreach ($areas as $area) {
            $body[] = "- [{$area->name}](".route('products.index.md', ['area' => $area->slug]).'): '.($area->summary ?: 'Formulations in '.$area->name);
        }
        $body[] = '';

        return implode("\n", array_merge($yaml, [''], $body));
    }

    /**
     * Generate the markdown twin for the PTR/PTS pricing calculator.
     */
    public static function pricingCalculator(): string
    {
        $yaml = [];
        $yaml[] = '---';
        $yaml[] = 'type: pricing_calculator_reference';
        $yaml[] = 'title: "Pharma Trade Pricing Calculator (PTR & PTS)"';
        $yaml[] = 'country: IN';
        $yaml[] = 'currency: INR';
        $yaml[] = 'url: '.route('pricing-calculator');
        $yaml[] = '---';

        $body = [];
        $body[] = '# Pharmaceutical Pricing Calculator & Trade Margin Reference';
        $body[] = '';
        $body[] = '> Official mathematical reference and methodology for calculating Price to Retailer (PTR), Price to Stockist (PTS), Scheme-adjusted pricing, and GST invoice breakdowns in the Indian pharmaceutical trade.';
        $body[] = '';
        $body[] = '## Interactive Tool';
        $body[] = '';
        $body[] = 'Use the live calculator at: ['.route('pricing-calculator').']('.route('pricing-calculator').')';
        $body[] = '';
        $body[] = '## Core Trade Formulas';
        $body[] = '';
        $body[] = '### 1. Price to Retailer (PTR)';
        $body[] = 'Price at which the stockist bills the chemist / retail pharmacy.';
        $body[] = '```';
        $body[] = 'PTR = (MRP * (1 - (Retailer Margin % / 100))) / (1 + (GST % / 100))';
        $body[] = '```';
        $body[] = '- **Standard Retailer Margin in India**: 20% on price to retailer (or 16.67% to 20% depending on DPCO regulation).';
        $body[] = '- **GST Rates**: Typically 12% for most finished pharmaceutical formulations (or 5% for critical life-saving items).';
        $body[] = '';
        $body[] = '### 2. Price to Stockist (PTS)';
        $body[] = 'Price at which the manufacturer or C&F bills the stockist / distributor.';
        $body[] = '```';
        $body[] = 'PTS = PTR * (1 - (Stockist Margin % / 100))';
        $body[] = '```';
        $body[] = '- **Standard Stockist Margin in India**: 10% on price to stockist.';
        $body[] = '';
        $body[] = '### 3. Scheme Adjustments (Free Goods / Deals)';
        $body[] = 'When promotional schemes such as "10 + 1 free" or "10 + 2 free" apply:';
        $body[] = '```';
        $body[] = 'Effective PTR = PTR * (Paid Units / (Paid Units + Free Units))';
        $body[] = 'Effective PTS = PTS * (Paid Units / (Paid Units + Free Units))';
        $body[] = '```';
        $body[] = '';
        $body[] = '### 4. Worked Calculation Example';
        $body[] = '- **Given**: MRP = ₹120.00, Retailer Margin = 20%, Stockist Margin = 10%, GST = 12%';
        $body[] = '- **PTR Calculation**:';
        $body[] = '  - Retailer cut: ₹120 * 0.20 = ₹24.00';
        $body[] = '  - Retail price before tax: ₹120 - ₹24 = ₹96.00';
        $body[] = '  - PTR (excluding GST): ₹96.00 / 1.12 = ₹85.71';
        $body[] = '- **PTS Calculation**:';
        $body[] = '  - Stockist margin: ₹85.71 * 0.10 = ₹8.57';
        $body[] = '  - PTS (excluding GST): ₹85.71 - ₹8.57 = ₹77.14';
        $body[] = '- **Invoice with GST (12%)**:';
        $body[] = '  - GST amount on PTS: ₹77.14 * 0.12 = ₹9.26';
        $body[] = '  - Stockist Invoice Total: ₹77.14 + ₹9.26 = ₹86.40';
        $body[] = '';

        return implode("\n", array_merge($yaml, [''], $body));
    }

    /**
     * Generate the markdown twin for the About page.
     */
    public static function about(): string
    {
        $siteUrl = rtrim(config('app.url', url('/')), '/');

        $yaml = [];
        $yaml[] = '---';
        $yaml[] = 'type: company_profile';
        $yaml[] = 'name: "Exponit Labs"';
        $yaml[] = 'headquarters: "Mumbai, Maharashtra, India"';
        $yaml[] = 'url: '.route('about');
        $yaml[] = '---';

        $body = [];
        $body[] = '# About Exponit Labs';
        $body[] = '';
        $body[] = '> Exponit Labs is a focused pharmaceutical company based in Mumbai, Maharashtra, India, dedicated to delivering reliable, high-bioavailability prescription medicines across core therapeutic disciplines.';
        $body[] = '';
        $body[] = '## Quality & Manufacturing Standards';
        $body[] = '';
        $body[] = '- **WHO-GMP Manufacturing**: All manufacturing partners operate state-of-the-art facilities certified for Good Manufacturing Practices (GMP) by the World Health Organization.';
        $body[] = '- **DCGI Approved**: Formulations strictly comply with Drug Controller General of India (DCGI) regulatory approvals and pharmacopeial standards (IP/BP/USP).';
        $body[] = '- **Analytical Verification**: Multi-stage quality control from raw Active Pharmaceutical Ingredients (API) to finished dosage forms, including stability testing across Zone IV climatic zones.';
        $body[] = '';
        $body[] = '## Therapeutic Focus Areas';
        $body[] = '';
        $body[] = '1. **Pain Management**: Non-steroidal anti-inflammatory drugs (NSAIDs), proteolytic enzymes (Serratiopeptidase), and muscle relaxants.';
        $body[] = '2. **Gastro Care**: Proton pump inhibitors (Pantoprazole, Rabeprazole), antiemetics/prokinetics (Domperidone), and mucosal protectants.';
        $body[] = '3. **Antibiotics**: Targeted and broad-spectrum antimicrobial therapies.';
        $body[] = '4. **Allergy**: Antihistaminic and respiratory symptom-control medications.';
        $body[] = '';
        $body[] = '## Contact & Headquarters';
        $body[] = '';
        $body[] = '- **Location**: Mumbai, Maharashtra, India';
        $body[] = '- **Official Website**: '.$siteUrl;
        $body[] = '- **Inquiries**: info@exponit.com | ['.route('contact').']('.route('contact').')';
        $body[] = '';

        return implode("\n", array_merge($yaml, [''], $body));
    }
}
