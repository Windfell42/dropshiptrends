/* ─── Dashboard Charts ─── */

document.addEventListener('DOMContentLoaded', () => {
    const COLORS = [
        '#6c5ce7', '#74b9ff', '#00b894', '#fdcb6e', '#e17055',
        '#a29bfe', '#55efc4', '#ff7675', '#fab1a0', '#81ecec'
    ];

    const GRID_COLOR = 'rgba(46,51,69,.6)';
    const LABEL_COLOR = '#8b8fa3';

    // ─── 30-Day Trend Lines (top 5) ───
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx && window.__dailyTrends) {
        const { labels, datasets } = window.__dailyTrends;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels,
                datasets: datasets.map((ds, i) => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: COLORS[i],
                    backgroundColor: COLORS[i] + '18',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: LABEL_COLOR, boxWidth: 12, padding: 15, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#222632',
                        titleColor: '#e4e6ed',
                        bodyColor: '#e4e6ed',
                        borderColor: '#2e3345',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { color: GRID_COLOR },
                        ticks: { color: LABEL_COLOR, font: { size: 10 }, maxTicksLimit: 10 }
                    },
                    y: {
                        grid: { color: GRID_COLOR },
                        ticks: { color: LABEL_COLOR, font: { size: 10 } },
                        beginAtZero: false,
                        min: 20,
                        max: 100,
                    }
                }
            }
        });
    }

    // ─── Category Breakdown Doughnut ───
    const catCtx = document.getElementById('categoryChart');
    if (catCtx && window.__categories) {
        const cats = window.__categories;
        const labels = Object.keys(cats);
        const data = labels.map(k => cats[k].avg_score);

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: COLORS.slice(0, labels.length),
                    borderColor: '#222632',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: LABEL_COLOR, boxWidth: 10, padding: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#222632',
                        titleColor: '#e4e6ed',
                        bodyColor: '#e4e6ed',
                        borderColor: '#2e3345',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed.toFixed(1)} avg score`
                        }
                    }
                }
            }
        });
    }

    // ─── YoY Comparison Bar Chart ───
    const yoyCtx = document.getElementById('yoyChart');
    if (yoyCtx && window.__products) {
        const top10 = window.__products.slice(0, 10);
        const labels = top10.map(p => p.name.length > 18 ? p.name.substring(0, 16) + '…' : p.name);

        new Chart(yoyCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'This Year',
                        data: top10.map(p => p.yoy_current),
                        backgroundColor: '#6c5ce7cc',
                        borderRadius: 4,
                        barPercentage: 0.7,
                    },
                    {
                        label: 'Last Year',
                        data: top10.map(p => p.yoy_previous),
                        backgroundColor: '#636e7244',
                        borderColor: '#636e72',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.7,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: LABEL_COLOR, boxWidth: 12, padding: 15, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#222632',
                        titleColor: '#e4e6ed',
                        bodyColor: '#e4e6ed',
                        borderColor: '#2e3345',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { color: GRID_COLOR },
                        ticks: { color: LABEL_COLOR, font: { size: 10 } },
                        beginAtZero: true,
                        max: 100,
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: LABEL_COLOR, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // ─── Inline Sparklines ───
    document.querySelectorAll('.sparkline-canvas').forEach(canvas => {
        const data = JSON.parse(canvas.dataset.values);
        const direction = canvas.dataset.direction;
        const color = direction === 'up' ? '#00b894' : '#ff6b6b';

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data,
                    borderColor: color,
                    backgroundColor: color + '20',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 1.5,
                    pointRadius: 0,
                }]
            },
            options: {
                responsive: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false },
                    y: { display: false, min: Math.min(...data) - 5 }
                },
                animation: { duration: 600 }
            }
        });
    });
});
