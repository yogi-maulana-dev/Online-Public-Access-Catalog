<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CitationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OaiPmhController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/kategori/{category:slug}', [CatalogController::class, 'category'])->name('categories.show');

Route::get('/penulis', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/penulis/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');

Route::prefix('buku/{book:slug}')->name('books.')->group(function () {
    Route::get('/', [BookController::class, 'show'])->name('show');
    Route::get('/sitasi/{style}', [CitationController::class, 'show'])->name('citation');

    // Reading & downloading require a verified account.
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/baca', [BookController::class, 'read'])->name('read');
        Route::get('/stream', [BookController::class, 'stream'])->name('stream');
        Route::get('/unduh', [BookController::class, 'download'])->name('download');
    });
});

// SEO & interoperability
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/oai', [OaiPmhController::class, 'handle'])->name('oai');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
