<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DropShip Trends — Top 50 Products</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="container">

    <!-- ═══ Header ═══ -->
    <header class="header">
        <div>
            <h1>DropShip Trends</h1>
            <div class="subtitle">Top 50 products — 30-day summary — Google Trends + Amazon Movers &amp; Shakers</div>
        </div>
        <nav class="date-nav">
            <a href="?date=<?= htmlspecialchars($prevDate) . $catQuery ?>">&larr; Prev</a>
            <input type="date" class="date-picker" value="<?= htmlspecialchars($selectedDate) ?>" max="<?= $today ?>" onchange="window.location.href='?date='+this.value+'<?= htmlspecialchars($catQuery) ?>'">
            <?php if ($selectedDate < $today): ?>
                <a href="?date=<?= htmlspecialchars($nextDate) . $catQuery ?>">Next &rarr;</a>
            <?php else: ?>
                <span style="color:var(--muted);font-size:.85rem">Today</span>
            <?php endif; ?>
            <?php if ($selectedDate !== $today): ?>
                <a href="?date=<?= $today . $catQuery ?>" style="background:var(--accent);color:#fff">Today</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- ═══ Category Filter ═══ -->
    <div class="category-filter">
        <label class="filter-label">Category:</label>
        <a href="?date=<?= htmlspecialchars($selectedDate) ?>" class="filter-btn<?= $categoryParam === null ? ' active' : '' ?>">All</a>
        <?php foreach ($categoryGroups as $group): ?>
            <a href="?date=<?= htmlspecialchars($selectedDate) ?>&category=<?= urlencode($group) ?>"
               class="filter-btn<?= $categoryParam === $group ? ' active' : '' ?>">
                <?= htmlspecialchars($group) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ═══ Stat Cards ═══ -->
    <?php
        $avgScore   = round(array_sum(array_column($products, 'composite_score')) / count($products), 1);
        $avgPrice   = round(array_sum(array_column($products, 'avg_price')) / count($products), 2);
        $upCount    = count(array_filter($products, fn($p) => $p['trend_direction'] === 'up'));
        $downCount  = count($products) - $upCount;
        $avgYoy     = round(array_sum(array_column($products, 'yoy_change_pct')) / count($products), 1);
        $topProduct = $products[0];
    ?>
    <div class="stats-row">
        <div class="stat-card animate-in">
            <div class="label">30-Day Avg Composite</div>
            <div class="value"><?= $avgScore ?></div>
            <div class="change <?= $avgYoy >= 0 ? 'up' : 'down' ?>">
                <?= $avgYoy >= 0 ? '&#9650;' : '&#9660;' ?> <?= abs($avgYoy) ?>% vs last year
            </div>
        </div>
        <div class="stat-card animate-in">
            <div class="label">Avg Price</div>
            <div class="value">$<?= number_format($avgPrice, 2) ?></div>
            <div class="change" style="color:var(--muted)">$69.99 – $500.00 range</div>
        </div>
        <div class="stat-card animate-in">
            <div class="label">Trending Up</div>
            <div class="value" style="color:var(--green)"><?= $upCount ?></div>
            <div class="change" style="color:var(--muted)"><?= $downCount ?> trending down</div>
        </div>
        <div class="stat-card animate-in">
            <div class="label">#1 Product</div>
            <div class="value" style="font-size:1.15rem"><?= htmlspecialchars($topProduct['name']) ?></div>
            <div class="change up">Score: <?= $topProduct['composite_score'] ?></div>
        </div>
        <div class="stat-card animate-in">
            <div class="label">Categories Tracked</div>
            <div class="value"><?= count($categoryBreakdown) ?></div>
            <div class="change" style="color:var(--muted)">across <?= count($products) ?> products</div>
        </div>
    </div>

    <!-- ═══ Charts ═══ -->
    <div class="charts-grid">
        <div class="chart-card animate-in">
            <h2>30-Day Trend — Top 5 Products</h2>
            <div style="height:280px"><canvas id="trendChart"></canvas></div>
        </div>
        <div class="chart-card animate-in">
            <h2>Category Breakdown</h2>
            <div style="height:280px"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    <!-- ═══ YoY Comparison Bar Chart ═══ -->
    <div class="chart-card animate-in" style="margin-bottom:1.5rem">
        <h2>Year-over-Year Comparison (30-Day Avg) — <?= $dt->format('M j') ?>, <?= $dt->format('Y') ?> vs <?= $dt->format('Y') - 1 ?></h2>
        <div style="height:340px"><canvas id="yoyChart"></canvas></div>
    </div>

    <!-- ═══ Product Table ═══ -->
    <div class="table-card animate-in">
        <h2>Top Dropship Products — 30-Day Summary ending <?= $dt->format('M j, Y') ?><?= $categoryParam ? ' — ' . htmlspecialchars($categoryParam) : '' ?></h2>
        <div style="overflow-x:auto">
        <table class="product-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Avg Price</th>
                    <th>Trend</th>
                    <th>Avg Google Score</th>
                    <th>Avg Amazon Rank Δ</th>
                    <th>Avg Composite</th>
                    <th>30-Day Sparkline</th>
                    <th>YoY Change</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr class="animate-in">
                    <td>
                        <span class="rank-badge <?= $p['rank'] <= 3 ? 'rank-' . $p['rank'] : 'rank-other' ?>">
                            <?= $p['rank'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="product-name"><a href="https://www.amazon.com/s?k=<?= urlencode($p['name']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($p['name']) ?></a></div>
                        <div class="product-cat"><?= htmlspecialchars($p['category']) ?></div>
                    </td>
                    <td style="font-weight:600">$<?= number_format($p['avg_price'], 2) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['trend_direction'] ?>">
                            <?= $p['trend_direction'] === 'up' ? '&#9650; Rising' : '&#9660; Falling' ?>
                        </span>
                    </td>
                    <td>
                        <div class="score-bar-wrap">
                            <div class="score-bar" style="width:<?= $p['google_trend'] ?>%"></div>
                        </div>
                        <?= $p['google_trend'] ?>
                    </td>
                    <td style="color:<?= $p['amazon_rank_change'] >= 0 ? 'var(--green)' : 'var(--red)' ?>; font-weight:600">
                        <?= $p['amazon_rank_change'] >= 0 ? '+' : '' ?><?= $p['amazon_rank_change'] ?>%
                    </td>
                    <td style="font-weight:700"><?= $p['composite_score'] ?></td>
                    <td class="sparkline-cell">
                        <canvas class="sparkline-canvas" width="110" height="32"
                                data-values='<?= json_encode($p['sparkline_30d']) ?>'
                                data-direction="<?= $p['trend_direction'] ?>"></canvas>
                    </td>
                    <td>
                        <span class="yoy-change <?= $p['yoy_change_pct'] >= 0 ? 'up' : 'down' ?>">
                            <?= $p['yoy_change_pct'] >= 0 ? '&#9650;' : '&#9660;' ?>
                            <?= abs($p['yoy_change_pct']) ?>%
                        </span>
                        <div style="font-size:.7rem;color:var(--muted)">
                            <?= $p['yoy_previous'] ?> &rarr; <?= $p['yoy_current'] ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ═══ YoY Snapshot Cards ═══ -->
    <div class="yoy-section animate-in">
        <h2>Year-over-Year Snapshot (30-Day Avg) — <?= $dt->format('M j') ?></h2>
        <div class="yoy-grid">
        <?php foreach ($products as $p): ?>
            <div class="yoy-card">
                <div class="yoy-icon <?= $p['yoy_change_pct'] >= 0 ? 'up' : 'down' ?>">
                    <?= $p['yoy_change_pct'] >= 0 ? '&#9650;' : '&#9660;' ?>
                </div>
                <div class="yoy-info">
                    <div class="yoy-name"><a href="https://www.amazon.com/s?k=<?= urlencode($p['name']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($p['name']) ?></a></div>
                    <div class="yoy-detail">
                        <?= htmlspecialchars($p['category']) ?> &middot;
                        <?= $dt->format('Y') - 1 ?>: <?= $p['yoy_previous'] ?> &rarr;
                        <?= $dt->format('Y') ?>: <?= $p['yoy_current'] ?>
                    </div>
                </div>
                <div class="yoy-pct <?= $p['yoy_change_pct'] >= 0 ? 'up' : 'down' ?>">
                    <?= $p['yoy_change_pct'] >= 0 ? '+' : '' ?><?= $p['yoy_change_pct'] ?>%
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ═══ Footer ═══ -->
    <footer class="footer">
        DropShip Trends Dashboard &middot; Data from Google Trends &amp; Amazon Movers &amp; Shakers &middot;
        Generated <?= date('M j, Y H:i') ?> UTC
    </footer>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<!-- Pass PHP data to JS -->
<script>
    window.__products    = <?= $productsJson ?>;
    window.__categories  = <?= $categoriesJson ?>;
    window.__dailyTrends = <?= $dailyTrendsJson ?>;
</script>
<script src="/js/dashboard.js"></script>

</body>
</html>
