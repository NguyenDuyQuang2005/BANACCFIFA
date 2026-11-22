<?php

namespace Tests\Unit;

use App\Support\Tools\OvrCalculator;
use App\Support\Tools\TaxCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class ToolCalculatorsTest extends TestCase
{
    public function test_tax_without_perks_keeps_sixty_percent(): void
    {
        $result = (new TaxCalculator)->calculate(1_000_000, 0, 0);

        $this->assertSame(400_000, $result['tax']);
        $this->assertSame(600_000, $result['net']);
    }

    public function test_svip_and_coupon_each_refund_part_of_the_tax(): void
    {
        $result = (new TaxCalculator)->calculate(1_000_000, 30, 20);

        $this->assertSame(400_000, $result['tax']);
        $this->assertSame(120_000, $result['perk_refund']);
        $this->assertSame(80_000, $result['coupon_refund']);
        $this->assertSame(800_000, $result['net']);
    }

    public function test_negative_price_is_treated_as_zero(): void
    {
        $this->assertSame(0, (new TaxCalculator)->calculate(-50, 30, 40)['net']);
    }

    public function test_ovr_is_weighted_average_for_position(): void
    {
        $stats = array_fill_keys(array_keys(config('tools.ovr.positions.ST.weights')), 120);

        $this->assertSame(['before' => 120.0, 'after' => 120.0], (new OvrCalculator)->calculate('ST', $stats));
    }

    public function test_training_raises_ovr_by_stat_weight_and_respects_limits(): void
    {
        $stats = array_fill_keys(array_keys(config('tools.ovr.positions.ST.weights')), 100);

        $result = (new OvrCalculator)->calculate('ST', $stats, ['finishing' => 2]);
        $this->assertSame(100.36, $result['after']);

        $capped = (new OvrCalculator)->calculate('ST', $stats, ['finishing' => 9]);
        $this->assertSame(100.36, $capped['after']);

        $tooMany = (new OvrCalculator)->calculate('ST', $stats, [
            'finishing' => 2, 'positioning' => 2, 'ball_control' => 2, 'shot_power' => 2, 'heading' => 2, 'reactions' => 2,
        ]);
        $this->assertSame(round(100 + (18 + 13 + 10 + 10 + 10) * 2 / 100, 2), $tooMany['after']);
    }

    public function test_every_position_weights_are_known_stats(): void
    {
        $statNames = config('tools.ovr.stat_names');

        foreach (config('tools.ovr.positions') as $key => $position) {
            $this->assertEmpty(array_diff(array_keys($position['weights']), array_keys($statNames)), "Position {$key} uses unknown stats.");
        }
    }

    public function test_unknown_position_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new OvrCalculator)->calculate('XX', []);
    }
}
