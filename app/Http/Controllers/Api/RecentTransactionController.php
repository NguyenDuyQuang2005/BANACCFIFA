<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CatalogFeed;
use Illuminate\Http\JsonResponse;

class RecentTransactionController extends Controller
{
    public function __construct(private CatalogFeed $catalogFeed) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->catalogFeed->recentCompletedTransactions()->values(),
        ]);
    }
}
