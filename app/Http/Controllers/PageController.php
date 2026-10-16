<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::published()->where('slug', 'home')->first();

        if (! $page) {
            return view('welcome-setup');
        }

        return $this->render($page);
    }

    public function show(string $slug)
    {
        if ($slug === 'home') {
            return redirect('/', 301);
        }

        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return $this->render($page);
    }

    protected function render(Page $page)
    {
        return view('pages.show', compact('page'));
    }
}
