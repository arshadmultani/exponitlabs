<?php

use App\Models\ContactSubmission;
use App\Models\NewsPost;
use App\Models\Product;
use App\Models\TherapeuticArea;
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

it('ships robots.txt and llms.txt with the right content', function () {
    // Static files served by the webserver, not the Laravel router — assert on disk.
    expect(file_get_contents(public_path('robots.txt')))
        ->toContain('Sitemap:')
        ->toContain('Disallow: /console');

    expect(file_get_contents(public_path('llms.txt')))
        ->toContain('Exponit Labs');
});
