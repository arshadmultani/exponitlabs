<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use App\Models\Product;
use App\Models\TherapeuticArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Pain Management',
                'summary' => 'Analgesics and anti-inflammatory formulations for acute and chronic pain relief.',
                'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
                'products' => [
                    ['name' => 'Exonac SP', 'composition' => 'Aceclofenac + Serratiopeptidase', 'strength' => '100/15 mg', 'featured' => true],
                    ['name' => 'Exopara 650', 'composition' => 'Paracetamol', 'strength' => '650 mg', 'featured' => true],
                    ['name' => 'Exoflam Gel', 'composition' => 'Diclofenac Diethylamine', 'strength' => '1.16% w/w', 'featured' => false],
                ],
            ],
            [
                'name' => 'Gastro Care',
                'summary' => 'Acidity, reflux and gut-health formulations built for everyday gastrointestinal care.',
                'icon' => 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5',
                'products' => [
                    ['name' => 'Exopraz D', 'composition' => 'Pantoprazole + Domperidone', 'strength' => '40/30 mg', 'featured' => true],
                    ['name' => 'Exorab DSR', 'composition' => 'Rabeprazole + Domperidone SR', 'strength' => '20/30 mg', 'featured' => false],
                ],
            ],
            [
                'name' => 'Antibiotics',
                'summary' => 'Broad and targeted-spectrum antibacterials manufactured to consistent quality standards.',
                'icon' => 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
                'products' => [
                    ['name' => 'Exoclav 625', 'composition' => 'Amoxicillin + Clavulanic Acid', 'strength' => '500/125 mg', 'featured' => true],
                    ['name' => 'Exozith 500', 'composition' => 'Azithromycin', 'strength' => '500 mg', 'featured' => false],
                    ['name' => 'Exocef 200', 'composition' => 'Cefixime', 'strength' => '200 mg', 'featured' => false],
                ],
            ],
            [
                'name' => 'Allergy',
                'summary' => 'Antihistamines and respiratory-allergy relief for seasonal and chronic symptoms.',
                'icon' => 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z',
                'products' => [
                    ['name' => 'Exocet M', 'composition' => 'Levocetirizine + Montelukast', 'strength' => '5/10 mg', 'featured' => true],
                    ['name' => 'Exofex 120', 'composition' => 'Fexofenadine', 'strength' => '120 mg', 'featured' => false],
                ],
            ],
        ];

        foreach ($areas as $order => $data) {
            $products = $data['products'];
            unset($data['products']);

            $area = TherapeuticArea::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'summary' => $data['summary'],
                'icon' => $data['icon'],
                'sort_order' => $order,
                'is_active' => true,
            ]);

            foreach ($products as $i => $p) {
                Product::create([
                    'therapeutic_area_id' => $area->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'category' => 'Tablet',
                    'composition' => $p['composition'],
                    'strength' => $p['strength'],
                    'packaging' => '10x10 strip',
                    'description' => "{$p['name']} is part of Exponit Labs' {$area->name} range — a {$p['composition']} formulation manufactured through trusted, quality-controlled partnerships.",
                    'is_featured' => $p['featured'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            }
        }

        $news = [
            [
                'title' => 'Exponit Labs expands its Gastro Care range',
                'excerpt' => 'Two new acidity-care formulations join the portfolio, extending our everyday gastrointestinal offering.',
            ],
            [
                'title' => 'Strengthening manufacturing quality partnerships',
                'excerpt' => 'A renewed focus on consistent, quality-controlled manufacturing across all therapeutic areas.',
            ],
            [
                'title' => 'Exponit Labs at the regional pharma forum',
                'excerpt' => 'Our team shared its focused-portfolio approach with healthcare professionals across the region.',
            ],
        ];

        foreach ($news as $i => $n) {
            NewsPost::create([
                'title' => $n['title'],
                'slug' => Str::slug($n['title']),
                'excerpt' => $n['excerpt'],
                'body' => "<p>{$n['excerpt']}</p><p>Exponit Labs continues to deliver focused pharmaceutical products across pain management, gastro care, antibiotics and allergy treatment through trusted manufacturing partnerships.</p>",
                'published_at' => now()->subDays(($i + 1) * 9),
                'is_published' => true,
            ]);
        }
    }
}
