<?php

/**
 * Affiliate defaults. Admin dev can override every value in Admin → Affiliate (stored in `settings`).
 */
return [

    'enabled' => (bool) env('AFFILIATE_ENABLED', true),

    /** Commission paid to the referrer, in % of each completed account order of the referred member. */
    'rate' => (float) env('AFFILIATE_RATE', 5),

    'min_withdraw' => (int) env('AFFILIATE_MIN_WITHDRAW', 30000),

    'max_withdraw' => (int) env('AFFILIATE_MAX_WITHDRAW', 10000000),

    /** How long a referral link click is remembered before the visitor registers. */
    'cookie_days' => (int) env('AFFILIATE_COOKIE_DAYS', 30),

];
