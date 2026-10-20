<?php

use App\Models\ContactSubmission;
use App\Models\NewsPost;
use App\Models\Product;
use App\Models\TherapeuticArea;
use App\Observers\ProductObserver;
use App\Observers\TherapeuticAreaObserver;
use App\Support\LlmsTxt;
use Illuminate\Support\Facades\Http;

it('renders the static public pages', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with([
    'home',
    'about',
    'products.index',
    'news.index',
    'contact',
    'privacy',
    'terms',
]);

it('renders the homepage with seeded content', function () {
    $area = TherapeuticArea::factory()->create();
    Product::factory()->featured()->for($area, 'therapeuticArea')->create(['name' => 'Exonac SP']);
    NewsPost::factory()->create(['title' => 'Big news today']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Exonac SP')
        ->assertSee($area->name)
        ->assertSee('Big news today');
});

it('shows an active product and 404s an inactive one', function () {
    $active = Product::factory()->create(['is_active' => true]);
    $inactive = Product::factory()->create(['is_active' => false]);

    $this->get(route('products.show', $active))->assertOk()->assertSee($active->name);
    $this->get(route('products.show', $inactive))->assertNotFound();
});

it('filters products by therapeutic area', function () {
    $pain = TherapeuticArea::factory()->create(['name' => 'Pain Management']);
    $gastro = TherapeuticArea::factory()->create(['name' => 'Gastro Care']);
    Product::factory()->for($pain, 'therapeuticArea')->create(['name' => 'PainProductX']);
    Product::factory()->for($gastro, 'therapeuticArea')->create(['name' => 'GastroProductY']);

    $this->get(route('products.index', ['area' => $pain->slug]))
        ->assertOk()
        ->assertSee('PainProductX')
        ->assertDontSee('GastroProductY');
});

it('stores a contact submission and flashes success', function () {
    Http::fake(); // intercept the Discord webhook call

    $payload = [
        'name' => 'Dr. Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '9999999999',
        'organization' => 'City Clinic',
        'message' => 'Please share more about your gastro range.',
    ];

    $this->post(route('contact.submit'), $payload)
        ->assertRedirect()
        ->assertSessionHas('contact_success', true);

    expect(ContactSubmission::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('validates the contact form', function () {
    $this->post(route('contact.submit'), [])
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(ContactSubmission::count())->toBe(0);
});

it('blocks a contact submission that trips the honeypot', function () {
    config([
        'honeypot.enabled' => true,
        'honeypot.randomize_name_field_name' => false,
        'honeypot.name_field_name' => 'my_name',
    ]);

    $this->post(route('contact.submit'), [
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'message' => 'buy cheap things',
        'my_name' => 'i am a bot', // honeypot field a human never fills
    ]);

    expect(ContactSubmission::count())->toBe(0);
});

it('serves a valid XML sitemap including products', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $res = $this->get('/sitemap.xml');

    $res->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    expect($res->getContent())
        ->toContain('<?xml')
        ->toContain('<urlset')
        ->toContain(route('products.show', $product));
});

it('emits sitewide and per-page JSON-LD', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $this->get(route('home'))
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"FAQPage"', false)
        ->assertSee('"contactPoint"', false);

    $this->get(route('products.show', $product))
        ->assertSee('"@type":"Product"', false)
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('"manufacturer"', false);
});

it('shows a published news post and 404s a draft', function () {
    $post = NewsPost::factory()->create(['title' => 'A Real Update']);
    $draft = NewsPost::factory()->draft()->create();

    $this->get(route('news.show', $post))
        ->assertOk()
        ->assertSee('A Real Update')
        ->assertSee('"@type":"NewsArticle"', false);

    $this->get(route('news.show', $draft))->assertNotFound();
});

it('lists news posts that link to their detail pages', function () {
    $post = NewsPost::factory()->create();

    $this->get(route('news.index'))
        ->assertOk()
        ->assertSee(route('news.show', $post));
});

it('includes published news in the sitemap', function () {
    $post = NewsPost::factory()->create();

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('news.show', $post));
});

it('shows the custom 404 page with the game', function () {
    $this->get('/this-page-does-not-exist-xyz')
        ->assertNotFound()
        ->assertSee('404')
        ->assertSee('Back to home');
});

it('serves the internal pricing calculator as a noindex page', function () {
    $this->get(route('pricing-calculator'))
        ->assertOk()
        ->assertSee('pharmaCalc', false)
        ->assertSee('noindex, nofollow', false);
});

it('ships robots.txt, llms.txt, and llms-full.txt with the right content', function () {
    // Static files served by the webserver, not the Laravel router — assert on disk.
    expect(file_get_contents(public_path('robots.txt')))
        ->toContain('Sitemap:')
        ->toContain('Disallow: /console')
        ->toContain('User-agent: GPTBot')
        ->toContain('User-agent: OAI-SearchBot');

    expect(file_get_contents(public_path('llms.txt')))
        ->toContain('Exponit Labs')
        ->toContain('## Core Therapeutic Areas')
        ->toContain('## Product Directory');

    expect(file_get_contents(public_path('llms-full.txt')))
        ->toContain('## 1. Company Profile & Quality Standards')
        ->toContain('## 2. Therapeutic Areas & Clinical Divisions')
        ->toContain('## 3. Product Master Table')
        ->toContain('Pharmaceutical Trade Pricing Reference (India: PTR, PTS & Schemes)');
});

it('serves dynamic /llms.txt and /llms-full.txt with active catalog data and excludes inactive products', function () {
    $area = TherapeuticArea::factory()->create(['name' => 'Cardio Care']);
    $activeProduct = Product::factory()->for($area, 'therapeuticArea')->create([
        'name' => 'Cardionit-50',
        'composition' => 'Atenolol 50mg',
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->for($area, 'therapeuticArea')->create([
        'name' => 'SecretMolecule-X',
        'is_active' => false,
    ]);

    $this->get(route('llms.txt'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Cardionit-50')
        ->assertSee('Atenolol 50mg')
        ->assertDontSee('SecretMolecule-X');

    $this->get(route('llms-full.txt'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Cardionit-50')
        ->assertSee('PTR (Price to Retailer)')
        ->assertDontSee('SecretMolecule-X');
});

it('includes the describedby link in the public layout head pointing to llms.txt', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<link rel="describedby" type="text/plain" href="'.route('llms.txt').'">', false);
});

it('includes the pricing calculator in sitemap.xml', function () {
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('pricing-calculator'), false);
});

it('generates llms.txt and llms-full.txt via artisan command', function () {
    $tempDir = sys_get_temp_dir().'/llms-test-'.uniqid();
    mkdir($tempDir);

    $this->artisan('seo:generate-llms', ['--dir' => $tempDir])
        ->assertSuccessful();

    expect(file_exists($tempDir.'/llms.txt'))->toBeTrue();
    expect(file_exists($tempDir.'/llms-full.txt'))->toBeTrue();

    unlink($tempDir.'/llms.txt');
    unlink($tempDir.'/llms-full.txt');
    rmdir($tempDir);
});

it('serves product markdown twin with YAML frontmatter, specs table, and alternate link', function () {
    $area = TherapeuticArea::factory()->create(['name' => 'Pain Management']);
    $activeProduct = Product::factory()->for($area, 'therapeuticArea')->create([
        'name' => 'Acevo-Fast',
        'composition' => 'Aceclofenac 100mg + Paracetamol 325mg',
        'strength' => '100mg/325mg',
        'packaging' => '10x10 Tablets',
        'mrp' => 85.50,
        'description' => 'Fast-acting anti-inflammatory analgesic.',
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->for($area, 'therapeuticArea')->create([
        'name' => 'HiddenProduct',
        'is_active' => false,
    ]);

    // Test .md twin route
    $this->get(route('products.show.md', $activeProduct))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: pharmaceutical_product')
        ->assertSee('slug: '.$activeProduct->slug)
        ->assertSee('mrp: 85.50')
        ->assertSee('Aceclofenac 100mg + Paracetamol 325mg')
        ->assertSee('Fast-acting anti-inflammatory analgesic.');

    // Inactive product 404s
    $this->get(route('products.show.md', $inactiveProduct))
        ->assertNotFound();

    // HTML view includes alternate markdown link
    $this->get(route('products.show', $activeProduct))
        ->assertOk()
        ->assertSee('<link rel="alternate" type="text/markdown" href="'.route('products.show.md', $activeProduct).'">', false);
});

it('serves products catalog markdown twin with area filtering and alternate link', function () {
    $pain = TherapeuticArea::factory()->create(['name' => 'Pain Management']);
    $gastro = TherapeuticArea::factory()->create(['name' => 'Gastro Care']);
    $p1 = Product::factory()->for($pain, 'therapeuticArea')->create(['name' => 'PainRelief-X', 'is_active' => true]);
    $p2 = Product::factory()->for($gastro, 'therapeuticArea')->create(['name' => 'GastroRelief-Y', 'is_active' => true]);

    $this->get(route('products.index.md'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('PainRelief-X')
        ->assertSee('GastroRelief-Y');

    $this->get(route('products.index.md', ['area' => $pain->slug]))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('PainRelief-X')
        ->assertDontSee('GastroRelief-Y');

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('<link rel="alternate" type="text/markdown" href="'.route('products.index.md').'">', false);
});

it('serves pricing calculator markdown twin and alternate link', function () {
    $this->get(route('pricing-calculator.md'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: pricing_calculator_reference')
        ->assertSee('Price to Retailer (PTR)')
        ->assertSee('Price to Stockist (PTS)')
        ->assertSee('PTR = (MRP * (1 - (Retailer Margin % / 100)))');

    $this->get(route('pricing-calculator'))
        ->assertOk()
        ->assertSee('<link rel="alternate" type="text/markdown" href="'.route('pricing-calculator.md').'">', false);
});

it('serves about markdown twin and alternate link', function () {
    $this->get(route('about.md'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: company_profile')
        ->assertSee('WHO-GMP Manufacturing')
        ->assertSee('DCGI Approved');

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('<link rel="alternate" type="text/markdown" href="'.route('about.md').'">', false);
});

it('supports content negotiation via Accept: text/markdown', function () {
    $product = Product::factory()->create([
        'name' => 'NegotiatedProduct',
        'is_active' => true,
    ]);

    $this->withHeader('Accept', 'text/markdown')
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: pharmaceutical_product')
        ->assertSee('NegotiatedProduct');

    $this->withHeader('Accept', 'text/markdown')
        ->get(route('pricing-calculator'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: pricing_calculator_reference');

    $this->withHeader('Accept', 'text/markdown')
        ->get(route('about'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
        ->assertSee('type: company_profile');
});

it('emits rich pharmaceutical Drug and Product structured data on product detail pages', function () {
    $area = TherapeuticArea::factory()->create(['name' => 'Gastro Care']);
    $product = Product::factory()->for($area, 'therapeuticArea')->create([
        'name' => 'Gastronit-Max',
        'composition' => 'Pantoprazole 40mg + Domperidone 30mg',
        'category' => 'Capsule',
        'mrp' => 145.00,
        'description' => 'Indicated for GERD, reflux esophagitis, and acid peptic disorders.',
        'is_active' => true,
    ]);

    $response = $this->get(route('products.show', $product))->assertOk();

    // Verify Product schema with commercial offers
    $response
        ->assertSee('"@type":"Product"', false)
        ->assertSee('"name":"Gastronit-Max"', false)
        ->assertSee('"sku":"'.$product->slug.'"', false)
        ->assertSee('"price":145', false)
        ->assertSee('"priceCurrency":"INR"', false)
        ->assertSee('"availability":"https://schema.org/InStock"', false);

    // Verify Drug schema with clinical properties
    $response
        ->assertSee('"@type":"Drug"', false)
        ->assertSee('"activeIngredient":"Pantoprazole 40mg + Domperidone 30mg"', false)
        ->assertSee('"dosageForm":"Capsule"', false)
        ->assertSee('"prescriptionStatus":"https://schema.org/PrescriptionOnly"', false)
        ->assertSee('Schedule prescription drug', false);
});

it('emits MedicalBusiness additionalType on organization schema', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('"additionalType":["https://schema.org/MedicalBusiness","https://schema.org/Corporation"]', false)
        ->assertSee('Pharmaceutical Manufacturing', false);
});

it('emits WebApplication schema on pricing calculator page', function () {
    $this->get(route('pricing-calculator'))
        ->assertOk()
        ->assertSee('"@type":"WebApplication"', false)
        ->assertSee('Pharmaceutical Trade Pricing Calculator (PTR & PTS)', false)
        ->assertSee('BusinessApplication', false);
});

it('automatically regenerates llms.txt when a product is created, edited, or deleted', function () {
    $tempDir = sys_get_temp_dir().'/llms-product-observer-'.uniqid();
    mkdir($tempDir);

    LlmsTxt::$diskDirectory = $tempDir;
    ProductObserver::$enabledInTests = true;

    try {
        $area = TherapeuticArea::factory()->create(['name' => 'Cardiology Care']);

        // 1. Creation test (new product)
        $product = Product::factory()->for($area, 'therapeuticArea')->create([
            'name' => 'Cardio-Stab 50',
            'mrp' => 210.00,
            'is_active' => true,
        ]);

        expect(file_exists($tempDir.'/llms.txt'))->toBeTrue();
        expect(file_get_contents($tempDir.'/llms.txt'))->toContain('Cardio-Stab 50');
        expect(file_get_contents($tempDir.'/llms-full.txt'))->toContain('Cardio-Stab 50');

        // 2. Edit test (update existing product)
        $product->update([
            'name' => 'Cardio-Stab 100 Ultra',
            'mrp' => 350.00,
        ]);

        expect(file_get_contents($tempDir.'/llms.txt'))->toContain('Cardio-Stab 100 Ultra');
        expect(file_get_contents($tempDir.'/llms.txt'))->not->toContain('Cardio-Stab 50');
        expect(file_get_contents($tempDir.'/llms-full.txt'))->toContain('Cardio-Stab 100 Ultra');

        // 3. Deletion test
        $product->delete();

        expect(file_get_contents($tempDir.'/llms.txt'))->not->toContain('Cardio-Stab 100 Ultra');
    } finally {
        LlmsTxt::$diskDirectory = null;
        ProductObserver::$enabledInTests = false;

        if (file_exists($tempDir.'/llms.txt')) {
            unlink($tempDir.'/llms.txt');
        }
        if (file_exists($tempDir.'/llms-full.txt')) {
            unlink($tempDir.'/llms-full.txt');
        }
        rmdir($tempDir);
    }
});

it('automatically regenerates llms.txt when a therapeutic area is saved or updated', function () {
    $tempDir = sys_get_temp_dir().'/llms-area-observer-'.uniqid();
    mkdir($tempDir);

    LlmsTxt::$diskDirectory = $tempDir;
    TherapeuticAreaObserver::$enabledInTests = true;

    try {
        $area = TherapeuticArea::factory()->create([
            'name' => 'Neurology Solutions',
            'summary' => 'Formulations for nervous system and neurological care.',
            'is_active' => true,
        ]);

        expect(file_exists($tempDir.'/llms.txt'))->toBeTrue();
        expect(file_get_contents($tempDir.'/llms.txt'))->toContain('Neurology Solutions');
        expect(file_get_contents($tempDir.'/llms-full.txt'))->toContain('Neurology Solutions');
    } finally {
        LlmsTxt::$diskDirectory = null;
        TherapeuticAreaObserver::$enabledInTests = false;

        if (file_exists($tempDir.'/llms.txt')) {
            unlink($tempDir.'/llms.txt');
        }
        if (file_exists($tempDir.'/llms-full.txt')) {
            unlink($tempDir.'/llms-full.txt');
        }
        rmdir($tempDir);
    }
});
