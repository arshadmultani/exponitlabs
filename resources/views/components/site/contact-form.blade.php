@props(['compact' => false])

<section id="contact" class="{{ $compact ? '' : 'py-24' }}">
    <div class="mx-auto max-w-3xl px-6 lg:px-12">
        @unless ($compact)
            <x-site.reveal class="reveal text-center">
                <x-site.section-heading align="center"
                    eyebrow="Get in touch"
                    title="Contact Exponit Labs."
                    subtitle="Send us a message and our team will get back to you." />
            </x-site.reveal>
        @endunless

        @if (session('contact_success'))
            <div class="mt-10 rounded-2xl border border-brand/30 bg-brand-50 px-6 py-5 text-center text-ink">
                <p class="font-semibold">Thank you — your message has been sent.</p>
                <p class="text-sm text-muted mt-1">We’ll be in touch shortly.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('contact.submit') }}"
            class="mt-10 grid gap-5 rounded-3xl border border-line bg-surface p-7 sm:p-10 shadow-[0_24px_60px_rgba(15,42,68,0.05)] sm:grid-cols-2">
            @csrf
            <x-honeypot />

            <div>
                <label for="name" class="block text-sm font-medium text-ink">Name <span class="text-brand">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-surface">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-ink">Email <span class="text-brand">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-surface">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-ink">Phone</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                    class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-surface">
            </div>

            <div>
                <label for="organization" class="block text-sm font-medium text-ink">Organization</label>
                <input type="text" name="organization" id="organization" value="{{ old('organization') }}"
                    class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-surface">
            </div>

            <div class="sm:col-span-2">
                <label for="message" class="block text-sm font-medium text-ink">Message <span class="text-brand">*</span></label>
                <textarea name="message" id="message" rows="5" required
                    class="mt-2 w-full rounded-xl border border-line bg-surface-alt px-4 py-3 text-ink outline-none transition focus:border-brand focus:bg-surface">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit"
                    class="w-full rounded-2xl bg-ink px-7 py-4 font-medium text-white transition hover:bg-ink-soft sm:w-auto">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</section>
