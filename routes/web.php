<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Newsroom\BreakingNewsController;
use App\Http\Controllers\Newsroom\CategoryController as NewsroomCategoryController;
use App\Http\Controllers\Newsroom\CurationController;
use App\Http\Controllers\Newsroom\DashboardController;
use App\Http\Controllers\Newsroom\InteractiveDeskController;
use App\Http\Controllers\Newsroom\PostController as NewsroomPostController;
use App\Http\Controllers\Newsroom\UserController as NewsroomUserController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\EpaperController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PollVoteController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\PostInteractionController;
use App\Http\Controllers\Public\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik Utama
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/cari', SearchController::class)->name('search');
Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::post('/survei/{poll}/vote', [PollVoteController::class, 'store'])->name('polls.vote');
Route::get('/epaper', [EpaperController::class, 'show'])->name('epaper.show');
Route::get('/epaper/unduh', [EpaperController::class, 'download'])->name('epaper.download');
Route::get('/epaper/ruang-data-edisi-cetak.pdf', [EpaperController::class, 'download']);

// Interaksi Artikel (Like, Komentar, & Profil Pembaca)
Route::post('/interaksi/{post}/like', [PostInteractionController::class, 'toggleLike'])->name('posts.like');
Route::post('/interaksi/{post}/komentar', [PostInteractionController::class, 'storeComment'])->name('posts.comments.store');
Route::delete('/interaksi/komentar/{comment}', [PostInteractionController::class, 'destroyComment'])->name('posts.comments.destroy');
Route::post('/profil/foto', [PostInteractionController::class, 'updateAvatar'])->middleware('auth')->name('profile.avatar.update');

/*
|--------------------------------------------------------------------------
| Autentikasi & Pendaftaran Pembaca
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Verifikasi Email Pembaca
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Verifikasi OTP Email (Role Sensitif Redaksi)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/verifikasi-otp', [EmailOtpController::class, 'challenge'])->name('otp.challenge');
    Route::post('/verifikasi-otp', [EmailOtpController::class, 'verify'])->name('otp.verify');
    Route::post('/verifikasi-otp/resend', [EmailOtpController::class, 'resend'])->name('otp.resend');
});

/*
|--------------------------------------------------------------------------
| Studio Newsroom (Dapur Redaksi)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'crew', 'crew.2fa', 'crew.idle'])
    ->prefix('newsroom')
    ->name('newsroom.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        // Manajemen Naskah Berita
        Route::get('/naskah', [NewsroomPostController::class, 'index'])->name('posts.index');
        Route::get('/naskah/tulis', [NewsroomPostController::class, 'create'])->name('posts.create');
        Route::post('/naskah', [NewsroomPostController::class, 'store'])->name('posts.store');
        Route::get('/naskah/{post}/sunting', [NewsroomPostController::class, 'edit'])->name('posts.edit');
        Route::put('/naskah/{post}', [NewsroomPostController::class, 'update'])->name('posts.update');
        Route::delete('/naskah/{post}', [NewsroomPostController::class, 'destroy'])->name('posts.destroy');

        // Kurasi Homepage (Pemred & Admin)
        Route::get('/kurasi-homepage', [CurationController::class, 'index'])->name('curation.index');
        Route::post('/kurasi-homepage', [CurationController::class, 'update'])->name('curation.update');

        // Manajemen Breaking News Ticker (Pemred & Admin)
        Route::get('/breaking-news', [BreakingNewsController::class, 'index'])->name('breaking-news.index');
        Route::post('/breaking-news', [BreakingNewsController::class, 'store'])->name('breaking-news.store');
        Route::post('/breaking-news/{breakingNews}/toggle', [BreakingNewsController::class, 'toggle'])->name('breaking-news.toggle');
        Route::delete('/breaking-news/{breakingNews}', [BreakingNewsController::class, 'destroy'])->name('breaking-news.destroy');

        // Manajemen Rubrik Kategori (Pemred & Admin)
        Route::resource('/kategori', NewsroomCategoryController::class)
            ->names('categories')
            ->parameters(['kategori' => 'category']);

        // Manajemen Survei Pembaca & E-Paper (Pemred & Admin)
        Route::get('/interaktif', [InteractiveDeskController::class, 'index'])->name('interactive.index');
        Route::post('/interaktif/survei', [InteractiveDeskController::class, 'storePoll'])->name('interactive.polls.store');
        Route::post('/interaktif/survei/{poll}/aktifkan', [InteractiveDeskController::class, 'activatePoll'])->name('interactive.polls.activate');
        Route::delete('/interaktif/survei/{poll}', [InteractiveDeskController::class, 'destroyPoll'])->name('interactive.polls.destroy');
        Route::post('/interaktif/epaper', [InteractiveDeskController::class, 'storeEpaper'])->name('interactive.epapers.store');
        Route::post('/interaktif/epaper/{epaper}/aktifkan', [InteractiveDeskController::class, 'activateEpaper'])->name('interactive.epapers.activate');
        Route::delete('/interaktif/epaper/{epaper}', [InteractiveDeskController::class, 'destroyEpaper'])->name('interactive.epapers.destroy');

        // Manajemen Kru & Pengguna (Khusus Super Admin)
        Route::resource('/kru', NewsroomUserController::class)
            ->names('users')
            ->parameters(['kru' => 'user']);
    });

/*
|--------------------------------------------------------------------------
| Halaman Baca Berita (Single Post)
|--------------------------------------------------------------------------
*/

Route::get('/{categorySlug}/{postSlug}', [PostController::class, 'show'])->name('posts.show');
