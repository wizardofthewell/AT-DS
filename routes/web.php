<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::post('/contact-submit', [InquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');

Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', function () {
    $urls = collect()
        ->merge(Page::published()->get()->map(fn (Page $p) => ['loc' => $p->url, 'lastmod' => $p->updated_at]))
        ->merge(Service::published()->get()->map(fn (Service $s) => ['loc' => $s->url, 'lastmod' => $s->updated_at]))
        ->merge(Post::published()->get()->map(fn (Post $p) => ['loc' => $p->url, 'lastmod' => $p->updated_at]));

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');

// CMS pages (catch-all; keep last so application routes take priority)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');
