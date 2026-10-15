<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContentPageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = ContentPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return Inertia::render('public/page', [
            'page' => [
                'title' => $page->title,
                // Admin-authored markdown; raw HTML is stripped and unsafe links are dropped.
                'html' => Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
