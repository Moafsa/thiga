<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RouteComparisonService
{
    /**
     * Compare multiple routes and find the best one
     * 
     * @param array $routes Array of route options
     * @param array $weights Weights for different factors ['cost' => 0.5, 'time' => 0.3, 'distance' => 0.2]
     * @return array Best route and comparison data
     */
    public function findBestRoute(array $routes, array $weights = []): array
    {
        if (empty($routes)) {
            return [
                'best_route' => null,
                'best_option' => null,
                'comparison' => [],
            ];
        }

        // Default weights
        $defaultWeights = [
            'cost' => 0.5,      // Cost is most important
            'time' => 0.3,      // Time is important
            'distance' => 0.2,  // Distance is less important
        ];
        
        $weights = array_merge($defaultWeights, $weights);
        
        // Normalize weights to sum to 1
        $totalWeight = array_sum($weights);
        foreach ($weights as $key => $value) {
            $weights[$key] = $value / $totalWeight;
        }

        $comparison = [];
        $bestRoute = null;
        $bestScore = PHP_FLOAT_MAX;
        $bestOption = null;

        // Find min and max values for normalization
        $costs = array_column($routes, 'estimated_cost');
        $durations = array_column($routes, 'duration');
        $distances = array_column($routes, 'distance');
        
        $minCost = min($costs);
        $maxCost = max($costs);
        $minDuration = min($durations);
        $maxDuration = max($durations);
        $minDistance = min($distances);
        $maxDistance = max($distances);

        foreach ($routes as $route) {
            // Normalize values (0 = best, 1 = worst)
            $normalizedCost = $maxCost > $minCost 
                ? ($route['estimated_cost'] - $minCost) / ($maxCost - $minCost)
                : 0;
            
            $normalizedDuration = $maxDuration > $minDuration
                ? ($route['duration'] - $minDuration) / ($maxDuration - $minDuration)
                : 0;
            
            $normalizedDistance = $maxDistance > $minDistance
                ? ($route['distance'] - $minDistance) / ($maxDistance - $minDistance)
                : 0;

            // Calculate weighted score (lower is better)
            $score = ($normalizedCost * $weights['cost']) +
                     ($normalizedDuration * $weights['time']) +
                     ($normalizedDistance * $weights['distance']);

            $comparison[] = [
                'option' => $route['option'],
                'name' => $route['name'],
                'cost' => $route['estimated_cost'],
                'fuel_cost' => $route['fuel_cost'] ?? 0,
                'toll_cost' => $route['total_toll_cost'] ?? 0,
                'duration' => $route['duration'],
                'distance' => $route['distance'],
                'score' => round($score, 4),
                'normalized' => [
                    'cost' => round($normalizedCost, 4),
                    'duration' => round($normalizedDuration, 4),
                    'distance' => round($normalizedDistance, 4),
                ],
            ];

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestRoute = $route;
                $bestOption = $route['option'];
            }
        }

        // Sort comparison by score
        usort($comparison, function($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        Log::info('Route comparison completed', [
            'total_routes' => count($routes),
            'best_option' => $bestOption,
            'best_score' => $bestScore,
            'comparison' => $comparison,
        ]);

        return [
            'best_route' => $bestRoute,
            'best_option' => $bestOption,
            'best_score' => $bestScore,
            'comparison' => $comparison,
            'weights' => $weights,
        ];
    }

    /**
     * Get route recommendation based on priorities
     * 
     * @param array $routes
     * @param string $priority 'cost', 'time', 'distance', or 'balanced'
     * @return array
     */
    public function getRecommendation(array $routes, string $priority = 'balanced'): array
    {
        $weights = match($priority) {
            'cost' => ['cost' => 0.7, 'time' => 0.2, 'distance' => 0.1],
            'time' => ['cost' => 0.2, 'time' => 0.7, 'distance' => 0.1],
            'distance' => ['cost' => 0.3, 'time' => 0.2, 'distance' => 0.5],
            'balanced' => ['cost' => 0.5, 'time' => 0.3, 'distance' => 0.2],
            default => ['cost' => 0.5, 'time' => 0.3, 'distance' => 0.2],
        };

        return $this->findBestRoute($routes, $weights);
    }
}






























