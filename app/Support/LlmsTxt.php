<?php

namespace App\Support;

use App\Models\Product;
use App\Models\TherapeuticArea;

class LlmsTxt
{
    /**
     * Generate the standard curated /llms.txt markdown index.
     */
    public static function generate(): string
    {
        $siteUrl = rtrim(config('app.url', url('/')), '/');

        $out = [];
        $out[] = '# Exponit Labs';
        $out[] = '';
        $out[] = '> Exponit Labs is a focused pharmaceutical company based in Mumbai, Maharashtra, India, delivering reliable prescription products across core therapeutic areas — Pain Management, Gastro Care, Antibiotics, and Allergy Treatment — through trusted, WHO-GMP quality-controlled manufacturing partnerships.';
        $out[] = '>';
        $out[] = "> Official Website: {$siteUrl}";
        $out[] = '';
        $out[] = 'Important: Products referenced are prescription medicines for healthcare professionals, distributors, and general trade reference. Information is provided for reference only and does not constitute medical advice.';
        $out[] = '';
        $out[] = 'Every public product, catalog, and reference page has a Markdown twin at the same URL with a .md suffix (content negotiation is also supported via Accept: text/markdown header).';
        $out[] = '';

        // Core Therapeutic Areas
        $out[] = '## Core Therapeutic Areas';
        $out[] = '';

        $areas = TherapeuticArea::active()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        if ($areas->isNotEmpty()) {
            foreach ($areas as $area) {
                $summary = $area->summary ?: 'Prescription formulations in '.$area->name;
                $areaUrl = route('products.index', ['area' => $area->slug]);
                $count = $area->products_count;
                $out[] = "- [{$area->name}]({$areaUrl}): {$summary} ({$count} active ".($count === 1 ? 'formulation' : 'formulations').')';
            }
        } else {
            $out[] = "- [Pain Management]({$siteUrl}/products): Analgesics, anti-inflammatory, and muscle relaxant formulations.";
            $out[] = "- [Gastro Care]({$siteUrl}/products): Proton pump inhibitors, anti-reflux, and gut-health formulations.";
            $out[] = "- [Antibiotics]({$siteUrl}/products): Broad-spectrum and targeted antibacterial therapies.";
            $out[] = "- [Allergy]({$siteUrl}/products): Antihistamines and respiratory allergy relief formulations.";
        }
        $out[] = '';

        // Product Directory
        $out[] = '## Product Directory';
        $out[] = '';

        $products = Product::active()
            ->with('therapeuticArea')
            ->orderBy('therapeutic_area_id')
            ->orderBy('sort_order')
            ->get();

        if ($products->isNotEmpty()) {
            foreach ($products as $product) {
                $productUrl = route('products.show', $product);
                $mdUrl = route('products.show.md', $product);
                $parts = [];

                if ($product->composition) {
                    $parts[] = $product->composition;
                }
                if ($product->strength) {
                    $parts[] = 'Strength: '.$product->strength;
                }
                if ($product->packaging) {
                    $parts[] = 'Pack: '.$product->packaging;
                }
                if ($product->mrp !== null) {
                    $parts[] = 'MRP: ₹'.number_format((float) $product->mrp, 2);
                }
                if ($product->therapeuticArea) {
                    $parts[] = 'Therapeutic Area: '.$product->therapeuticArea->name;
                }

                $meta = implode(' | ', $parts);
                $out[] = "- [{$product->name}]({$productUrl})".($meta ? ": {$meta}" : '')." ([Markdown Twin]({$mdUrl}))";
            }
        } else {
            $out[] = '- Full prescription catalog available at: '.route('products.index');
        }
        $out[] = '';

        // Tools & Trade Resources
        $out[] = '## Tools & Trade Resources';
        $out[] = '';
        $out[] = '- [Pharma Pricing Calculator (PTR & PTS)]('.route('pricing-calculator').') ([Markdown Twin]('.route('pricing-calculator.md').')): Interactive trade calculation tool for Indian pharmaceuticals. Computes Price to Retailer (PTR), Price to Stockist (PTS), scheme adjustments (free goods), GST tax splits (5% / 12%), and retailer/stockist profit margins.';
        $out[] = '- [Product Catalog]('.route('products.index').') ([Markdown Twin]('.route('products.index.md').')): Full catalog of prescription medicines with composition, strength, and packaging details.';
        $out[] = '- [News & Company Updates]('.route('news.index').'): Portfolio announcements, clinical notes, and partnership updates.';
        $out[] = '- [Contact & PCD Franchise Inquiries]('.route('contact').'): Trade inquiries for PCD pharma franchise, hospital supplies, stockist partnerships, and doctor sampling.';
        $out[] = '';

        // Key Information
        $out[] = '## Key Information';
        $out[] = '';
        $out[] = '- [About Exponit Labs]('.route('about').') ([Markdown Twin]('.route('about.md').')): Corporate philosophy, quality assurance, WHO-GMP compliant manufacturing partners, and headquarters in Mumbai, India.';
        $out[] = '- [XML Sitemap]('.route('sitemap').'): Full site index.';
        $out[] = '- [Privacy Policy]('.route('privacy').'): Privacy and personal data processing policies.';
        $out[] = '- [Terms & Conditions]('.route('terms').'): Website terms of use.';
        $out[] = '';

        // Optional Companion
        $out[] = '## Optional';
        $out[] = '';
        $out[] = '- [Full Knowledge Base]('.route('llms-full.txt').'): Complete consolidated text reference of all products, detailed compositions, indications, PTR/PTS formulas, and company background in a single document.';
        $out[] = '';

        return implode("\n", $out);
    }

