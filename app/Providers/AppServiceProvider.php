<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Comment;
use App\Models\EditorialPick;
use App\Models\Media;
use App\Models\Post;
use App\Models\RightOfReplyRequest;
use App\Models\User;
use App\Policies\BreakingNewsPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\EditorialPickPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PostPolicy;
use App\Policies\RightOfReplyPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Peta model ke Policy-nya.
     *
     * @var array<class-string<Model>, class-string>
     */
    private const POLICIES = [
        Post::class => PostPolicy::class,
        Category::class => CategoryPolicy::class,
        Comment::class => CommentPolicy::class,
        Media::class => MediaPolicy::class,
        User::class => UserPolicy::class,
        BreakingNews::class => BreakingNewsPolicy::class,
        EditorialPick::class => EditorialPickPolicy::class,
        RightOfReplyRequest::class => RightOfReplyPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configureDates();
        $this->configureUrls();
        $this->configurePasswordRules();
        $this->configureGates();
        $this->configurePolicies();
        $this->configureRateLimiters();
        $this->configureViews();
    }

    private function configureViews(): void
    {
        \Illuminate\Support\Facades\View::composer(['layouts.public', 'public.*'], static function ($view): void {
            $view->with('navCategories', Category::active()->ordered()->get());
        });
    }

    /**
     * Zero N+1 Queries: aktifkan pencegahan lazy loading di luar produksi.
     * Kegagalan lazy loading menjadi exception saat development/testing sehingga
     * query berlebih terdeteksi sebelum sampai produksi.
     */
    private function configureModels(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
    }

    /**
     * Seluruh waktu disimpan UTC dan ditampilkan WIB melalui presenter terpusat.
     * Locale Carbon disetel ke id agar diffForHumans() dan format tanggal
     * mengikuti bahasa Indonesia.
     */
    private function configureDates(): void
    {
        Date::use(Carbon::class);
        Carbon::setLocale((string) config('app.locale', 'id'));
        Carbon::macro('toWib', function (): Carbon {
            /** @var Carbon $this */
            return $this->copy()->setTimezone(config('app.public_timezone', 'Asia/Jakarta'));
        });
    }

    /**
     * Paksa HTTPS pada environment produksi untuk mencegah mixed content
     * dan kebocoran cookie sesi.
     */
    private function configureUrls(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Kebijakan kata sandi default: minimal 12 karakter.
     * Akun kru (is_crew = true) memakai aturan ini tanpa pengecualian.
     */
    private function configurePasswordRules(): void
    {
        Password::defaults(static function (): Password {
            $rule = Password::min(12)->letters()->mixedCase()->numbers();

            return app()->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });
    }

    /**
     * Super-admin memperoleh seluruh wewenang lewat Gate::before.
     *
     * Mengembalikan null (bukan false) untuk peran lain agar pemeriksaan
     * dilanjutkan ke Policy terkait.
     */
    private function configureGates(): void
    {
        Gate::before(static function (User $user, string $ability): ?bool {
            return $user->hasRole(RoleName::SUPER_ADMIN->value) ? true : null;
        });
    }

    /**
     * Daftarkan seluruh Policy entitas.
     */
    private function configurePolicies(): void
    {
        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Rate limiter endpoint sensitif (login, komentar, pencarian, hak jawab).
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', static function (Request $request): Limit {
            $email = mb_strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('comments', static function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(10)->by('comment|'.$key);
        });

        RateLimiter::for('search', static function (Request $request): Limit {
            return Limit::perMinute(30)->by('search|'.$request->ip());
        });

        RateLimiter::for('right-of-reply', static function (Request $request): Limit {
            return Limit::perHour(3)->by('ror|'.$request->ip());
        });

        RateLimiter::for('report-comment', static function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(5)->by('report|'.$key);
        });

        RateLimiter::for('register', static function (Request $request): Limit {
            return Limit::perMinute(5)->by('register|'.$request->ip());
        });
    }
}
