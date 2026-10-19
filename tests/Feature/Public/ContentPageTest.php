<?php

use App\Models\ContentPage;
use Inertia\Testing\AssertableInertia as Assert;

it('renders published content pages from safe markdown', function () {
    ContentPage::where('slug', 'faq')->update([
        'is_published' => true,
        'body' => "## Apa itu Sotra?\n\nPlatform **analisa** trader.\n\n<script>alert('x')</script>",
    ]);

    $this->get(route('pages.show', 'faq'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/page')
            ->where('page.title', 'FAQ')
            ->where('page.html', fn (string $html) => str_contains($html, '<h2>Apa itu Sotra?</h2>')
                && str_contains($html, '<strong>analisa</strong>')
                && ! str_contains($html, '<script>')));
});

it('hides draft pages', function () {
    ContentPage::where('slug', 'syarat-ketentuan')->update(['is_published' => false]);

    $this->get(route('pages.show', 'syarat-ketentuan'))->assertNotFound();
});

it('shares published legal pages for the footer', function () {
    $this->get(route('pages.show', 'disclaimer-risiko'))
        ->assertInertia(fn (Assert $page) => $page->where('legalPages.0.slug', 'disclaimer-risiko'));
});
