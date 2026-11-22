<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CoachSkill;
use App\Models\PaymentMethod;
use App\Models\Player;
use App\Models\PlayerSeason;
use App\Models\Popup;
use App\Models\Product;
use App\Models\Service;
use App\Models\SkillMove;
use App\Models\User;
use App\Observers\AdminActivityObserver;
use App\Support\SiteSettings;
use App\View\Composers\SiteLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        $this->app->make(SiteSettings::class)->applyToConfig();

        Gate::define('access-admin', fn (User $user): bool => $user->isStaff() && ! $user->isBanned());
        Gate::define('manage-system', fn (User $user): bool => $user->isAdmin() && ! $user->isBanned());

        foreach ([Category::class, Product::class, PaymentMethod::class, Banner::class, Popup::class, Service::class, Player::class, PlayerSeason::class, SkillMove::class, CoachSkill::class] as $model) {
            $model::observe(AdminActivityObserver::class);
        }

        View::composer('components.layouts.app', SiteLayoutComposer::class);
    }
}
