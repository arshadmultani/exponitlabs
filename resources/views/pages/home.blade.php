<x-layouts.public
    title="Exponit Labs — Reliable Pharmaceutical Products"
    description="Exponit Labs is a Mumbai-based pharmaceutical company with a focused range of prescription products in pain management, gastro care, antibiotics and allergy treatment.">

    <x-slot:schema>
        {!! \App\Support\Seo::webPage()->toScript() !!}
        {!! \App\Support\Seo::faqPage()->toScript() !!}
    </x-slot:schema>

    <x-site.hero />

    <x-site.therapeutic-areas :areas="$areas" />

    <x-site.featured-products :products="$featuredProducts" />

    <x-site.science-quality />

    <x-site.news-teaser :posts="$news" />

    <x-site.faq />

    <x-site.cta-band />

    <x-site.contact-form />

</x-layouts.public>
