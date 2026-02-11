<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DropShip Trends — Top 20 Products</title>
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
            <div class="subtitle">Top 20 products — Google Trends + Amazon Movers &amp; Shakers</div>
        </div>
        <nav class="date-nav">
            <a href="?date=<?= htmlspecialchars($prevDate) ?>">&larr; Prev</a>
            <span class="current-date"><?= $dt->format('M j, Y') ?></span>
            <?php if ($selectedDate < $today): ?>
                <a href="?date=<?= htmlspecialchars($nextDate) ?>">Next &rarr;</a>
            <?php else: ?>
                <span style="color:var(--muted);font-size:.85rem">Today</span>
            <?php endif; ?>
            <?php if ($selectedDate !== $today): ?>
                <a href="?date=<?= $today ?>" style="background:var(--accent);color:#fff">Today</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- ═══ Stat Cards ═══ -->
    <?php
        $avgScore   = round(array_sum(array_column($products, 'composite_score')) / count($products), 1);
        $upCount    = count(array_filter($products, fn($p) => $p['trend_direction'] === 'up'));
        $downCount  = count($products) - $upCount;
        $avgYoy     = round(array_sum(array_column($products, 'yoy_change_pct')) / count($products), 1);
        $topProduct = $products[0];
    ?>
    <div class="stats-row">
        <div class="stat-card animate-in">
            <div class="label">Avg Composite Score</div>
            <div class="value"><?= $avgScore ?></div>
            <div class="change <?= $avgYoy >= 0 ? 'up' : 'down' ?>">
                <?= $avgYoy >= 0 ? '&#9650;' : '&#9660;' ?> <?= abs($avgYoy) ?>% vs last year
            </div>
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
            <div class="change" style="color:var(--muted)">across 20 products</div>
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
        <h2>Year-over-Year Comparison — <?= $dt->format('M j') ?>, <?= $dt->format('Y') ?> vs <?= $dt->format('Y') - 1 ?></h2>
        <div style="height:340px"><canvas id="yoyChart"></canvas></div>
    </div>

    <!-- ═══ Product Table ═══ -->
    <div class="table-card animate-in">
        <h2>Top 20 Dropship Products — <?= $dt->format('M j, Y') ?></h2>
        <div style="overflow-x:auto">
        <table class="product-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Trend</th>
                    <th>Google Score</th>
                    <th>Amazon Rank Δ</th>
                    <th>Composite</th>
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
                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-cat"><?= htmlspecialchars($p['category']) ?></div>
                    </td>
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
        <h2>Year-over-Year Snapshot — <?= $dt->format('M j') ?></h2>
        <div class="yoy-grid">
        <?php foreach ($products as $p): ?>
            <div class="yoy-card">
                <div class="yoy-icon <?= $p['yoy_change_pct'] >= 0 ? 'up' : 'down' ?>">
                    <?= $p['yoy_change_pct'] >= 0 ? '&#9650;' : '&#9660;' ?>
                </div>
                <div class="yoy-info">
                    <div class="yoy-name"><?= htmlspecialchars($p['name']) ?></div>
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
