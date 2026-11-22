<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Service;
use App\Support\CatalogFeed;
use App\Support\HeroBanner;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private CatalogFeed $catalogFeed) {}

    public function index(HeroBanner $heroBanner): View
    {
        return view('home', [
            'hero' => $heroBanner->current(),
            'banners' => Banner::query()->live()->limit(6)->get(),
            'categories' => $this->catalogFeed->visibleCategories(),
            'services' => Service::query()->visible()->limit(8)->get(),
            'products' => $this->catalogFeed->featuredProducts(),
            'transactions' => $this->catalogFeed->recentCompletedTransactions(),
            'posts' => $this->catalogFeed->latestPosts(),
            'memberCount' => $this->catalogFeed->memberCount(),
            'completedOrderCount' => $this->catalogFeed->completedOrderCount(),
            'tools' => collect(config('tools.modules', [])),
        ]);
    }
}
