<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\TrendDataProvider;

$provider = new TrendDataProvider();

// Selected date (default: today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$dt = new DateTimeImmutable($selectedDate);

// Category filter
$categoryGroups   = $provider->getCategoryGroups();
$selectedCategory = $_GET['category'] ?? '';
$categoryParam    = in_array($selectedCategory, $categoryGroups, true) ? $selectedCategory : null;

$products          = $provider->getTopProducts($selectedDate, $categoryParam);
$categoryBreakdown = $provider->getCategoryBreakdown($selectedDate, $categoryParam);
$dailyTrends       = $provider->getDailyTrends($selectedDate, 5, $categoryParam);

// JSON-encode data for JavaScript charts
$productsJson    = json_encode($products);
$categoriesJson  = json_encode($categoryBreakdown);
$dailyTrendsJson = json_encode($dailyTrends);

// Date navigation — preserve category in links
$prevDate = $dt->modify('-1 day')->format('Y-m-d');
$nextDate = $dt->modify('+1 day')->format('Y-m-d');
$today    = date('Y-m-d');
$catQuery = $categoryParam ? '&category=' . urlencode($categoryParam) : '';

require __DIR__ . '/../templates/dashboard.php';
