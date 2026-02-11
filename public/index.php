<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\TrendDataProvider;

$provider = new TrendDataProvider();

// Selected date (default: today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$dt = new DateTimeImmutable($selectedDate);

$products          = $provider->getTopProducts($selectedDate);
$categoryBreakdown = $provider->getCategoryBreakdown($selectedDate);
$dailyTrends       = $provider->getDailyTrends($selectedDate, 5);

// JSON-encode data for JavaScript charts
$productsJson    = json_encode($products);
$categoriesJson  = json_encode($categoryBreakdown);
$dailyTrendsJson = json_encode($dailyTrends);

// Date navigation
$prevDate = $dt->modify('-1 day')->format('Y-m-d');
$nextDate = $dt->modify('+1 day')->format('Y-m-d');
$today    = date('Y-m-d');

require __DIR__ . '/../templates/dashboard.php';
