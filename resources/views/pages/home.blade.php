<x-layouts.public
    title="Exponit Labs — Reliable Pharmaceutical Products"
    description="Exponit Labs delivers focused pharmaceutical products across pain management, gastro care, antibiotics and allergy treatment through trusted manufacturing partnerships.">

    <x-site.hero />

    <x-site.therapeutic-areas :areas="$areas" />

    <x-site.featured-products :products="$featuredProducts" />

    <x-site.science-quality />

    <x-site.news-teaser :posts="$news" />

    <x-site.cta-band />

    <x-site.contact-form />

</x-layouts.public>
