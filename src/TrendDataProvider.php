<?php

namespace App;

/**
 * Provides dropship product trend data by combining signals from
 * Google Trends interest scores and Amazon Movers & Shakers rank changes.
 *
 * Data is generated deterministically from date-seeded randomness so the
 * dashboard always shows consistent, realistic numbers for any given day
 * while varying naturally across days and products.
 */
class TrendDataProvider
{
    /** Product catalog: name, category, base popularity, seasonality peak month (1-12), avg price */
    private const PRODUCTS = [
        ['name' => 'Portable Blender',            'cat' => 'Kitchen',       'base' => 82, 'peak' => 6,  'price' => 89.99],
        ['name' => 'LED Strip Lights',             'cat' => 'Home Decor',   'base' => 90, 'peak' => 12, 'price' => 74.99],
        ['name' => 'Posture Corrector',            'cat' => 'Health',       'base' => 75, 'peak' => 1,  'price' => 79.99],
        ['name' => 'Phone Camera Lens Kit',        'cat' => 'Electronics',  'base' => 68, 'peak' => 11, 'price' => 124.99],
        ['name' => 'Resistance Bands Set',         'cat' => 'Fitness',      'base' => 88, 'peak' => 1,  'price' => 84.99],
        ['name' => 'Car Phone Mount',              'cat' => 'Auto',         'base' => 72, 'peak' => 7,  'price' => 79.99],
        ['name' => 'Heated Blanket',               'cat' => 'Home',         'base' => 65, 'peak' => 11, 'price' => 149.99],
        ['name' => 'Bluetooth Earbuds',            'cat' => 'Electronics',  'base' => 95, 'peak' => 12, 'price' => 189.99],
        ['name' => 'Pet Grooming Glove',           'cat' => 'Pets',         'base' => 70, 'peak' => 4,  'price' => 39.99],
        ['name' => 'Ring Light',                   'cat' => 'Electronics',  'base' => 85, 'peak' => 9,  'price' => 129.99],
        ['name' => 'Yoga Mat',                     'cat' => 'Fitness',      'base' => 80, 'peak' => 1,  'price' => 89.99],
        ['name' => 'Smart Watch Band',             'cat' => 'Accessories',  'base' => 78, 'peak' => 12, 'price' => 79.99],
        ['name' => 'Portable Projector',           'cat' => 'Electronics',  'base' => 60, 'peak' => 11, 'price' => 249.99],
        ['name' => 'Reusable Water Bottle',        'cat' => 'Fitness',      'base' => 76, 'peak' => 6,  'price' => 44.99],
        ['name' => 'Electric Scalp Massager',      'cat' => 'Health',       'base' => 55, 'peak' => 2,  'price' => 99.99],
        ['name' => 'Wireless Charging Pad',        'cat' => 'Electronics',  'base' => 83, 'peak' => 12, 'price' => 89.99],
        ['name' => 'Silicone Kitchen Utensil Set', 'cat' => 'Kitchen',      'base' => 62, 'peak' => 5,  'price' => 109.99],
        ['name' => 'Neck Fan',                     'cat' => 'Personal',     'base' => 58, 'peak' => 7,  'price' => 79.99],
        ['name' => 'Sunset Lamp Projector',        'cat' => 'Home Decor',   'base' => 73, 'peak' => 10, 'price' => 94.99],
        ['name' => 'Mini Waffle Maker',            'cat' => 'Kitchen',      'base' => 69, 'peak' => 3,  'price' => 84.99],
        ['name' => 'Beard Trimmer Kit',            'cat' => 'Grooming',     'base' => 71, 'peak' => 11, 'price' => 119.99],
        ['name' => 'Laptop Stand',                 'cat' => 'Office',       'base' => 77, 'peak' => 9,  'price' => 139.99],
        ['name' => 'Insulated Tumbler',            'cat' => 'Kitchen',      'base' => 86, 'peak' => 6,  'price' => 74.99],
        ['name' => 'Magnetic Phone Case',          'cat' => 'Accessories',  'base' => 64, 'peak' => 12, 'price' => 69.99],
        ['name' => 'Ice Roller Face Massager',     'cat' => 'Beauty',       'base' => 59, 'peak' => 7,  'price' => 74.99],
        ['name' => 'Desk Organizer',               'cat' => 'Office',       'base' => 66, 'peak' => 9,  'price' => 99.99],
        ['name' => 'Electric Lighter',             'cat' => 'Gadgets',      'base' => 53, 'peak' => 12, 'price' => 49.99],
        ['name' => 'Cloud Slides Slippers',        'cat' => 'Fashion',      'base' => 81, 'peak' => 3,  'price' => 79.99],
        ['name' => 'Magnetic Eyelashes',           'cat' => 'Beauty',       'base' => 67, 'peak' => 2,  'price' => 89.99],
        ['name' => 'Solar Power Bank',             'cat' => 'Electronics',  'base' => 74, 'peak' => 6,  'price' => 134.99],
        ['name' => 'Acupressure Mat',              'cat' => 'Health',       'base' => 56, 'peak' => 1,  'price' => 109.99],
        ['name' => 'LED Desk Lamp',                'cat' => 'Office',       'base' => 63, 'peak' => 9,  'price' => 89.99],
        ['name' => 'Digital Kitchen Scale',        'cat' => 'Kitchen',      'base' => 61, 'peak' => 1,  'price' => 74.99],
        ['name' => 'Foldable Travel Bag',          'cat' => 'Travel',       'base' => 79, 'peak' => 6,  'price' => 94.99],
        ['name' => 'Teeth Whitening Kit',          'cat' => 'Beauty',       'base' => 84, 'peak' => 5,  'price' => 149.99],
        ['name' => 'Sterling Silver Chain Necklace','cat' => 'Jewelry',     'base' => 72, 'peak' => 12, 'price' => 189.99],
        ['name' => 'Cubic Zirconia Ring Set',      'cat' => 'Jewelry',      'base' => 65, 'peak' => 2,  'price' => 129.99],
        ['name' => 'Beaded Bracelet Collection',   'cat' => 'Jewelry',      'base' => 58, 'peak' => 5,  'price' => 89.99],
        ['name' => 'Dash Cam Recorder',            'cat' => 'Auto',         'base' => 76, 'peak' => 8,  'price' => 179.99],
        ['name' => 'Car Seat Organizer',           'cat' => 'Auto',         'base' => 63, 'peak' => 6,  'price' => 84.99],
        ['name' => 'Jade Roller Set',              'cat' => 'Beauty',       'base' => 62, 'peak' => 3,  'price' => 79.99],
        ['name' => 'USB Desk Fan',                 'cat' => 'Office',       'base' => 57, 'peak' => 7,  'price' => 74.99],
        ['name' => 'Foam Roller',                  'cat' => 'Fitness',      'base' => 73, 'peak' => 1,  'price' => 84.99],
        ['name' => 'Essential Oil Diffuser',       'cat' => 'Home',         'base' => 77, 'peak' => 11, 'price' => 99.99],
        ['name' => 'Pet Water Fountain',           'cat' => 'Pets',         'base' => 66, 'peak' => 6,  'price' => 119.99],
        ['name' => 'Tire Pressure Gauge',          'cat' => 'Auto',         'base' => 54, 'peak' => 5,  'price' => 74.99],
        ['name' => 'Clip-On Reading Light',        'cat' => 'Office',       'base' => 52, 'peak' => 9,  'price' => 74.99],
        ['name' => 'Layered Pendant Necklace',     'cat' => 'Jewelry',      'base' => 69, 'peak' => 12, 'price' => 159.99],
        ['name' => 'Vitamin Organizer Case',       'cat' => 'Health',       'base' => 60, 'peak' => 1,  'price' => 74.99],
        ['name' => 'Car Trunk Organizer',          'cat' => 'Auto',         'base' => 58, 'peak' => 4,  'price' => 94.99],
        ['name' => 'Hair Claw Clips Set',          'cat' => 'Fashion',      'base' => 75, 'peak' => 8,  'price' => 34.99],
        ['name' => 'Mini Portable Speaker',        'cat' => 'Electronics',  'base' => 71, 'peak' => 6,  'price' => 124.99],
        ['name' => 'Silicone Baking Mat Set',      'cat' => 'Kitchen',      'base' => 64, 'peak' => 11, 'price' => 79.99],
        ['name' => 'Ankle Brace Support',          'cat' => 'Health',       'base' => 56, 'peak' => 3,  'price' => 84.99],
    ];

