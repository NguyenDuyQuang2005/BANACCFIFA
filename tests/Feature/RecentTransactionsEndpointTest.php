<?php

namespace Tests\Feature;

use Tests\TestCase;

class RecentTransactionsEndpointTest extends TestCase
{
    public function test_recent_transactions_endpoint_returns_an_empty_list_when_no_orders_exist(): void
    {
        $response = $this->getJson(route('transactions.recent'));

        $response->assertOk();
        $response->assertExactJson(['data' => []]);
    }
}
