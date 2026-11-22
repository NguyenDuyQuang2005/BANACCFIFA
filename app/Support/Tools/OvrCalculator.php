<?php

namespace App\Support\Tools;

use InvalidArgumentException;

/**
 * OVR = Σ(stat × weight) / Σ(weight) using the position weights in config/tools.php.
 */
class OvrCalculator
{
    /**
     * @param  array<string, int|float>  $stats  stat key => value
     * @param  array<string, int>  $training  stat key => extra points
     * @return array{before: float, after: float}
     */
    public function calculate(string $position, array $stats, array $training = []): array
    {
        $weights = config('tools.ovr.positions.'.$position.'.weights');

        if (! is_array($weights) || $weights === []) {
            throw new InvalidArgumentException('Vị trí không hợp lệ.');
        }

        $maxTrained = (int) config('tools.ovr.max_trained_stats', 5);
        $maxPerStat = (int) config('tools.ovr.max_per_stat', 2);

        $training = array_filter(
            array_map(fn ($points): int => max(0, min($maxPerStat, (int) $points)), array_intersect_key($training, $weights)),
        );
        $training = array_slice($training, 0, $maxTrained, true);

        $totalWeight = array_sum($weights);
        $before = 0.0;
        $after = 0.0;

        foreach ($weights as $stat => $weight) {
            $value = (float) ($stats[$stat] ?? 0);
            $before += $value * $weight;
            $after += ($value + ($training[$stat] ?? 0)) * $weight;
        }

        return [
            'before' => round($before / $totalWeight, 2),
            'after' => round($after / $totalWeight, 2),
        ];
    }
}
