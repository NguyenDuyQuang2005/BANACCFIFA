<?php

namespace App\View\Composers;

use App\Models\Popup;
use App\Support\CatalogFeed;
use Illuminate\View\View;

class SiteLayoutComposer
{
    public function __construct(private CatalogFeed $catalogFeed) {}

    public function compose(View $view): void
    {
        $view->with([
            'site' => config('site'),
            'navCategories' => $this->catalogFeed->visibleCategories(),
            'navTools' => collect(config('tools.modules', [])),
            'sitePopup' => Popup::query()->live()->first(),
        ]);
    }
}