    /**
     * Generate the comprehensive /llms-full.txt document.
     */
    public static function generateFull(): string
    {
        $siteUrl = rtrim(config('app.url', url('/')), '/');

        $out = [];
        $out[] = '# Exponit Labs — Complete Knowledge Base & Product Compendium';
        $out[] = '';
        $out[] = '> Comprehensive reference document for Exponit Labs, Mumbai, India. Covers company profile, complete pharmaceutical product master, clinical indications, trade pricing mechanisms (PTR & PTS calculation), and distribution terms.';
        $out[] = '';

        // Section 1: Company Profile
        $out[] = '## 1. Company Profile & Quality Standards';
        $out[] = '';
        $out[] = '- **Company Name**: Exponit Labs';
        $out[] = '- **Headquarters**: Mumbai, Maharashtra, India';
        $out[] = '- **Core Focus**: Prescription pharmaceuticals across Pain Management, Gastro Care, Antibiotics, and Allergy Treatment.';
        $out[] = '- **Manufacturing**: Collaborates exclusively with WHO-GMP compliant, state-of-the-art manufacturing facilities adhering to stringent quality control, validated stability testing, and DCGI approved formulations.';
        $out[] = '- **Official Website**: '.$siteUrl;
        $out[] = '- **Contact**: info@exponit.com | '.route('contact');
        $out[] = '';

        // Section 2: Complete Product Master Table
        $out[] = '## 2. Product Master Table';
        $out[] = '';
        $out[] = '| Brand Name | Generic Composition | Strength | Packaging | MRP (INR) | Therapeutic Area |';
        $out[] = '| :--- | :--- | :--- | :--- | :--- | :--- |';

        $products = Product::active()
            ->with('therapeuticArea')
            ->orderBy('therapeutic_area_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($products as $p) {
            $name = str_replace('|', '-', $p->name);
            $comp = str_replace('|', '-', $p->composition ?? 'Prescription formulation');
            $strength = str_replace('|', '-', $p->strength ?? 'As per formulation');
            $pack = str_replace('|', '-', $p->packaging ?? 'Strip/Bottle');
            $mrp = $p->mrp !== null ? '₹'.number_format((float) $p->mrp, 2) : 'Trade List';
            $area = str_replace('|', '-', $p->therapeuticArea?->name ?? 'Pharmaceutical');

            $out[] = "| [{$name}](".route('products.show', $p).") | {$comp} | {$strength} | {$pack} | {$mrp} | {$area} |";
        }
        $out[] = '';

        // Section 3: Detailed Product Datasheets
        $out[] = '## 3. Product Datasheets';
        $out[] = '';

        foreach ($products as $p) {
            $out[] = "### {$p->name}";
            $out[] = '';
            $out[] = '- **URL**: '.route('products.show', $p);
            $out[] = '- **Therapeutic Area**: '.($p->therapeuticArea?->name ?? 'General Medicine');
            if ($p->category) {
                $out[] = "- **Category / Form**: {$p->category}";
            }
            if ($p->composition) {
                $out[] = "- **Active Ingredients (Composition)**: {$p->composition}";
            }
            if ($p->strength) {
                $out[] = "- **Strength**: {$p->strength}";
            }
            if ($p->packaging) {
                $out[] = "- **Packaging**: {$p->packaging}";
            }
            if ($p->mrp !== null) {
                $out[] = '- **Maximum Retail Price (MRP)**: ₹'.number_format((float) $p->mrp, 2).' (Inclusive of all taxes)';
            }
            if ($p->description) {
                $out[] = "- **Clinical Overview & Indications**: {$p->description}";
            }
            $out[] = '- **Prescription Status**: Schedule prescription drug (for use under medical supervision).';
            $out[] = '';
        }

        // Section 4: Indian Pharmaceutical Trade Pricing Standards (PTR & PTS)
        $out[] = '## 4. Pharmaceutical Trade Pricing Reference (India: PTR, PTS & Schemes)';
        $out[] = '';
        $out[] = 'Exponit Labs provides an interactive trade calculation tool at '.route('pricing-calculator').'. Below is the standard mathematical reference used across the Indian pharmaceutical distribution ecosystem:';
        $out[] = '';
        $out[] = '### Pricing Definitions & Formulas';
        $out[] = '1. **MRP (Maximum Retail Price)**: Maximum price printed on the pack inclusive of all taxes, including GST.';
        $out[] = '2. **PTR (Price to Retailer)**: The price at which a stockist invoices the chemist / retail pharmacy, before GST.';
        $out[] = '   - Formula without GST: `PTR = (MRP * (1 - Retailer Margin %)) / (1 + (GST % / 100))`';
        $out[] = '   - Standard Indian trade practice provides 20% margin to retailers on price to retailer (or 16.67% to 20% depending on DPCO category).';
        $out[] = '3. **PTS (Price to Stockist)**: The price at which the pharmaceutical manufacturer or C&F invoices the stockist / distributor, before GST.';
        $out[] = '   - Formula: `PTS = PTR * (1 - Stockist Margin %)`';
        $out[] = '   - Standard Indian trade practice provides 10% margin to stockists.';
        $out[] = '4. **Scheme Adjustments (Free Goods)**:';
        $out[] = '   - When promotional schemes apply (e.g. 10 + 1 free, or 10 + 2 free):';
        $out[] = '   - `Effective PTR = PTR * (Paid Units / (Paid Units + Free Units))`';
        $out[] = '   - `Effective PTS = PTS * (Paid Units / (Paid Units + Free Units))`';
        $out[] = '5. **GST Invoice Calculation**:';
        $out[] = '   - Pharma products in India are typically taxed at either 5% or 12% GST.';
        $out[] = '   - For Intra-State Supply: CGST (half) + SGST (half).';
        $out[] = '   - For Inter-State Supply: IGST (full).';
        $out[] = '   - `Invoice Amount = Taxable Value + (Taxable Value * GST %)`.';
        $out[] = '';

        // Section 5: Distribution & PCD Franchise Opportunities
        $out[] = '## 5. Commercial, Distribution & PCD Franchise';
        $out[] = '';
        $out[] = 'Exponit Labs invites partnerships with pharmaceutical distributors, stockists, and PCD franchise partners across India:';
        $out[] = '- **Monopoly Rights**: District-wise / territory-wise marketing exclusivity available for eligible partners.';
        $out[] = '- **Promotional Support**: Product visual aids, MR bags, doctor samples, reminder cards, catch covers, and digital marketing collaterals.';
        $out[] = '- **Supply Terms**: Transparent commercial billing, zero supply disruption, prompt dispatch from Mumbai logistics hubs.';
        $out[] = '- **Inquiry Link**: Submit trade inquiries directly at '.route('contact').' or via email at info@exponit.com.';
        $out[] = '';

        return implode("\n", $out);
    }

    /**
     * Persist llms.txt and llms-full.txt to disk for ultra-fast static webserver delivery.
     */
    public static function saveToDisk(?string $directory = null): void
    {
        $dir = $directory ?? public_path();

        if (is_dir($dir)) {
            file_put_contents($dir.'/llms.txt', static::generate());
            file_put_contents($dir.'/llms-full.txt', static::generateFull());
        }
    }
}
