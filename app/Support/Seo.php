<?php

namespace App\Support;

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

    /** Sitewide company identity. */
    public static function organization(): \Spatie\SchemaOrg\Organization
    {
        return Schema::organization()
            ->name(self::NAME)
            ->url(url('/'))
            ->logo(asset('images/logo.svg'))
            ->description(self::DESCRIPTION)
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
            ->url(url('/'));
    }

    /** Product detail structured data. */
    public static function product(Product $product): \Spatie\SchemaOrg\Product
    {
        $schema = Schema::product()
            ->name($product->name)
            ->url(route('products.show', $product))
            ->category($product->therapeuticArea?->name);

        if ($product->description) {
            $schema->description($product->description);
        }

        if ($product->composition) {
            $schema->activeIngredient($product->composition);
        }

        if ($product->imageUrl()) {
            $schema->image($product->imageUrl());
        }

        $schema->brand(Schema::brand()->name(self::NAME));

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
}