    /** Broad filter categories mapped to product-level categories */
    private const CATEGORY_GROUPS = [
        'Health Products' => ['Health', 'Fitness', 'Beauty', 'Grooming', 'Personal'],
        'Automotive'      => ['Auto', 'Travel'],
        'Household'       => ['Home', 'Home Decor', 'Kitchen', 'Office', 'Pets'],
        'Electronics'     => ['Electronics', 'Gadgets'],
        'Jewelry'         => ['Jewelry', 'Accessories', 'Fashion'],
    ];

    public function getCategoryGroups(): array
    {
        return array_keys(self::CATEGORY_GROUPS);
    }

    /**
     * Get the top 20 products ranked by 30-day average composite trend score,
     * including 30-day sparkline history and year-over-year comparison.
     * Optionally filter by a broad category group.
     */
    public function getTopProducts(string $date, ?string $categoryGroup = null): array
    {
        $dt = new \DateTimeImmutable($date);
        $allowedCats = null;
        if ($categoryGroup !== null && isset(self::CATEGORY_GROUPS[$categoryGroup])) {
            $allowedCats = self::CATEGORY_GROUPS[$categoryGroup];
        }

        $products = [];

        foreach (self::PRODUCTS as $i => $p) {
            if ($allowedCats !== null && !in_array($p['cat'], $allowedCats, true)) {
                continue;
            }
            // Price filter: only include products > $69.99 and < $400.00
            if ($p['price'] <= 69.99 || $p['price'] >= 400.00) {
                continue;
            }

            // 30-day sparkline and running sums for averages
            $sparkline = [];
            $googleSum = 0;
            $amazonSum = 0;
            for ($d = 29; $d >= 0; $d--) {
                $pastDate = $dt->modify("-{$d} days");
                $gs = $this->googleTrendScore($i, $pastDate);
                $sparkline[] = $gs;
                $googleSum += $gs;
                $amazonSum += $this->amazonRankChange($i, $pastDate);
            }

            // 30-day averages
            $googleTrend = (int) round($googleSum / 30);
            $amazonRank  = round($amazonSum / 30, 1);

            // Normalize Amazon rank change (-5..+5 range) to 0-100 scale, then blend
            $amazonNorm  = max(0, min(100, 50 + $amazonRank * 10));
            $composite   = ($googleTrend * 0.6) + ($amazonNorm * 0.4);

            // Year-over-year (also 30-day average)
            $yoyGoogleSum = 0;
            for ($d = 29; $d >= 0; $d--) {
                $pastDate = $dt->modify("-1 year -{$d} days");
                $yoyGoogleSum += $this->googleTrendScore($i, $pastDate);
            }
            $yoyPrevious  = (int) round($yoyGoogleSum / 30);
            $yoyCurrent   = $googleTrend;
            $yoyChange    = $yoyPrevious > 0
                ? round(($yoyCurrent - $yoyPrevious) / $yoyPrevious * 100, 1)
                : 0;

            // Trend direction from 7-day moving average
            $recent3 = array_slice($sparkline, -3);
            $prev3   = array_slice($sparkline, -6, 3);
            $dir     = array_sum($recent3) / 3 > array_sum($prev3) / 3 ? 'up' : 'down';

            $products[] = [
                'name'              => $p['name'],
                'category'          => $p['cat'],
                'avg_price'         => $p['price'],
                'google_trend'      => $googleTrend,
                'amazon_rank_change'=> $amazonRank,
                'composite_score'   => round($composite, 1),
                'trend_direction'   => $dir,
                'sparkline_30d'     => $sparkline,
                'yoy_current'       => $yoyCurrent,
                'yoy_previous'      => $yoyPrevious,
                'yoy_change_pct'    => $yoyChange,
            ];
        }

        // Sort by composite score descending, take top 50
        usort($products, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);
        $products = array_slice($products, 0, 50);

        // Add rank
        foreach ($products as $i => &$p) {
            $p['rank'] = $i + 1;
        }

        return $products;
    }

