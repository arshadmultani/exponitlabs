<?php

namespace App\Support;

use App\Models\NewsPost;
use App\Models\Product;
use Spatie\SchemaOrg\Schema;

/**
 * Central builder for the site's JSON-LD structured data. Answer engines
 * (Perplexity, ChatGPT, AI Overviews) and search engines read these heavily,
 * so they back both SEO and AEO. Each method returns a Spatie schema object;
 * call ->toScript() in a Blade view to emit the <script type="ld+json"> tag.
 */
class Seo
{
    public const NAME = 'Exponit Labs';

    public const DESCRIPTION = 'Focused pharmaceutical products across pain management, gastro care, antibiotics and allergy treatment, through trusted manufacturing partnerships.';

    /** Contact email surfaced in structured data. Update if it changes. */
    public const EMAIL = 'info@exponit.com';

    /** Therapeutic areas — fixed list for schema knowsAbout (avoids a per-page query). */
    public const AREAS = ['Pain Management', 'Gastro Care', 'Antibiotics', 'Allergy'];

    /** Official profiles — used for schema sameAs (entity disambiguation) and the footer. */
    public const SOCIALS = [
        'instagram' => 'https://instagram.com/exponitlabs',
        'x' => 'https://x.com/exponitlabs',
        'facebook' => 'https://www.facebook.com/exponitlabs',
        'linkedin' => 'https://www.linkedin.com/company/exponitlabs',
    ];

    /** Brand FAQs — single source for both the on-page section and FAQPage schema. */
    public const FAQS = [
        [
            'q' => 'What does Exponit Labs do?',
            'a' => 'Exponit Labs is a pharmaceutical company that delivers a focused range of prescription products across pain management, gastro care, antibiotics and allergy treatment, manufactured through trusted, quality-controlled partnerships.',
        ],
        [
            'q' => 'Which therapeutic areas does Exponit Labs cover?',
            'a' => 'Four: pain management, gastro care, antibiotics and allergy treatment.',
        ],
        [
            'q' => 'Where is Exponit Labs based?',
            'a' => 'Exponit Labs is based in Mumbai, Maharashtra, India.',
        ],
        [
            'q' => 'Are Exponit Labs products available without a prescription?',
            'a' => 'No. Products referenced are prescription medicines and should only be used under medical supervision. Information on this site is for healthcare professionals and general reference, not medical advice.',
        ],
        [
            'q' => 'How can I contact Exponit Labs about products or partnerships?',
            'a' => 'Use the contact form on the website or email '.self::EMAIL.'. Our team responds to product and partnership enquiries.',
        ],
    ];

    /** Sitewide company identity — a rich entity to aid brand recognition. */
    public static function organization(): \Spatie\SchemaOrg\Organization
    {
        return Schema::organization()
            ->setProperty('@id', url('/').'#organization')
            ->name(self::NAME)
            ->alternateName('Exponit')
            ->url(url('/'))
            ->logo(Schema::imageObject()->url(asset('images/logo.svg')))
            ->image(asset('og-image.png'))
            ->description(self::DESCRIPTION)
            ->knowsAbout(self::AREAS)
            ->email(self::EMAIL)
            ->sameAs(array_values(self::SOCIALS))
            ->contactPoint(
                Schema::contactPoint()
                    ->contactType('customer service')
                    ->email(self::EMAIL)
                    ->areaServed('IN')
            )
            ->address(
                Schema::postalAddress()
                    ->addressLocality('Mumbai')
                    ->addressRegion('Maharashtra')
                    ->addressCountry('IN')
            );
    }

    /** Sitewide WebSite node. */
    public static function website(): \Spatie\SchemaOrg\WebSite
    {
        return Schema::webSite()
            ->name(self::NAME)
            ->url(url('/'))
            ->publisher(Schema::organization()->setProperty('@id', url('/').'#organization'));
    }

    /** Product detail structured data. */
    public static function product(Product $product): \Spatie\SchemaOrg\Product
    {
        $schema = Schema::product()
            ->name($product->name)
            ->url(route('products.show', $product))
            ->category($product->therapeuticArea?->name)
            ->brand(Schema::brand()->name(self::NAME))
            ->manufacturer(Schema::organization()->setProperty('@id', url('/').'#organization'));

        if ($product->description) {
            $schema->description($product->description);
        }

        if ($product->composition) {
            $schema->activeIngredient($product->composition);
        }

        if ($product->imageUrl()) {
            $schema->image($product->imageUrl());
        }

        return $schema;
    }

    /** Breadcrumb trail for a product page. */
    public static function productBreadcrumb(Product $product): \Spatie\SchemaOrg\BreadcrumbList
    {
        $items = [
            ['Products', route('products.index')],
        ];

        if ($product->therapeuticArea) {
            $items[] = [$product->therapeuticArea->name, route('products.index', ['area' => $product->therapeuticArea->slug])];
        }

        $items[] = [$product->name, route('products.show', $product)];

        return Schema::breadcrumbList()->itemListElement(
            collect($items)->values()->map(fn ($item, $i) => Schema::listItem()
                ->position($i + 1)
                ->name($item[0])
                ->item($item[1])
            )->all()
        );
    }

    /** News article structured data. */
    public static function newsArticle(NewsPost $post): \Spatie\SchemaOrg\NewsArticle
    {
        $schema = Schema::newsArticle()
            ->headline($post->title)
            ->mainEntityOfPage(route('news.show', $post))
            ->datePublished(optional($post->published_at)->toAtomString())
            ->dateModified($post->updated_at?->toAtomString())
            ->author(Schema::organization()->setProperty('@id', url('/').'#organization'))
            ->publisher(Schema::organization()->setProperty('@id', url('/').'#organization'));

        if ($post->excerpt) {
            $schema->description($post->excerpt);
        }

        if ($post->coverUrl()) {
            $schema->image($post->coverUrl());
        }

        return $schema;
    }

    /** FAQ structured data built from the shared FAQS list. */
    public static function faqPage(): \Spatie\SchemaOrg\FAQPage
    {
        return Schema::fAQPage()->mainEntity(
            collect(self::FAQS)->map(fn ($faq) => Schema::question()
                ->name($faq['q'])
                ->acceptedAnswer(Schema::answer()->text($faq['a']))
            )->all()
        );
    }
}
