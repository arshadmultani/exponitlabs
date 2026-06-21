<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\NewsPost;
use App\Models\Product;
use App\Models\TherapeuticArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'areas' => TherapeuticArea::active()->withCount(['products' => fn ($q) => $q->where('is_active', true)])->get(),
            'featuredProducts' => Product::active()->featured()->with('therapeuticArea')->take(6)->get(),
            'news' => NewsPost::published()->take(3)->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'areas' => TherapeuticArea::active()->get(),
        ]);
    }

    public function products(Request $request)
    {
        $areas = TherapeuticArea::active()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get();

        $activeSlug = $request->query('area');

        return view('pages.products.index', [
            'areas' => $areas,
            'activeSlug' => $activeSlug,
        ]);
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('therapeuticArea');

        $related = Product::active()
            ->where('therapeutic_area_id', $product->therapeutic_area_id)
            ->whereKeyNot($product->getKey())
            ->take(3)
            ->get();

        return view('pages.products.show', [
            'product' => $product,
            'related' => $related,
        ]);
    }

    public function news()
    {
        return view('pages.news.index', [
            'posts' => NewsPost::published()->paginate(9),
        ]);
    }

    public function newsShow(NewsPost $post)
    {
        abort_unless($post->is_published && $post->published_at && $post->published_at->lte(now()), 404);

        $related = NewsPost::published()
            ->whereKeyNot($post->getKey())
            ->take(3)
            ->get();

        return view('pages.news.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sitemap()
    {
        $static = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('news.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('contact'), 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => route('privacy'), 'priority' => '0.2', 'changefreq' => 'yearly'],
            ['loc' => route('terms'), 'priority' => '0.2', 'changefreq' => 'yearly'],
        ]);

        $products = Product::active()->get()->map(fn (Product $p) => [
            'loc' => route('products.show', $p),
            'lastmod' => $p->updated_at?->toAtomString(),
            'priority' => '0.8',
            'changefreq' => 'monthly',
        ]);

        $news = NewsPost::published()->get()->map(fn (NewsPost $p) => [
            'loc' => route('news.show', $p),
            'lastmod' => $p->updated_at?->toAtomString(),
            'priority' => '0.6',
            'changefreq' => 'monthly',
        ]);

        // Build the XML directly — a Blade view can't safely emit the "<?xml" prolog.
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($static->merge($products)->merge($news) as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1).'</loc>'."\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $submission = ContactSubmission::create($data);

        // Best-effort notification — never let an alert failure break the form.
        // The dedicated discord-alerts queue connection is "sync", so this sends now.
        try {
            DiscordAlert::message(
                "📨 New contact enquiry from **{$submission->name}** ({$submission->email})\n"
                .($submission->organization ? "Org: {$submission->organization}\n" : '')
                .">>> ".\Illuminate\Support\Str::limit($submission->message, 400)
            );
        } catch (\Throwable $e) {
            Log::warning('Contact Discord alert failed: '.$e->getMessage());
        }

        return back()->with('contact_success', true);
    }
}