    /**
     * Get aggregate category performance for the chart breakdown.
     *
     * @return array<string, array{avg_score: float, product_count: int, trend: string}>
     */
    public function getCategoryBreakdown(string $date, ?string $categoryGroup = null): array
    {
        $products = $this->getTopProducts($date, $categoryGroup);
        $cats = [];

        foreach ($products as $p) {
            $cat = $p['category'];
            if (!isset($cats[$cat])) {
                $cats[$cat] = ['scores' => [], 'directions' => []];
            }
            $cats[$cat]['scores'][] = $p['composite_score'];
            $cats[$cat]['directions'][] = $p['trend_direction'];
        }

        $result = [];
        foreach ($cats as $cat => $data) {
            $avg = round(array_sum($data['scores']) / count($data['scores']), 1);
            $upCount = count(array_filter($data['directions'], fn($d) => $d === 'up'));
            $result[$cat] = [
                'avg_score'     => $avg,
                'product_count' => count($data['scores']),
                'trend'         => $upCount >= count($data['scores']) / 2 ? 'up' : 'down',
            ];
        }

        arsort($result);
        return $result;
    }

    /**
     * Get 30-day daily composite scores for the top N products (for multi-line chart).
     */
    public function getDailyTrends(string $date, int $topN = 5, ?string $categoryGroup = null): array
    {
        $dt = new \DateTimeImmutable($date);
        $topProducts = $this->getTopProducts($date, $categoryGroup);
        $topSlice = array_slice($topProducts, 0, $topN);

        $labels = [];
        for ($d = 29; $d >= 0; $d--) {
            $labels[] = $dt->modify("-{$d} days")->format('M j');
        }

        $datasets = [];
        foreach ($topSlice as $p) {
            $datasets[] = [
                'label' => $p['name'],
                'data'  => $p['sparkline_30d'],
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Simulated Google Trends interest score (0-100) for a product on a given date.
     */
    private function googleTrendScore(int $productIndex, \DateTimeImmutable $date): int
    {
        $p = self::PRODUCTS[$productIndex];
        $dayOfYear = (int) $date->format('z');
        $year = (int) $date->format('Y');

        // Seasonality: cosine curve peaking at the product's peak month
        $peakDay = ($p['peak'] - 1) * 30.44 + 15;
        $seasonality = cos(2 * M_PI * ($dayOfYear - $peakDay) / 365);
        $seasonalBoost = $seasonality * 15;

        // Deterministic pseudo-random daily noise
        $seed = crc32($p['name'] . $date->format('Y-m-d'));
        mt_srand($seed);
        $noise = mt_rand(-8, 8);

        // Slight year-over-year growth trend
        $yearGrowth = ($year - 2024) * 3;

        $score = (int) round($p['base'] + $seasonalBoost + $noise + $yearGrowth);
        return max(5, min(100, $score));
    }

    /**
     * Simulated Amazon Movers & Shakers rank change percentage.
     * Positive = rising in sales rank (good for dropshipping).
     */
    private function amazonRankChange(int $productIndex, \DateTimeImmutable $date): float
    {
        $p = self::PRODUCTS[$productIndex];
        $seed = crc32('amazon_' . $p['name'] . $date->format('Y-m-d'));
        mt_srand($seed);

        $base = ($p['base'] - 50) / 10; // Higher base popularity = more positive rank change
        $noise = (mt_rand(0, 1000) - 500) / 100;

        return round($base + $noise, 1);
    }
}
